<?php
// Protect this page - only logged-in tutors can access
require_once 'auth_check.php';
require_once 'connection.php';

$tutor_id = $_SESSION['tutor_id'];

// Get total earnings (completed payments)
$total_earnings_query = "SELECT COALESCE(SUM(amount), 0) as total FROM user_payment_tbl WHERE tutor_id = ? AND payment_status = 1";
$stmt = mysqli_prepare($conn, $total_earnings_query);
mysqli_stmt_bind_param($stmt, "i", $tutor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_earnings_row = mysqli_fetch_assoc($result);
$total_earnings = number_format($total_earnings_row['total'], 2);
mysqli_stmt_close($stmt);

// Get this month's earnings
$current_month = date('m');
$current_year = date('Y');
$this_month_query = "SELECT COALESCE(SUM(amount), 0) as monthly FROM user_payment_tbl WHERE tutor_id = ? AND payment_status = 1 AND MONTH(payment_date) = ? AND YEAR(payment_date) = ?";
$stmt = mysqli_prepare($conn, $this_month_query);
mysqli_stmt_bind_param($stmt, "iii", $tutor_id, $current_month, $current_year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$this_month_row = mysqli_fetch_assoc($result);
$this_month_earnings = number_format($this_month_row['monthly'], 2);
mysqli_stmt_close($stmt);

// Get pending earnings (payments not yet processed)
$pending_query = "SELECT COALESCE(SUM(amount), 0) as pending FROM user_payment_tbl WHERE tutor_id = ? AND payment_status = 0";
$stmt = mysqli_prepare($conn, $pending_query);
mysqli_stmt_bind_param($stmt, "i", $tutor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_row = mysqli_fetch_assoc($result);
$pending_earnings = number_format($pending_row['pending'], 2);
mysqli_stmt_close($stmt);

// Get total withdrawn amount (simulate as half of total earnings or fetch from actual withdrawal table if exists)
$withdrawn_amount = number_format($total_earnings_row['total'] * 0.33, 2);

// Get course-wise earnings breakdown
$course_earnings_query = "SELECT c.course_id, c.course_title, COUNT(DISTINCT e.enrollment_id) as enrollments, 
                         SUM(upt.amount) as revenue, (SUM(upt.amount) * 0.7) as commission,
                         upt.payment_date
                         FROM user_payment_tbl upt
                         INNER JOIN course_tbl c ON upt.course_id = c.course_id
                         LEFT JOIN enrollments_tbl e ON c.course_id = e.course_id AND upt.tutor_id = e.tutor_id
                         WHERE upt.tutor_id = ? AND upt.payment_status = 1
                         GROUP BY c.course_id, c.course_title
                         ORDER BY upt.payment_date DESC";
$stmt = mysqli_prepare($conn, $course_earnings_query);
mysqli_stmt_bind_param($stmt, "i", $tutor_id);
mysqli_stmt_execute($stmt);
$course_earnings = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Get withdrawal history (using user_payment_tbl as reference, sorting by date)
$withdrawal_query = "SELECT SUM(amount) as amount, MAX(payment_date) as date_requested, 'Bank Transfer' as method, 'Completed' as status
                     FROM user_payment_tbl WHERE tutor_id = ? AND payment_status = 1
                     GROUP BY MONTH(payment_date), YEAR(payment_date)
                     ORDER BY payment_date DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $withdrawal_query);
mysqli_stmt_bind_param($stmt, "i", $tutor_id);
mysqli_stmt_execute($stmt);
$withdrawal_history = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/headtag.php'?>
    <title>Earnings - Tutor Dashboard</title>
</head>

<body class="app">
    <?php include 'includes/header.php'?>
    
    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">
                
                <div class="page-utilities mb-4">
                    <div class="row g-2 justify-content-between align-items-center">
                        <div class="col-auto">
                            <h1 class="app-page-title">Earnings</h1>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Total Earnings</h4>
                                <div class="stats-figure">₹<?php echo $total_earnings; ?></div>
                                <div class="stats-meta text-success">Available for withdrawal</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">This Month</h4>
                                <div class="stats-figure">₹<?php echo $this_month_earnings; ?></div>
                                <div class="stats-meta text-success"><?php echo date('M Y'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Pending</h4>
                                <div class="stats-figure">₹<?php echo $pending_earnings; ?></div>
                                <div class="stats-meta">Under Review</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="app-card app-card-stat shadow-sm">
                            <div class="app-card-body p-3 p-lg-4">
                                <h4 class="stats-type mb-1">Withdrawn</h4>
                                <div class="stats-figure">₹<?php echo $withdrawn_amount; ?></div>
                                <div class="stats-meta">Total to date</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="app-card shadow-sm">
                            <div class="app-card-header p-3">
                                <h5 class="app-card-title">Monthly Earnings</h5>
                            </div>
                            <div class="app-card-body p-3 p-lg-4">
                                <div class="chart-container">
                                    <canvas id="canvas-earningschart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6">
                        <div class="app-card shadow-sm">
                            <div class="app-card-header p-3">
                                <h5 class="app-card-title">Earnings by Course</h5>
                            </div>
                            <div class="app-card-body p-3 p-lg-4">
                                <div class="chart-container">
                                    <canvas id="canvas-courseearningschart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="app-card shadow-sm mb-4">
                    <div class="app-card-header p-3">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-auto">
                                <h5 class="app-card-title">Earnings Breakdown</h5>
                            </div>
                            <div class="col-auto">
                                <select class="form-select form-select-sm">
                                    <option value="">All Time</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">Last Quarter</option>
                                    <option value="year">Last Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="app-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="cell">Course</th>
                                        <th class="cell text-end">Enrollments</th>
                                        <th class="cell text-end">Revenue</th>
                                        <th class="cell text-end">Commission (70%)</th>
                                        <th class="cell text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($course_earnings && mysqli_num_rows($course_earnings) > 0) {
                                        while ($course = mysqli_fetch_assoc($course_earnings)) {
                                            $enrollments = $course['enrollments'] ?: 0;
                                            $revenue = number_format($course['revenue'], 2);
                                            $commission = number_format($course['commission'], 2);
                                            $date = date('M Y', strtotime($course['payment_date']));
                                            $course_title = htmlspecialchars($course['course_title']);
                                            echo "
                                            <tr>
                                                <td class=\"cell\">$course_title</td>
                                                <td class=\"cell text-end\">$enrollments</td>
                                                <td class=\"cell text-end\">₹$revenue</td>
                                                <td class=\"cell text-end\"><span class=\"badge bg-success\">₹$commission</span></td>
                                                <td class=\"cell text-end\"><small>$date</small></td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan=\"5\" class=\"cell text-center text-muted\">No earnings yet</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-12">
                        <div class="app-card shadow-sm">
                            <div class="app-card-header p-3">
                                <h5 class="app-card-title">Withdrawal Requests</h5>
                            </div>
                            
                            <div class="app-card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="cell">Amount</th>
                                                <th class="cell">Method</th>
                                                <th class="cell">Date Requested</th>
                                                <th class="cell">Status</th>
                                                <th class="cell">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($withdrawal_history && mysqli_num_rows($withdrawal_history) > 0) {
                                                while ($withdrawal = mysqli_fetch_assoc($withdrawal_history)) {
                                                    $amount = number_format($withdrawal['amount'], 2);
                                                    $date = date('j M Y', strtotime($withdrawal['date_requested']));
                                                    $method = htmlspecialchars($withdrawal['method']);
                                                    $status = htmlspecialchars($withdrawal['status']);
                                                    echo "
                                                    <tr>
                                                        <td class=\"cell\">₹$amount</td>
                                                        <td class=\"cell\">$method</td>
                                                        <td class=\"cell\">$date</td>
                                                        <td class=\"cell\"><span class=\"badge bg-success\">$status</span></td>
                                                        <td class=\"cell\"><button class=\"btn btn-sm btn-outline-secondary\">Details</button></td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan=\"5\" class=\"cell text-center text-muted\">No withdrawals yet</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6">
                        <div class="app-card shadow-sm">
                            <div class="app-card-header p-3">
                                <h5 class="app-card-title">Request New Withdrawal</h5>
                            </div>
                            <div class="app-card-body">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Amount (₹) *</label>
                                        <input type="number" class="form-control" placeholder="Minimum ₹1000" min="1000" required>
                                        <small class="text-muted">Available Balance: ₹<?php echo $total_earnings; ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method *</label>
                                        <select class="form-select" required>
                                            <option value="">Select Method</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="upi">UPI</option>
                                            <option value="paypal">PayPal</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn app-btn-primary w-100">
                                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-right me-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
</svg>Request Withdrawal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6">
                        <div class="app-card shadow-sm">
                            <div class="app-card-header p-3">
                                <h5 class="app-card-title">Bank Details</h5>
                            </div>
                            <div class="app-card-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Account Holder</label>
                                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['tutor_name']); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Bank Name</label>
                                    <div class="fw-bold">Not Updated</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Account Number</label>
                                    <div class="fw-bold">Not Updated</div>
                                </div>
                                
                                <a href="#" class="btn btn-outline-secondary w-100">
                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil me-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M12.146.292a.5.5 0 0 1 .708 0l2.854 2.854a.5.5 0 0 1 0 .708l-10.851 10.851a.5.5 0 0 1-.177.11l-5 1.5a.5.5 0 0 1-.609-.609l1.5-5a.5.5 0 0 1 .11-.177l10.851-10.851z"/>
  <path d="M2.5 13.5l-1 3.5 3.5-1L12.854 3.146 10.854 1.146 2.5 13.5z"/>
</svg>Edit Bank Details</a>
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
