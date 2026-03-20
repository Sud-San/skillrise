<?php
/**
 * ==========================================
 * UNIFIED AJAX VALIDATION ENDPOINTS
 * ==========================================
 * Handles duplicate checking for all admin forms
 */

include "connection.php";
include "validation-helper.php";

header('Content-Type: application/json; charset=utf-8');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'exists' => false]);
    exit;
}

$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$response = ['exists' => false, 'valid' => true, 'message' => ''];

try {
    switch ($action) {
        // =====================
        // COURSE VALIDATION
        // =====================
        case 'check_course':
            $name = isset($_GET['name']) ? trim($_GET['name']) : '';
            $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
            
            if (empty($name)) {
                $response['exists'] = false;
                break;
            }
            
            if (ValidationHelper::checkDuplicate($conn, 'course_tbl', 'course_name', $name, $course_id, 'course_id')) {
                $response['exists'] = true;
                $response['message'] = 'Course name already exists';
            }
            break;

        // =====================
        // CATEGORY VALIDATION
        // =====================
        case 'check_category':
            $type = isset($_GET['type']) ? $_GET['type'] : 'name';
            $value = isset($_GET['value']) ? trim($_GET['value']) : '';
            $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
            
            if (empty($value)) {
                $response['exists'] = false;
                break;
            }
            
            $field = ($type === 'slug') ? 'category_code' : 'category_name';
            
            if (ValidationHelper::checkDuplicate($conn, 'category_tbl', $field, $value, $category_id, 'category_id')) {
                $response['exists'] = true;
                $response['message'] = ucfirst($type) . ' already exists';
            }
            break;

        // =====================
        // CITY VALIDATION
        // =====================
        case 'check_city':
            $name = isset($_GET['name']) ? trim($_GET['name']) : '';
            $city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;
            
            if (empty($name)) {
                $response['exists'] = false;
                break;
            }
            
            if (ValidationHelper::checkDuplicate($conn, 'city_tbl', 'city_name', $name, $city_id, 'city_id')) {
                $response['exists'] = true;
                $response['message'] = 'City name already exists';
            }
            break;

        // =====================
        // STATE VALIDATION
        // =====================
        case 'check_state':
            $name = isset($_GET['name']) ? trim($_GET['name']) : '';
            $state_id = isset($_GET['state_id']) ? intval($_GET['state_id']) : 0;
            
            if (empty($name)) {
                $response['exists'] = false;
                break;
            }
            
            if (ValidationHelper::checkDuplicate($conn, 'state_tbl', 'state_name', $name, $state_id, 'state_id')) {
                $response['exists'] = true;
                $response['message'] = 'State name already exists';
            }
            break;

        // =====================
        // PACKAGE VALIDATION
        // =====================
        case 'check_package':
            $name = isset($_GET['name']) ? trim($_GET['name']) : '';
            $package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
            
            if (empty($name)) {
                $response['exists'] = false;
                break;
            }
            
            if (ValidationHelper::checkDuplicate($conn, 'package_tbl', 'package_name', $name, $package_id, 'package_id')) {
                $response['exists'] = true;
                $response['message'] = 'Package name already exists';
            }
            break;

        // =====================
        // COLLEGE VALIDATION
        // =====================
        case 'check_college':
            $field = isset($_GET['field']) ? trim($_GET['field']) : 'clg_name';
            $value = isset($_GET['value']) ? trim($_GET['value']) : '';
            $college_id = isset($_GET['college_id']) ? intval($_GET['college_id']) : 0;
            
            if (empty($value)) {
                $response['exists'] = false;
                break;
            }
            
            // Map frontend field names to database fields
            $dbFieldMap = [
                'clg_name' => 'clg_name',
                'clg_email' => 'clg_email',
                'clg_contact' => 'clg_contact',
                'clg_website' => 'clg_website',
                'clg_slug' => 'clg_slug'
            ];
            
            $dbField = isset($dbFieldMap[$field]) ? $dbFieldMap[$field] : 'clg_name';
            
            if (ValidationHelper::checkDuplicate($conn, 'cllg_tbl', $dbField, $value, $college_id, 'clg_id')) {
                $response['exists'] = true;
                $response['message'] = ucfirst($field) . ' already exists';
            }
            break;

        // =====================
        // GENERAL VALIDATION
        // =====================
        case 'validate_field':
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $value = isset($_GET['value']) ? trim($_GET['value']) : '';
            
            if (empty($value)) {
                $response['valid'] = false;
                $response['message'] = 'Field is required';
                break;
            }
            
            switch ($type) {
                case 'email':
                    $response['valid'] = ValidationHelper::validateEmail($value);
                    $response['message'] = !$response['valid'] ? 'Invalid email format' : '';
                    break;
                    
                case 'phone':
                    $response['valid'] = ValidationHelper::validatePhone($value);
                    $response['message'] = !$response['valid'] ? 'Invalid phone number' : '';
                    break;
                    
                case 'url':
                    $response['valid'] = ValidationHelper::validateUrl($value);
                    $response['message'] = !$response['valid'] ? 'Invalid URL format' : '';
                    break;
                    
                case 'slug':
                    $response['valid'] = ValidationHelper::validateSlug($value);
                    $response['message'] = !$response['valid'] ? 'Invalid slug format (lowercase alphanumeric with hyphens/underscores)' : '';
                    break;
                    
                default:
                    $response['valid'] = true;
            }
            break;

        default:
            http_response_code(400);
            $response['error'] = 'Unknown action';
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
    $response['exists'] = false;
}

echo json_encode($response);
exit;

?>
