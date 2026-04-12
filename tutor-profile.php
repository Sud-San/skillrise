<?php
session_start();
include 'connection.php';

// Check if tutor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: teams.php");
    exit();
}

$tutor_id = intval($_GET['id']);

// Fetch tutor details
$sql = "
    SELECT 
        t.tutor_id,
        t.tutor_name,
        t.tutor_email,
        t.tutor_status,
        t.verification_status,
        t.created_at,
        tp.profile_pic,
        tp.expertise,
        tp.bio
    FROM tutor_tbl t
    LEFT JOIN tutor_profile_tbl tp ON t.tutor_id = tp.tutor_id
    WHERE t.tutor_id = ? 
    AND t.tutor_status = 1 
    AND t.verification_status = 'approved'
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $tutor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tutor = mysqli_fetch_assoc($result);

if (!$tutor) {
    header("Location: teams.php");
    exit();
}

// Handle profile picture path
$profilePic = 'assets/images/default-profile.png';
if (!empty($tutor['profile_pic'])) {
    if (filter_var($tutor['profile_pic'], FILTER_VALIDATE_URL)) {
        $profilePic = $tutor['profile_pic'];
    } elseif (strpos($tutor['profile_pic'], 'assets/') === 0) {
        $profilePic = $tutor['profile_pic'];
    } elseif (strpos($tutor['profile_pic'], 'uploads/') === 0) {
        $profilePic = $tutor['profile_pic'];
    } else {
        $profilePic = $tutor_profile_path . $tutor['profile_pic'];
    }
}

// Fetch courses taught by this tutor
$courses_sql = "
    SELECT 
        c.*,
        cat.category_name
    FROM course_tbl c
    LEFT JOIN category_tbl cat ON c.category_id = cat.category_id
    WHERE c.tutor_id = ? 
    AND c.course_status = 1
    ORDER BY c.created_at DESC
    LIMIT 6
";

// Check if tutor_id column exists, if not try user_id
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM course_tbl LIKE 'tutor_id'");
if (mysqli_num_rows($check_column) == 0) {
    $courses_sql = "
        SELECT 
            c.*,
            cat.category_name,
        FROM course_tbl c
        LEFT JOIN category_tbl cat ON c.category_id = cat.category_id
        WHERE c.user_id = ? 
        AND c.course_status = 1
        ORDER BY c.created_at DESC
        LIMIT 6
    ";
}

