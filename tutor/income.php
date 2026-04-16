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

	<link rel="stylesheet" href="assets/css/income.css">

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