<?php
/**
 * ==========================================
 * VALIDATION HELPER CLASS
 * ==========================================
 * Provides server-side validation utilities
 */

class ValidationHelper {
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (basic validation)
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+\-\s()]/', '', $phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);
        return strlen($digits) >= 10;
    }
    
    /**
     * Validate URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate name (alphanumeric with spaces and basic symbols)
     */
    public static function validateName($name) {
        return preg_match('/^[a-zA-Z0-9\s\-&.,()\']$/u', $name) && 
               strlen($name) >= 2 && 
               strlen($name) <= 255;
    }
    
    /**
     * Validate slug (lowercase alphanumeric with hyphens/underscores)
     */
    public static function validateSlug($slug) {
        return preg_match('/^[a-z0-9]+(?:[_-][a-z0-9]+)*$/', $slug) && 
               strlen($slug) >= 2;
    }
    
    /**
     * Validate code (uppercase alphanumeric with underscores)
     */
    public static function validateCode($code) {
        return preg_match('/^[A-Z0-9_]*$/', $code) && strlen($code) >= 2;
    }
    
    /**
     * Validate number
     */
    public static function validateNumber($number) {
        return is_numeric($number) && $number >= 0;
    }
    
    /**
     * Validate required field
     */
    public static function validateRequired($value) {
        return !empty(trim($value));
    }
    
    /**
     * Validate string length
     */
    public static function validateLength($value, $min = 2, $max = 255) {
        $length = strlen(trim($value));
        return $length >= $min && $length <= $max;
    }
    
    /**
     * Check if value exists in database (duplicate check)
     */
    public static function checkDuplicate($conn, $table, $field, $value, $excludeId = null, $excludeField = 'id') {
        if (empty($value)) {
            return false;
        }
        
        $field = mysqli_real_escape_string($conn, $field);
        $value = mysqli_real_escape_string($conn, $value);
        
        $sql = "SELECT COUNT(*) as cnt FROM $table WHERE LOWER($field) = LOWER('$value')";
        
        if ($excludeId) {
            $excludeId = intval($excludeId);
            $excludeField = mysqli_real_escape_string($conn, $excludeField);
            $sql .= " AND $excludeField != $excludeId";
        }
        
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['cnt'] > 0;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
        if (empty($file['name'])) {
            return ['valid' => false, 'error' => 'No file uploaded'];
        }
        
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExt, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File size exceeds maximum limit of ' . ($maxSize / 1024 / 1024) . 'MB'];
        }
        
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Invalid file upload'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitize($input, $type = 'string') {
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'number':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'string':
            default:
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Get sanitized POST value
     */
    public static function getPost($key, $type = 'string', $required = false) {
        if (!isset($_POST[$key])) {
            return $required ? null : '';
        }
        
        return self::sanitize($_POST[$key], $type);
    }
    
    /**
     * Get sanitized GET value
     */
    public static function getGet($key, $type = 'string', $required = false) {
        if (!isset($_GET[$key])) {
            return $required ? null : '';
        }
        
        return self::sanitize($_GET[$key], $type);
    }
}

?>
