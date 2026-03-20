<?php
require_once('includes/init.php');
include 'connection.php';

// ===== HANDLE PAYMENT STATUS TOGGLE (AJAX) =====
if (
	$_SERVER['REQUEST_METHOD'] === 'POST'
	&& ($_POST['action'] ?? '') === 'toggle_payment_status'
) {

	header('Content-Type: application/json');

	$purchaseId = (int) ($_POST['purchase_id'] ?? 0);
	$status = (int) ($_POST['status'] ?? 0);

	if ($purchaseId <= 0) {
		echo json_encode(['success' => false, 'message' => 'Invalid ID']);
		exit;
	}

	$stmt = mysqli_prepare(
		$conn,
		"UPDATE tutor_package_tbl SET status = ? WHERE purchase_id = ?"
	);

	if (!$stmt) {
		echo json_encode(['success' => false, 'message' => 'DB error']);
		exit;
	}

	mysqli_stmt_bind_param($stmt, "ii", $status, $purchaseId);
	mysqli_stmt_execute($stmt);

	echo json_encode(['success' => true, 'message' => 'Status updated']);

	mysqli_stmt_close($stmt);
	exit;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>

	<?php include 'includes/headtag.php' ?>

	<style>
		/* Card hover effect */
		.card:hover {
			transform: translateY(-2px);
			transition: all 0.3s ease;
		}

		/* Table header bold and uppercase */
		#datatable thead th {
			font-weight: 600;
			letter-spacing: 0.5px;
		}

		/* Center the actions column */
		#datatable td:last-child {
			width: 120px;
		}

		/* Modal styling */
		.modal-header {
			background: #28a745;
			color: white;
		}

		.modal-header .btn-close {
			filter: brightness(0) invert(1);
		}

		.form-label {
			font-weight: 600;
			color: #495057;
		}

		/* Dynamic validation styles */
		.error-border {
			border-color: #dc3545 !important;
			box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
		}

		.success-border {
			border-color: #28a745 !important;
			box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
		}

		.error-message {
			color: #dc3545;
			font-size: 0.875rem;
			margin-top: 0.25rem;
			display: none;
		}

		.error-message.show {
			display: block;
		}

		/* Filter Button Styles */
		.filter-btn {
			background-color: #f8f9fa;
			border: 1px solid #dee2e6;
			color: #495057;
			padding: 8px 20px;
			border-radius: 8px;
			font-weight: 500;
			transition: all 0.3s ease;
		}

		.filter-btn:hover {
			background-color: #e9ecef;
			border-color: #adb5bd;
		}

		.filter-btn i {
			margin-right: 8px;
		}

		/* Add Order Button */
		.add-order-btn {
			background-color: #28a745;
			border: none;
			color: white;
			padding: 10px 24px;
			border-radius: 8px;
			font-weight: 600;
			transition: all 0.3s ease;
			box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
		}

		.add-order-btn:hover {
			background-color: #218838;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
		}

		.add-order-btn i {
			margin-right: 8px;
		}

		/* Dropdown menu styling */
		.dropdown-menu {
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
			border: none;
		}

		.dropdown-item {
			padding: 10px 20px;
			transition: all 0.2s ease;
		}

		.dropdown-item:hover {
			background-color: #f8f9fa;
		}

		.dropdown-item.active {
			background-color: #28a745;
			color: white;
		}



		/* MODAL */
		/* PROFILE MODAL */
		/* MODERN PROFILE MODAL OVERHAUL */
		.profile-modal .modal-content {
			border: none;
			border-radius: 24px;
			overflow: hidden;
			box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
		}

		/* Creative Header with Gradient and Glassmorphism */
		.profile-header {
			background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);
			padding: 40px 30px;
			position: relative;
			color: white;
		}

		.profile-header::after {
			content: '';
			position: absolute;
			bottom: -1px;
			left: 0;
			right: 0;
			height: 40px;
			background: white;
			clip-path: ellipse(60% 100% at 50% 100%);
		}

		.profile-avatar {
			width: 110px;
			height: 110px;
			border-radius: 20px;
			border: 4px solid rgba(255, 255, 255, 0.3);
			object-fit: cover;
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
			transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
		}

		.profile-avatar:hover {
			transform: scale(1.05) rotate(3deg);
		}

		/* Professional Typography */
		#profileName {
			font-size: 24px;
			font-weight: 800;
			letter-spacing: -0.5px;
			margin-bottom: 2px;
		}

		.header-meta {
			display: flex;
			gap: 15px;
			font-size: 13px;
			opacity: 0.9;
		}

		/* Info Cards */
		.info-card {
			background: #ffffff;
			border: 1px solid #f1f4f8;
			border-radius: 20px;
			padding: 24px;
			height: 100%;
		}

		.info-title {
			font-size: 15px;
			font-weight: 700;
			color: #1a202c;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 2px solid #e9f5ff;
			display: flex;
			align-items: center;
		}

		.info-title i {
			background: #e9f5ff;
			color: #28a745;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 8px;
			margin-right: 12px;
		}

		/* Badge Style Lists */
		.clean-list li {
			display: inline-block;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			padding: 6px 14px;
			border-radius: 50px;
			margin: 3px;
			font-size: 12px;
			font-weight: 600;
			color: #475569;
			transition: all 0.2s;
		}

		.clean-list li:hover {
			background: #28a745;
			color: white;
			border-color: #28a745;
		}

		/* Soft Boxes for Grid Items */
		.info-item.soft {
			border: 1px solid #f1f5f9;
			background: #fdfdfd;
			padding: 15px;
			border-radius: 12px;
		}

		.info-item label {
			color: #94a3b8;
			font-size: 10px;
			letter-spacing: 1px;
			margin-bottom: 6px;
		}

		/* Eye icon styling */
		.view-user-info {
			transition: all 0.3s ease;
			font-size: 16px;
			color: #28a745;
		}

		.view-user-info:hover {
			color: #1e7e34 !important;
			transform: scale(1.2);
		}

		/* Professional Modal Styling */
		#userProfileModal .modal-content {
			border-radius: 16px;
			overflow: hidden;
		}

		#userProfileModal .modal-header {
			padding: 1.5rem 2rem;
		}

		#userProfileModal .modal-body {
			background-color: #f8f9fa;
		}

		#userProfileModal .card {
			border-radius: 12px;
			transition: transform 0.3s ease;
		}

		#userProfileModal .card:hover {
			transform: translateY(-2px);
		}

		#userProfileModal .card-header {
			padding: 1rem 1.25rem;
			border-radius: 12px 12px 0 0 !important;
		}

		#userProfileModal .card-body {
			padding: 1.5rem 1.25rem;
		}

		#userProfileModal table td {
			padding: 0.75rem 0.5rem;
		}

		#userProfileModal .progress {
			border-radius: 10px;
			background-color: #e9ecef;
		}

		#userProfileModal .progress-bar {
			border-radius: 10px;
		}
	</style>

