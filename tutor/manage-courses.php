<?php
// Protect this page - only logged-in tutors can access
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/headtag.php'?>
    <title>Manage Courses - Tutor Dashboard</title>
</head>

<body class="app">
    <?php include 'includes/header.php'?>
    
    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">
                
                <div class="page-utilities mb-4 d-print-none">
                    <div class="row g-2 justify-content-between align-items-center">
                        <div class="col-auto">
                            <h1 class="app-page-title">Manage Courses</h1>
                        </div>
                        <div class="col-auto">
                            <a class="btn app-btn-primary" href="add_course.php">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus-circle me-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path fill-rule="evenodd" d="M7.5 8a.5.5 0 0 1 1 0v2.5h2.5a.5.5 0 0 1 0 1h-2.5V14a.5.5 0 0 1-1 0v-2.5H5a.5.5 0 0 1 0-1h2.5V8z"/>
</svg>Add New Course</a>
                        </div>
                    </div>
                </div>
                
                <div class="app-card shadow-sm">
                    <div class="app-card-header p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-auto flex-grow-1">
                                <input type="search" class="form-control form-control-sm" placeholder="Search courses...">
                            </div>
                            <div class="col-auto">
                                <select class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="app-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="cell">Course Name</th>
                                        <th class="cell">Category</th>
                                        <th class="cell">Students</th>
                                        <th class="cell">Rating</th>
                                        <th class="cell">Price</th>
                                        <th class="cell">Status</th>
                                        <th class="cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-play-circle me-2 text-primary" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path fill-rule="evenodd" d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
</svg>
                                                <div>
                                                    <div class="fw-bold">Advanced Python Programming</div>
                                                    <div class="text-muted small">Created 2 months ago</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">Programming</td>
                                        <td class="cell">
                                            <span class="badge bg-light text-dark">78 enrolled</span>
                                        </td>
                                        <td class="cell"><span class="badge bg-success">4.8★</span></td>
                                        <td class="cell">₹2,999</td>
                                        <td class="cell"><span class="badge bg-success">Published</span></td>
                                        <td class="cell">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M12.146.292a.5.5 0 0 1 .708 0l2.854 2.854a.5.5 0 0 1 0 .708l-10.851 10.851a.5.5 0 0 1-.177.11l-5 1.5a.5.5 0 0 1-.609-.609l1.5-5a.5.5 0 0 1 .11-.177l10.851-10.851z"/>
  <path d="M2.5 13.5l-1 3.5 3.5-1L12.854 3.146 10.854 1.146 2.5 13.5z"/>
</svg>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" title="View">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-eye" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
</svg>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 1V2h11V1h-11z"/>
</svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-play-circle me-2 text-primary" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path fill-rule="evenodd" d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
</svg>
                                                <div>
                                                    <div class="fw-bold">Web Development 101</div>
                                                    <div class="text-muted small">Created 1 month ago</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">Web Development</td>
                                        <td class="cell">
                                            <span class="badge bg-light text-dark">65 enrolled</span>
                                        </td>
                                        <td class="cell"><span class="badge bg-success">4.6★</span></td>
                                        <td class="cell">₹1,999</td>
                                        <td class="cell"><span class="badge bg-success">Published</span></td>
                                        <td class="cell">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M12.146.292a.5.5 0 0 1 .708 0l2.854 2.854a.5.5 0 0 1 0 .708l-10.851 10.851a.5.5 0 0 1-.177.11l-5 1.5a.5.5 0 0 1-.609-.609l1.5-5a.5.5 0 0 1 .11-.177l10.851-10.851z"/>
  <path d="M2.5 13.5l-1 3.5 3.5-1L12.854 3.146 10.854 1.146 2.5 13.5z"/>
</svg>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" title="View">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-eye" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
</svg>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 1V2h11V1h-11z"/>
</svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-play-circle me-2 text-warning" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path fill-rule="evenodd" d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
</svg>
                                                <div>
                                                    <div class="fw-bold">Data Science Fundamentals</div>
                                                    <div class="text-muted small">Created 3 weeks ago</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">Data Science</td>
                                        <td class="cell">
                                            <span class="badge bg-light text-dark">52 enrolled</span>
                                        </td>
                                        <td class="cell"><span class="badge bg-success">4.5★</span></td>
                                        <td class="cell">₹3,499</td>
                                        <td class="cell"><span class="badge bg-warning text-dark">Draft</span></td>
                                        <td class="cell">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M12.146.292a.5.5 0 0 1 .708 0l2.854 2.854a.5.5 0 0 1 0 .708l-10.851 10.851a.5.5 0 0 1-.177.11l-5 1.5a.5.5 0 0 1-.609-.609l1.5-5a.5.5 0 0 1 .11-.177l10.851-10.851z"/>
  <path d="M2.5 13.5l-1 3.5 3.5-1L12.854 3.146 10.854 1.146 2.5 13.5z"/>
</svg>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" title="View">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-eye" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
</svg>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 1V2h11V1h-11z"/>
</svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
