# Database Migration & Schema Guide

## Current Database Structure

### Existing Tables Analysis

```sql
-- Ledger/User table
CREATE TABLE tbl_ledger (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100),
    password VARCHAR(100),  -- IMPORTANT: Switch to bcrypt hash
    ledger_name VARCHAR(255),
    parent_id INT,
    updated_by INT,
    is_master INT,
    status INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Alternative login table (optional, to be analyzed)
CREATE TABLE tbl_user_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_name VARCHAR(100),
    password VARCHAR(100),
    -- other fields...
);

-- Transaction/Entry table (inferred from Entry-page.php)
-- CREATE TABLE tbl_entries (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     ledger_id INT,
--     date DATE,
--     amount DECIMAL(10, 2),
--     description TEXT,
--     created_by INT,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (ledger_id) REFERENCES tbl_ledger(id)
-- );
```

## Migration Strategy

### Phase 1: Non-Breaking Changes (Weeks 1-2)

1. **Add new columns for JWT** (optional, for backward compatibility):
   ```sql
   ALTER TABLE tbl_ledger ADD COLUMN last_login TIMESTAMP;
   ALTER TABLE tbl_ledger ADD COLUMN token_version INT DEFAULT 0;
   ```

2. **Analyze all existing tables**:
   ```sql
   -- Identify all tables related to the application
   SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
   WHERE TABLE_SCHEMA = '555prodb';
   
   -- Check table structure
   DESCRIBE tbl_ledger;
   DESCRIBE tbl_user_login;
   ```

3. **Create indexes for performance**:
   ```sql
   -- If not already present
   ALTER TABLE tbl_ledger 
   ADD INDEX idx_username (username),
   ADD INDEX idx_parent_id (parent_id),
   ADD INDEX idx_status (status);
   ```

### Phase 2: Data Security Improvements (Week 3)

1. **Hash existing passwords** (one-time migration):
   ```php
   // Migration script (run once)
   $conn = new mysqli('localhost', 'user', 'pass', 'db');
   $result = $conn->query("SELECT id, password FROM tbl_ledger");
   
   while ($row = $result->fetch_assoc()) {
       $hashed = password_hash($row['password'], PASSWORD_BCRYPT);
       $id = $row['id'];
       $conn->query("UPDATE tbl_ledger SET password = '$hashed' WHERE id = $id");
   }
   ```

2. **Update password comparison in API**:
   ```php
   // Old: Direct string comparison
   // WHERE password = ?
   
   // New: Use password_verify()
   if (password_verify($input_password, $db_hashed_password)) {
       // Authenticate
   }
   ```

### Phase 3: Create Entry Tables (if missing)

```sql
-- Create entries table if not exists
CREATE TABLE IF NOT EXISTS tbl_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ledger_id INT NOT NULL,
    date DATE NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    description TEXT,
    entry_type ENUM('DEBIT', 'CREDIT') DEFAULT 'DEBIT',
    status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    created_by INT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ledger_id) REFERENCES tbl_ledger(id),
    FOREIGN KEY (created_by) REFERENCES tbl_ledger(id),
    FOREIGN KEY (approved_by) REFERENCES tbl_ledger(id),
    INDEX idx_ledger_id (ledger_id),
    INDEX idx_date (date),
    INDEX idx_status (status)
);
```

## Data Preservation Strategy

### Backup Before Migration

```bash
# Full database backup
mysqldump -u 555prouser -p 555prodb > backup_before_migration.sql

# Specific table backup
mysqldump -u 555prouser -p 555prodb tbl_ledger > tbl_ledger_backup.sql
```

### Dual-Write Period (Optional)

During transition period:
1. Keep writing to both old PHP code AND new API
2. Gradually shift reads to new API
3. After 2-3 weeks, deprecate old code
4. Keep backups for 1 month

### Validation Queries

After migration, verify data integrity:

