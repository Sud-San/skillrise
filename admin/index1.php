<?php
include_once("connection.php");

// Fetch Dashboard Statistics
$total_students = mysqli_num_rows(mysqli_query($conn, "SELECT user_id FROM user_tbl"));
$total_instructors = mysqli_num_rows(mysqli_query($conn, "SELECT tutor_id FROM tutor_tbl"));
$total_courses = mysqli_num_rows(mysqli_query($conn, "SELECT course_id FROM course_tbl"));

// Corrected Revenue Calculation from both student payments and tutor packages
$user_rev_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM user_payment_tbl WHERE payment_status = 1");
$user_rev = mysqli_fetch_assoc($user_rev_q)['total'] ?? 0;

$tutor_rev_q = mysqli_query($conn, "SELECT SUM(amount_paid) as total FROM tutor_package_tbl WHERE payment_status = 1");
$tutor_rev = mysqli_fetch_assoc($tutor_rev_q)['total'] ?? 0;

$total_revenue = $user_rev + $tutor_rev;

$active_enrollments = mysqli_num_rows(mysqli_query($conn, "SELECT enrollment_id FROM enrollments_tbl"));
$pending_approvals = mysqli_num_rows(mysqli_query($conn, "SELECT course_id FROM course_tbl WHERE course_status = 0"));

// Currency Symbol from settings
$currency = mysqli_fetch_assoc(mysqli_query($conn, "SELECT setting_value FROM settings_tbl WHERE setting_key='currency_symbol'"))['setting_value'] ?? '₹';

// Monthly Revenue Data for Chart
$monthly_rev = array_fill(1, 12, 0);
$rev_q = mysqli_query($conn, "SELECT MONTH(payment_date) as mn, SUM(amount) as amt FROM (
    SELECT amount, payment_date FROM user_payment_tbl WHERE payment_status = 1
    UNION ALL
    SELECT amount_paid as amount, created_at as payment_date FROM tutor_package_tbl WHERE payment_status = 1
) as combined_rev GROUP BY mn");
while ($r = mysqli_fetch_assoc($rev_q))
    $monthly_rev[$r['mn']] = (float) $r['amt'];

// Category Distribution Data
$cat_labels = [];
$cat_counts = [];
$cat_q = mysqli_query($conn, "SELECT cat.category_name, COUNT(c.course_id) as cnt FROM course_tbl c JOIN category_tbl cat ON c.category_id = cat.category_id GROUP BY cat.category_id");
while ($r = mysqli_fetch_assoc($cat_q)) {
    $cat_labels[] = $r['category_name'];
    $cat_counts[] = (int) $r['cnt'];
}
// Last 7 days students for sparkline
$student_spark = array_fill(0, 7, 0);
$std_spark_q = mysqli_query($conn, "SELECT DATEDIFF(CURDATE(), created_at) as diff, COUNT(*) as cnt FROM user_tbl WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY diff");
while ($r = mysqli_fetch_assoc($std_spark_q)) {
    $diff = (int) $r['diff'];
    if ($diff >= 0 && $diff < 7)
        $student_spark[6 - $diff] = (int) $r['cnt'];
}

// Last 7 days revenue for sparkline
$rev_spark = array_fill(0, 7, 0);
$rev_spark_q = mysqli_query($conn, "SELECT DATEDIFF(CURDATE(), payment_date) as diff, SUM(amount) as amt FROM (
    SELECT amount, payment_date FROM user_payment_tbl WHERE payment_status = 1
    UNION ALL
    SELECT amount_paid as amount, created_at as payment_date FROM tutor_package_tbl WHERE payment_status = 1
) as combined_rev WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY diff");
while ($r = mysqli_fetch_assoc($rev_spark_q)) {
    $diff = (int) $r['diff'];
    if ($diff >= 0 && $diff < 7)
        $rev_spark[6 - $diff] = (float) $r['amt'];
}

