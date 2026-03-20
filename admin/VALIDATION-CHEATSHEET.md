# Validation System - Quick Reference Cheat Sheet

## 📌 Include Files

### In Header
```html
<link rel="stylesheet" href="assets/css/validation.css">
<script src="validation.js"></script>
```

### In PHP
```php
<?php
include "validation-helper.php";
include "validation-config.php";
?>
```

---

## 🏷️ Input Attributes

```html
<!-- Basic validation -->
<input 
    data-validate="[type]"           <!-- Field type -->
    required                          <!-- Make required -->
>

<!-- With duplicate check -->
<input 
    data-validate="name"
    data-duplicate-check="ajax-validation.php?action=check_category&type=name"
    data-label="Category Name"        <!-- Label for error messages -->
    required
>
```

---

## 🎯 Field Types Cheat Sheet

### Text/Name Fields
```html
<!-- Category Name -->
<input type="text" name="category_name"
    data-validate="name"
    data-duplicate-check="ajax-validation.php?action=check_category&type=name"
    placeholder="Enter name"
    required>

<!-- Course Name -->
<input type="text" name="course_name"
    data-validate="name"
    data-duplicate-check="ajax-validation.php?action=check_course"
    required>

<!-- City Name -->
<input type="text" name="city_name"
    data-validate="name"
    data-duplicate-check="ajax-validation.php?action=check_city"
    required>
```

### Email Fields
```html
<input type="email" name="email"
    data-validate="email"
    data-duplicate-check="ajax-validation.php?action=check_college&field=clg_email"
    placeholder="user@example.com"
    required>
```

### Phone Fields
```html
<input type="tel" name="phone"
    data-validate="phone"
    placeholder="10 digit number"
    required>
```

### URL Fields
```html
<input type="url" name="website"
    data-validate="url"
    placeholder="https://example.com"
    required>
```

### Slug Fields
```html
<input type="text" name="slug"
    data-validate="slug"
    data-duplicate-check="ajax-validation.php?action=check_category&type=slug"
    placeholder="lowercase-with-hyphens"
    required>
```

### Code Fields
```html
<input type="text" name="code"
    data-validate="code"
    placeholder="UPPERCASE_CODE"
    required>
```

### Number Fields
```html
<input type="number" name="quantity"
    data-validate="number"
    required>
```

### Text Area
```html
<textarea name="description"
    data-validate="text"
    rows="4"
    required></textarea>
```

---

## 🔄 AJAX Actions Reference

```
Check Category:   action=check_category&type=name
Check Category Code: action=check_category&type=slug
Check Course:     action=check_course
Check City:       action=check_city
Check State:      action=check_state
Check Package:    action=check_package
Check College:    action=check_college&field=clg_name
Validate Field:   action=validate_field&type=email
```

---

## ✅ PHP Validation Functions

### Check if Valid
```php
// Email
ValidationHelper::validateEmail('user@example.com');

// Phone
ValidationHelper::validatePhone('9876543210');

// URL
ValidationHelper::validateUrl('https://example.com');

// Name
ValidationHelper::validateName('Category Name');

// Slug
ValidationHelper::validateSlug('my-slug');

// Code
ValidationHelper::validateCode('MY_CODE');

// Required
ValidationHelper::validateRequired($value);

// Length
ValidationHelper::validateLength($value, 2, 255);
```

### Check Duplicates
```php
// Basic duplicate check
ValidationHelper::checkDuplicate($conn, 'table', 'field', $value);

// With exclude ID (for edit mode)
ValidationHelper::checkDuplicate($conn, 'table', 'field', $value, $id, 'id_field');

// Example
if (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $name, $cat_id, 'category_id')) {
    echo "Name already exists!";
}
```

### Get & Sanitize Input
```php
// From POST
$name = ValidationHelper::getPost('name', 'string');
$email = ValidationHelper::getPost('email', 'email');
$phone = ValidationHelper::getPost('phone', 'string');

// From GET
$id = ValidationHelper::getGet('id', 'number');

// With required check
$name = ValidationHelper::getPost('name', 'string', true); // returns null if missing
```

### Validate File Upload
```php
$validation = ValidationHelper::validateFileUpload(
    $_FILES['logo'],
    ['jpg', 'jpeg', 'png'],  // allowed types
    5242880                   // max size in bytes
);

if (!$validation['valid']) {
    echo $validation['error'];
}
```

