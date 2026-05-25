<?php
/**
 * Login API Endpoint
 * POST /api/auth/login
 * 
 * Converts PHP session-based login to JWT authentication
 * 
 * Old (LoginCode.php):
 *   - Direct query with string concatenation (SQL injection risk)
 *   - Set $_SESSION variables
 *   - Redirect user
 * 
 * New (JWT-based):
 *   - Prepared statements
 *   - Return JSON with token
 *   - Client handles token storage
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('METHOD_NOT_ALLOWED', 'Only POST method is allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($input['username']) || empty($input['password'])) {
    Response::error('VALIDATION_ERROR', 'Username and password are required', 400, [
        'username' => empty($input['username']) ? 'Username is required' : null,
        'password' => empty($input['password']) ? 'Password is required' : null
    ]);
}

$username = trim($input['username']);
$password = trim($input['password']);

$conn = getDBConnection();

// Prepare statement - replaces old string concatenation
$stmt = $conn->prepare(
    "SELECT id, ledger_name, username, updated_by 
     FROM tbl_ledger 
     WHERE username = ? AND password = ? AND is_master = 0 AND status = 1"
);

if (!$stmt) {
    Response::error('DATABASE_ERROR', 'Preparation failed', 500);
}

$stmt->bind_param('ss', $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('INVALID_CREDENTIALS', 'Invalid username or password', 401);
}

$user = $result->fetch_assoc();
$stmt->close();

// Create JWT token
$token = JWT::encode([
    'user_id' => $user['id'],
    'username' => $user['username'],
    'ledger_name' => $user['ledger_name'],
    'updated_by' => $user['updated_by']
]);

Response::success([
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'ledger_name' => $user['ledger_name']
    ]
], 'Login successful', 200);
