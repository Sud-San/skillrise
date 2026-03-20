<?php
/**
 * ==========================================
 * VALIDATION TEMPLATE EXAMPLE
 * ==========================================
 * This file demonstrates how to implement
 * validation in a form page
 */

include "connection.php";
include "validation-helper.php";

// =====================
// VARIABLES
// =====================
$status = "";
$category_id = 0;
$category_name = $category_code = $category_description = "";
$errors = [];

// =====================
// FETCH MODE (EDIT)
// =====================
if (isset($_GET['category_id']) && intval($_GET['category_id']) > 0) {
    $category_id = intval($_GET['category_id']);
    
    $sql = "SELECT * FROM category_tbl WHERE category_id = $category_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $category_name = $row['category_name'];
        $category_code = $row['category_code'];
        $category_description = $row['category_description'];
    }
}

// =====================
// FORM SUBMIT
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_category'])) {
    
    // Get and sanitize inputs
    $category_name = ValidationHelper::getPost('category_name', 'string');
    $category_code = ValidationHelper::getPost('category_code', 'string');
    $category_description = ValidationHelper::getPost('category_description', 'string');
    $category_id_post = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    
    // =====================
    // VALIDATION
    // =====================
    
    // Name validation
    if (!ValidationHelper::validateRequired($category_name)) {
        $errors[] = "Category name is required";
    } elseif (!ValidationHelper::validateLength($category_name, 2, 255)) {
        $errors[] = "Category name must be between 2 and 255 characters";
    } elseif (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_name', $category_name, $category_id_post, 'category_id')) {
        $errors[] = "Category name already exists";
    }
    
    // Code validation
    if (!ValidationHelper::validateRequired($category_code)) {
        $errors[] = "Category code is required";
    } elseif (!ValidationHelper::validateCode($category_code)) {
        $errors[] = "Category code must contain only uppercase letters, numbers, and underscores";
    } elseif (ValidationHelper::checkDuplicate($conn, 'category_tbl', 'category_code', $category_code, $category_id_post, 'category_id')) {
        $errors[] = "Category code already exists";
    }
    
    // Description validation
    if (!ValidationHelper::validateRequired($category_description)) {
        $errors[] = "Description is required";
    } elseif (!ValidationHelper::validateLength($category_description, 10, 5000)) {
        $errors[] = "Description must be between 10 and 5000 characters";
    }
    
    // =====================
    // PROCESS IF NO ERRORS
    // =====================
    if (empty($errors)) {
        date_default_timezone_set('Asia/Kolkata');
        $time = date('Y-m-d H:i:s');
        
        if ($category_id_post > 0) {
            // UPDATE
            $sql = "UPDATE category_tbl SET 
                        category_name = '$category_name',
                        category_code = '$category_code',
                        category_description = '$category_description',
                        updated_at = '$time'
                   WHERE category_id = $category_id_post";
            
            if (mysqli_query($conn, $sql)) {
                $status = "success";
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'manage-category.php';
                    }, 2000);
                </script>";
            } else {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        } else {
            // INSERT
            $sql = "INSERT INTO category_tbl (category_name, category_code, category_description, category_status, created_at, updated_at)
                    VALUES ('$category_name', '$category_code', '$category_description', '1', '$time', '$time')";
            
            if (mysqli_query($conn, $sql)) {
                $status = "success";
                // Clear form
                $category_name = $category_code = $category_description = "";
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'manage-category.php';
                    }, 2000);
                </script>";
            } else {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Validation CSS -->
    <link rel="stylesheet" href="assets/css/validation.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo $category_id > 0 ? 'Edit Category' : 'Add New Category'; ?></h4>
                </div>
                
                <div class="card-body">
                    
                    <!-- Success Message -->
                    <?php if ($status === 'success'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> Category <?php echo $category_id > 0 ? 'updated' : 'created'; ?> successfully. Redirecting...
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Form -->
                    <form method="POST" id="categoryForm" class="needs-validation">
                        
                        <!-- Hidden field for category ID -->
                        <?php if ($category_id > 0): ?>
                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                            <input type="hidden" data-exclude-id="<?php echo $category_id; ?>">
                        <?php endif; ?>
                        
                        <!-- Category Name Field -->
                        <div class="form-group mb-3">
                            <label for="category_name" class="form-label required">Category Name</label>
                            <input 
                                type="text" 
                                id="category_name" 
                                name="category_name" 
                                class="form-control"
                                data-validate="name"
                                data-duplicate-check="ajax-validation.php?action=check_category&type=name"
                                data-label="Category Name"
                                value="<?php echo htmlspecialchars($category_name); ?>"
                                placeholder="Enter category name"
                                required>
                            <small class="form-text text-muted">Must be 2-255 characters (letters, numbers, spaces, and basic symbols)</small>
                        </div>
                        
                        <!-- Category Code Field -->
                        <div class="form-group mb-3">
                            <label for="category_code" class="form-label required">Category Code</label>
                            <input 
                                type="text" 
                                id="category_code" 
                                name="category_code" 
                                class="form-control"
                                data-validate="code"
                                data-duplicate-check="ajax-validation.php?action=check_category&type=slug"
                                data-label="Category Code"
                                value="<?php echo htmlspecialchars($category_code); ?>"
                                placeholder="e.g., WEB_DEV, MOBILE_APP"
                                required>
                            <small class="form-text text-muted">Uppercase letters, numbers, and underscores only</small>
                        </div>
                        
                        <!-- Description Field -->
                        <div class="form-group mb-3">
                            <label for="category_description" class="form-label required">Description</label>
                            <textarea 
                                id="category_description" 
                                name="category_description" 
                                class="form-control"
                                data-validate="text"
                                rows="4"
                                placeholder="Enter category description"
                                required><?php echo htmlspecialchars($category_description); ?></textarea>
                            <small class="form-text text-muted">Must be 10-5000 characters</small>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-group">
                            <button type="submit" name="btn_category" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $category_id > 0 ? 'Update Category' : 'Add Category'; ?>
                            </button>
                            <a href="manage-category.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                        
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Validation JS -->
<script src="validation.js"></script>

<script>
    // Show success message with SweetAlert
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($status === 'success'): ?>
            Swal.fire({
                title: 'Success!',
                text: 'Category <?php echo $category_id > 0 ? 'updated' : 'created'; ?> successfully',
                icon: 'success',
                timer: 2000
            });
        <?php elseif (!empty($errors)): ?>
            Swal.fire({
                title: 'Validation Errors!',
                text: 'Please fix the errors and try again',
                icon: 'error'
            });
        <?php endif; ?>
    });
</script>

</body>
</html>
