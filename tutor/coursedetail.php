<?php
require_once('includes/init.php');
include 'connection.php';
// Auth check — redirect if not logged in
if (!isset($_SESSION['tutor_id'])) {
	header('Location: login.php');
	exit();
}
// ============== HANDLE STATUS TOGGLE (CHECK THIS FIRST!) ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {

	$courseId = (int) $_POST['course_id'];
	$newStatus = (int) $_POST['status']; // 1 or 0

	$query = "UPDATE course_tbl SET course_status = $newStatus WHERE course_id = $courseId";

	if (mysqli_query($conn, $query)) {
		echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
	} else {
		echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
	}
	exit; // Stop execution after handling status toggle
}

// ============== HANDLE COURSE UPDATE ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id']) && isset($_POST['title'])) {

	$id = (int) $_POST['course_id'];
	$title = mysqli_real_escape_string($conn, $_POST['title']);
	$description = mysqli_real_escape_string($conn, $_POST['description']);
	$level = mysqli_real_escape_string($conn, $_POST['level']);
	$lesson = (int) $_POST['lesson'];
	$price = (float) $_POST['price'];

	$query = "
        UPDATE course_tbl SET
            course_title       = '$title',
            course_description = '$description',
            course_level       = '$level',
            total_lesson       = $lesson,
            price              = $price
        WHERE course_id = $id
    ";

	if (mysqli_query($conn, $query)) {
		echo 'success';
	} else {
		echo mysqli_error($conn);
	}
	exit; // Stop execution after handling course update
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

	<?php include 'includes/headtag.php' ?>

	<!-- SweetAlert2 CDN -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

	<link rel="stylesheet" href="assets/css/coursedetail.css">

</head>

