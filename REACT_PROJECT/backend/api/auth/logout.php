<?php
/**
 * Logout API Endpoint
 * POST /api/auth/logout
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('METHOD_NOT_ALLOWED', 'Only POST method is allowed', 405);
}

// Verify token (just to ensure user is authenticated)
$payload = AuthMiddleware::verify();

// JWT logout is client-side (token deletion)
// Server doesn't maintain token blacklist by default
// Optional: Add token to blacklist in cache/database for enhanced security

Response::success(null, 'Logged out successfully', 200);
