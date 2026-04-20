<?php
session_start();
require 'connection.php';

require('vendor/autoload.php');
use Razorpay\Api\Api;

// Get course ID safely
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


// =====================
// 🔹 Fetch Course Details - CORRECTED QUERY
// =====================
$courseQuery = "
    SELECT 
        c.course_id, 
        c.category_id,
        c.course_title AS title, 
        c.course_description AS description, 
        c.price, 
        c.course_thumbnail AS thumbnail,
        c.course_level AS level,
        t.tutor_id,
        tp.profile_pic, 
        t.tutor_name,
        cat.category_name,
        (SELECT COUNT(*) FROM lessons_tbl l WHERE l.course_id = c.course_id) AS lesson_count
    FROM course_tbl c
    INNER JOIN tutor_tbl t ON c.tutor_id = t.tutor_id
    LEFT JOIN tutor_profile_tbl tp ON t.tutor_id = tp.tutor_id
    INNER JOIN category_tbl cat ON c.category_id = cat.category_id
    WHERE c.course_id = $course_id
    AND c.course_status = 1
    AND t.tutor_status = 1
";

$courseResult = mysqli_query($conn, $courseQuery);
$course = mysqli_fetch_assoc($courseResult);

if (!$course) {
    die("<h2 class='text-center py-20 text-red-600'>Course not found</h2>");
}

// ==========================
// 🔹 Check if user already purchased the course
// ==========================
$already_purchased = false;
$certificate = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Check in enrollments table
    $checkQuery = "SELECT * FROM enrollments_tbl WHERE user_id = $user_id AND course_id = $course_id LIMIT 1";
    $result = mysqli_query($conn, $checkQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $already_purchased = true;
        $certificates = mysqli_fetch_assoc($result);
        $certificate = $certificates['certificate_issued'] ?? 0;
    }
}

// =====================
// 🔹 Fetch Lessons - CORRECTED QUERY
// =====================
$lessonQuery = "
    SELECT * 
    FROM lessons_tbl 
    WHERE course_id = $course_id 
    ORDER BY lesson_order ASC
";
$lessons = mysqli_query($conn, $lessonQuery);

// =====================
// 🔹 Fetch Course Notes
// =====================
$noteQuery = "
    SELECT * 
    FROM course_notes 
    WHERE course_id = $course_id
    ORDER BY created_at DESC
";
$notes = mysqli_query($conn, $noteQuery);

// For assignment
$assignmentQuery = "
    SELECT *
    FROM assignment_tbl
    WHERE course_id = $course_id
    ORDER BY created_at DESC
";
$assignments = mysqli_query($conn, $assignmentQuery);

// =====================
// 🔹 Razorpay Order Creation
// =====================
$order_id = null;

