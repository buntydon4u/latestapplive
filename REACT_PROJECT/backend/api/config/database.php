<?php
/**
 * Database Configuration
 * Centralized database connection management
 */

// Load environment variables
$env = parse_ini_file(__DIR__ . '/../../.env', true);

// Database Configuration
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_USER', $env['DB_USER'] ?? '555prouser');
define('DB_PASSWORD', $env['DB_PASSWORD'] ?? 'e2OFVjrRK77ljyfs4z@R');
define('DB_NAME', $env['DB_NAME'] ?? '555prodb');

// JWT Configuration
define('JWT_SECRET', $env['JWT_SECRET'] ?? 'your-secret-key-change-in-production');
define('JWT_EXPIRY', $env['JWT_EXPIRY'] ?? 86400); // 24 hours

// CORS Configuration
define('CORS_ORIGIN', $env['CORS_ORIGIN'] ?? 'http://localhost:5173');

// Database Connection
function getDBConnection() {
    static $conn;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'status' => 500,
                'error' => 'DATABASE_ERROR',
                'message' => 'Database connection failed'
            ]));
        }
        
        $conn->set_charset('utf8mb4');
    }
    
    return $conn;
}

// Close connection (call at script end)
function closeDBConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}
