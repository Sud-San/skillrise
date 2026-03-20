<?php
/**
 * ==========================================
 * VALIDATION FUNCTIONS REFERENCE GUIDE
 * ==========================================
 * Complete list of all available validation functions
 * This file serves as documentation and reference
 */

/**
 * ==========================================
 * CLIENT-SIDE VALIDATION (JavaScript)
 * ==========================================
 * Found in: validation.js
 */

// Initialize validation system
// initializeValidation()
// Auto-called on DOM ready
// Sets up all validation listeners

// Validate single field
// validateField(field)
// @param field - DOM element
// @return boolean - true if valid

// Check for duplicate via AJAX
// checkDuplicate(field)
// @param field - DOM element with data-duplicate-check attribute
// Calls ajax-validation.php and updates field status

// Validate entire form
// validateAllFields(form)
// @param form - Form element
// @return boolean - true if all fields valid

// Show invalid field
// showInvalidField(field, message)
// @param field - DOM element
// @param message - Error message to display
// Adds red border and error text

// Show valid field
// showValidField(field)
// @param field - DOM element
// Adds green border

// Clear all validation
// clearValidation(form)
// @param form - Form element
// Removes all validation styling and messages

// Show error notification
// showErrorNotification(message)
// @param message - Message to display
// Uses SweetAlert or alert()

// Show success notification
// showSuccessNotification(message)
// @param message - Message to display

/**
 * ==========================================
 * SERVER-SIDE VALIDATION (PHP)
 * ==========================================
 * Found in: validation-helper.php
 * Usage: ValidationHelper::methodName()
 */

/**
 * Email Validation
 * 
 * ValidationHelper::validateEmail($email)
 * @param string $email
 * @return boolean
 * 
 * Example:
 *   if (ValidationHelper::validateEmail($email)) {
 *       // Email is valid
 *   }
 */

/**
 * Phone Validation
 * 
 * ValidationHelper::validatePhone($phone)
 * @param string $phone - Phone number
 * @return boolean
 * 
 * Checks: at least 10 digits
 * Accepts: spaces, hyphens, parentheses, plus sign
 * 
 * Example:
 *   if (ValidationHelper::validatePhone('+1 (555) 123-4567')) {
 *       // Valid
 *   }
 */

/**
 * URL Validation
 * 
 * ValidationHelper::validateUrl($url)
 * @param string $url
 * @return boolean
 * 
 * Example:
 *   if (ValidationHelper::validateUrl('https://example.com')) {
 *       // Valid URL
 *   }
 */

/**
 * Name Validation
 * 
 * ValidationHelper::validateName($name)
 * @param string $name
 * @return boolean
 * 
 * Checks:
 *   - Length: 2-255 characters
 *   - Pattern: letters, numbers, spaces, -&.,'()
 * 
 * Example:
 *   if (ValidationHelper::validateName('John Doe')) {
 *       // Valid name
 *   }
 */

/**
 * Slug Validation
 * 
 * ValidationHelper::validateSlug($slug)
 * @param string $slug
 * @return boolean
 * 
 * Checks:
 *   - Lowercase letters, numbers
 *   - Hyphens and underscores only
 *   - Minimum 2 characters
 * 
 * Example:
 *   if (ValidationHelper::validateSlug('my-slug-name')) {
 *       // Valid slug
 *   }
 */

/**
 * Code Validation
 * 
 * ValidationHelper::validateCode($code)
 * @param string $code
 * @return boolean
 * 
 * Checks:
 *   - Uppercase letters, numbers
 *   - Underscores only
 *   - Minimum 2 characters
 * 
 * Example:
 *   if (ValidationHelper::validateCode('MY_CODE')) {
 *       // Valid code
 *   }
 */

/**
 * Number Validation
 * 
 * ValidationHelper::validateNumber($number)
 * @param mixed $number
 * @return boolean
 * 
 * Checks: numeric value >= 0
 * 
 * Example:
 *   if (ValidationHelper::validateNumber('123')) {
 *       // Valid number
 *   }
 */

/**
 * Required Field Validation
 * 
 * ValidationHelper::validateRequired($value)
 * @param string $value
 * @return boolean - true if not empty
 * 
 * Example:
 *   if (!ValidationHelper::validateRequired($name)) {
 *       $error = "Name is required";
 *   }
 */

/**
 * Length Validation
 * 
 * ValidationHelper::validateLength($value, $min = 2, $max = 255)
 * @param string $value
 * @param integer $min - Minimum length (default: 2)
 * @param integer $max - Maximum length (default: 255)
 * @return boolean
 * 
 * Example:
 *   if (ValidationHelper::validateLength($description, 10, 5000)) {
 *       // Valid length
 *   }
 */

