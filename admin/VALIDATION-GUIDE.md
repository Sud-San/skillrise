# Admin Form Validation System Documentation

## Overview

This comprehensive validation system provides:
- **Client-side validation** with real-time feedback
- **Server-side validation** with proper sanitization
- **AJAX duplicate checking** with visual red border on input fields
- **Unified validation endpoints** for all admin forms
- **Multiple field type support** (email, phone, URL, slug, etc.)

## Files Included

1. **validation.js** - Client-side validation logic
2. **validation-helper.php** - Server-side validation utilities
3. **ajax-validation.php** - AJAX endpoints for duplicate checking
4. **validation.css** - Styling for validation feedback
5. **VALIDATION-GUIDE.md** - This documentation

---

## Installation & Setup

### Step 1: Include Files in Admin Header

Add these lines to your admin `header.php`:

```html
<!-- Validation CSS -->
<link rel="stylesheet" href="assets/css/validation.css">

<!-- Validation JS (at end of body or in header) -->
<script src="validation.js"></script>
```

### Step 2: Include Helper in PHP Files

At the top of your admin PHP files (like category.php, course.php, etc.):

```php
<?php
include "connection.php";
include "validation-helper.php";
?>
```

---

## Usage Examples

### Example 1: Simple Name Field with Duplicate Check

```html
<form method="POST" action="">
    <div class="form-group">
        <label for="category_name" class="required">Category Name</label>
        <input 
            type="text" 
            id="category_name" 
            name="category_name" 
            class="form-control"
            data-validate="name"
            data-duplicate-check="ajax-validation.php?action=check_category"
            data-label="Category Name"
            required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Example 2: Email Field with Validation

```html
<div class="form-group">
    <label for="clg_email" class="required">Email Address</label>
    <input 
        type="email" 
        id="clg_email" 
        name="clg_email" 
        class="form-control"
        data-validate="email"
        data-duplicate-check="ajax-validation.php?action=check_college&field=clg_email"
        data-label="Email"
        required>
</div>
```

### Example 3: Course Name Field

```html
<div class="form-group">
    <label for="course_name" class="required">Course Name</label>
    <input 
        type="text" 
        id="course_name" 
        name="course_name" 
        class="form-control"
        data-validate="name"
        data-duplicate-check="ajax-validation.php?action=check_course"
        data-label="Course Name"
        required>
</div>
```

### Example 4: Phone Field

```html
<div class="form-group">
    <label for="clg_contact" class="required">Contact Number</label>
    <input 
        type="tel" 
        id="clg_contact" 
        name="clg_contact" 
        class="form-control"
        data-validate="phone"
        data-duplicate-check="ajax-validation.php?action=check_college&field=clg_contact"
        data-label="Contact Number"
        required>
</div>
```

### Example 5: URL Field

```html
<div class="form-group">
    <label for="clg_website" class="required">Website</label>
    <input 
        type="url" 
        id="clg_website" 
        name="clg_website" 
        class="form-control"
        data-validate="url"
        data-duplicate-check="ajax-validation.php?action=check_college&field=clg_website"
        data-label="Website"
        required>
</div>
```

### Example 6: Slug Field

```html
<div class="form-group">
    <label for="clg_slug" class="required">Slug</label>
    <input 
        type="text" 
        id="clg_slug" 
        name="clg_slug" 
        class="form-control"
        data-validate="slug"
        data-duplicate-check="ajax-validation.php?action=check_college&field=clg_slug"
        data-label="Slug"
        placeholder="lowercase-with-hyphens"
        required>
    <small class="form-text">Use lowercase letters, numbers, hyphens, and underscores only</small>
</div>
```

---

## Validation Attributes

Add these data attributes to form fields:

### `data-validate` - Validates field type

Allowed values:
- `name` - Alphanumeric with spaces and basic symbols (2-255 chars)
- `email` - Email format validation
- `phone` - Phone number (10+ digits)
- `url` - URL format validation
- `slug` - Lowercase alphanumeric with hyphens/underscores
- `code` - Uppercase alphanumeric with underscores
- `number` - Numeric only
- `text` - General text (2-5000 chars)

### `data-duplicate-check` - Enables AJAX duplicate checking

Value should be the endpoint URL:
```
ajax-validation.php?action=check_[type]
```

### `data-label` - Custom label for error messages

Used in duplicate check error messages (defaults to field name)

### `required` - HTML5 required attribute

Marks field as required (validation will fail if empty)

---

## Server-Side Validation Usage

### ValidationHelper Methods

```php
// Validate email
if (!ValidationHelper::validateEmail($email)) {
    $error = "Invalid email";
}

// Validate phone
if (!ValidationHelper::validatePhone($phone)) {
    $error = "Invalid phone number";
}

// Validate URL
if (!ValidationHelper::validateUrl($url)) {
    $error = "Invalid URL";
}

// Validate name
if (!ValidationHelper::validateName($name)) {
    $error = "Invalid name format";
}

// Validate slug
if (!ValidationHelper::validateSlug($slug)) {
    $error = "Invalid slug format";
}

// Check for duplicates
if (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $name, $category_id, 'category_id')) {
    $error = "Category name already exists";
}

// Validate string length
if (!ValidationHelper::validateLength($value, 2, 255)) {
    $error = "Invalid length";
}

// Validate file upload
$fileValidation = ValidationHelper::validateFileUpload($_FILES['logo'], ['jpg', 'jpeg', 'png']);
if (!$fileValidation['valid']) {
    $error = $fileValidation['error'];
}

