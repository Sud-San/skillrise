<?php
include "connection.php";

// Initialize variables
$game_id = $name = $slug = $icon = $category = $description = $difficulty = $base_duration_minutes = $is_active = "";
$is_edit = false;
$errors = [];

// Define valid ENUM values from database
$valid_categories = ['Debugging', 'Logic', 'Syntax', 'Speed', 'Puzzle', 'Database', 'Optimization', 'Competitive', 'Fun'];
$valid_difficulties = ['Easy', 'Medium', 'Hard', 'Expert'];

// Check if editing existing game
if (isset($_GET['game_id']) && is_numeric($_GET['game_id'])) {
    $game_id = $_GET['game_id'];
    $is_edit = true;
    
    // Fetch game details
    $query = "SELECT * FROM games WHERE game_id = '$game_id'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $name = $row['name'];
        $slug = $row['slug'];
        $icon = $row['icon'];
        $category = $row['category'];
        $description = $row['description'];
        $difficulty = $row['difficulty'];
        $base_duration_minutes = $row['base_duration_minutes'];
        $is_active = $row['is_active'];
    } else {
        header("Location: manage-games.php?error=not_found");
        exit;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and validate inputs
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $slug = trim(mysqli_real_escape_string($conn, $_POST['slug']));
    $icon = trim(mysqli_real_escape_string($conn, $_POST['icon']));
    $category = trim(mysqli_real_escape_string($conn, $_POST['category']));
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $difficulty = trim(mysqli_real_escape_string($conn, $_POST['difficulty']));
    $base_duration_minutes = (int)$_POST['base_duration_minutes'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Basic Validations
    if (empty($name)) {
        $errors[] = "Game name is required.";
    } elseif (strlen($name) < 3) {
        $errors[] = "Game name must be at least 3 characters long.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Game name must not exceed 100 characters.";
    }
    
    if (empty($slug)) {
        // Auto-generate slug from name if not provided
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens.";
    }
    
    // Check for duplicate slug (except current game in edit mode)
    $slug_check_query = "SELECT game_id FROM games WHERE slug = '$slug'";
    if ($is_edit) {
        $slug_check_query .= " AND game_id != '$game_id'";
    }
    $slug_check = mysqli_query($conn, $slug_check_query);
    if (mysqli_num_rows($slug_check) > 0) {
        $errors[] = "This slug is already in use. Please choose another.";
    }
    
    // Validate icon - schema is varchar(10)
    if (!empty($icon) && mb_strlen($icon) > 10) {
        $errors[] = "Icon string is too long. Maximum 10 characters allowed (use emojis).";
    }
    
    // CATEGORY VALIDATION - ENUM check
    if (empty($category)) {
        $errors[] = "Category is required.";
    } elseif (!in_array($category, $valid_categories)) {
        $errors[] = "Please select a valid category. Allowed values: " . implode(', ', $valid_categories);
    }
    
    if (!empty($description) && strlen($description) > 500) {
        $errors[] = "Description must not exceed 500 characters.";
    }
    
    // Validate difficulty - ENUM check
    if (!empty($difficulty) && !in_array($difficulty, $valid_difficulties)) {
        $errors[] = "Please select a valid difficulty level.";
    }
    
    // Validate base duration
    if (empty($base_duration_minutes)) {
        $errors[] = "Base duration is required.";
    } elseif ($base_duration_minutes < 1) {
        $errors[] = "Base duration must be at least 1 minute.";
    } elseif ($base_duration_minutes > 1440) {
        $errors[] = "Base duration must not exceed 1440 minutes (24 hours).";
    }
    
    // If no errors, proceed with insert/update
    if (empty($errors)) {
        if ($is_edit) {
            // Update existing game
            $query = "UPDATE games SET 
                      name = '$name',
                      slug = '$slug',
                      icon = '$icon',
                      category = '$category',
                      description = '$description',
                      difficulty = '$difficulty',
                      base_duration_minutes = '$base_duration_minutes',
                      is_active = '$is_active'
                      WHERE game_id = '$game_id'";
            
            if (mysqli_query($conn, $query)) {
                header("Location: manage-games.php?updated=1");
                exit;
            } else {
                $errors[] = "Error updating game: " . mysqli_error($conn);
            }
        } else {
            // Insert new game
            $query = "INSERT INTO games (name, slug, icon, category, description, difficulty, base_duration_minutes, is_active, created_at) 
                      VALUES ('$name', '$slug', '$icon', '$category', '$description', '$difficulty', '$base_duration_minutes', '$is_active', NOW())";
            
            if (mysqli_query($conn, $query)) {
                header("Location: manage-games.php?added=1");
                exit;
            } else {
                $errors[] = "Error adding game: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Game | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Theme Config Js -->
    <script src="assets/js/config.js"></script>
    <!-- Vendor css -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <!-- Icons css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .icon-preview {
            font-size: 32px;
            margin-top: 10px;
            padding: 15px;
            border: 1px dashed #ddd;
            border-radius: 8px;
            text-align: center;
            background-color: #f8f9fa;
        }
        .icon-preview i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #5b69bc;
        }
        .icon-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .icon-suggestion-item {
            cursor: pointer;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .icon-suggestion-item:hover {
            background-color: #5b69bc;
            color: white;
            border-color: #5b69bc;
        }
        .icon-suggestion-item:hover i {
            color: white;
        }
        .category-badge {
            cursor: pointer;
            padding: 5px 10px;
            margin: 2px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidenav Menu Start -->
        <?php include_once("sidebar.php"); ?>
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        <?php include_once("header.php"); ?>
        <!-- Topbar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="page-content">
            <div class="page-container">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-2"><?php echo $is_edit ? 'Edit' : 'Add'; ?> Game</h4>
                                <p class="text-muted mb-0">
                                    <?php echo $is_edit ? 'Update game details' : 'Add a new game to the system'; ?>
                                </p>
                            </div>
                            <div class="card-body">
                                
                                <!-- Display errors if any -->
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>Error!</strong> Please fix the following issues:
                                        <ul class="mb-0 mt-2">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Game Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($name); ?>" 
                                                   placeholder="Enter game name" required maxlength="100">
                                            <div class="invalid-feedback">
                                                Please enter a game name (min 3 characters).
                                            </div>
                                            <small class="text-muted">Minimum 3 characters, maximum 100 characters.</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="slug" class="form-label">Slug</label>
                                            <input type="text" class="form-control" id="slug" name="slug" 
                                                   value="<?php echo htmlspecialchars($slug); ?>" 
                                                   placeholder="game-url-slug" pattern="[a-z0-9-]+">
                                            <div class="invalid-feedback">
                                                Slug can only contain lowercase letters, numbers, and hyphens.
                                            </div>
                                            <small class="text-muted">Leave empty to auto-generate from name. Use only lowercase letters, numbers, and hyphens.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="icon" class="form-label">Icon (Emoji)</label>
                                            <input type="text" class="form-control" id="icon" name="icon" 
                                                   value="<?php echo htmlspecialchars($icon); ?>" 
                                                   placeholder="e.g. 🎮" 
                                                   maxlength="10">
                                            <small class="text-muted">Enter a single emoji or short character code. Maximum 10 characters.</small>
                                            
                                            <!-- Character counter for icon -->
                                            <small class="text-muted d-block mt-1">
                                                <span id="iconCharCount"><?php echo mb_strlen($icon); ?></span>/10 characters
                                            </small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="Debugging" <?php echo ($category == 'Debugging') ? 'selected' : ''; ?>>Debugging</option>
                                                <option value="Logic" <?php echo ($category == 'Logic') ? 'selected' : ''; ?>>Logic</option>
                                                <option value="Syntax" <?php echo ($category == 'Syntax') ? 'selected' : ''; ?>>Syntax</option>
                                                <option value="Speed" <?php echo ($category == 'Speed') ? 'selected' : ''; ?>>Speed</option>
                                                <option value="Puzzle" <?php echo ($category == 'Puzzle') ? 'selected' : ''; ?>>Puzzle</option>
                                                <option value="Database" <?php echo ($category == 'Database') ? 'selected' : ''; ?>>Database</option>
                                                <option value="Optimization" <?php echo ($category == 'Optimization') ? 'selected' : ''; ?>>Optimization</option>
                                                <option value="Competitive" <?php echo ($category == 'Competitive') ? 'selected' : ''; ?>>Competitive</option>
                                                <option value="Fun" <?php echo ($category == 'Fun') ? 'selected' : ''; ?>>Fun</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a valid category.
                                            </div>
                                            
                                            
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="3" placeholder="Enter game description" maxlength="500"><?php echo htmlspecialchars($description); ?></textarea>
                                        <small class="text-muted">Maximum 500 characters. <span id="charCount">0</span>/500</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="difficulty" class="form-label">Difficulty</label>
                                            <select class="form-select" id="difficulty" name="difficulty">
                                                <option value="">Select Difficulty</option>
                                                <option value="Easy" <?php echo ($difficulty == 'Easy') ? 'selected' : ''; ?>>Easy</option>
                                                <option value="Medium" <?php echo ($difficulty == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                                <option value="Hard" <?php echo ($difficulty == 'Hard') ? 'selected' : ''; ?>>Hard</option>
                                                <option value="Expert" <?php echo ($difficulty == 'Expert') ? 'selected' : ''; ?>>Expert</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="base_duration_minutes" class="form-label">Base Duration (minutes) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="base_duration_minutes" 
                                                   name="base_duration_minutes" 
                                                   value="<?php echo htmlspecialchars($base_duration_minutes); ?>" 
                                                   placeholder="30" min="1" max="1440" required>
                                            <div class="invalid-feedback">
                                                Please enter a valid duration (1-1440 minutes).
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_active" 
                                                       name="is_active" <?php echo ($is_active == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_active">Active Status</label>
                                            </div>
                                            <small class="text-muted">Toggle to activate/deactivate this game.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" style="background:#5b69bc;">
                                            <i class="fa-solid fa-<?php echo $is_edit ? 'pen-to-square' : 'plus'; ?> me-1"></i>
                                            <?php echo $is_edit ? 'Update Game' : 'Add Game'; ?>
                                        </button>
                                        <a href="manage-games.php" class="btn btn-secondary ms-2">
                                            <i class="fa-solid fa-times me-1"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div> <!-- end row-->
            </div> <!-- container -->
            
            <!-- Footer Start -->
            <?php include_once("footer.php"); ?>
            <!-- end Footer -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    
    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    
    <script>
        // Character counter for description
        document.getElementById('description').addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });
        
        // Icon character counter
        document.getElementById('icon').addEventListener('input', function() {
            let iconLength = this.value.length;
            document.getElementById('iconCharCount').textContent = iconLength;
            
            // Change color if approaching limit
            let counterSpan = document.getElementById('iconCharCount');
            if (iconLength > 8) {
                counterSpan.style.color = 'red';
            } else if (iconLength > 5) {
                counterSpan.style.color = 'orange';
            } else {
                counterSpan.style.color = 'inherit';
            }
        });
        
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('blur', function() {
            let slugField = document.getElementById('slug');
            if (slugField.value.trim() === '') {
                let name = this.value.toLowerCase();
                let slug = name.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                slugField.value = slug;
            }
        });
        
        // Set initial values
        document.addEventListener('DOMContentLoaded', function() {
            let desc = document.getElementById('description');
            if (desc) {
                document.getElementById('charCount').textContent = desc.value.length;
            }
            
            let iconField = document.getElementById('icon');
            if (iconField) {
                document.getElementById('iconCharCount').textContent = iconField.value.length;
            }
        });
        
        // Bootstrap form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    
    <!-- Display success/error messages from URL parameters -->
    <script>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'not_found'): ?>
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Game not found.",
                timer: 2000,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>
</body>
</html>