<?php
header("Location: add_course.php");
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/headtag.php'?>
    <title>Add Course - Tutor Dashboard</title>
</head>

<body class="app">
    <?php include 'includes/header.php'?>
    
    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">
                
                <div class="page-utilities mb-4 d-print-none">
                    <div class="row g-2 justify-content-between align-items-center">
                        <div class="col-auto">
                            <h1 class="app-page-title">Add New Course</h1>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <form class="app-form">
                            <div class="app-card shadow-sm">
                                <div class="app-card-header p-3">
                                    <h5 class="app-card-title">Course Information</h5>
                                </div>
                                <div class="app-card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Course Title *</label>
                                        <input type="text" class="form-control" placeholder="Enter course title" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Course Description *</label>
                                        <textarea class="form-control" rows="4" placeholder="Describe your course..." required></textarea>
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Category *</label>
                                            <select class="form-select" required>
                                                <option value="">Select Category</option>
                                                <option value="programming">Programming</option>
                                                <option value="web-dev">Web Development</option>
                                                <option value="data-science">Data Science</option>
                                                <option value="design">Design</option>
                                                <option value="business">Business</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Level *</label>
                                            <select class="form-select" required>
                                                <option value="">Select Level</option>
                                                <option value="beginner">Beginner</option>
                                                <option value="intermediate">Intermediate</option>
                                                <option value="advanced">Advanced</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="app-card shadow-sm mt-4">
                                <div class="app-card-header p-3">
                                    <h5 class="app-card-title">Pricing</h5>
                                </div>
                                <div class="app-card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Course Price (₹) *</label>
                                            <input type="number" class="form-control" placeholder="0" step="100" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Your Commission %</label>
                                            <input type="number" class="form-control" placeholder="70" value="70" disabled>
                                            <small class="text-muted">Platform keeps 30%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="app-card shadow-sm mt-4">
                                <div class="app-card-header p-3">
                                    <h5 class="app-card-title">Course Image</h5>
                                </div>
                                <div class="app-card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Upload Thumbnail *</label>
                                        <input type="file" class="form-control" accept="image/*" required>
                                        <small class="text-muted">Recommended size: 1280x720px</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-4">
                                <div class="col-auto">
                                    <button type="submit" class="btn app-btn-primary">
                                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-lg me-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.017 1.052L7.88 12.414a.75.75 0 1 1-1.060-1.06l5.858-5.859a.75.75 0 0 1 .075-.075zm2.468-2.468a2.25 2.25 0 0 0-3.182 0l-.623.622a.75.75 0 1 0 1.06 1.06l.624-.623a1.25 1.25 0 0 1 1.767 0 1.25 1.25 0 0 1 0 1.768l-6.424 6.424a.75.75 0 1 0 1.06 1.06l6.424-6.424a2.25 2.25 0 0 0 0-3.182l-.622-.623zM4.82 4.82a.75.75 0 0 0-1.06 1.06L12.3 15.121a.75.75 0 1 0 1.06-1.06L4.82 4.82z"/>
</svg>Create Course</button>
                                </div>
                                <div class="col-auto">
                                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="col-12 col-lg-4">
                        <div class="app-card shadow-sm">
                            <div class="app-card-header p-3">
                                <h5 class="app-card-title">Course Tips</h5>
                            </div>
                            <div class="app-card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold">Good Course Practices</h6>
                                    <ul class="small">
                                        <li>Use clear and descriptive titles</li>
                                        <li>Create a professional thumbnail</li>
                                        <li>Write detailed descriptions</li>
                                        <li>Price competitively</li>
                                        <li>Start with quality content</li>
                                    </ul>
                                </div>
                                <div class="alert alert-info small">
                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-info-circle me-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533l.738-3.468c.194-.897-.105-1.319-.808-1.319-.545 0-1.178.252-1.465.598l-.088.416c.2-.176.492-.246.686-.246.275 0 .375.193.304.533z"/>
  <circle cx="8" cy="12.5" r=".5"/>
</svg>
                                    Your course will be reviewed before publishing
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <?php include 'includes/script.php'?>
    
</body>
</html>