/**
 * Duplicate Check
 * 
 * ValidationHelper::checkDuplicate($conn, $table, $field, $value, $excludeId = null, $excludeField = 'id')
 * @param mysqli $conn - Database connection
 * @param string $table - Table name
 * @param string $field - Field to check
 * @param string $value - Value to look for
 * @param integer $excludeId - ID to exclude (for edits)
 * @param string $excludeField - ID field name (default: 'id')
 * @return boolean - true if duplicate found
 * 
 * Examples:
 *   // Check if category name exists
 *   if (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $name)) {
 *       echo "Category already exists";
 *   }
 *   
 *   // When editing, exclude current record
 *   if (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $name, $cat_id, 'category_id')) {
 *       echo "Name already used by another category";
 *   }
 */

/**
 * File Upload Validation
 * 
 * ValidationHelper::validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880)
 * @param array $file - $_FILES array element
 * @param array $allowedTypes - Allowed file extensions
 * @param integer $maxSize - Maximum file size in bytes
 * @return array - ['valid' => boolean, 'error' => 'error message']
 * 
 * Example:
 *   $validation = ValidationHelper::validateFileUpload(
 *       $_FILES['logo'],
 *       ['jpg', 'jpeg', 'png'],
 *       5242880  // 5MB
 *   );
 *   
 *   if (!$validation['valid']) {
 *       echo $validation['error'];
 *   }
 */

/**
 * Sanitize Input
 * 
 * ValidationHelper::sanitize($input, $type = 'string')
 * @param string $input - Input to sanitize
 * @param string $type - Type: 'string', 'email', 'url', 'number'
 * @return string - Sanitized input
 * 
 * Types:
 *   - 'string': HTML special characters escaped
 *   - 'email': Email filter applied
 *   - 'url': URL filter applied
 *   - 'number': Numbers only
 * 
 * Example:
 *   $name = ValidationHelper::sanitize($name, 'string');
 *   $email = ValidationHelper::sanitize($email, 'email');
 */

/**
 * Get POST Value (with sanitization)
 * 
 * ValidationHelper::getPost($key, $type = 'string', $required = false)
 * @param string $key - POST variable name
 * @param string $type - Type: 'string', 'email', 'url', 'number'
 * @param boolean $required - If true, returns null if missing
 * @return string|null - Sanitized value or null
 * 
 * Example:
 *   $name = ValidationHelper::getPost('name', 'string');
 *   $email = ValidationHelper::getPost('email', 'email');
 *   $quantity = ValidationHelper::getPost('qty', 'number', true);
 */

/**
 * Get GET Value (with sanitization)
 * 
 * ValidationHelper::getGet($key, $type = 'string', $required = false)
 * @param string $key - GET variable name
 * @param string $type - Type: 'string', 'email', 'url', 'number'
 * @param boolean $required - If true, returns null if missing
 * @return string|null - Sanitized value or null
 * 
 * Example:
 *   $id = ValidationHelper::getGet('id', 'number');
 *   $sort = ValidationHelper::getGet('sort', 'string');
 */

/**
 * ==========================================
 * AJAX ENDPOINTS
 * ==========================================
 * Found in: ajax-validation.php
 * 
 * All endpoints return JSON:
 *   {
 *       "exists": boolean,
 *       "valid": boolean,
 *       "message": "Error message if any"
 *   }
 */

/**
 * Check Course Name
 * 
 * URL: ajax-validation.php?action=check_course
 * Parameters:
 *   - name: Course name to check
 *   - course_id: (optional) Current course ID (exclude from check)
 * 
 * Example JavaScript:
 *   data-duplicate-check="ajax-validation.php?action=check_course"
 */

/**
 * Check Category
 * 
 * URL: ajax-validation.php?action=check_category
 * Parameters:
 *   - type: 'name' or 'slug'
 *   - value: Value to check
 *   - category_id: (optional) Current category ID
 * 
 * Example:
 *   data-duplicate-check="ajax-validation.php?action=check_category&type=name"
 */

/**
 * Check City Name
 * 
 * URL: ajax-validation.php?action=check_city
 * Parameters:
 *   - name: City name to check
 *   - city_id: (optional) Current city ID
 */

/**
 * Check State Name
 * 
 * URL: ajax-validation.php?action=check_state
 * Parameters:
 *   - name: State name to check
 *   - state_id: (optional) Current state ID
 */

/**
 * Check Package Name
 * 
 * URL: ajax-validation.php?action=check_package
 * Parameters:
 *   - name: Package name to check
 *   - package_id: (optional) Current package ID
 */

