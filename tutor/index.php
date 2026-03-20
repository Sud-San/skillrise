<?php
// Protect this page - only logged-in tutors can access
require_once 'auth_check.php';
require_once 'connection.php';

$tutor_id = $_SESSION['tutor_id'];

// 1. Fetch Summary Stats
// Total Courses
$total_courses_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM course_tbl WHERE tutor_id = $tutor_id");
$total_courses = mysqli_fetch_assoc($total_courses_res)['count'];

// Total Students Enrolled (Unique)
$total_students_res = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as count FROM enrollments_tbl WHERE tutor_id = $tutor_id");
$total_students = mysqli_fetch_assoc($total_students_res)['count'];

// Total Earnings (Paid)
$total_earnings_res = mysqli_query($conn, "SELECT SUM(amount) as sum FROM user_payment_tbl WHERE tutor_id = $tutor_id AND payment_status = 1");
$total_earnings = mysqli_fetch_assoc($total_earnings_res)['sum'] ?? 0;

// Pending Reviews
$pending_reviews_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM feedback_tbl WHERE tutor_id = $tutor_id AND status = 0");
$pending_reviews = mysqli_fetch_assoc($pending_reviews_res)['count'];

// Active Courses
$active_courses_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM course_tbl WHERE tutor_id = $tutor_id AND course_status = 1");
$active_courses = mysqli_fetch_assoc($active_courses_res)['count'];

// 2. Data for Charts
// Monthly Enrollments (Last 6 Months)
$enrollment_chart_data = [];
for ($i = 5; $i >= 0; $i--) {
	$month = date('Y-m', strtotime("-$i months"));
	$month_name = date('M', strtotime("-$i months"));
	$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments_tbl WHERE tutor_id = $tutor_id AND DATE_FORMAT(enrolled_at, '%Y-%m') = '$month'");
	$count = mysqli_fetch_assoc($res)['count'];
	$enrollment_chart_data[$month_name] = $count;
}

// Revenue Growth (Last 6 Months)
$revenue_chart_data = [];
for ($i = 5; $i >= 0; $i--) {
	$month = date('Y-m', strtotime("-$i months"));
	$month_name = date('M', strtotime("-$i months"));
	$res = mysqli_query($conn, "SELECT SUM(amount) as sum FROM user_payment_tbl WHERE tutor_id = $tutor_id AND payment_status = 1 AND DATE_FORMAT(payment_date, '%Y-%m') = '$month'");
	$sum = mysqli_fetch_assoc($res)['sum'] ?? 0;
	$revenue_chart_data[$month_name] = $sum;
}

// Course Popularity (Top 5)
$popularity_labels = [];
$popularity_counts = [];
$popularity_res = mysqli_query($conn, "SELECT c.course_title, COUNT(e.enrollment_id) as enrolled FROM course_tbl c LEFT JOIN enrollments_tbl e ON c.course_id = e.course_id WHERE c.tutor_id = $tutor_id GROUP BY c.course_id ORDER BY enrolled DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($popularity_res)) {
	$popularity_labels[] = $row['course_title'];
	$popularity_counts[] = $row['enrolled'];
}

// 3. Recent Activity Data
// Top Courses by Enrollment
$top_courses = [];
$top_courses_res = mysqli_query($conn, "SELECT c.course_title, COUNT(e.enrollment_id) as enrolled FROM course_tbl c LEFT JOIN enrollments_tbl e ON c.course_id = e.course_id WHERE c.tutor_id = $tutor_id GROUP BY c.course_id ORDER BY enrolled DESC LIMIT 4");
while ($row = mysqli_fetch_assoc($top_courses_res)) {
	$top_courses[] = $row;
}

// Full Course List (Management)
$my_courses = [];
$my_courses_res = mysqli_query($conn, "SELECT c.*, COUNT(e.enrollment_id) as enrolled_count FROM course_tbl c LEFT JOIN enrollments_tbl e ON c.course_id = e.course_id WHERE c.tutor_id = $tutor_id GROUP BY c.course_id ORDER BY c.course_id DESC");
while ($row = mysqli_fetch_assoc($my_courses_res)) {
	$my_courses[] = $row;
}

