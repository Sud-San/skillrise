<?php
session_start();
include 'connection.php'; // must set $conn (mysqli)

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* --------------------------
   Fetch user basic details
   -------------------------- */
$userStmt = $conn->prepare("SELECT * FROM user_tbl WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user) {
    // if somehow user not found, logout safe
    header("Location: logout.php");
    exit;
}


/* --------------------------
   Fetch enrolled courses
   (enrollments_tbl uses ruser_id and rcourse_id)
   -------------------------- */
$coursesStmt = $conn->prepare("
    SELECT c.course_id, c.course_title, c.course_thumbnail, c.course_description, c.price, 
    e.enrollment_status, e.enrolled_at
    FROM enrollments_tbl e
    JOIN course_tbl c ON e.course_id = c.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$coursesStmt->bind_param("i", $user_id);
$coursesStmt->execute();
$coursesResult = $coursesStmt->get_result();

/* --------------------------
   Compute counters: projects (count), earnings (sum final_price), success rate (paid%)
   -------------------------- */
$countStmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_enrolled,
        SUM(CASE WHEN e.enrollment_status = 'active' THEN 1 ELSE 0 END) AS active_count
    FROM enrollments_tbl e
    WHERE e.user_id = ?
");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countRes = $countStmt->get_result()->fetch_assoc();
$countStmt->close();

$projects = (int) ($countRes['total_enrolled'] ?? 0);
$active_count = (int) ($countRes['active_count'] ?? 0);
$success_rate = $projects > 0 ? round(($active_count / $projects) * 100) : 0;

/* small helper to escape output */
function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <?php include 'headtag.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        button {
            background: white !important;
            color: black !important;
        }

        button,
        .btn {
            background: #fff;
        }
    </style>


</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <?php include 'user-header.php'; ?>

    <!-- Start Hero -->
    <section class="relative bg-gray-50 dark:bg-gray-800 py-24 border border-gray-100 dark:border-gray-700">
        <?php include 'user.php'; ?>

        <div class="lg:col-span-8 md:col-span-8">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-lg">

                <!-- Heading -->
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                    Enrolled Courses
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-6">

                    <?php
                    // Dynamic fetch enrolled courses
                    $fetch = "
         SELECT 
			e.enrolled_at,
			p.payment_status,
			p.amount,
            p.user_payment_id,
			c.course_id,
			c.course_title,
			c.course_thumbnail,
			c.course_description,
			c.course_level,
			e.enrollment_status,
            e.progress
		FROM enrollments_tbl e
		LEFT JOIN user_payment_tbl p 
				ON e.user_payment_id = p.user_payment_id
		JOIN course_tbl c 
				ON e.course_id = c.course_id
		WHERE e.user_id = $user_id
		ORDER BY e.enrolled_at DESC;

        ";

                    $courses = mysqli_query($conn, $fetch);

                    if (mysqli_num_rows($courses) == 0) {
                        echo "<p class='text-gray-500 dark:text-gray-300'>No courses enrolled yet.</p>";
                    }

                    while ($course = mysqli_fetch_assoc($courses)):

                        // Generate progress dynamically (OR you can add a progress column later)
                        $progress = rand(20, 90);
                        ?>

                        <!-- Course Card -->

                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl transition-all p-6 border border-gray-100 dark:border-gray-700">

                            <div class="flex items-start gap-4">
                                <!-- Thumbnail -->
                                <!-- <a href="course-detail.php?id=<?php echo $course['course_id'] ?>"> -->
                                <img src="<?php echo './assets/images/thumbnail/' . e($course['course_thumbnail']); ?>"
                                    class="w-20 h-20 rounded-xl object-cover shadow-sm" alt="course">


                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                        <?= e($course['course_title']) ?>
                                    </h4>

                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                        Level: <?= ucfirst($course['course_level']) ?>
                                    </p>

                                    <p class="text-xs text-gray-400 mt-2">
                                        📅 Enrolled on <?= date("d M Y", strtotime($course['enrolled_at'])) ?>
                                    </p>

                                </div>
                                <!-- </a> -->
                            </div>

                            <!-- Progress -->
                            <div class="mt-6">
                                <p class="text-sm text-gray-600 dark:text-gray-300 font-medium mb-2">
                                    Progress: <span class="font-semibold text-green-600">
                                        <?php echo $course['progress'] ?>%
                                    </span>
                                </p>
                            </div>

                            <!-- Resume Button -->
                            <button
                                class="mt-2 w-full py-3 rounded-xl font-semibold shadow-md transition-all duration-200 flex items-center justify-center gap-2 hover:opacity-90"
                                style="background-color:#0c7a33 !important; color:white !important;"
                                onclick="window.location.href='course-detail.php?id=<?= $course['course_id'] ?>'">

                                <span
                                    class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs text-white"><i
                                        class="fa-solid fa-play fa-lg"></i></span>
                                Resume Course
                            </button>

                            <button
                                class="mt-2 w-full py-3 rounded-xl font-semibold shadow-md transition-all duration-200 flex items-center justify-center gap-2 hover:opacity-90"
                                style="background-color:#0c7a33 !important; color:white !important;"
                                onclick="window.location.href='user-invoice.php?id=<?= $course['user_payment_id'] ?>'">
                                <span
                                    class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs text-white">
                                    <i class="fa-solid fa-download fa-lg"></i></span>
                                Download Invoice
                            </button>
                            <?php if ($course['progress'] == 100): ?>
                                <button
                                    class="mt-2 w-full py-3 rounded-xl font-semibold shadow-md transition-all duration-200 flex items-center justify-center gap-2 hover:opacity-90"
                                    style="background-color:#0c7a33 !important; color:white !important;"
                                    onclick="window.location.href='certi.php?user_id=<?= $user_id ?>&course_id=<?= $course['course_id'] ?>'">
                                    <span
                                        class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs text-white">
                                        <i class="fa-solid fa-certificate fa-lg"></i></span>
                                    See Certificate
                                </button>
                            <?php endif; ?>
                        </div>
                        </a>
                    <?php endwhile; ?>

                </div>

            </div>


            <!-- COPY ABOVE CARD FOR DYNAMIC LOOP -->

        </div>
        </div>
        </div>

    </section><!--end section-->
    <!-- End Hero -->


    <?php include 'footer.php'; ?>

    <!-- Back to top -->
    <a href="#" onclick="topFunction()" id="back-to-top"
        class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i
            class="ri-arrow-up-line"></i></a>
    <!-- Back to top -->


    <!-- JAVASCRIPTS -->
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>
    <!-- JAVASCRIPTS -->
</body>

</html>