if (isset($_SESSION['user_id']) && !$already_purchased) {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    $amount_in_paisa = (int) round((float) $course['price'] * 100);

    try {
        $order = $api->order->create([
            'receipt' => 'order_' . time(),
            'amount' => $amount_in_paisa,
            'currency' => 'INR',
            'notes' => [
                'course_id' => $course_id,
                'user_id' => $user_id
            ]
        ]);
        $order_id = $order['id'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        $order_id = null;
    }
}
?>


<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<!-- dream class  courses.php       -->

<head>
    <?php include 'headtag.php'; ?>
    <link rel="stylesheet" href="assets/css/course-detail.css">
    <!-- Plyr CSS -->
    <!-- <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" /> -->

</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-no-repeat bg-center bg-cover">
        </div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">
                    <?php echo htmlspecialchars($course['title']); ?>
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
                        <a href="courses.php">Courses</a>
                    </li>
                    <li
                        class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180">
                        <i class="ri-arrow-right-s-line"></i>
                    </li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white"
                        aria-current="page"><a
                            href="courses-by-language.php?cat_id=<?php echo $course['category_id'] ?? ''; ?>">
                            <?php echo htmlspecialchars($course['category_name']); ?>
                        </a></li>
                </ul>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->

    <!-- Start -->
    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <div class="grid md:grid-cols-12 grid-cols-1 gap-6">

                <!-- LEFT SIDE CONTENT (Course Details) -->
                <div class="lg:col-span-8 md:col-span-7 col-span-1">



                    <h5 class="text-2xl font-semibold my-4">Overview</h5>
                    <p class="text-gray-400 mb-3">
                        <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                    </p>

                    <h5 class="text-2xl font-semibold my-4">Curriculum</h5>
                    <div class="grid md:grid-cols-2 grid-cols-1 gap-6">
                        <?php
                        $lessonIndex = 1;
                        if (mysqli_num_rows($lessons) > 0) {
                            while ($lesson = mysqli_fetch_assoc($lessons)) {

                                // 🔹 Lock logic
                                if ($already_purchased) {
                                    $isLocked = false; // all lessons unlocked
                                } else {
                                    // first lesson free, rest locked
                                    $isLocked = ($lessonIndex > 1);
                                }

                                // 🔹 Link logic for videos_tbl
                                $videoQuery = "
                                    SELECT v.video_id, v.video_url, l.lesson_order 
                                    FROM videos_tbl v 
                                    JOIN lessons_tbl l ON v.lesson_id = l.lesson_id
                                    WHERE v.course_id = $course_id AND l.lesson_id = " . $lesson['lesson_id'] . " LIMIT 1
                                ";
                                $videoResult = mysqli_query($conn, $videoQuery);
                                $videoRow = mysqli_fetch_assoc($videoResult);

                                //$videoUrl = $videoRow ? $videoRow['video_url'] : '';
                                $videoUrl = "";

                                if ($videoRow) {
                                    if ($isLocked) {
                                        // Nothing will show
                                        $lessonLink = "javascript:void(0);";
                                    } else {
                                        // Redirects to video page
                                        $lessonLink = "videos/index.php?course_id=$course_id&video_id=" . $videoRow['video_id'];
                                    }
                                } else {
                                    $lessonLink = "javascript:void(0);";
                                }


                                ?>
                                <div>
                                    <div
                                        class="relative overflow-x-auto block w-full bg-white dark:bg-gray-900 shadow-sm shadow-gray-100 dark:shadow-gray-800 rounded-md">
                                        <table class="w-full text-start">
                                            <tbody>
                                                <tr class="border-t border-gray-100 dark:border-gray-800">
                                                    <td class="p-4">
                                                        <a href="<?php echo $lessonLink; ?>" target="1"
                                                            class="hover:text-primary <?php echo ($isLocked) ? 'opacity-75' : ''; ?>">
                                                            <i
                                                                class="<?php echo ($isLocked) ? 'ri-lock-line' : 'ri-play-circle-line'; ?> me-1"></i>
                                                            Lesson <?php echo $lessonIndex; ?><br>
                                                            <span
                                                                class="text-sm font-semibold"><?php echo htmlspecialchars($lesson['lesson_title']); ?></span>
                                                        </a>
                                                    </td>
                                                    <td class="p-4 text-end">
                                                        <span
                                                            class="bg-primary/5 text-primary text-xs font-medium px-2.5 py-0.5 rounded h-5">
                                                            <?php echo ($lessonIndex == 1 && !$already_purchased) ? 'Watch Free' : ($isLocked ? 'Locked' : 'Watch'); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php
                                $lessonIndex++;
                            }
                        } else { ?>
                            <div class="col-span-full text-center text-gray-500">No lessons added yet.</div>
                        <?php } ?>
                    </div>

                    <!-- ── Course Notes ─────────────────────────── -->
                    <?php if ($already_purchased) { ?>
                        <?php if (mysqli_num_rows($notes) > 0): ?>
                            <h5 class="text-2xl font-semibold my-6 border-b border-gray-100 dark:border-gray-800 pb-2">Course
                                Notes</h5>
                            <div class="grid grid-cols-1 gap-4">
                                <?php while ($note = mysqli_fetch_assoc($notes)): ?>
                                    <div
                                        class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 flex justify-between items-center transition hover:shadow-md">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center me-3 text-xl">
                                                <i class="ri-file-text-line"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-400 mt-1"> &nbsp;
                                                    <?= htmlspecialchars($note['description']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <a href="<?= "." . $note['file_url']; ?>" download class="">
                                            <i class="ri-download-2-line"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    <?php } else { ?>
                        <div class="mt-8">
                            <h5 class="text-2xl font-semibold mb-4">Course Notes</h5>
                            <p class="text-gray-500 dark:text-gray-400">You need to purchase the course to download the
                                notes.</p>
                        </div>
                    <?php } ?>

                    <!-- ── Assignment Section ─────────────────────────── -->
                    <?php if ($already_purchased) { ?>
                        <?php if ($assignments && mysqli_num_rows($assignments) > 0) { ?>
                            <div class="mt-8">
                                <h5 class="text-2xl font-semibold mb-4">Assignments</h5>
                                <div class="space-y-3">
                                    <?php while ($assignment = mysqli_fetch_assoc($assignments)): ?>
                                        <div
                                            class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 flex justify-between items-center transition hover:shadow-md">
                                            <div class="flex items-center">
                                                <div class="flex items-center justify-center me-3 text-xl">
                                                    <i class="ri-file-text-line"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-400 mt-1"> &nbsp;
                                                        <?= htmlspecialchars($assignment['title']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="<?= "./tutor/assets/assignments/" . htmlspecialchars($assignment['file_url']); ?>"
                                                download class="">
                                                <i class="ri-download-2-line"></i>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="mt-8">
                            <h5 class="text-2xl font-semibold mb-4">Assignments</h5>
                            <p class="text-gray-500 dark:text-gray-400">You need to purchase the course to download the
                                assignments.</p>
                        </div>
                    <?php } ?>

                    <!-- certificate -->
                    <?php if ($already_purchased) { ?>
                        <?php if ($certificate) { ?>
                            <div class="mt-8">
                                <h5 class="text-2xl font-semibold mb-4">Certificate</h5>
                                <a href="certi.php?course_id=<?php echo $course_id; ?>"
                                    class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/90 transition">
                                    Download Certificate
                                </a>
                            </div>
                        <?php } else { ?>
                            <div class="mt-8">
                                <h5 class="text-2xl font-semibold mb-4">Certificate</h5>
                                <p class="text-gray-500 dark:text-gray-400">You need to complete the course to download the
                                    certificate.</p>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="mt-8">
                            <h5 class="text-2xl font-semibold mb-4">Certificate</h5>
                            <p class="text-gray-500 dark:text-gray-400">You need to purchase the course to download the
                                certificate.</p>
                        </div>
                    <?php } ?>
                    <!-- ── Reviews Section ──────────────────────── -->
                    <h5 class="text-2xl font-semibold my-6 border-b border-gray-100 dark:border-gray-800 pb-2">Reviews &
                        Ratings</h5>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $q1 = "SELECT * FROM feedback_tbl WHERE user_id = {$_SESSION['user_id']} AND course_id = $course_id LIMIT 1";
                        $res_review = mysqli_query($conn, $q1);

                        if ($res_review && mysqli_num_rows($res_review) > 0) {
                            $already_review = true;
                        } else {
                            $already_review = false;
                        }
                        ?>
                        <!-- Review Submission Form (Enrolled Users Only) -->
                        <?php if ($already_purchased && !$already_review): ?>
                            <div
                                class="p-6 bg-gray-50 dark:bg-gray-800 rounded-lg mb-8 border border-gray-100 dark:border-gray-700 shadow-inner">
                                <h6 class="text-lg font-semibold mb-4 flex items-center text-primary">
                                    <i class="ri-edit-line me-2"></i> Leave a Review
                                </h6>
                                <form id="reviewForm" class="space-y-4" action="ajax/add_review.php" method="POST">
                                    <input type="hidden" name="course_id" value="<?= $course_id; ?>">
                                    <input type="hidden" name="tutor_id" value="<?= $course['tutor_id']; ?>">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                                        <select name="rating"
                                            class="w-full p-2.5 rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                                            required>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Very Good</option>
                                            <option value="3">3 - Good</option>
                                            <option value="2">2 - Fair</option>
                                            <option value="1">1 - Poor</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comment</label>
                                        <textarea name="comment" rows="3"
                                            class="w-full p-2.5 rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"
                                            placeholder="Tell us what you think..." required></textarea>
                                    </div>
                                    <button type="submit"
                                        class="w-full bg-primary text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition shadow-sm">Submit
                                        Review</button>
                                </form>
                            </div>
                        <?php endif;
                    } ?>

                    <!-- Reviews List (Loaded via AJAX) -->
                    <div id="reviews-list" class="space-y-4">
                        <div class="text-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                            <p class="text-gray-400 mt-2 text-sm">Loading reviews...</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE CONTENT (Course Fees Box) -->
                <div class="lg:col-span-4 md:col-span-5 col-span-1"> <!-- outer col div -->

                    <div
                        class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800">
                        <!-- outer card div -->

                        <div class="p-6 bg-primary rounded-lg"> <!-- inner card div (title, price, buy button) -->
                            <span class="text-white/80"><?php echo htmlspecialchars($course['title']); ?></span>

                            <?php if (!$already_purchased) { ?>
                                <!-- Show price and Buy Now only if NOT purchased -->
                                <h5 class="text-3xl font-semibold text-white">₹
                                    <?php echo number_format($course['price'], 2); ?>
                                </h5>
                                <span class="text-white/80">30 Days Money Back Guarantee</span>

                                <div class="mt-3"> <!-- Buy Now button container -->
                                    <button id="buyNowBtn"
                                        class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-yellow-500/10 hover:bg-yellow-500 text-yellow-500 hover:text-white w-full">
                                        Buy Now
                                    </button>
                                </div> <!-- /Buy Now button container -->
                            <?php } else { ?>
                                <!-- Optional: Show a message if already purchased -->
                                <div class="mt-3 text-center text-white/80 font-medium">
                                    You have already purchased this course.
                                </div>
                            <?php } ?>

                        </div> <!-- /inner card div -->



                        <div class="mt-4"> <!-- course details container -->
                            <h5 class="text-lg font-medium mb-3">This Course Includes:</h5>

                            <div class="flex items-center">
                                <i class="ri-user-line text-primary text-lg me-2"></i>
                                <div class="flex-1">
                                    <h6 class="text-sm mb-0">
                                        <span class="font-semibold">Instructor :</span>
                                        <span
                                            class="text-gray-400"><?php echo htmlspecialchars($course['tutor_name']); ?></span>
                                    </h6>
                                </div>
                            </div>

                            <div class="flex items-center mt-2">
                                <i class="ri-book-line text-primary text-lg me-2"></i>
                                <div class="flex-1">
                                    <h6 class="text-sm mb-0"><span class="font-semibold">Lesson :</span> <span
                                            class="text-gray-400"><?php echo $course['lesson_count']; ?></span></h6>
                                </div>
                            </div>

                            <div class="flex items-center mt-2">
                                <i class="ri-flag-line text-primary text-lg me-2"></i>
                                <div class="flex-1">
                                    <h6 class="text-sm mb-0"><span class="font-semibold">Level :</span> <span
                                            class="text-gray-400"><?php echo ucfirst($course['level']); ?></span></h6>
                                </div>
                            </div>

                            <div class="flex items-center mt-2">
                                <i class="ri-global-line text-primary text-lg me-2"></i>
                                <div class="flex-1">
                                    <h6 class="text-sm mb-0"><span class="font-semibold">Category :</span> <span
                                            class="text-gray-400"><?php echo htmlspecialchars($course['category_name']); ?></span>
                                    </h6>
                                </div>
                            </div>

                        </div> <!-- /course details container -->

                    </div> <!-- /outer card div -->

                </div> <!-- /outer col div -->


            </div><!-- end grid -->
        </div><!-- end container -->
    </section>



    <?php if (!$already_purchased): ?>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.getElementById("buyNowBtn").onclick = function () {

                // Prevent multiple clicks
                var btn = this;
                btn.disabled = true;
                btn.innerHTML = "Processing...";

                // Get user session data
                var userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

                if (!userId) {
                    Swal.fire('Login Required', "Please login to purchase this course.", 'warning');
                    window.location.href = "login.php";

                    return;
                }

                // Create payment data
                var options = {
                    key: "<?php echo RAZORPAY_KEY_ID; ?>",
                    amount: <?php echo json_encode((int) round((float) $course['price'] * 100)); ?>,
                    currency: "INR",
                    order_id: "<?php echo $order_id; ?>",
                    name: "<?php echo $company_name; ?> Learning Platform",
                    description: "Course: <?php echo addslashes($course['title']); ?>",
                    image: "assets/images/logo.png", // Add your logo path
                    handler: function (response) {
                        // Re-enable button immediately
                        btn.disabled = false;
                        btn.innerHTML = "Buy Now";

                        // Create loading indicator
                        var loadingMsg = document.createElement('div');
                        loadingMsg.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"><div class="bg-white p-6 rounded-lg"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div><p class="mt-4">Verifying payment...</p></div></div>';
                        document.body.appendChild(loadingMsg);

                        // Prepare data exactly as your verify_payment.php expects
                        var formData = new FormData();
                        formData.append('course_id', <?php echo json_encode($course_id); ?>);
                        formData.append('amount', <?php echo json_encode($course['price']); ?>);
                        formData.append('payment_id', response.razorpay_payment_id);
                        formData.append('razorpay_payment_id', response.razorpay_payment_id);
                        formData.append('razorpay_order_id', response.razorpay_order_id);
                        formData.append('razorpay_signature', response.razorpay_signature);

                        // Send verification request
                        fetch("verify_payment.php", {
                            method: "POST",
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                            }
                        })
                            .then(res => {
                                // First check if response is OK
                                if (!res.ok) {
                                    throw new Error('Network response was not ok: ' + res.statusText);
                                }
                                return res.text();
                            })
                            .then(data => {
                                // Remove loading
                                if (document.body.contains(loadingMsg)) {
                                    document.body.removeChild(loadingMsg);
                                }

                                console.log("Verification response:", data);

                                // Trim and check response
                                var trimmedData = data.trim();

                                if (trimmedData === "success") {
                                    // Success - show success message
                                    var successMsg = document.createElement('div');
                                    successMsg.innerHTML = '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"><div class="bg-white p-6 rounded-lg text-center"><i class="ri-checkbox-circle-fill text-4xl text-green-500"></i><h3 class="text-xl font-semibold mt-4">Payment Successful!</h3><p class="text-gray-600 mt-2">Course purchased successfully. Redirecting...</p></div></div>';
                                    document.body.appendChild(successMsg);

                                    // Redirect after 2 seconds
                                    setTimeout(function () {
                                        window.location.href = "course-detail.php?id=" + <?php echo json_encode($course_id); ?>;
                                    }, 2000);
                                } else if (trimmedData === "user_not_logged_in") {
                                    Swal.fire('Error', "You are not logged in. Please login and try again.", 'error');
                                    window.location.href = "login.php";
                                } else if (trimmedData === "invalid_input") {
                                    Swal.fire('Error', "Invalid payment data. Please try again.", 'error');
                                    location.reload();
                                } else if (trimmedData === "payment_failed") {
                                    Swal.fire('Error', "Payment failed to save. Please contact support.", 'error');
                                } else {
                                    // Unknown response
                                    console.error("Unknown verification response:", trimmedData);
                                    Swal.fire('Error', "Payment verification returned unexpected response: " + trimmedData, 'error');
                                }
                            })
                            .catch(error => {
                                // Remove loading
                                if (document.body.contains(loadingMsg)) {
                                    document.body.removeChild(loadingMsg);
                                }

                                console.error("Fetch error:", error);
                                Swal.fire('Error', "Payment verification failed. Error: " + error.message, 'error');
                                btn.disabled = false;
                                btn.innerHTML = "Buy Now";
                            });
                    },
                    prefill: {
                        name: <?php echo isset($_SESSION['user_name']) ? json_encode($_SESSION['user_name']) : '""'; ?>,
                        email: <?php echo isset($_SESSION['user_email']) ? json_encode($_SESSION['user_email']) : '""'; ?>,
                        contact: "" // Add phone if available
                    },
                    theme: {
                        color: "#054b40"
                    },
                    modal: {
                        ondismiss: function () {
                            // Re-enable button if modal is closed
                            btn.disabled = false;
                            btn.innerHTML = "Buy Now";
                        }
                    }
                };

                // Check if Razorpay is loaded
                if (typeof Razorpay === 'undefined') {
                    Swal.fire('Error', "Payment gateway not loaded. Please refresh the page.", 'error');
                    btn.disabled = false;
                    btn.innerHTML = "Buy Now";
                    return;
                }

                // Create Razorpay instance
                var rzp = new Razorpay(options);

                // Handle payment failure
                rzp.on('payment.failed', function (response) {
                    console.error("Payment failed:", response.error);
                    Swal.fire('Error', "Payment failed. Error: " + response.error.description, 'error');
                    btn.disabled = false;
                    btn.innerHTML = "Buy Now";
                });

                // Open payment modal
                rzp.open();

                // Re-enable button if user closes modal without paying
                rzp.on('modal.closed', function () {
                    btn.disabled = false;
                    btn.innerHTML = "Buy Now";
                });
            };
        </script>
    <?php endif; ?>

    <!-- End -->

    <?php include 'footer.php' ?>


    <!-- Plyr JS -->
    <!-- <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script> -->

    <!-- Scripts -->
    <!-- jQuery (Required for AJAX Reviews) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

    <!--  AJAX Reviews Logic -->
    <script>
        $(document).ready(function () {
            const courseId = <?= $course_id; ?>;

            // F unction to load reviews
            function loadReviews() {
                $.ajax({
                    url: 'ajax/fetch_reviews.php',
                    type: 'GET',
                    data: { course_id: courseId },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            let html = '';
                            if (res.reviews.length === 0) {
                                html = '<p class="text-center text-gray-500 py-4">No reviews yet. Be the first to share your experience!</p>';
                            } else {
                                res.reviews.forEach(review => {
                                    let stars = '';
                                    for (let i = 1; i <= 5; i++) {
                                        stars += `<i class="ri-star-fill ${i <= review.rating ? 'text-yellow-400' : 'text-gray-300'}"></i>`;
                                    }

                                    html += `
                                        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <h6 class="font-semibold text-gray-800 dark:text-gray-100">${review.user_name}</h6>
                                                    <div class="flex text-xs space-x-1">${stars}</div>
                                                </div>
                                                <span class="text-xs text-gray-400">${review.created_at}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">${review.comment}</p>
                                            </div>
                                        `;
                                });
                            }
                            $('#reviews-list').html(html);
                        } else {
                            $('#reviews-list').html(`<p class="text-center text-red-500 py-4">Error loading reviews: ${res.message}</p>`);
                        }
                    },
                    error: function () {
                        $('#reviews-list').html('<p class="text-center text-red-500 py-4">Failed to connect to the server.</p>');
                    }
                });
            }

            // Initial load
            loadReviews();
            // R    e-using SweetAlert functions if they exist, else defining basic ones
            function showSuccess(msg) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Success', text: msg, timer: 2000, showConfirmButton: false });
                } else {
                    alert(msg);
                }
            }
            function showError(msg) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
                } else {
                    alert(msg);
                }
            }
        });
    </script>
</body>

</html>