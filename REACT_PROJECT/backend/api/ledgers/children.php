<?php
/**
 * Get Ledger Children API Endpoint
 * GET /api/ledgers/:id/children
 * 
 * Converts Parent.php logic to API
 * 
 * Old (Parent.php):
 *   - Query with string params
 *   - Render HTML form
 * 
 * New:
 *   - Return JSON with children ledgers
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('METHOD_NOT_ALLOWED', 'Only GET method is allowed', 405);
}

// Verify authentication
$payload = AuthMiddleware::verify();

// Get parent ID from URL
$parent_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($parent_id === 0) {
    Response::error('VALIDATION_ERROR', 'Parent ID is required', 400);
}

$conn = getDBConnection();

// Use prepared statement
$stmt = $conn->prepare(
    "SELECT id, ledger_name, parent_id, status, created_at 
     FROM tbl_ledger 
     WHERE parent_id = ? AND status = 1
     ORDER BY ledger_name ASC"
);

if (!$stmt) {
    Response::error('DATABASE_ERROR', 'Preparation failed', 500);
}

$stmt->bind_param('i', $parent_id);
$stmt->execute();
$result = $stmt->get_result();

$ledgers = [];
while ($row = $result->fetch_assoc()) {
    $ledgers[] = [
        'id' => (int)$row['id'],
        'ledger_name' => $row['ledger_name'],
        'parent_id' => (int)$row['parent_id'],
        'status' => $row['status'],
        'created_at' => $row['created_at']
    ];
}

$stmt->close();

Response::success($ledgers, 'Ledgers retrieved successfully', 200);