### Sanitize
```php
$clean = ValidationHelper::sanitize($input, 'string');  // HTML escape
$email = ValidationHelper::sanitize($input, 'email');   // Email sanitize
$url = ValidationHelper::sanitize($input, 'url');       // URL sanitize
$number = ValidationHelper::sanitize($input, 'number'); // Number sanitize
```

---

## 🎨 CSS Classes

```css
/* Applied automatically by validation.js */

.is-valid          /* Green border - valid input */
.is-invalid        /* Red border - invalid input */
.validating        /* Yellow border - checking */
.validation-valid  /* Green error message */
.validation-invalid /* Red error message */
```

---

## 🌐 Browser DevTools

### Check if JS loaded
```javascript
// In browser console
typeof initializeValidation // Should return "function"
typeof ValidationHelper    // Should return "undefined" (PHP class)
```

### Test validation
```javascript
// Manually trigger validation on a field
const field = document.getElementById('category_name');
validateField(field);

// Check for errors
document.querySelectorAll('.is-invalid'); // Get all invalid fields
document.querySelectorAll('.is-valid');   // Get all valid fields
```

### Test AJAX
```javascript
// In Network tab, look for:
// GET ajax-validation.php?action=...
// Response should be JSON: {"exists": true/false}
```

---

## 🚨 Common Errors & Solutions

| Error | Solution |
|-------|----------|
| "validation.js not found" | Check file path, refresh browser |
| "No validation feedback" | Add `data-validate` attribute |
| "Red border always shows" | Check regex pattern in config |
| "Duplicate check not working" | Verify ajax-validation.php exists |
| "AJAX request 404" | Check action parameter value |
| "Database error" | Check connection, table names |
| "Styling broken" | Clear cache, verify CSS path |
| "Form submits with errors" | Check form validation listener |

---

## 📝 Complete Form Template

```html
<?php
include "validation-helper.php";

if ($_POST && isset($_POST['btn_submit'])) {
    $name = ValidationHelper::getPost('name', 'string');
    
    if (!ValidationHelper::validateRequired($name)) {
        $error = "Name required";
    } elseif (ValidationHelper::checkDuplicate($conn, 'table', 'field', $name)) {
        $error = "Name exists";
    }
    
    if (!isset($error)) {
        // Insert/Update code here
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/validation.css">
</head>
<body>

<form method="POST" id="myForm">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-group">
        <label for="name" class="required">Name</label>
        <input 
            type="text" 
            id="name" 
            name="name"
            class="form-control"
            data-validate="name"
            data-duplicate-check="ajax-validation.php?action=check_category&type=name"
            data-label="Name"
            required>
    </div>
    
    <button type="submit" name="btn_submit">Submit</button>
</form>

<script src="validation.js"></script>
</body>
</html>
```

---

## 🔐 Security Checklist

- [ ] Always validate on server-side
- [ ] Use prepared statements or escape queries
- [ ] Sanitize all inputs
- [ ] Validate file uploads
- [ ] Use HTTPS
- [ ] Implement CSRF tokens
- [ ] Log validation failures
- [ ] Monitor error logs
- [ ] Keep dependencies updated
- [ ] Test with SQL injection payloads

---

## 🎯 Implementation Checklist

- [ ] Copy validation.js to admin folder
- [ ] Copy validation.css to admin/assets/css
- [ ] Add files to header.php
- [ ] Create ajax-validation.php
- [ ] Include validation-helper.php in forms
- [ ] Add data-validate attributes
- [ ] Add data-duplicate-check attributes
- [ ] Test each field type
- [ ] Test duplicate checking
- [ ] Test error messages
- [ ] Test mobile responsiveness
- [ ] Test AJAX in slow network
- [ ] Add server-side validation
- [ ] Test form submission
- [ ] Deploy to production

---

## 📞 Quick Support

| Issue | Check |
|-------|-------|
| Nothing validates | Console errors? Files linked? |
| AJAX fails | Network tab? Database connected? |
| Styling off | CSS file linked? Cache cleared? |
| Form submits bad data | Server validation added? |
| Duplicate check broken | ajax-validation.php exists? |
| Wrong error message | data-label attribute set? |

---

## 🎓 Learning Resources

1. **VALIDATION-GUIDE.md** - Complete documentation
2. **INTEGRATION-GUIDE.md** - Quick setup
3. **category-form-template.php** - Working example
4. **validation-helper.php** - Function reference
5. **ajax-validation.php** - Endpoint reference

---

**Keep this cheat sheet handy for quick reference while implementing validation!**
