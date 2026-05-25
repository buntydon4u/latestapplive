<?php
/**
 * JWT Authentication Middleware
 */

class AuthMiddleware {
    public static function verify() {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';
        
        if (empty($auth)) {
            Response::error('UNAUTHORIZED', 'No authorization token provided', 401);
        }
        
        $parts = explode(' ', $auth);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            Response::error('UNAUTHORIZED', 'Invalid authorization header format', 401);
        }
        
        $token = $parts[1];
        $payload = JWT::decode($token);
        
        if ($payload === null) {
            Response::error('UNAUTHORIZED', 'Invalid or expired token', 401);
        }
        
        return $payload;
    }
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    header('Access-Control-Allow-Origin: ' . CORS_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

header('Access-Control-Allow-Origin: ' . CORS_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
