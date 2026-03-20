/**
 * ==========================================
 * COMPREHENSIVE ADMIN FORM VALIDATION SYSTEM
 * ==========================================
 * Features:
 * - Client-side validation with visual feedback
 * - AJAX duplicate checking with red border on input
 * - Real-time validation messages
 * - Support for multiple field types
 */

// Configuration for different field types
const validationConfig = {
    name: {
        pattern: /^[a-zA-Z0-9\s\-&.,'()]*$/,
        minLength: 2,
        maxLength: 255,
        message: 'Name must be 2-255 characters and contain only letters, numbers, spaces, and basic symbols'
    },
    email: {
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        message: 'Please enter a valid email address'
    },
    phone: {
        pattern: /^[0-9+\-\s()]*$/,
        minLength: 10,
        message: 'Please enter a valid phone number (at least 10 digits)'
    },
    url: {
        pattern: /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/,
        message: 'Please enter a valid URL'
    },
    slug: {
        pattern: /^[a-z0-9]+(?:[_-][a-z0-9]+)*$/,
        minLength: 2,
        message: 'Slug must contain lowercase letters, numbers, hyphens, and underscores only'
    },
    code: {
        pattern: /^[A-Z0-9_]*$/,
        minLength: 2,
        message: 'Code must contain uppercase letters, numbers, and underscores only'
    },
    number: {
        pattern: /^[0-9]*$/,
        message: 'Please enter a valid number'
    },
    text: {
        minLength: 2,
        maxLength: 5000,
        message: 'Text must be between 2 and 5000 characters'
    }
};

/**
 * Initialize validation on page load
 */
function initializeValidation() {
    console.log('Validation system initialized');
    
    // Add event listeners to all input fields
    document.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // Add listeners for duplicate checking
    document.querySelectorAll('[data-duplicate-check]').forEach(field => {
        field.addEventListener('blur', function() {
            checkDuplicate(this);
        });
    });

    // Form submit validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateAllFields(this)) {
                e.preventDefault();
                showErrorNotification('Please fix the errors before submitting');
                return false;
            }
        });
    });
}

/**
 * Validate a single field
 */
function validateField(field) {
    const fieldType = field.getAttribute('data-validate');
    const fieldValue = field.value.trim();
    const fieldId = field.id || field.name;
    
    // Remove previous validation messages
    removeValidationMessage(field);
    
    // Skip validation if field is empty and not required
    if (!fieldValue && !field.hasAttribute('required')) {
        field.classList.remove('is-invalid');
        field.classList.remove('is-valid');
        return true;
    }
    
    // Check if field is empty and required
    if (!fieldValue && field.hasAttribute('required')) {
        showInvalidField(field, 'This field is required');
        return false;
    }
    
    // Get validation rules for this field type
    const rules = validationConfig[fieldType];
    
    if (!rules) {
        console.warn(`No validation rules found for type: ${fieldType}`);
        return true;
    }
    
    // Check minimum length
    if (rules.minLength && fieldValue.length < rules.minLength) {
        showInvalidField(field, `Minimum ${rules.minLength} characters required`);
        return false;
    }
    
    // Check maximum length
    if (rules.maxLength && fieldValue.length > rules.maxLength) {
        showInvalidField(field, `Maximum ${rules.maxLength} characters allowed`);
        return false;
    }
    
    // Check pattern
    if (rules.pattern && !rules.pattern.test(fieldValue)) {
        showInvalidField(field, rules.message);
        return false;
    }
    
    // All validations passed
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    showValidField(field);
    return true;
}

/**
 * Check for duplicate values via AJAX
 */