// Recent Enrollments (Detailed)
$recent_enrollments = [];
$recent_enrollments_res = mysqli_query($conn, "SELECT u.user_name, u.profile_pic, u.user_email, c.course_title, e.enrolled_at FROM enrollments_tbl e JOIN user_tbl u ON e.user_id = u.user_id JOIN course_tbl c ON e.course_id = c.course_id WHERE e.tutor_id = $tutor_id ORDER BY e.enrolled_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($recent_enrollments_res)) {
	$recent_enrollments[] = $row;
}

// Recent Messages (Feedback)
$recent_messages = [];
$recent_messages_res = mysqli_query($conn, "SELECT u.user_name, f.message, f.created_at FROM feedback_tbl f JOIN user_tbl u ON f.user_id = u.user_id WHERE f.tutor_id = $tutor_id ORDER BY f.created_at DESC LIMIT 3");
while ($row = mysqli_fetch_assoc($recent_messages_res)) {
	$recent_messages[] = $row;
}

// Calculate Monthly Earnings for Overview
$current_month = date('Y-m');
$monthly_earnings_res = mysqli_query($conn, "SELECT SUM(amount) as sum FROM user_payment_tbl WHERE tutor_id = $tutor_id AND payment_status = 1 AND DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'");
$monthly_earnings = mysqli_fetch_assoc($monthly_earnings_res)['sum'] ?? 0;

// Assignment Statistics
$total_assignments_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM assignment_tbl a JOIN course_tbl c ON a.course_id = c.course_id WHERE c.tutor_id = $tutor_id");
$total_assignments = mysqli_fetch_assoc($total_assignments_res)['count'];

