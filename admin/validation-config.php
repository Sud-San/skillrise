<?php
/**
 * ==========================================
 * VALIDATION CONFIGURATION FILE
 * ==========================================
 * Centralized configuration for validation system
 */

// Validation message templates
define('VALIDATION_MESSAGES', [
    'required' => 'This field is required',
    'name_invalid' => 'Name must be 2-255 characters and contain only letters, numbers, spaces, and basic symbols',
    'name_min_length' => 'Name must be at least 2 characters',
    'name_max_length' => 'Name cannot exceed 255 characters',
    
    'email_invalid' => 'Please enter a valid email address',
    'email_duplicate' => 'This email address already exists',
    
    'phone_invalid' => 'Please enter a valid phone number (at least 10 digits)',
    'phone_duplicate' => 'This phone number already exists',
    
    'url_invalid' => 'Please enter a valid URL',
    'url_duplicate' => 'This URL already exists',
    
    'slug_invalid' => 'Slug must contain lowercase letters, numbers, hyphens, and underscores only',
    'slug_min_length' => 'Slug must be at least 2 characters',
    'slug_duplicate' => 'This slug already exists',
    
    'code_invalid' => 'Code must contain uppercase letters, numbers, and underscores only',
    'code_min_length' => 'Code must be at least 2 characters',
    'code_duplicate' => 'This code already exists',
    
    'text_min_length' => 'Text must be at least 2 characters',
    'text_max_length' => 'Text cannot exceed 5000 characters',
    
    'number_invalid' => 'Please enter a valid number',
    
    'file_invalid_type' => 'Invalid file type. Allowed types: ',
    'file_too_large' => 'File size exceeds maximum limit',
    'file_upload_error' => 'Error uploading file',
    
    'length_invalid' => 'Length must be between {min} and {max} characters',
    'duplicate' => '{field} already exists',
]);

// Validation field types configuration
define('VALIDATION_FIELDS', [
    'name' => [
        'pattern' => '/^[a-zA-Z0-9\s\-&.,()\']$/u',
        'minLength' => 2,
        'maxLength' => 255,
        'allowEmpty' => false,
    ],
    'email' => [
        'pattern' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
        'allowEmpty' => false,
    ],
    'phone' => [
        'pattern' => '/^[0-9+\-\s()]*$/',
        'minLength' => 10,
        'allowEmpty' => false,
    ],
    'url' => [
        'pattern' => '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i',
        'allowEmpty' => false,
    ],
    'slug' => [
        'pattern' => '/^[a-z0-9]+(?:[_-][a-z0-9]+)*$/',
        'minLength' => 2,
        'allowEmpty' => false,
    ],
    'code' => [
        'pattern' => '/^[A-Z0-9_]*$/',
        'minLength' => 2,
        'allowEmpty' => false,
    ],
    'number' => [
        'pattern' => '/^[0-9]*$/',
        'allowEmpty' => false,
    ],
    'text' => [
        'minLength' => 2,
        'maxLength' => 5000,
        'allowEmpty' => false,
    ],
]);

// File upload configuration
define('UPLOAD_CONFIG', [
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
    'max_file_size' => 5242880, // 5MB
    'upload_dir' => 'uploads/',
    'image_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'document_types' => ['pdf'],
]);

// Database tables for duplicate checking
define('DUPLICATE_CHECK_TABLES', [
    'course' => [
        'table' => 'course_tbl',
        'field' => 'course_name',
        'id_field' => 'course_id',
    ],
    'category' => [
        'table' => 'category_tbl',
        'field' => 'category_name',
        'id_field' => 'category_id',
    ],
    'city' => [
        'table' => 'city_tbl',
        'field' => 'city_name',
        'id_field' => 'city_id',
    ],
    'state' => [
        'table' => 'state_tbl',
        'field' => 'state_name',
        'id_field' => 'state_id',
    ],
    'package' => [
        'table' => 'package_tbl',
        'field' => 'package_name',
        'id_field' => 'package_id',
    ],
    'college' => [
        'table' => 'cllg_tbl',
        'field' => 'clg_name',
        'id_field' => 'clg_id',
    ],
]);

// Email regex pattern
define('EMAIL_REGEX', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');

// Password regex pattern (strong password requirement)
define('PASSWORD_REGEX', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/');

// Phone regex patterns by country
define('PHONE_PATTERNS', [
    'US' => '/^(\+?1)?[-.\s]?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}$/',
    'UK' => '/^(\+44)?[-.\s]?[0-9]{4}[-.\s]?[0-9]{6}$/',
    'IN' => '/^(\+91)?[-.\s]?[0-9]{10}$/',
    'general' => '/^[0-9+\-\s()]{10,}$/',
]);

// Timezone for all date operations
define('APP_TIMEZONE', 'Asia/Kolkata');

// Enable/Disable features
define('VALIDATION_CONFIG', [
    'enable_ajax_validation' => true,
    'enable_client_side_validation' => true,
    'enable_server_side_validation' => true,
    'show_validation_messages' => true,
    'use_sweetalert' => true,
    'case_insensitive_duplicate_check' => true,
    'trim_inputs' => true,
    'sanitize_inputs' => true,
]);

?>