/**
 * Check College Fields
 * 
 * URL: ajax-validation.php?action=check_college
 * Parameters:
 *   - field: Field name (clg_name, clg_email, clg_contact, clg_website, clg_slug)
 *   - value: Value to check
 *   - college_id: (optional) Current college ID
 * 
 * Example:
 *   data-duplicate-check="ajax-validation.php?action=check_college&field=clg_email"
 */

/**
 * Validate Field Format
 * 
 * URL: ajax-validation.php?action=validate_field
 * Parameters:
 *   - type: Field type (email, phone, url, slug, etc.)
 *   - value: Value to validate
 * 
 * Returns validation result (not duplicate check)
 * 
 * Example:
 *   data-duplicate-check="ajax-validation.php?action=validate_field&type=email"
 */

/**
 * ==========================================
 * DATA ATTRIBUTES
 * ==========================================
 * Used on HTML form inputs
 */

// data-validate="[type]"
// Validation type: name, email, phone, url, slug, code, number, text
// Example:
//   <input data-validate="email">

// data-duplicate-check="[url]"
// AJAX endpoint URL for duplicate checking
// Example:
//   <input data-duplicate-check="ajax-validation.php?action=check_category&type=name">

// data-label="[label]"
// Label for error messages
// Example:
//   <input data-label="Category Name">

// required
// Standard HTML attribute for required fields
// Example:
//   <input required>

/**
 * ==========================================
 * VALIDATION CONFIG
 * ==========================================
 * Found in: validation-config.php
 * 
 * VALIDATION_MESSAGES - Error message templates
 * VALIDATION_FIELDS - Field type definitions
 * UPLOAD_CONFIG - File upload settings
 * DUPLICATE_CHECK_TABLES - Database table mapping
 * EMAIL_REGEX - Email pattern
 * PASSWORD_REGEX - Strong password pattern
 * PHONE_PATTERNS - Phone patterns by country
 * APP_TIMEZONE - Application timezone
 * VALIDATION_CONFIG - Feature flags
 */

/**
 * ==========================================
 * COMMON USAGE PATTERNS
 * ==========================================
 */

// Pattern 1: Simple duplicate check
/*
if (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $name)) {
    echo "Category name already exists";
}
*/

// Pattern 2: Multiple validations
/*
$errors = [];

if (!ValidationHelper::validateRequired($name)) {
    $errors[] = "Name is required";
} elseif (!ValidationHelper::validateLength($name, 2, 255)) {
    $errors[] = "Name must be 2-255 characters";
} elseif (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $name)) {
    $errors[] = "Name already exists";
}

if (!empty($errors)) {
    // Show errors
}
*/

// Pattern 3: Sanitize and validate POST data
/*
$name = ValidationHelper::getPost('name', 'string');
$email = ValidationHelper::getPost('email', 'email');
$phone = ValidationHelper::getPost('phone', 'string');

if (!ValidationHelper::validateRequired($name)) {
    // Handle error
}

if (!ValidationHelper::validateEmail($email)) {
    // Handle error
}

if (!ValidationHelper::validatePhone($phone)) {
    // Handle error
}
*/

// Pattern 4: File upload validation
/*
if (!empty($_FILES['avatar']['name'])) {
    $result = ValidationHelper::validateFileUpload(
        $_FILES['avatar'],
        ['jpg', 'jpeg', 'png'],
        5242880 // 5MB
    );
    
    if (!$result['valid']) {
        echo $result['error'];
    }
}
*/

/**
 * ==========================================
 * RETURN VALUES
 * ==========================================
 */

// Boolean validation methods return:
// true  - Validation passed
// false - Validation failed

// Array validation methods return:
// [
//     'valid' => boolean,
//     'error' => 'Error message or empty string'
// ]

// AJAX endpoints return:
// {
//     "exists": boolean,      // true if duplicate found
//     "valid": boolean,       // true if format valid
//     "message": "string"     // Error message if any
// }

/**
 * ==========================================
 * ERROR HANDLING
 * ==========================================
 */

// Check for database errors
/*
$result = ValidationHelper::checkDuplicate($conn, 'table', 'field', $value);
if ($conn->connect_error) {
    echo "Database error: " . $conn->connect_error;
}
*/

// Graceful error handling
/*
try {
    if (ValidationHelper::validateRequired($value)) {
        // Process
    }
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
*/

/**
 * ==========================================
 * NOTES & BEST PRACTICES
 * ==========================================
 */

// 1. Always validate on server-side (client validation can be bypassed)
// 2. Use proper error handling for all validation
// 3. Sanitize inputs before database operations
// 4. Use prepared statements when possible
// 5. Log validation failures for security auditing
// 6. Provide user-friendly error messages
// 7. Test all validation scenarios
// 8. Keep validation rules consistent across app
// 9. Document custom validation rules
// 10. Regularly update validation patterns

?>