<body class="app">

	<?php
	include 'includes/header.php'
		?>

	<div class="app-wrapper">

		<div class="app-content pt-2 p-md-3 p-lg-4">
			<div class="container-xl">

				<div class="row g-3 mb-4 align-items-center justify-content-between">
					<div class="col-auto">
					</div>


					<!-- Main Content -->
					<div class="main-content">

						<div class="mt-1">

							<div class="card shadow-sm border-0 rounded-4">
								<div class="card-header bg-white pt-3 pb-3 border-bottom">
									<div class="row align-items-center">
										<div class="col">
											<h4 class="h3 mb-0">Course Details</h4>
										</div>
										<div class="col-auto">
											<div class="d-flex gap-2">
												<!-- Filter Dropdown Button -->
												<div class="dropdown">
													<button class="btn filter-btn dropdown-toggle" type="button"
														id="filterDropdown" data-bs-toggle="dropdown"
														aria-expanded="false">
														<i class="fa-solid fa-filter"></i>&nbsp;&nbsp;Filter by
													</button>
													<ul class="dropdown-menu" aria-labelledby="filterDropdown">
														<li><a class="dropdown-item filter-option active" href="#"
																data-filter="">Show All</a></li>
														<li><a class="dropdown-item filter-option" href="#"
																data-filter="active">Active Only</a></li>
														<li><a class="dropdown-item filter-option" href="#"
																data-filter="inactive">Inactive Only</a></li>
													</ul>
												</div>

												<!-- Add Order Button -->
												<a href="add_course.php" class="btn add-order-btn">
													<i class="fa-solid fa-plus"></i>&nbsp;&nbsp;Add New Course
												</a>

											</div>
										</div>
									</div>
								</div>



								<div class="card-body">
									<div class="table-responsive">

										<table id="datatable" enctype="multipart/form-data"
											class="table table-bordered table-hover align-middle text-center"
											style="width:100%">
											<thead class="table-light text-uppercase">
												<tr>
													<th>Sr No</th>
													<th>Course ID</th>
													<th>Profile</th>
													<th>Course Title</th>
													<th>Description</th>
													<th>Level</th>
													<th>Total Lessons</th>
													<th>Price</th>
													<th>Status</th>
													<th>Approved By</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<?php
												include 'connection.php'; // make sure your DB connection is included
												
												$logged_tutor_id = $_SESSION['tutor_id'];

												// Fetch courses with approved admin info
												$query = "
														SELECT 
															c.course_id,
															c.tutor_id,
															c.course_title,
															c.course_thumbnail,
															c.course_description,
															c.course_level,
															c.total_lesson,
															c.price,
															c.course_status,
															c.approved_by,

															a.admin_name,

															-- Tutor basic info
															t.tutor_name,
															t.tutor_email,
															t.tutor_phone,
															t.tutor_status,
															t.verification_status,

															-- Tutor profile
															tp.bio,
															tp.expertise,
															tp.education,
															tp.experience,
															tp.achievements,
															tp.country,
															tp.languages_known,
															tp.profile_pic,

															-- Tutor certificates
															td.certificate_name,
															td.institute_name

														FROM course_tbl c
														LEFT JOIN admin_tbl a 
															ON c.approved_by = a.admin_id

														LEFT JOIN tutor_tbl t 
															ON c.tutor_id = t.tutor_id

														LEFT JOIN tutor_profile_tbl tp 
															ON tp.tutor_id = t.tutor_id

														LEFT JOIN tutor_details td 
															ON td.tutor_id = t.tutor_id
														
														WHERE c.tutor_id = $logged_tutor_id
														
														ORDER BY c.course_id DESC;

													";

												$result = mysqli_query($conn, $query);

												if ($result && mysqli_num_rows($result) > 0) {
													$sr_no = 1;
													while ($row = mysqli_fetch_assoc($result)) {
														$statusChecked = ($row['course_status'] == 1) ? 'checked' : '';
														$profileImg = $row['course_thumbnail'] ? $row['course_thumbnail'] : 'default-profile.png';
														?>
														<tr>
															<td><?= $sr_no++; ?></td>
															<td><?= $row['course_id']; ?></td>
															<td>
																<img src="../assets/images/thumbnail/<?= $profileImg; ?>"
																	width="80">
															</td>
															<td><?= htmlspecialchars($row['course_title']); ?></td>
															<td><?= htmlspecialchars(substr($row['course_description'], 0, 50)) . '...'; ?>
															</td>
															<td><?= ucfirst($row['course_level']); ?></td>
															<td><?= $row['total_lesson']; ?></td>
															<td><?= number_format($row['price'], 2); ?></td>
															<td>
																<div class="d-flex justify-content-center">
																	<div class="form-check form-switch custom-switch">
																		<input class="form-check-input status-switch"
																			type="checkbox"
																			data-course-id="<?= $row['course_id']; ?>"
																			<?= $statusChecked; ?>>
																		<label class="form-check-label"></label>
																	</div>
																</div>
															</td>
															<td><?= $row['admin_name'] ?? 'N/A'; ?></td>
															<td>
																<a class="text-primary edit-btn" href="#"
																	data-id="<?= $row['course_id']; ?>"
																	data-title="<?= htmlspecialchars($row['course_title']); ?>"
																	data-desc="<?= htmlspecialchars($row['course_description']); ?>"
																	data-level="<?= $row['course_level']; ?>"
																	data-lesson="<?= $row['total_lesson']; ?>"
																	data-price="<?= $row['price']; ?>" data-bs-toggle="modal"
																	data-bs-target="#editModal">
																	<i class="fa-solid fa-pen"></i>
																</a>
																&nbsp;|&nbsp;
																<a class="text-danger delete-btn ms-2" href="#"
																	data-course-id="<?= $row['course_id']; ?>">
																	<i class="fa-solid fa-trash"></i>
																</a>

															</td>
														</tr>
														<?php
													}
												} else {
													echo '<tr><td colspan="11">No courses found.</td></tr>';
												}
												?>
											</tbody>

										</table>
									</div>
								</div>
							</div>
							<?php
							// Handle delete via GET — now handled via SweetAlert + AJAX below,
							// but keeping PHP fallback for non-JS environments
							if (isset($_GET['id']) && isset($_GET['confirm_delete'])) {
								$id = (int) $_GET['id'];
								if ($conn->query("DELETE FROM course_tbl WHERE course_id = $id")) {
									echo "<script>
												window.location.href = 'coursedetail.php?msg=success';
											</script>";
									exit;
								} else {
									header('Location: coursedetail.php?msg=error');
									exit;
								}
							}
							?>




						</div>
					</div>
				</div><!--//tab-content-->



			</div><!--//container-fluid-->
		</div><!--//app-content-->



	</div><!--//app-wrapper-->

	<!-- Edit Modal -->
	<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editModalLabel" style="color: #ffffff;"><i
							class="fa-solid fa-edit me-2"></i>Edit Order Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="editCourseForm">
						<input type="hidden" id="editCourseId">

						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Course Title</label>
								<input type="text" class="form-control" id="editCourseTitle">
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Course Level</label>
								<select class="form-select" id="editCourseLevel">
									<option value="beginner">Beginner</option>
									<option value="intermediate">Intermediate</option>
									<option value="advanced">Advanced</option>
								</select>
							</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Description</label>
							<textarea class="form-control" id="editCourseDescription" rows="3"></textarea>
						</div>

						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Total Lessons</label>
								<input type="number" class="form-control" id="editTotalLesson">
							</div>

							<div class="col-md-4 mb-3">
								<label class="form-label">Price</label>
								<input type="number" class="form-control" id="editPrice">
							</div>
						</div>
					</form>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger"
						style="background-color: #C0392B; border-color: #28a745; color: #ffffff;"
						data-bs-dismiss="modal"><i class="fa-solid fa-times me-2"></i>Cancel</button>
					<button type="button" class="btn btn-success" id="saveChanges"
						style="background-color: #28a745; border-color: #28a745; color: #ffffff;"><i
							class="fa-solid fa-save me-2"></i>Save Changes</button>
				</div>
			</div>
		</div>
	</div>


	<!-- Tutor Profile Modal -->
	<div class="modal fade" id="userProfileModal" tabindex="-1">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content profile-modal">
				<!-- Header Section -->
				<div class="profile-header">
					<div class="d-flex align-items-center gap-4">
						<img id="modalProfilePic" class="profile-avatar" alt="User">

						<div class="flex-grow-1">
							<div class="badge bg-light text-success mb-2 px-3 py-2 rounded-pill">
								<i class="fa-solid fa-circle-check me-1"></i>Verified Tutor
							</div>
							<h5 id="profileName" class="text-white mb-1"></h5>
							<div class="header-meta">
								<span><i class="fa-solid fa-location-dot me-1"></i> <span
										id="profileLocation"></span></span>
							</div>
						</div>
					</div>
					<button class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
						data-bs-dismiss="modal"></button>
				</div>

				<!-- Body Section -->
				<div class="modal-body p-4 bg-light">
					<div class="row g-4">
						<!-- Contact Information Card -->
						<div class="col-md-6">
							<div class="info-card">
								<div class="info-title">
									<i class="fa-solid fa-id-card"></i>&nbsp; Contact Information
								</div>
								<div class="info-grid">
									<div class="info-item soft mb-3">
										<label>EMAIL ADDRESS</label>
										<div id="infoEmail" class="fw-bold text-dark"></div>
									</div>
									<div class="info-item soft mb-3">
										<label>PHONE NUMBER</label>
										<div id="infoPhone" class="fw-bold text-dark"></div>
									</div>
									<div class="info-item soft mb-3">
										<label>PREFERRED LANGUAGE</label>
										<div id="infoLanguage" class="fw-bold text-dark"></div>
									</div>
								</div>
							</div>
						</div>

						<!-- Academic Profile Card -->
						<div class="col-md-6">
							<div class="info-card">
								<div class="info-title">
									<i class="fa-solid fa-graduation-cap"></i>&nbsp; Academic Profile
								</div>

								<!-- Highest Degree -->
								<div class="mb-3">
									<label class="text-muted small fw-bold mb-2 d-block">HIGHEST DEGREE</label>
									<div id="infoDegree" class="h6 fw-bold mb-0"></div>
								</div>

								<!-- Expertise -->
								<div class="mb-3">
									<label class="text-muted small fw-bold mb-2 d-block">EXPERTISE</label>
									<ul id="infoCourses" class="clean-list p-0 m-0"></ul>
								</div>

								<!-- Achievements -->
								<div class="mb-3">
									<label class="text-muted small fw-bold mb-2 d-block">ACHIEVEMENTS</label>
									<ul id="infoCertificates" class="clean-list p-0 m-0"></ul>
								</div>
							</div>
						</div>

						<!-- Certifications Card (Full Width) -->
						<div class="col-12">
							<div class="info-card">
								<div class="info-title">
									<i class="fa-solid fa-certificate"></i>&nbsp; Certifications
								</div>
								<div id="certificateList" class="row g-3"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Javascript -->
	<script src="assets/plugins/popper.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

	<?php include 'includes/script.php' ?>

	<script src="assets/js/coursedetail.js"></script>

	<script>
		$(document).ready(function () {
			// ============== CHECK FOR PHP-SIDE SUCCESS/ERROR MESSAGES ==============
			// Show SweetAlert if redirected back with ?msg= param
			<?php if (isset($_GET['msg'])): ?>
				<?php if ($_GET['msg'] === 'success'): ?>
					$(document).ready(function () {
						showSuccess('Operation completed successfully!');
					});
				<?php elseif ($_GET['msg'] === 'error'): ?>
					$(document).ready(function () {
						showError('Something went wrong. Please try again.');
					});
				<?php endif; ?>
			<?php endif; ?>

		});
	</script>



</body>

</html>