```sql
-- Check ledger counts
SELECT COUNT(*) FROM tbl_ledger WHERE status = 1;

-- Check for orphaned entries
SELECT id FROM tbl_entries 
WHERE ledger_id NOT IN (SELECT id FROM tbl_ledger);

-- Verify user hierarchy
SELECT id, parent_id, COUNT(*) as children 
FROM tbl_ledger 
GROUP BY id;

-- Check for null critical fields
SELECT * FROM tbl_ledger WHERE username IS NULL OR ledger_name IS NULL;
```

## Password Migration

### Strategy 1: Lazy Migration (Recommended)

Users get password rehashed on next login:

```php
if (password_verify($input_password, $db_hashed_password)) {
    // Check if password needs upgrade
    if (password_needs_rehash($db_hashed_password, PASSWORD_BCRYPT)) {
        // Rehash and update
        $new_hash = password_hash($input_password, PASSWORD_BCRYPT);
        $conn->query("UPDATE tbl_ledger SET password = ? WHERE id = ?");
    }
    // Authenticate user
}
```

### Strategy 2: Immediate Migration

One-time admin script to hash all passwords:

```php
// Run once via admin interface or command line
$conn = new mysqli('localhost', 'user', 'pass', 'db');
$result = $conn->query("SELECT id, password FROM tbl_ledger WHERE password NOT LIKE '$2%'");

$count = 0;
while ($row = $result->fetch_assoc()) {
    $hashed = password_hash($row['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE tbl_ledger SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $hashed, $row['id']);
    $stmt->execute();
    $count++;
}

echo "Migrated $count passwords\n";
```

## Index Optimization

Analyze current indexes and add missing ones:

```sql
-- View current indexes
SHOW INDEX FROM tbl_ledger;
SHOW INDEX FROM tbl_entries;

-- Add indexes for frequent queries
ALTER TABLE tbl_ledger ADD INDEX idx_parent_id_status (parent_id, status);
ALTER TABLE tbl_entries ADD INDEX idx_ledger_date (ledger_id, date);
ALTER TABLE tbl_entries ADD INDEX idx_date_range (date, status);
```

## Rollback Plan

If issues occur, rollback is simple:

```bash
# Restore from backup
mysql -u 555prouser -p 555prodb < backup_before_migration.sql

# Verify
mysql -u 555prouser -p 555prodb
SELECT COUNT(*) FROM tbl_ledger;
```

## Post-Migration Verification

```php
// Check data consistency
$checks = [
    'total_ledgers' => $conn->query("SELECT COUNT(*) as cnt FROM tbl_ledger")->fetch_assoc()['cnt'],
    'active_ledgers' => $conn->query("SELECT COUNT(*) as cnt FROM tbl_ledger WHERE status = 1")->fetch_assoc()['cnt'],
    'total_entries' => $conn->query("SELECT COUNT(*) as cnt FROM tbl_entries")->fetch_assoc()['cnt'],
    'orphaned_entries' => $conn->query("SELECT COUNT(*) as cnt FROM tbl_entries WHERE ledger_id NOT IN (SELECT id FROM tbl_ledger)")->fetch_assoc()['cnt']
];

echo json_encode($checks, JSON_PRETTY_PRINT);
```

## Timeline

| Phase | Week | Task | Status |
|-------|------|------|--------|
| 1 | Week 1 | Analyze existing schema | [ ] |
| 1 | Week 2 | Add new indexes | [ ] |
| 2 | Week 3 | Hash passwords | [ ] |
| 3 | Week 4 | Create entry tables | [ ] |
| 4 | Week 5 | Test API with data | [ ] |
| 5 | Week 6 | Verify integrity | [ ] |
| 6 | Week 7 | Prepare rollback | [ ] |
| 7 | Week 8 | Go live | [ ] |

## Important Notes

- ✓ No existing data will be lost
- ✓ Database schema changes are additive (no deletions)
- ✓ All old queries remain valid
- ✓ New API accesses same tables
- ✓ Passwords MUST be hashed before production
- ✓ Backups MUST be tested before going live