</head>

<body class="app">

	<?php include 'includes/header.php' ?>

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
											<h4 class="h3 mb-0">Student Payment Details </h4>
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
												<!--		<a href="add_course.php" class="btn add-order-btn">
															<i class="fa-solid fa-plus"></i>&nbsp;&nbsp;Add New Course
														</a>	-->

											</div>
										</div>
									</div>
								</div>



								<div class="card-body">
									<div class="table-responsive">

										<table id="datatable"
											class="table table-bordered table-hover align-middle text-center"
											style="width:100%">
											<thead class="table-light text-uppercase">
												<th>Payment ID</th>
												<th>Tutor Name</th>
												<th>Razorpay ID</th>
												<th>Package Name</th>
												<th>Payment Amount</th>
												<th>Start Date</th>
												<th>End Date</th>
												<th>Remaining Days</th>
												<th>Status</th>
											</thead>
											<tbody>
												<?php

												$display_query = "SELECT * FROM tutor_package_tbl 
																INNER JOIN tutor_tbl ON tutor_tbl.tutor_id = tutor_package_tbl.tutor_id
																INNER JOIN package_tbl ON package_tbl.package_id = tutor_package_tbl.package_id
																ORDER BY purchase_id DESC";
												$result = mysqli_query($conn, $display_query);

												while ($payment = mysqli_fetch_assoc($result)) {
													?>

													<tr>
														<td><?php echo $payment['purchase_id']; ?></td>
														<td><?php echo $payment['tutor_name']; ?></td>
														<td><?php echo $payment['razorpay_id']; ?></td>
														<td><?php echo $payment['package_name']; ?></td>
														<td><?php echo "₹" . $payment['amount_paid']; ?></td>
														<td><?php echo date('d-m-Y', strtotime($payment['start_date'])); ?>
														</td>
														<td><?php echo date('d-m-Y', strtotime($payment['end_date'])); ?>
														</td>
														<td>
															<?php
															$endDate = new DateTime($payment['end_date']);
															$today = new DateTime(); // current date
														
															$diff = date_diff($today, $endDate);
															if ($diff->invert == 1) {
																echo "<span style='font-size: 14px;' class='badge bg-danger'>Expired</span>";
															} elseif ($diff->days == 0) {
																echo "<span style='font-size: 14px;' class='badge bg-warning'>Expiring Today</span>";
															} else {
																echo "<span style='font-size: 14px;' class='badge bg-success'>" . $diff->days . " Days</span>";
															}
															?>
														</td>
														<td>
															<div class="d-flex justify-content-center">
																<div class="form-check form-switch custom-switch">
																	<input type="checkbox"
																		class="form-check-input toggle-switch"
																		data-id="<?= $payment['purchase_id']; ?>"
																		<?= ($payment['status'] == 1) ? 'checked' : ''; ?>>
																</div>
															</div>
										</div>
										</td>
										</tr>
									<?php } ?>
									</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div><!--//tab-content-->
		</div><!--//container-fluid-->
	</div><!--//app-content-->
	</div><!--//app-wrapper-->

	<!-- Javascript -->
	<script src="assets/plugins/popper.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

	<?php include 'includes/script.php' ?>


	<script>
		$(document).ready(function () {

			// ============== DATATABLE WITH FILTER ==============

			let currentFilter = '';

			// Custom FILTER for Active / Inactive
			$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

				// No filter → show all
				if (!currentFilter) return true;

				let api = new $.fn.dataTable.Api(settings);
				let node = api.row(dataIndex).node();
				if (!node) return true;

				// ✅ FIXED CLASS NAME
				let $switch = $(node).find('.toggle-switch');

				if ($switch.length === 0) return true;

				let isChecked = $switch.prop('checked');

				if (currentFilter === 'active') {
					return isChecked === true;
				}

				if (currentFilter === 'inactive') {
					return isChecked === false;
				}

				return true;
			});

			// DataTable init
			if ($.fn.DataTable.isDataTable('#datatable')) {
				$('#datatable').DataTable().destroy();
			}

			let table = $('#datatable').DataTable({
				scrollX: true,
				scrollCollapse: true,
				responsive: false,
				lengthChange: false,
				autoWidth: false,
				pageLength: 10,
				dom: "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
					"<'row'<'col-sm-12'tr>>" +
					"<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
				buttons: [
					{ extend: 'copy', className: 'btn btn-sm btn-outline-default me-1' },
					{ extend: 'csv', className: 'btn btn-sm btn-outline-default me-1' },
					{ extend: 'excel', className: 'btn btn-sm btn-outline-default me-1' },
					{ extend: 'pdf', className: 'btn btn-sm btn-outline-default me-1' },
					{ extend: 'print', className: 'btn btn-sm btn-outline-default me-1' },
					{ extend: 'colvis', className: 'btn btn-sm btn-outline-default' }
				],
				language: {
					paginate: {
						previous: "<i class='fa-solid fa-angle-left'></i>",
						next: "<i class='fa-solid fa-angle-right'></i>"
					}
				}
			});

			// Filter dropdown functionality
			$('.filter-option').on('click', function (e) {
				e.preventDefault();

				let filterValue = $(this).data('filter');
				console.log('Filter clicked:', filterValue); // DEBUG

				// Remove active class from all options
				$('.filter-option').removeClass('active');

				// Add active class to clicked option
				$(this).addClass('active');

				// Set filter value
				currentFilter = filterValue;

				console.log('Current filter set to:', currentFilter); // DEBUG

				// Redraw table with filter
				table.draw();

				console.log('Table redrawn'); // DEBUG
			});

			// ============== STATUS TOGGLE FUNCTIONALITY ==============
			$(document).on('change', '.toggle-switch', function () {
				let checkbox = $(this);
				let purchaseId = checkbox.data('id');
				let newStatus = checkbox.is(':checked') ? 1 : 0;

				console.log('Toggle clicked - ID:', purchaseId, 'New Status:', newStatus);

				// Validate purchase ID
				if (!purchaseId) {
					console.error('Purchase ID is missing!');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Purchase ID is missing!',
						confirmButtonColor: '#28a745'
					});
					checkbox.prop('checked', !newStatus);
					return;
				}

				$.ajax({
					url: 'income.php', // Make sure this matches your PHP filename
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'toggle_payment_status',
						purchase_id: purchaseId,
						status: newStatus
					},
					success: function (response) {
						console.log('Server Response:', response);

						if (response.success) {
							// Show success message with SweetAlert
							Swal.fire({
								icon: 'success',
								title: 'Status Updated',
								text: 'Payment status updated successfully!',
								timer: 1500,
								showConfirmButton: false
							});
						} else {
							// Show error and revert
							Swal.fire({
								icon: 'error',
								title: 'Update Failed',
								text: response.message || 'Failed to update status',
								confirmButtonColor: '#28a745'
							});
							checkbox.prop('checked', !newStatus);
						}
					},
					error: function (xhr, status, error) {
						console.error('AJAX Error:', error);
						console.error('Response:', xhr.responseText);

						Swal.fire({
							icon: 'error',
							title: 'Server Error',
							text: 'An error occurred while updating status',
							confirmButtonColor: '#28a745'
						});
						checkbox.prop('checked', !newStatus);
					}
				});
			});



		});
	</script>

</body>

</html>