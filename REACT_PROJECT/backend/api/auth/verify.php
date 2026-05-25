<?php
/**
 * Verify Token API Endpoint
 * GET /api/auth/verify
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('METHOD_NOT_ALLOWED', 'Only GET method is allowed', 405);
}

$payload = AuthMiddleware::verify();

Response::success([
    'id' => $payload['user_id'],
    'username' => $payload['username'],
    'ledger_name' => $payload['ledger_name']
], 'Token is valid', 200);
