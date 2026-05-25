<?php
/**
 * API Response Helper
 * Consistent response formatting
 */

class Response {
    public static function success($data = null, $message = 'Success', $status = 200) {
        self::setHeaders($status);
        echo json_encode([
            'success' => true,
            'status' => $status,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
    
    public static function error($error, $message = 'An error occurred', $status = 400, $details = null) {
        self::setHeaders($status);
        $response = [
            'success' => false,
            'status' => $status,
            'error' => $error,
            'message' => $message
        ];
        
        if ($details) {
            $response['details'] = $details;
        }
        
        echo json_encode($response);
        exit;
    }
    
    private static function setHeaders($status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: ' . CORS_ORIGIN);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
}

/**
 * Validation Helper
 */
class Validator {
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                $errors[$field] = "$field is required";
            }
        }
        
        return empty($errors) ? null : $errors;
    }
}
