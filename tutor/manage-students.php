<?php
// Protect this page - only logged-in tutors can access
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/headtag.php'?>
    <title>Student Management - Tutor Dashboard</title>
</head>

<body class="app">
    <?php include 'includes/header.php'?>
    
    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">
                
                <div class="page-utilities mb-4">
                    <div class="row g-2 justify-content-between align-items-center">
                        <div class="col-auto">
                            <h1 class="app-page-title">Student Management</h1>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Total Students</h4>
                                <div class="stats-figure">342</div>
                                <div class="stats-meta text-success">
                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-up" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/>
</svg> 12% this month</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Active Now</h4>
                                <div class="stats-figure">28</div>
                                <div class="stats-meta">Currently Learning</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Avg. Progress</h4>
                                <div class="stats-figure">54%</div>
                                <div class="stats-meta">Across Courses</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Completed</h4>
                                <div class="stats-figure">67</div>
                                <div class="stats-meta">Certificates Issued</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="app-card shadow-sm">
                    <div class="app-card-header p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-auto flex-grow-1">
                                <input type="search" class="form-control form-control-sm" placeholder="Search students...">
                            </div>
                            <div class="col-auto">
                                <select class="form-select form-select-sm">
                                    <option value="">All Courses</option>
                                    <option value="1">Advanced Python</option>
                                    <option value="2">Web Development 101</option>
                                    <option value="3">Data Science Basics</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="app-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="cell">Student Name</th>
                                        <th class="cell">Email</th>
                                        <th class="cell">Course</th>
                                        <th class="cell">Progress</th>
                                        <th class="cell">Joined</th>
                                        <th class="cell">Last Active</th>
                                        <th class="cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <img class="avatar-img" src="https://via.placeholder.com/48x48?text=AS" alt="Avatar">
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Alex Smith</div>
                                                    <div class="text-muted small">Premium Member</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">alex@example.com</td>
                                        <td class="cell">Advanced Python</td>
                                        <td class="cell">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 75%">75%</div>
                                            </div>
                                        </td>
                                        <td class="cell"><small>2 Jan 2026</small></td>
                                        <td class="cell"><span class="badge bg-info">2 days ago</span></td>
                                        <td class="cell">
                                            <button class="btn btn-sm btn-outline-primary">Message</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <img class="avatar-img" src="https://via.placeholder.com/48x48?text=JD" alt="Avatar">
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Jane Doe</div>
                                                    <div class="text-muted small">Standard Member</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">jane@example.com</td>
                                        <td class="cell">Web Development 101</td>
                                        <td class="cell">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 45%">45%</div>
                                            </div>
                                        </td>
                                        <td class="cell"><small>15 Jan 2026</small></td>
                                        <td class="cell"><span class="badge bg-info">5 hours ago</span></td>
                                        <td class="cell">
                                            <button class="btn btn-sm btn-outline-primary">Message</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <img class="avatar-img" src="https://via.placeholder.com/48x48?text=RJ" alt="Avatar">
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Robert Johnson</div>
                                                    <div class="text-muted small">Premium Member</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">robert@example.com</td>
                                        <td class="cell">Data Science Basics</td>
                                        <td class="cell">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: 25%">25%</div>
                                            </div>
                                        </td>
                                        <td class="cell"><small>20 Jan 2026</small></td>
                                        <td class="cell"><span class="badge bg-danger">1 week ago</span></td>
                                        <td class="cell">
                                            <button class="btn btn-sm btn-outline-primary">Message</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cell">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <img class="avatar-img" src="https://via.placeholder.com/48x48?text=MP" alt="Avatar">
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Maria Patel</div>
                                                    <div class="text-muted small">Standard Member</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cell">maria@example.com</td>
                                        <td class="cell">Advanced Python</td>
                                        <td class="cell">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 88%">88%</div>
                                            </div>
                                        </td>
                                        <td class="cell"><small>8 Dec 2025</small></td>
                                        <td class="cell"><span class="badge bg-success">Just now</span></td>
                                        <td class="cell">
                                            <button class="btn btn-sm btn-outline-primary">Message</button>
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