$active_assignments_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM assignment_tbl a JOIN course_tbl c ON a.course_id = c.course_id WHERE c.tutor_id = $tutor_id AND a.status = 1");
$active_assignments = mysqli_fetch_assoc($active_assignments_res)['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<?php include 'includes/headtag.php' ?>
</head>

<body class="app">
	<?php include 'includes/header.php' ?>

	<div class="app-wrapper">

		<div class="app-content pt-3 p-md-3 p-lg-4">
			<div class="container-xl">

				<div class="page-utilities mb-4 d-print-none">
					<div class="row g-2 justify-content-between align-items-center">
						<div class="col-auto">
							<h1 class="app-page-title">Dashboard Overview</h1>
						</div>
						<div class="col-auto">
							<a class="btn btn-success" href="add_course.php">
								<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus-circle me-2"
									fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd"
										d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
									<path fill-rule="evenodd"
										d="M7.5 8a.5.5 0 0 1 1 0v2.5h2.5a.5.5 0 0 1 0 1h-2.5V14a.5.5 0 0 1-1 0v-2.5H5a.5.5 0 0 1 0-1h2.5V8z" />
								</svg>Add Course</a>
						</div>
					</div>
				</div><!--//page-utilities-->

				<!-- Summary Cards -->
				<div class="row g-4 mb-4">
					<div class="col-12 col-sm-6 col-lg-3">
						<div class="app-card app-card-stat shadow-sm h-100">
							<div class="app-card-body p-3 p-lg-4">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h4 class="stats-type mb-0">Total Courses</h4>
									<svg width="1.5em" height="1.5em" viewBox="0 0 16 16"
										class="bi bi-book text-primary" fill="currentColor"
										xmlns="http://www.w3.org/2000/svg">
										<path
											d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zm9 0A1.5 1.5 0 0 1 11.5 1h3A1.5 1.5 0 0 1 16 2.5v3A1.5 1.5 0 0 1 14.5 7h-3A1.5 1.5 0 0 1 10 5.5v-3z" />
										<path
											d="M2 7.5a.5.5 0 0 1 1 0v1A1.5 1.5 0 0 1 2.5 10h-1A1.5 1.5 0 0 1 0 8.5v-1a.5.5 0 0 1 1 0v1A.5.5 0 0 0 1.5 9h1a.5.5 0 0 0 .5-.5v-1zm9 0a.5.5 0 0 1 1 0v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1a.5.5 0 0 1 1 0v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1z" />
										<path
											d="M2 4.5a.5.5 0 0 1 1 0v1a.5.5 0 0 1-1 0v-1zm9 0a.5.5 0 0 1 1 0v1a.5.5 0 0 1-1 0v-1z" />
									</svg>
								</div>
								<div class="stats-figure"><?php echo $total_courses; ?></div>
								<div class="stats-meta text-success">
									<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-up"
										fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd"
											d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z" />
									</svg> <?php echo $active_courses; ?> Active
								</div>
							</div><!--//app-card-body-->
							<a class="app-card-link-mask" href="coursedetail.php"></a>
						</div><!--//app-card-->
					</div><!--//col-->

					<div class="col-12 col-sm-6 col-lg-3">
						<div class="app-card app-card-stat shadow-sm h-100">
							<div class="app-card-body p-3 p-lg-4">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h4 class="stats-type mb-0">Total Students</h4>
									<svg width="1.5em" height="1.5em" viewBox="0 0 16 16"
										class="bi bi-people text-success" fill="currentColor"
										xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd"
											d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.995-1.679a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0zM7.588 4a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2a.5.5 0 0 1 .5-.5zm5.396.5a1.5 1.5 0 0 0-3 0v2a1.5 1.5 0 0 0 3 0v-2z" />
									</svg>
								</div>
								<div class="stats-figure"><?php echo $total_students; ?></div>
								<div class="stats-meta text-success">
									Total Enrolled
								</div>
							</div><!--//app-card-body-->
							<a class="app-card-link-mask" href="studentdetail.php"></a>
						</div><!--//app-card-->
					</div><!--//col-->
					<div class="col-12 col-sm-6 col-lg-3">
						<div class="app-card app-card-stat shadow-sm h-100">
							<div class="app-card-body p-3 p-lg-4">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h4 class="stats-type mb-0">Total Earnings</h4>
									<svg width="1.5em" height="1.5em" viewBox="0 0 16 16"
										class="bi bi-cash-coin text-warning" fill="currentColor"
										xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd"
											d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z" />
										<path
											d="M9.438 11.944c.047.020.1.033.157.033a.75.75 0 1 0 0-1.5.75.75 0 0 1 0-1.5.75.75 0 1 0 0-1.5z" />
										<path fill-rule="evenodd"
											d="M8 3a5 5 0 0 0-4.546 2.916A.75.75 0 0 0 3.627 6.47c.883-1.346 2.246-2.457 3.873-2.457a4 4 0 0 1 0 8A3.973 3.973 0 0 1 5 10.757a.75.75 0 0 0-1.268.628A5.373 5.373 0 0 0 8 11a5 5 0 0 0 0-10z" />
									</svg>
								</div>
								<div class="stats-figure">₹<?php echo number_format($total_earnings, 0); ?></div>
								<div class="stats-meta text-success">
									Overall Growth
								</div>
							</div><!--//app-card-body-->
							<a class="app-card-link-mask" href="user-payment.php"></a>
						</div><!--//app-card-->
					</div><!--//col-->
					<div class="col-12 col-sm-6 col-lg-3">
						<div class="app-card app-card-stat shadow-sm h-100">
							<div class="app-card-body p-3 p-lg-4">
								<div class="d-flex align-items-center justify-content-between mb-2">
									<h4 class="stats-type mb-0">Pending Reviews</h4>
									<svg width="1.5em" height="1.5em" viewBox="0 0 16 16"
										class="bi bi-chat-dots text-danger" fill="currentColor"
										xmlns="http://www.w3.org/2000/svg">
										<path
											d="M5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
										<path
											d="M2.165 15.803l.02-.004c1.83-.363 2.948-.842 3.468-1.105A9.06 9.06 0 0 0 8 15c4.418 0 8-1.79 8-4s-3.582-4-8-4-8 1.79-8 4c0 .393.049.78.14 1.15l.023.04C.08 13.75 1.068 14.381 2.165 15.803zm0-1.066.002-.001a.5.5 0 0 0 .531.478c.582-.122 1.414-.289 2.265-.553A15.4 15.4 0 1 1 8 13c3.314 0 6.130-1.133 6.823-2.664.04-.066.076-.132.11-.2a.5.5 0 0 0-.531-.478c-.582.122-1.414.289-2.265.553A15.4 15.4 0 0 0 8 11c-3.314 0-6.130 1.133-6.823 2.664a13.07 13.07 0 0 1-.11.2z" />
										<path
											d="M14.208 15s.974.294 1.6-.525c.158-.193.288-.409.372-.64l.064-.159c.097-.274.145-.539.145-.804 0-1.664-2.966-3.408-6.364-3.408s-6.364 1.744-6.364 3.408c0 .265.048.53.145.804l.064.159c.084.231.214.447.372.64.626.819 1.6.525 1.6.525.628-1.124 1.851-1.837 3.256-1.837s2.628.713 3.256 1.837z" />
									</svg>
								</div>
								<div class="stats-figure"><?php echo $pending_reviews; ?></div>
								<div class="stats-meta text-danger">Action Required</div>
							</div><!--//app-card-body-->
							<a class="app-card-link-mask" href="feedback.php"></a>
						</div><!--//app-card-->
					</div><!--//col-->
				</div><!--//row-->
				<!-- Analytics Section -->
				<h2 class="app-page-title-sub mt-4 mb-3">Analytics</h2>
				<div class="row g-4 mb-4">
					<div class="col-12 col-lg-6">
						<div class="app-card app-card-chart h-100 shadow-sm">
							<div class="app-card-header p-3">
								<div class="row justify-content-between align-items-center">
									<div class="col-auto">
										<h4 class="app-card-title">Student Enrollments</h4>
									</div><!--//col-->
									<div class="col-auto">
										<div class="card-header-action">

										</div><!--//card-header-actions-->
									</div><!--//col-->
								</div><!--//row-->
							</div><!--//app-card-header-->
							<div class="app-card-body p-3 p-lg-4">
								<div class="chart-container">
									<canvas id="canvas-enrollchart"></canvas>
								</div>
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->
					<div class="col-12 col-lg-6">
						<div class="app-card app-card-chart h-100 shadow-sm">
							<div class="app-card-header p-3">
								<div class="row justify-content-between align-items-center">
									<div class="col-auto">
										<h4 class="app-card-title">Revenue Growth</h4>
									</div><!--//col-->
									<div class="col-auto">
										<div class="card-header-action">

										</div><!--//card-header-actions-->
									</div><!--//col-->
								</div><!--//row-->
							</div><!--//app-card-header-->
							<div class="app-card-body p-3 p-lg-4">
								<div class="chart-container">
									<canvas id="canvas-revenuechart"></canvas>
								</div>
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->
				</div><!--//row-->

				<div class="row g-4 mb-4">
					<div class="col-12 col-lg-6">
						<div class="app-card app-card-chart h-100 shadow-sm">
							<div class="app-card-header p-3">
								<h4 class="app-card-title">Course Popularity</h4>
							</div><!--//app-card-header-->
							<div class="app-card-body p-3 p-lg-4">
								<div class="chart-container">
									<canvas id="canvas-coursepopularity"></canvas>
								</div>
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->

					<div class="col-12 col-lg-6">
						<div class="app-card app-card-stats-table h-100 shadow-sm">
							<div class="app-card-header p-3">
								<h4 class="app-card-title">Top Courses by Enrollment</h4>
							</div><!--//app-card-header-->
							<div class="app-card-body p-3 p-lg-4">
								<div class="table-responsive">
									<table class="table table-sm table-borderless">
										<thead>
											<tr>
												<th class="meta">Course</th>
												<th class="meta stat-cell text-end">Students</th>
											</tr>
										</thead>
										<tbody>
											<?php if (empty($top_courses)): ?>
												<tr>
													<td colspan="2" class="text-center text-muted">No data available</td>
												</tr>
											<?php else: ?>
												<?php foreach ($top_courses as $course): ?>
													<tr>
														<td>
															<div class="d-flex align-items-center">
																<svg width="1em" height="1em" viewBox="0 0 16 16"
																	class="bi bi-star-fill text-warning me-2"
																	fill="currentColor" xmlns="http://www.w3.org/2000/svg">
																	<path
																		d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.283.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
																</svg>
																<span><?php echo htmlspecialchars($course['course_title']); ?></span>
															</div>
														</td>
														<td class="stat-cell text-end"><?php echo $course['enrolled']; ?></td>
													</tr>
												<?php endforeach; ?>
											<?php endif; ?>
										</tbody>
									</table>
								</div><!--//table-responsive-->
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->
				</div><!--//row-->
				<!-- Course Management Section -->
				<h2 class="app-page-title-sub mt-5 mb-3">Course Management</h2>
				<div class="row g-4 mb-4">
					<div class="col-12">
						<div class="app-card shadow-sm">
							<div class="app-card-header p-3">
								<div class="row justify-content-between align-items-center">
									<div class="col-auto">
										<h4 class="app-card-title">My Courses</h4>
									</div><!--//col-->
									<div class="col-auto">
										<a class="btn btn-sm btn-success" href="add-course.php">
											<svg width="1em" height="1em" viewBox="0 0 16 16"
												class="bi bi-plus-circle me-1" fill="currentColor"
												xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
												<path fill-rule="evenodd"
													d="M7.5 8a.5.5 0 0 1 1 0v2.5h2.5a.5.5 0 0 1 0 1h-2.5V14a.5.5 0 0 1-1 0v-2.5H5a.5.5 0 0 1 0-1h2.5V8z" />
											</svg>Add New</a>
									</div><!--//col-->
								</div><!--//row-->
							</div><!--//app-card-header-->
							<div class="app-card-body p-0">
								<div class="table-responsive">
									<table class="table table-hover mb-0">
										<thead class="table-light">
											<tr>
												<th class="cell">Course Name</th>
												<th class="cell">Students</th>
												<th class="cell">Rating</th>
												<th class="cell">Price</th>
												<th class="cell">Status</th>
												<!-- <th class="cell">Actions</th> -->
											</tr>
										</thead>
										<tbody>
											<?php if (empty($my_courses)): ?>
												<tr>
													<td colspan="6" class="text-center text-muted">No courses found. <a
															href="add_course.php">Create one now</a>.</td>
												</tr>
											<?php else: ?>
												<?php foreach ($my_courses as $course): ?>
													<tr>
														<td class="cell">
															<div class="d-flex align-items-center">
																<svg width="1.5em" height="1.5em" viewBox="0 0 16 16"
																	class="bi bi-play-circle me-2 <?php echo $course['course_status'] == 1 ? 'text-primary' : 'text-warning'; ?>"
																	fill="currentColor" xmlns="http://www.w3.org/2000/svg">
																	<path fill-rule="evenodd"
																		d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
																	<path fill-rule="evenodd"
																		d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z" />
																</svg>
																<div>
																	<div class="fw-bold">
																		<?php echo htmlspecialchars($course['course_title']); ?>
																	</div>
																	<div class="text-muted small">
																		<?php echo htmlspecialchars($course['course_level'] ?? 'All'); ?>
																		Level
																	</div>
																</div>
															</div>
														</td>
														<td class="cell"><?php echo $course['enrolled_count']; ?></td>
														<td class="cell"><span class="badge bg-success">Dynamic</span></td>
														<td class="cell">
															₹<?php echo number_format($course['price'], 0); ?></td>
														<td class="cell">
															<span
																class="badge <?php echo $course['course_status'] == 1 ? 'bg-success' : 'bg-warning text-dark'; ?>">
																<?php echo $course['course_status'] == 1 ? 'Published' : 'Draft'; ?>
															</span>
														</td>
														<!-- <td class="cell">
															<div class="btn-group" role="group">
																<a href="edit_course.php?id=<?php echo $course['course_id']; ?>"
																	class="btn btn-sm btn-outline-secondary" title="Edit">
																	<svg width="1em" height="1em" viewBox="0 0 16 16"
																		class="bi bi-pencil" fill="currentColor">
																		<path
																			d="M12.146.292a.5.5 0 0 1 .708 0l2.854 2.854a.5.5 0 0 1 0 .708l-10.851 10.851a.5.5 0 0 1-.177.11l-5 1.5a.5.5 0 0 1-.609-.609l1.5-5a.5.5 0 0 1 .11-.177l10.851-10.851z" />
																		<path
																			d="M2.5 13.5l-1 3.5 3.5-1L12.854 3.146 10.854 1.146 2.5 13.5z" />
																	</svg>
																</a>
																<a href="coursedetail.php?id=<?php echo $course['course_id']; ?>"
																	class="btn btn-sm btn-outline-secondary" title="View">
																	<svg width="1em" height="1em" viewBox="0 0 16 16"
																		class="bi bi-eye" fill="currentColor">
																		<path
																			d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
																		<path
																			d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
																	</svg>
																</a>
															</div>
														</td> -->
													</tr>
												<?php endforeach; ?>
											<?php endif; ?>
										</tbody>
									</table>
								</div><!--//table-responsive-->
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->
				</div><!--//row-->

				<!-- Student Management Section -->
				<h2 class="app-page-title-sub mt-5 mb-3">Student Management</h2>
				<div class="row g-4 mb-4">
					<div class="col-12">
						<div class="app-card shadow-sm">
							<div class="app-card-header p-3">
								<h4 class="app-card-title">Recent Enrollments</h4>
							</div><!--//app-card-header-->
							<div class="app-card-body p-0">
								<div class="table-responsive">
									<table class="table table-hover mb-0">
										<thead class="table-light">
											<tr>
												<th class="cell">Student Name</th>
												<th class="cell">Email</th>
												<th class="cell">Course</th>
												<th class="cell">Progress</th>
												<th class="cell">Last Active</th>
												<!-- <th class="cell">Actions</th> -->
											</tr>
										</thead>
										<tbody>
											<?php if (empty($recent_enrollments)): ?>
												<tr>
													<td colspan="6" class="text-center text-muted">No students enrolled yet.
													</td>
												</tr>
											<?php else: ?>
												<?php foreach ($recent_enrollments as $enrollment): ?>
													<tr>
														<td class="cell">
															<div class="d-flex align-items-center">
																<div class="avatar avatar-sm me-2">
																	<img class="avatar-img" width="50px" height="50px"
																		src="<?php echo "../" . $user_profile_path . $enrollment['profile_pic']; ?>"
																		alt="<?php echo $enrollment['user_name'] ?>">
																</div>
																<div>
																	<div class="fw-bold">
																		<?php echo htmlspecialchars($enrollment['user_name']); ?>
																	</div>
																</div>
															</div>
														</td>
														<td class="cell">
															<?php echo htmlspecialchars($enrollment['user_email']); ?>
														</td>
														<td class="cell">
															<?php echo htmlspecialchars($enrollment['course_title']); ?>
														</td>
														<td class="cell">
															<div class="progress" style="height: 10px;">
																<div class="progress-bar bg-success" role="progressbar"
																	style="width: 0%">0%</div>
															</div>
														</td>
														<td class="cell"><span
																class="badge bg-info"><?php echo date('d M Y', strtotime($enrollment['enrolled_at'])); ?></span>
														</td>
														<!-- <td class="cell">
															<button class="btn btn-sm btn-outline-success">Message</button>
														</td> -->
													</tr>
												<?php endforeach; ?>
											<?php endif; ?>
										</tbody>
									</table>
								</div><!--//table-responsive-->
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->
				</div><!--//row-->

				<!-- Earnings & Messages Section -->
				<div class="row g-4 mb-4">
					<div class="col-12 col-lg-6">
						<div class="app-card shadow-sm">
							<div class="app-card-header p-3">
								<div class="row justify-content-between align-items-center">
									<div class="col-auto">
										<h4 class="app-card-title">Assignment Overview</h4>
									</div><!--//col-->
									<div class="col-auto">
										<a href="assignmentdetail.php" class="btn btn-sm btn-outline-success">View
											Details</a>
									</div><!--//col-->
								</div><!--//row-->
							</div><!--//app-card-header-->
							<div class="app-card-body p-3 p-lg-4">
								<div class="row g-3 mb-4">
									<div class="col-12 col-sm-6">
										<div class="d-flex align-items-center">
											<svg width="2em" height="2em" viewBox="0 0 16 16"
												class="bi bi-file-earmark-text me-3 text-success" fill="currentColor"
												xmlns="http://www.w3.org/2000/svg">
												<path
													d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z" />
												<path
													d="M9.5 0c.062 0 .123.014.179.04a1 1 0 0 1 .173.102l3.707 3.707a1 1 0 0 1 .102.173A1 1 0 0 1 14 4.5V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
												<path
													d="M5 4h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm0 2h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm0 2h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm0 2h4a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1z" />
											</svg>
											<div>
												<div class="text-muted small">Total Assignments</div>
												<div class="fw-bold fs-5">
													<?php echo $total_assignments; ?>
												</div>
											</div>
										</div>
									</div>
									<div class="col-12 col-sm-6">
										<div class="d-flex align-items-center">
											<svg width="2em" height="2em" viewBox="0 0 16 16"
												class="bi bi-check2-circle me-3 text-primary" fill="currentColor"
												xmlns="http://www.w3.org/2000/svg">
												<path
													d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z" />
												<path
													d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z" />
											</svg>
											<div>
												<div class="text-muted small">Active Assignments</div>
												<div class="fw-bold fs-5">
													<?php echo $active_assignments; ?>
												</div>
											</div>
										</div>
									</div>
								</div>
								<a href="add_assignment.php" class="btn btn-success w-100">Add New Assignment</a>
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->

					<div class="col-12 col-lg-6">
						<div class="app-card shadow-sm">
							<div class="app-card-header p-3">
								<div class="row justify-content-between align-items-center">
									<div class="col-auto">
										<h4 class="app-card-title">Recent Messages</h4>
									</div><!--//col-->
									<div class="col-auto">
										<a href="feedback.php" class="btn btn-sm btn-outline-success">View All</a>
									</div><!--//col-->
								</div><!--//row-->
							</div><!--//app-card-header-->
							<div class="app-card-body p-0">
								<div class="list-group list-group-flush">
									<?php if (empty($recent_messages)): ?>
										<div class="list-group-item p-3 text-center text-muted">No recent messages</div>
									<?php else: ?>
										<?php foreach ($recent_messages as $msg): ?>
											<div class="list-group-item p-3 border-bottom">
												<div class="d-flex justify-content-between align-items-start">
													<div class="flex-grow-1">
														<h5 class="mb-1"><?php echo htmlspecialchars($msg['user_name']); ?></h5>
														<p class="mb-0 text-muted small">
															<?php echo htmlspecialchars(substr($msg['message'], 0, 80)) . (strlen($msg['message']) > 80 ? '...' : ''); ?>
														</p>
														<small
															class="text-muted"><?php echo date('d M, h:i A', strtotime($msg['created_at'])); ?></small>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</div><!--//app-card-body-->
						</div><!--//app-card-->
					</div><!--//col-->
				</div><!--//row-->

			</div><!--//container-fluid-->
		</div><!--//app-content-->



	</div><!--//app-wrapper-->
	<!-- Javascript -->
	<script src="assets/plugins/popper.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>



	<script>
		// Pass PHP data to JavaScript for charts
		window.chartData = {
			enrollments: {
				labels: <?php echo json_encode(array_keys($enrollment_chart_data)); ?>,
				data: <?php echo json_encode(array_values($enrollment_chart_data)); ?>
			},
			revenue: {
				labels: <?php echo json_encode(array_keys($revenue_chart_data)); ?>,
				data: <?php echo json_encode(array_values($revenue_chart_data)); ?>
			},
			popularity: {
				labels: <?php echo json_encode($popularity_labels); ?>,
				data: <?php echo json_encode($popularity_counts); ?>
			}
		};
	</script>
	<?php include 'includes/script.php' ?>

</body>

</html>