$admin_name = $_SESSION['admin_name'] ?? "Admin";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Dashboard | <?php echo $company_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script src="assets/js/config.js"></script>
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .bg-soft-primary {
            background-color: rgba(99, 102, 241, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(16, 185, 129, 0.1);
        }

        .bg-soft-warning {
            background-color: rgba(245, 158, 11, 0.1);
        }

        .text-primary {
            color: #6366f1 !important;
        }

        .text-success {
            color: #10b981 !important;
        }

        .text-warning {
            color: #f59e0b !important;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include_once("sidebar.php"); ?>
        <?php include_once("header.php"); ?>

        <div class="page-content">
            <div class="page-container">
                <!-- Welcome Hero -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-primary text-white border-0 overflow-hidden shadow"
                            style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;">
                            <div class="card-body p-4 position-relative">
                                <div class="row align-items-center">
                                    <div class="col-sm-7">
                                        <h2 class="fw-bold mb-2">Welcome Back, <?php echo $admin_name; ?>! 👋</h2>
                                        <p class="fs-16 opacity-75">Your platform is performing well today. You have
                                            <span class="fw-bold"><?php echo $pending_approvals; ?> pending course
                                                approvals</span> that need your attention.
                                        </p>
                                        <a href="manage-course.php?status=Pending"
                                            class="btn btn-light text-primary fw-bold mt-2">Take Action</a>
                                    </div>
                                    <div class="col-sm-5 text-center d-none d-sm-block">
                                        <i class="ri-rocket-2-line"
                                            style="font-size: 120px; opacity: 0.2; position: absolute; right: 40px; top: 20px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="avatar-md bg-soft-primary rounded-circle">
                                            <i class="ri-group-line fs-24 text-primary avatar-title"></i>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h3 class="text-dark mt-1" id="total_students_count">
                                            <?php echo number_format($total_students); ?>
                                        </h3>
                                        <p class="text-muted mb-1 text-truncate">Students</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <canvas id="studentSparkline" height="40"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="avatar-md bg-soft-success rounded-circle">
                                            <i class="ri-money-dollar-circle-line fs-24 text-success avatar-title"></i>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h3 class="text-dark mt-1">
                                            <?php echo $currency . number_format($total_revenue, 0); ?>
                                        </h3>
                                        <p class="text-muted mb-1 text-truncate">Revenue</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <canvas id="revenueSparkline" height="40"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="avatar-md bg-soft-warning rounded-circle">
                                            <i class="ri-book-3-line fs-24 text-warning avatar-title"></i>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h3 class="text-dark mt-1"><?php echo $pending_approvals; ?></h3>
                                        <p class="text-muted mb-1 text-truncate">Pending</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <canvas id="pendingSparkline" height="40"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="avatar-md bg-soft-info rounded-circle">
                                            <i class="ri-user-voice-line fs-24 text-info avatar-title"></i>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h3 class="text-dark mt-1"><?php echo $total_instructors; ?></h3>
                                        <p class="text-muted mb-1 text-truncate">Tutors</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress progress-soft progress-sm">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 70%"
                                            aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Revenue Analytics -->
                    <div class="col-xl-8">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                                <h4 class="header-title">Revenue Overview</h4>
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">Monthly Report</a>
                                        <a href="javascript:void(0);" class="dropdown-item">Annual Report</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div style="height: 350px;">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Distribution -->
                    <div class="col-xl-4">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                                <h4 class="header-title">Course Categories</h4>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px;">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Enrollments -->
                    <div class="col-xl-7">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                                <h4 class="header-title">Recent Enrollments</h4>
                                <a href="manage-enrollment.php" class="btn btn-sm btn-soft-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-nowrap table-centered mb-0">
                                        <thead class="bg-light bg-opacity-50">
                                            <tr>
                                                <th>Student</th>
                                                <th>Course</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $recent_e = mysqli_query($conn, "SELECT e.*, u.user_name, c.course_title FROM enrollments_tbl e 
                                                JOIN user_tbl u ON e.user_id = u.user_id 
                                                JOIN course_tbl c ON e.course_id = c.course_id 
                                                ORDER BY e.enrollment_id DESC LIMIT 5");
                                            while ($row = mysqli_fetch_assoc($recent_e)) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        <h6 class="mb-0"><?php echo $row['user_name']; ?></h6>
                                                    </td>
                                                    <td><?php echo $row['course_title']; ?></td>
                                                    <td><?php echo date('d M, Y', strtotime($row['enrollment_date'] ?? 'now')); ?>
                                                    </td>
                                                    <td><span
                                                            class="badge bg-soft-success text-success rounded-pill">Active</span>
                                                    </td>
                                                </tr>
                                            <?php }
                                            if (mysqli_num_rows($recent_e) == 0) { ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No recent enrollments</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Course Approvals -->
                    <div class="col-xl-5">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                                <h4 class="header-title">Pending Approvals</h4>
                                <span class="badge bg-soft-danger text-warning"><?php echo $pending_approvals; ?>
                                    Pending</span>
                            </div>
                            <div class="card-body">
                                <?php
                                $pending_q = mysqli_query($conn, "SELECT c.*, t.tutor_name FROM course_tbl c 
                                    JOIN tutor_tbl t ON c.tutor_id = t.tutor_id 
                                    WHERE c.course_status = 0 LIMIT 4");
                                while ($row = mysqli_fetch_assoc($pending_q)) {
                                    ?>
                                    <div
                                        class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-light bg-opacity-25 border border-dashed">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?php echo $row['course_title']; ?></h6>
                                            <small class="text-muted">By <?php echo $row['tutor_name']; ?></small>
                                        </div>
                                        <a href="manage-course.php?id=<?php echo $row['course_id']; ?>"
                                            class="btn btn-sm btn-primary rounded-pill">Approve</a>
                                    </div>
                                <?php }
                                if ($pending_approvals == 0) {
                                    ?>
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="ri-check-circle-line text-success fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold">No Pending Approvals</h6>
                                        <p class="text-muted small">All courses are up to date</p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once("footer.php"); ?>
    </div>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <script>
        // Charts Initialization
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_values($monthly_rev)); ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { borderDash: [5, 5] },
                        ticks: {
                            callback: v => '<?php echo $currency; ?>' + v.toLocaleString()
                        }
                    }
                }
            }
        });

        // Category Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_counts); ?>,
                    backgroundColor: ['#6366f1', '#a855f7', '#10c469', '#f59e0b', '#3b82f6', '#ec4899'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
            }
        });

        // Sparklines
        function spark(id, color, data) {
            new Chart(document.getElementById(id), {
                type: 'line',
                data: {
                    labels: [1, 2, 3, 4, 5, 6, 7],
                    datasets: [{
                        data: data,
                        borderColor: color,
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { display: false }, y: { display: false } }
                }
            });
        }
        spark('studentSparkline', '#6366f1', [<?php echo implode(',', $student_spark); ?>]);
        spark('revenueSparkline', '#10b981', [<?php echo implode(',', $rev_spark); ?>]);
        spark('pendingSparkline', '#f59e0b', [2, 1, 3, 2, 1, 4, 2]); 
    </script>
</body>

</html>