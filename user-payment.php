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
$userStmt = $conn->prepare("SELECT user_id, user_name, user_email FROM users_tbl WHERE user_id = ?");
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
   Fetch user profile details
   -------------------------- */
$detailStmt = $conn->prepare("SELECT * FROM users_details WHERE ruser_id = ?");
$detailStmt->bind_param("i", $user_id);
$detailStmt->execute();
$detailResult = $detailStmt->get_result();
$details = $detailResult->fetch_assoc();
$detailStmt->close();

/* Provide safe defaults */
$profile_pic = !empty($details['profile_pic']) ? $details['profile_pic'] : 'assets/images/team/1.jpg';
$aboutme = !empty($details['aboutme']) ? $details['aboutme'] : 'No bio yet.';
$skills = !empty($details['skills']) ? $details['skills'] : '';
$languages = !empty($details['languages']) ? $details['languages'] : '';
$mobile_no = !empty($details['mobile_no']) ? $details['mobile_no'] : '';
$gender = !empty($details['gender']) ? ucfirst($details['gender']) : '';
$date_of_birth = !empty($details['date_of_birth']) ? $details['date_of_birth'] : '';
$address = !empty($details['address']) ? $details['address'] : '';

/* --------------------------
   Fetch enrolled courses
   (enrollments_tbl uses ruser_id and rcourse_id)
   -------------------------- */
$coursesStmt = $conn->prepare("
    SELECT c.courses_id, c.title, c.thumbnail, c.description, c.duration, c.price, e.payment_status, e.final_price, e.enrolled_on
    FROM enrollments_tbl e
    JOIN course_tbl c ON e.rcourse_id = c.courses_id
    WHERE e.ruser_id = ?
    ORDER BY e.enrolled_on DESC
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
        SUM(final_price) AS total_earnings,
        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_count
    FROM enrollments_tbl
    WHERE ruser_id = ?
");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countRes = $countStmt->get_result()->fetch_assoc();
$countStmt->close();

$projects = (int)($countRes['total_enrolled'] ?? 0);
$total_earnings = (float)($countRes['total_earnings'] ?? 0);
$paid_count = (int)($countRes['paid_count'] ?? 0);
$success_rate = $projects > 0 ? round(($paid_count / $projects) * 100) : 0;

/* small helper to escape output */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    <head>
        <?php include 'headtag.php';?>

    </head>
    
    <body class="text-gray-900 dark:text-white dark:bg-gray-900">
        <?php include 'user-header.php';?>

        <!-- Start Hero -->
        <section class="relative bg-gray-50 dark:bg-gray-800 py-24 border border-gray-100 dark:border-gray-700">
            <?php include 'user.php';?>

                  <div class="lg:col-span-8 md:col-span-8">
    <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-lg">

        <!-- Heading -->
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
            Enrolled Courses
        </h3>

        <!-- Courses Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-6">

            <!-- COURSE CARD -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl transition-all p-6 border border-gray-100 dark:border-gray-700">

                <div class="flex items-start gap-4">
                    <!-- Thumbnail -->
                    <img src="https://via.placeholder.com/90" 
                        class="w-20 h-20 rounded-xl object-cover shadow-sm" alt="course">

                    <!-- Title & Lessons -->
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                            UI/UX Design Mastery
                        </h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                            32 Lessons • Beginner to Advance
                        </p>

                        <!-- Enrolled Date -->
                        <p class="text-xs text-gray-400 mt-2">
                            📅 Enrolled on 15 Apr 2025
                        </p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="mt-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300 font-medium mb-2">
                        Progress: <span class="font-semibold text-purple-600">57%</span>
                    </p>

                    <div class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden">
                        <div class="bg-purple-600 h-full rounded-full" style="width: 57%;"></div>
                    </div>
                </div>

                <!-- Button -->
                <button class="mt-6 w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold shadow-md transition">
                    Resume Course
                </button>
            </div>

            <!-- COPY ABOVE CARD FOR DYNAMIC LOOP -->

        </div>
    </div>
</div>

        </section><!--end section-->
        <!-- End Hero -->


		<?php include 'footer.php';?>

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
        <!-- Back to top -->
 

        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html>