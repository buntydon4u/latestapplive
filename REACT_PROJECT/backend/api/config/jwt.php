<?php
/**
 * JWT Token Management
 * Create and verify JWT tokens for authentication
 */

class JWT {
    private static $secret;
    private static $expiry;
    
    public static function init($secret, $expiry) {
        self::$secret = $secret;
        self::$expiry = $expiry;
    }
    
    /**
     * Encode JWT token
     */
    public static function encode($payload) {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload['exp'] = time() + self::$expiry;
        $payload['iat'] = time();
        
        $payload_json = json_encode($payload);
        
        $header_encoded = self::base64url_encode($header);
        $payload_encoded = self::base64url_encode($payload_json);
        $signature = self::sign($header_encoded . '.' . $payload_encoded);
        
        return $header_encoded . '.' . $payload_encoded . '.' . $signature;
    }
    
    /**
     * Decode and verify JWT token
     */
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        $header = self::base64url_decode($parts[0]);
        $payload = self::base64url_decode($parts[1]);
        $signature = $parts[2];
        
        $expected_signature = self::sign($parts[0] . '.' . $parts[1]);
        
        if (!hash_equals($signature, $expected_signature)) {
            return null;
        }
        
        $decoded = json_decode($payload, true);
        
        if ($decoded['exp'] < time()) {
            return null;
        }
        
        return $decoded;
    }
    
    private static function sign($data) {
        return self::base64url_encode(hash_hmac('sha256', $data, self::$secret, true));
    }
    
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}

// Initialize JWT
JWT::init(JWT_SECRET, JWT_EXPIRY);
