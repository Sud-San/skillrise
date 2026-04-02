<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

$user_id = (int) $_SESSION['user_id'];

/* --------------------------
   Fetch user basic details
-------------------------- */
$userStmt = $conn->prepare("SELECT * FROM user_tbl WHERE user_id = ?");
if (!$userStmt)
	die("SQL Error: " . $conn->error);

$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) {
	header("Location: logout.php");
	exit;
}


/* --------------------------
   Fetch enrolled courses
-------------------------- */
$coursesStmt = $conn->prepare("
    SELECT c.course_id, c.course_title, c.course_thumbnail, c.course_description, 
           c.price, e.enrollment_status, e.enrolled_at, e.progress
    FROM enrollments_tbl e
    JOIN course_tbl c ON e.course_id = c.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");

if ($coursesStmt) {
	$coursesStmt->bind_param("i", $user_id);
	$coursesStmt->execute();
	$coursesResult = $coursesStmt->get_result();
} else {
	$coursesResult = null;
}

/* --------------------------
   Compute counters
-------------------------- */
$countStmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_enrolled,
        SUM(CASE WHEN e.enrollment_status = 'paid' THEN 1 ELSE 0 END) AS paid_count
    FROM enrollments_tbl e
    WHERE e.user_id = ?
");

if ($countStmt) {
	$countStmt->bind_param("i", $user_id);
	$countStmt->execute();
	$countRes = $countStmt->get_result()->fetch_assoc();
	$countStmt->close();
} else {
	$countRes = ['total_enrolled' => 0, 'paid_count' => 0];
}

$projects = (int) ($countRes['total_enrolled'] ?? 0);
$paid_count = (int) ($countRes['paid_count'] ?? 0);
$success_rate = $projects > 0 ? round(($paid_count / $projects) * 100) : 0;

/* Escape helper */
function e($str)
{
	return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
	<?php include 'headtag.php'; ?>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
	<?php include 'user-header.php'; ?>
	<!-- Start Hero -->
	<section class="relative bg-gray-50 dark:bg-gray-800 py-24 border border-gray-100 dark:border-gray-700">
		<?php include 'user.php'; ?>

		<div class="lg:col-span-8 md:col-span-8">
			<div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
				<h5 class="font-semibold mb-4">Personal Detail :</h5>

				<form id="profileForm" action="user-update-profile.php" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="ruser_id" value="<?php echo e($user_id); ?>">

					<div class="grid md:grid-cols-12 grid-cols-1 gap-4">

						<!-- Name -->
						<div class="md:col-span-6">
							<label class="form-label font-medium text-sm">Name : <span
									class="text-red-600">*</span></label>
							<input type="text" name="user_name" value="<?php echo e($user['user_name']); ?>"
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
								placeholder="Name">
						</div>

						<!-- Email -->
						<div class="md:col-span-6">
							<label class="form-label font-medium text-sm">Your Email : <span
									class="text-red-600">*</span></label>
							<input type="email" name="user_email" value="<?php echo e($user['user_email']); ?>"
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
								placeholder="Email">
						</div>

						<!-- Phone -->
						<div class="md:col-span-6">
							<label class="form-label font-medium text-sm">Phone :</label>
							<input type="text" name="mobile" value="<?php echo $user['mobile']; ?>"
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
								placeholder="Phone">
						</div>

						<!-- Gender -->
						<div class="md:col-span-6">
							<label class="form-label font-medium text-sm">Gender :</label>
							<select name="gender" disabled
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2">
								<option value="">Select</option>
								<option value="male" <?php if ($user['gender'] == 'male')
									echo 'selected'; ?>>Male
								</option>
								<option value="female" <?php if ($user['gender'] == 'female')
									echo 'selected'; ?>>Female
								</option>
								<option value="other" <?php if ($user['gender'] == 'other')
									echo 'selected'; ?>>Other
								</option>

							</select>
						</div>

						<!-- Date of Birth -->
						<div class="md:col-span-6">
							<label class="form-label font-medium text-sm">Date of Birth :</label>
							<input type="date" name="dob" value="<?php echo $user['dob']; ?>"
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
								placeholder="e.g. html, css, js">
						</div>

						<!-- City -->
						<div class="md:col-span-6">
							<label class="form-label font-medium text-sm">City :</label>
							<?php
							$str = "select city_name from city_tbl where city_id=" . $user["city"] . ";";
							$res = mysqli_query($conn, $str);
							$row = mysqli_fetch_assoc($res);
							?>
							<input type="text" name="city" disabled value="<?php echo e($row['city_name']); ?>"
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
								placeholder="e.g. English, Chinese">
						</div>

						<!-- Profile Picture -->
						<div class="md:col-span-12">
							<label class="form-label font-medium text-sm">Profile Picture :</label>
							<input type="file" name="profile_pic"
								class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 mt-2">
						</div>

						<div class="md:col-span-12">
							<input type="submit"
								class="h-11 px-5 tracking-wider inline-flex items-center text-xs uppercase justify-center font-bold rounded-lg bg-primary text-white"
								value="Save Changes">
						</div>

					</div>
				</form>
			</div>

			<!-- Contact Info -->
			<div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
				<div class="grid lg:grid-cols-2 grid-cols-1 gap-5">

					<div>
						<h5 class="font-semibold mb-4">Contact Info :</h5>

						<form id="contactForm" action="user-update-contact.php" method="POST">

							<div class="grid grid-cols-1 gap-4">

								<div>
									<label class="form-label font-medium text-sm">Phone No. :</label>
									<input type="text" name="mobile" value="<?php echo e($user['mobile']); ?>"
										class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
										placeholder="Phone :">
								</div>

								<div>
									<label class="form-label font-medium text-sm">Add Address :</label>
									<input type="text" name="addr_short" value="<?php //echo e($user['address']); ?>"
										class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
										placeholder="Short address">
								</div>

								<div>
									<button type="submit"
										class="h-11 px-5 tracking-wider inline-flex items-center text-xs uppercase justify-center font-bold rounded-lg bg-primary text-white">
										Save Contact
									</button>
								</div>

							</div>
						</form>
					</div>

					<!-- Change Password -->
					<div>
						<h5 class="font-semibold mb-4">Change password :</h5>

						<form id="passwordForm" action="user-change-password.php" method="POST">

							<input type="hidden" name="user_id" value="<?php echo e($user_id); ?>">

							<div class="grid grid-cols-1 gap-4">

								<div>
									<label class="form-label font-medium text-sm">Old password :</label>
									<input type="password" name="old_password"
										class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
										placeholder="Old password">
								</div>

								<div>
									<label class="form-label font-medium text-sm">New password :</label>
									<input type="password" name="new_password"
										class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
										placeholder="New password">
								</div>

								<div>
									<label class="form-label font-medium text-sm">Re-type New password :</label>
									<input type="password" name="confirm_password"
										class="form-input px-3 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 mt-2"
										placeholder="Re-type New password">
								</div>

								<div>
									<button
										class="h-11 px-5 tracking-wider inline-flex items-center text-xs uppercase justify-center font-bold rounded-lg bg-primary text-white">
										Save password
									</button>
								</div>

							</div>

						</form>
					</div>

				</div>
			</div>

			<!-- Delete Account -->
			<div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900 mt-6">
				<h5 class="font-semibold mb-5 text-red-600">Logout Account :</h5>

				<p class="text-gray-400 mb-4">Do you want to Logout the account? Please press below "Logout" button</p>

				<a href="logout.php"
					class="h-11 px-5 tracking-wider inline-flex items-center text-xs uppercase justify-center font-bold rounded-lg bg-red-600/10 text-red-600 hover:bg-red-600 hover:text-white">
					Logout
				</a>
			</div>

		</div>
		<!--end grid-->
		</div><!--end container-->
	</section><!--end section-->
	<!-- End Hero -->

	<?php include 'footer.php'; ?>

	<!-- Back to top -->
	<a href="#" onclick="topFunction()" id="back-to-top"
		class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i
			class="ri-arrow-up-line"></i></a>
	<!-- Back to top -->

	<!-- JAVASCRIPTS -->
	<script>
		// preview profile image client-side (optional)
		var loadFile = function (event) {
			var output = document.getElementById('profile-image');
			output.src = URL.createObjectURL(event.target.files[0]);
			output.onload = function () {
				URL.revokeObjectURL(output.src)
			}
		};
	</script>
	<script src="assets/js/plugins.init.js"></script>
	<script src="assets/js/app.js"></script>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

	<script>
		/* ---------------------------
		   Profile Update (with photo)
		----------------------------*/
		$("#profileForm").on("submit", function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			$.ajax({
				url: "user-update-profile.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,

				success: function (response) {
					Swal.fire({
						title: 'Success',
						text: response,
						icon: 'success',
						confirmButtonText: 'OK'
					}).then((result) => {
						if (result.isConfirmed) {
							location.reload();
						}
					});
				}
			});
		});

		/* ---------------------------
		   Contact Update
		----------------------------*/
		$("#contactForm").on("submit", function (e) {
			e.preventDefault();

			$.ajax({
				url: "user-update-contact.php",
				type: "POST",
				data: $(this).serialize(),

				success: function (response) {
					Swal.fire('Success', response, 'success');
				});
		});

		/* ---------------------------
		   Change Password
		----------------------------*/
		$("#passwordForm").on("submit", function (e) {
			e.preventDefault();

			$.ajax({
				url: "user-change-password.php",
				type: "POST",
				data: $(this).serialize(),

				success: function (response) {
					Swal.fire('Success', response, 'success');
				}
			});
		});
	</script>

	<!-- JAVASCRIPTS -->
</body>

</html>