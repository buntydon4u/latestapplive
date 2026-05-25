<?php
/**
 * Create Entry API Endpoint
 * POST /api/entries
 * 
 * Converts Entry-page.php form submission to API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('METHOD_NOT_ALLOWED', 'Only POST method is allowed', 405);
}

$payload = AuthMiddleware::verify();
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['ledger_id', 'date', 'amount', 'description'];
$errors = [];

foreach ($required as $field) {
    if (empty($input[$field])) {
        $errors[$field] = "$field is required";
    }
}

if (!empty($errors)) {
    Response::error('VALIDATION_ERROR', 'Validation failed', 400, $errors);
}

$conn = getDBConnection();

// Prepare entry data
$ledger_id = (int)$input['ledger_id'];
$date = $input['date'];
$amount = (float)$input['amount'];
$description = trim($input['description']);
$created_by = $payload['user_id'];
$created_at = date('Y-m-d H:i:s');

// Insert entry with prepared statement
$stmt = $conn->prepare(
    "INSERT INTO tbl_entries (ledger_id, date, amount, description, created_by, created_at) 
     VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    Response::error('DATABASE_ERROR', 'Preparation failed', 500);
}

$stmt->bind_param('isdsss', $ledger_id, $date, $amount, $description, $created_by, $created_at);

if (!$stmt->execute()) {
    Response::error('DATABASE_ERROR', 'Failed to create entry', 500);
}

$entry_id = $conn->insert_id;
$stmt->close();

Response::success([
    'id' => $entry_id,
    'ledger_id' => $ledger_id,
    'date' => $date,
    'amount' => $amount,
    'description' => $description,
    'created_at' => $created_at
], 'Entry created successfully', 201);