$courses_stmt = mysqli_prepare($conn, $courses_sql);
mysqli_stmt_bind_param($courses_stmt, 'i', $tutor_id);
mysqli_stmt_execute($courses_stmt);
$courses_result = mysqli_stmt_get_result($courses_stmt);
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <?php include 'headtag.php'; ?>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <?php include 'header.php'; ?>

    <!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.html')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white mb-4">
                    <?php echo htmlspecialchars($tutor['tutor_name']); ?>
                </h3>

                <ul class="tracking-[0.5px] inline-block mt-2">
                    <li
                        class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white">
                        <a href="index.php"><?php echo $company_name; ?></a>
                    </li>
                    <li
                        class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180">
                        <i class="ri-arrow-right-s-line"></i>
                    </li>
                    <li
                        class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white">
                        <a href="teams.php">Teachers</a>
                    </li>
                    <li
                        class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180">
                        <i class="ri-arrow-right-s-line"></i>
                    </li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white"
                        aria-current="page">
                        Profile
                    </li>
                </ul>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->

    <!-- Tutor Profile Section -->
    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column - Tutor Info -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <!-- Tutor Card -->
                        <div
                            class="bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-200 dark:shadow-gray-800 p-6 text-center">
                            <div
                                class="relative mx-auto w-48 h-48 rounded-full overflow-hidden border-4 border-white shadow-md shadow-gray-100 dark:shadow-gray-800 mb-6">
                                <img src="<?php echo $profilePic; ?>" class="w-full h-full object-cover rounded-full"
                                    alt="<?php echo htmlspecialchars($tutor['tutor_name']); ?>"
                                    onerror="this.onerror=null; this.src='default-tutor.png';">
                            </div>

                            <h4 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($tutor['tutor_name']); ?>
                            </h4>
                            <p class="text-primary font-medium mb-4">
                                <?php echo !empty($tutor['expertise']) ? htmlspecialchars($tutor['expertise']) : 'Expert Tutor'; ?>
                            </p>

                            <div class="flex justify-center space-x-2 mb-6">
                                <a href="#"
                                    class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-full duration-500">
                                    <i class="ri-facebook-circle-line"></i>
                                </a>

                                <a href="#"
                                    class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-full duration-500">
                                    <i class="ri-instagram-line"></i>
                                </a>

                                <a href="#"
                                    class="size-9 inline-flex items-center justify-center bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-full duration-500">
                                    <i class="ri-linkedin-line"></i>
                                </a>
                            </div>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">Email:</span>
                                    <a href="mailto:<?php echo htmlspecialchars($tutor['tutor_email']); ?>"
                                        class="text-primary hover:underline text-sm">
                                        <?php echo htmlspecialchars($tutor['tutor_email']); ?>
                                    </a>
                                </div>

                                <?php if (!empty($tutor['created_at'])):
                                    $join_date = new DateTime($tutor['created_at']);
                                    $current_date = new DateTime();
                                    $interval = $current_date->diff($join_date);
                                    $experience_years = $interval->y;
                                    if ($experience_years > 0): ?>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600 dark:text-gray-400 text-sm">Experience:</span>
                                            <span class="font-medium text-sm"><?php echo $experience_years; ?>+ years</span>
                                        </div>
                                    <?php endif;
                                endif; ?>

                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">Status:</span>
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-xs font-medium">
                                        Verified
                                    </span>
                                </div>
                            </div>

                            <button
                                onclick="Swal.fire('Contact Information', 'Email: <?php echo htmlspecialchars($tutor['tutor_email']); ?>', 'info')"
                                class="h-11 w-full px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary/10 hover:bg-primary text-primary hover:text-white duration-500 cursor-pointer">
                                <i class="ri-chat-3-line me-2"></i>Contact Tutor
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Tutor Details -->
                <div class="lg:col-span-2">
                    <!-- Bio Section -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-200 dark:shadow-gray-800 p-6 mb-6">
                        <h4 class="mb-4 md:leading-normal text-xl leading-normal font-semibold flex items-center">
                            <i class="ri-user-3-line text-primary me-2"></i> About Me
                        </h4>

                        <?php if (!empty($tutor['bio'])): ?>
                            <p class="text-gray-400 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($tutor['bio'])); ?>
                            </p>
                        <?php else: ?>
                            <p class="text-gray-400 italic">
                                This tutor hasn't added a bio yet. Check back soon for more information!
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Skills Section -->
                    <!-- <div class="bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-200 dark:shadow-gray-800 p-6 mb-6">
                        <h4 class="mb-6 md:leading-normal text-xl leading-normal font-semibold flex items-center">
                            <i class="ri-tools-line text-primary me-2"></i> Teaching Expertise
                        </h4>

                        <?php if (!empty($tutor['expertise'])): ?>
                            <div class="space-y-4">
                                <?php
                                // Split expertise by comma if it contains multiple skills
                                $skills = explode(',', $tutor['expertise']);
                                foreach ($skills as $skill):
                                    $skill = trim($skill);
                                    if (!empty($skill)):
                                        $level = rand(80, 98);
                                        ?>
                                        <div>
                                            <div class="flex justify-between mb-2">
                                                <span class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($skill); ?></span>
                                                <span class="text-gray-600 dark:text-gray-400"><?php echo $level; ?>%</span>
                                            </div>
                                            <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                                <div class="h-full bg-primary rounded-full" style="width: <?php echo $level; ?>%"></div>
                                            </div>
                                        </div>
                                <?php endif;
                                endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-400 italic">
                                Expertise information not available.
                            </p>
                        <?php endif; ?>
                    </div> -->

                    <!-- Courses Section -->
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-200 dark:shadow-gray-800 p-6">
                        <h4 class="mb-6 md:leading-normal text-xl leading-normal font-semibold flex items-center">
                            <i class="ri-book-open-line text-primary me-2"></i> My Courses
                        </h4>

                        <?php if (mysqli_num_rows($courses_result) > 0): ?>
                            <div class="grid md:grid-cols-2 gap-6">
                                <?php while ($course = mysqli_fetch_assoc($courses_result)):
                                    // Get course details
                                    $course_title = $course['course_title'] ?? $course['title'] ?? $course['name'] ?? 'Untitled Course';
                                    $description = $course['course_description'] ?? $course['description'] ?? 'No description available';
                                    $price = $course['course_price'] ?? $course['price'] ?? 0;
                                    $duration = $course['course_duration'] ?? $course['duration'] ?? 'Self-paced';
                                    $level = $course['course_level'] ?? $course['level'] ?? 'All Levels';
                                    ?>
                                    <div
                                        class="group bg-gray-50 dark:bg-gray-800 rounded-lg p-5 transition hover:shadow-lg duration-300">
                                        <a
                                            href="course-detail.php?id=<?php echo $course['course_id'] ?? $course['id'] ?? 0; ?>">
                                            <div class="flex items-start justify-between mb-3">

                                                <div>
                                                    <h5
                                                        class="font-semibold text-lg mb-1 group-hover:text-primary duration-500">
                                                        <?php echo htmlspecialchars($course_title); ?>
                                                    </h5>
                                                    <span class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?>
                                                    </span>
                                                </div>
                                                <span
                                                    class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-medium">
                                                    <?php echo htmlspecialchars($level); ?>
                                                </span>

                                            </div>

                                            <p class="text-gray-400 text-sm mb-4 line-clamp-2">
                                                <?php
                                                echo htmlspecialchars(substr($description, 0, 100));
                                                if (strlen($description) > 100)
                                                    echo '...';
                                                ?>
                                            </p>

                                            <div class="flex items-center justify-between text-sm">
                                                <div class="flex items-center text-gray-500">
                                                    <i class="ri-time-line me-1"></i>
                                                    <span><?php echo htmlspecialchars($duration); ?></span>
                                                </div>
                                                <div class="text-primary font-semibold">
                                                    <?php
                                                    if (!empty($price) && $price > 0) {
                                                        echo '₹' . number_format($price);
                                                    } else {
                                                        echo 'Free';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <div class="mt-6 text-center">
                                <a href="courses.php?tutor=<?php echo $tutor_id; ?>"
                                    class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary/10 hover:bg-primary text-primary hover:text-white duration-500">
                                    View All Courses <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div
                                    class="inline-flex items-center justify-center size-16 bg-primary/10 text-primary rounded-full mb-4">
                                    <i class="ri-book-line text-2xl"></i>
                                </div>
                                <h5 class="text-lg font-semibold mb-2">No Courses Yet</h5>
                                <p class="text-gray-400 max-w-md mx-auto">This tutor hasn't published any courses yet. Check
                                    back soon for amazing learning content!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Tutor Profile -->

    <?php include 'footer.php'; ?>

    <!-- Back to top -->
    <a href="#" onclick="topFunction()" id="back-to-top"
        class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9">
        <i class="ri-arrow-up-line"></i>
    </a>

    <!-- JAVASCRIPTS -->
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        // Animate skill bars when page loads
        document.addEventListener('DOMContentLoaded', function () {
            const skillBars = document.querySelectorAll('.bg-primary');
            skillBars.forEach(bar => {
                if (bar.parentElement.classList.contains('bg-gray-100')) {
                    const width = bar.style.width;
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 300);
                }
            });
        });
    </script>
</body>

</html>

<?php
// Close database connections
if (isset($stmt))
    mysqli_stmt_close($stmt);
if (isset($courses_stmt))
    mysqli_stmt_close($courses_stmt);
if (isset($conn))
    mysqli_close($conn);
?>