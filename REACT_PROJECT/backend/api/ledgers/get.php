<?php
/**
 * Get Ledger Details API Endpoint
 * GET /api/ledgers/:id
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('METHOD_NOT_ALLOWED', 'Only GET method is allowed', 405);
}

$payload = AuthMiddleware::verify();

$ledger_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ledger_id === 0) {
    Response::error('VALIDATION_ERROR', 'Ledger ID is required', 400);
}

$conn = getDBConnection();

$stmt = $conn->prepare(
    "SELECT id, ledger_name, parent_id, updated_by, status, created_at 
     FROM tbl_ledger 
     WHERE id = ?"
);

if (!$stmt) {
    Response::error('DATABASE_ERROR', 'Preparation failed', 500);
}

$stmt->bind_param('i', $ledger_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('NOT_FOUND', 'Ledger not found', 404);
}

$ledger = $result->fetch_assoc();
$stmt->close();

Response::success([
    'id' => (int)$ledger['id'],
    'ledger_name' => $ledger['ledger_name'],
    'parent_id' => (int)$ledger['parent_id'],
    'updated_by' => $ledger['updated_by'],
    'status' => $ledger['status'],
    'created_at' => $ledger['created_at']
], 'Ledger retrieved successfully', 200);