// Get and sanitize POST value
$email = ValidationHelper::getPost('email', 'email');
$phone = ValidationHelper::getPost('phone', 'string');
$number = ValidationHelper::getPost('quantity', 'number');
```

---

## Complete Form Example

```php
<?php
include "connection.php";
include "validation-helper.php";

$status = "";
$name = $email = $phone = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = ValidationHelper::getPost('name', 'string');
    $email = ValidationHelper::getPost('email', 'email');
    $phone = ValidationHelper::getPost('phone', 'string');
    
    // Validate
    $errors = [];
    
    if (!ValidationHelper::validateRequired($name)) {
        $errors[] = "Name is required";
    }
    
    if (!ValidationHelper::validateRequired($email)) {
        $errors[] = "Email is required";
    } elseif (!ValidationHelper::validateEmail($email)) {
        $errors[] = "Invalid email format";
    } elseif (ValidationHelper::checkDuplicate($conn, 'users', 'email', $email)) {
        $errors[] = "Email already exists";
    }
    
    if (!ValidationHelper::validateRequired($phone)) {
        $errors[] = "Phone is required";
    } elseif (!ValidationHelper::validatePhone($phone)) {
        $errors[] = "Invalid phone number";
    }
    
    if (empty($errors)) {
        // Proceed with database insert
        $status = "success";
    } else {
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/validation.css">
</head>
<body>

<?php if ($status === 'success'): ?>
    <div class="alert alert-success">Form submitted successfully!</div>
<?php elseif ($status === 'error'): ?>
    <div class="alert alert-danger">Please fix the errors below</div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label for="name" class="required">Name</label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control"
            data-validate="name"
            value="<?php echo htmlspecialchars($name); ?>"
            required>
    </div>
    
    <div class="form-group">
        <label for="email" class="required">Email</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control"
            data-validate="email"
            data-duplicate-check="ajax-validation.php?action=validate_field&type=email"
            data-label="Email"
            value="<?php echo htmlspecialchars($email); ?>"
            required>
    </div>
    
    <div class="form-group">
        <label for="phone" class="required">Phone</label>
        <input 
            type="tel" 
            id="phone" 
            name="phone" 
            class="form-control"
            data-validate="phone"
            value="<?php echo htmlspecialchars($phone); ?>"
            required>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<script src="validation.js"></script>

</body>
</html>
```

---

## Features Explained

### 1. Real-Time Validation
- Fields are validated on blur (when user leaves the field)
- Invalid fields get a red border with error message
- Valid fields get a green border with checkmark

### 2. Duplicate Checking (AJAX)
- Checks database for existing values
- Shows red border on duplicate
- Displays specific error message
- Excludes current record when editing

### 3. Visual Feedback
- **Red border** (#dc3545) - Invalid/Duplicate
- **Green border** (#28a745) - Valid
- **Yellow border** - Validating (during AJAX call)
- **Checkmark (✓)** - Valid field
- **X mark (✗)** - Invalid field

### 4. Automatic Form Submit Validation
- All fields validated on form submit
- Form blocked if validation fails
- Shows alert with error message

### 5. Error Messages
- Field-specific validation messages
- Duplicate check custom messages
- Required field messages
- Format validation messages

---

## Customization

### Change Validation Rules

Edit the `validationConfig` object in `validation.js`:

```javascript
const validationConfig = {
    myFieldType: {
        pattern: /your-regex/,
        minLength: 2,
        maxLength: 100,
        message: 'Your custom message'
    }
};
```

### Change Error Colors

Edit `validation.css`:

```css
/* Change error color from red to orange */
input.is-invalid {
    border-color: #ff8c00 !important;
}
```

### Add Custom Validation

```javascript
// In validation.js, add to validateField function:
if (fieldType === 'custom') {
    if (!yourCustomValidation(fieldValue)) {
        showInvalidField(field, 'Custom error message');
        return false;
    }
}
```

---

## Security Best Practices

1. **Always validate on server side** - Never trust client-side only
2. **Escape all database queries** - Use prepared statements or proper escaping
3. **Sanitize file uploads** - Check file type, size, and content
4. **Use HTTPS** - For form submissions
5. **Implement CSRF protection** - Add tokens to forms
6. **Rate limit AJAX calls** - Prevent abuse

---

## Troubleshooting

### Validation not working
- Check if `validation.js` is loaded (check browser console)
- Ensure `data-validate` attributes are set correctly
- Check browser console for JavaScript errors

### Duplicate check not working
- Verify `ajax-validation.php` exists
- Check if database connection is working
- Inspect AJAX response in browser Network tab

### Styling issues
- Ensure `validation.css` is linked correctly
- Check for CSS conflicts with other stylesheets
- Verify Bootstrap or your CSS framework isn't overriding styles

### Form not submitting
- Open browser console and check for JavaScript errors
- Try submitting with invalid data to see validation messages
- Check server-side PHP for errors

---

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support (iOS 13+)
- IE11: Requires polyfills for Fetch API

---

## Performance Tips

1. Validate only on blur (not on every keystroke)
2. Debounce AJAX calls (already implemented)
3. Cache validation rules if possible
4. Use CSS animations sparingly
5. Minimize database queries

---

## Future Enhancements

- [ ] Multi-field validation rules
- [ ] Conditional validation based on other fields
- [ ] Custom validation handlers
- [ ] Integration with form builders
- [ ] Localization support
- [ ] Accessibility improvements
- [ ] Password strength indicator
- [ ] Credit card validation

---

## Support

For issues or questions, refer to:
- Browser console (F12 → Console tab)
- Network tab for AJAX debugging
- Check server logs for PHP errors
- Database error logs

---

**Last Updated:** 2026-02-05
**Version:** 1.0.0
