<?php
/**
 * Get Entries List API Endpoint
 * GET /api/entries
 * 
 * Converts View-page.php table display to API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('METHOD_NOT_ALLOWED', 'Only GET method is allowed', 405);
}

$payload = AuthMiddleware::verify();

// Get filters from query parameters
$ledger_id = isset($_GET['ledger_id']) ? (int)$_GET['ledger_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = ($page - 1) * $limit;

if (!$ledger_id) {
    Response::error('VALIDATION_ERROR', 'Ledger ID is required', 400);
}

$conn = getDBConnection();

// Build query with filters
$where = "WHERE ledger_id = ?";
$params = [$ledger_id];
$types = 'i';

if ($start_date) {
    $where .= " AND date >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if ($end_date) {
    $where .= " AND date <= ?";
    $params[] = $end_date;
    $types .= 's';
}

// Get total count
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_entries $where");
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countResult = $countStmt->get_result();
$total = $countResult->fetch_assoc()['total'];
$countStmt->close();

// Get paginated entries
$query = "SELECT id, ledger_id, date, amount, description, created_by, created_at 
          FROM tbl_entries 
          $where 
          ORDER BY date DESC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!$stmt) {
    Response::error('DATABASE_ERROR', 'Preparation failed', 500);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = [
        'id' => (int)$row['id'],
        'ledger_id' => (int)$row['ledger_id'],
        'date' => $row['date'],
        'amount' => (float)$row['amount'],
        'description' => $row['description'],
        'created_by' => (int)$row['created_by'],
        'created_at' => $row['created_at']
    ];
}

$stmt->close();

Response::success([
    'entries' => $entries,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'pages' => ceil($total / $limit)
    ]
], 'Entries retrieved successfully', 200);