function checkDuplicate(field) {
    const checkUrl = field.getAttribute('data-duplicate-check');
    const fieldValue = field.value.trim();
    const fieldId = field.id || field.name;
    
    // Skip if empty
    if (!fieldValue) {
        field.classList.remove('is-invalid');
        return;
    }
    
    // Get field-specific parameters
    const params = new URLSearchParams();
    params.append('value', fieldValue);
    
    // Add exclude ID if editing
    const excludeIdField = document.querySelector('[data-exclude-id]');
    if (excludeIdField) {
        const excludeId = excludeIdField.getAttribute('data-exclude-id');
        params.append('exclude_id', excludeId);
    }
    
    // Add field-specific parameters
    if (checkUrl.includes('check_course')) {
        params.append('name', fieldValue);
        const courseIdField = document.querySelector('[name="course_id"]');
        if (courseIdField && courseIdField.value) {
            params.append('course_id', courseIdField.value);
        }
    } else if (checkUrl.includes('check_category')) {
        const fieldName = field.name;
        params.append('type', fieldName === 'category_name' ? 'name' : 'slug');
        params.append('value', fieldValue);
        const categoryIdField = document.querySelector('[name="category_id"]');
        if (categoryIdField && categoryIdField.value) {
            params.append('category_id', categoryIdField.value);
        }
    } else if (checkUrl.includes('check_city')) {
        params.append('name', fieldValue);
        const cityIdField = document.querySelector('[name="city_id"]');
        if (cityIdField && cityIdField.value) {
            params.append('city_id', cityIdField.value);
        }
    } else if (checkUrl.includes('check_state')) {
        params.append('name', fieldValue);
        const stateIdField = document.querySelector('[name="state_id"]');
        if (stateIdField && stateIdField.value) {
            params.append('state_id', stateIdField.value);
        }
    } else if (checkUrl.includes('check_package')) {
        params.append('name', fieldValue);
        const packageIdField = document.querySelector('[name="package_id"]');
        if (packageIdField && packageIdField.value) {
            params.append('package_id', packageIdField.value);
        }
    } else if (checkUrl.includes('check_college')) {
        const fieldName = field.name;
        params.append('field', fieldName);
        params.append('value', fieldValue);
        const collegeIdField = document.querySelector('[name="college_id"]');
        if (collegeIdField && collegeIdField.value) {
            params.append('college_id', collegeIdField.value);
        }
    }
    
    // Show loading state
    field.classList.add('validating');
    
    fetch(`${checkUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            field.classList.remove('validating');
            
            if (data.exists) {
                const fieldLabel = field.getAttribute('data-label') || field.name;
                showInvalidField(field, `${fieldLabel} already exists. Please use a different value.`);
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                showValidField(field);
            }
        })
        .catch(error => {
            console.error('Duplicate check error:', error);
            field.classList.remove('validating');
        });
}

/**
 * Validate all fields in a form
 */
function validateAllFields(form) {
    let isValid = true;
    
    form.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Show invalid field styling
 */
function showInvalidField(field, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    // Add red border
    field.style.borderColor = '#dc3545';
    field.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
    
    // Show error message
    showValidationMessage(field, message, 'invalid');
}

/**
 * Show valid field styling
 */
function showValidField(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    
    // Add green border
    field.style.borderColor = '#28a745';
    field.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
}

/**
 * Show validation message
 */
function showValidationMessage(field, message, type) {
    const feedbackId = `${field.id || field.name}_feedback`;
    let feedbackElement = document.getElementById(feedbackId);
    
    if (!feedbackElement) {
        feedbackElement = document.createElement('div');
        feedbackElement.id = feedbackId;
        feedbackElement.className = `validation-${type}`;
        field.parentNode.insertBefore(feedbackElement, field.nextSibling);
    }
    
    feedbackElement.className = `validation-${type}`;
    feedbackElement.textContent = message;
    feedbackElement.style.display = 'block';
    feedbackElement.style.marginTop = '5px';
    feedbackElement.style.fontSize = '0.875rem';
    feedbackElement.style.color = type === 'invalid' ? '#dc3545' : '#28a745';
}

/**
 * Remove validation message
 */
function removeValidationMessage(field) {
    const feedbackId = `${field.id || field.name}_feedback`;
    const feedbackElement = document.getElementById(feedbackId);
    if (feedbackElement) {
        feedbackElement.remove();
    }
}

/**
 * Show error notification
 */
function showErrorNotification(message) {
    // Using SweetAlert if available, otherwise alert
    if (typeof Swal !== 'undefined') {
        Swal.fire('Validation Error', message, 'error');
    } else {
        alert(message);
    }
}

/**
 * Show success notification
 */
function showSuccessNotification(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire('Success', message, 'success');
    } else {
        alert(message);
    }
}

/**
 * Clear all validation messages
 */
function clearValidation(form) {
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.classList.remove('is-invalid', 'is-valid');
        field.style.borderColor = '';
        field.style.boxShadow = '';
        removeValidationMessage(field);
    });
}

// Initialize validation when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeValidation);
} else {
    initializeValidation();
}
