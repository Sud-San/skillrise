<?php
// Error reporting settings
error_reporting(0);
ini_set('display_errors', 0);

session_start();
ob_start();

try {
    include 'connection.php';

    if (!$conn) {
        throw new Exception("We're experiencing technical difficulties. Please try again later.");
    }

} catch (Exception $e) {
    $errorOccurred = true;
    $errorMessage = $e->getMessage();
}

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<!-- dream class  aboutus.html dream class    -->

<head>
    <?php include 'headtag.php'; ?>
    <style>
        .error-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .retry-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .retry-btn:hover {
            background: #0056b3;
        }

        .section-error {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            margin: 20px 0;
        }

        .section-error-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <!-- Error Overlay -->
    <!-- <div id="errorOverlay" class="error-overlay">
        <div class="error-container">
            <div class="error-icon">
                <i class="ri-error-warning-line"></i>
            </div>
            <h2 class="text-xl font-semibold mb-3">Oops! Something went wrong</h2>
            <p id="errorText" class="text-gray-600 mb-4">We're experiencing technical difficulties.</p>
            <button onclick="location.reload()" class="retry-btn">Try Again</button>
            <p class="text-sm text-gray-500 mt-4">If the problem persists, please contact support.</p>
        </div>
    </div> -->

    <?php
    if (isset($errorOccurred) && $errorOccurred && !isset($conn)) {
        echo '<script>document.getElementById("errorOverlay").style.display = "flex";</script>';
        exit();
    }
    ?>

    <?php
    try {
        include 'header.php';
    } catch (Exception $e) {
        echo '<script>showError("' . addslashes($e->getMessage()) . '");</script>';
    }
    ?>

    <!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.html')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">About Us</h3>

                <ul class="tracking-[0.5px] inline-block mt-2">
                    <li
                        class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white">
                        <a href="index.php"><?php echo $company_name; ?></a>
                    </li>
                    <li
                        class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180">
                        <i class="ri-arrow-right-s-line"></i>
                    </li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white"
                        aria-current="page">About Us</li>
                </ul>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->

    <!-- Start -->
    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <div class="grid md:grid-cols-12 grid-cols-1 items-center gap-6">
                <div class="lg:col-span-6 md:col-span-7">
                    <div class="relative">
                        <div class="relative md:shrink-0 lg:me-0 me-10">
                            <img class="object-cover md:w-96 w-84 h-full rounded-lg rounded-ss-[60px] shadow-sm shadow-gray-100 dark:shadow-gray-800"
                                src="assets/images/1.jpg" alt="<?php echo $company_name; ?> Platform">
                        </div>

                        <div class="absolute bottom-15 lg:end-6 end-0">
                            <div
                                class="relative z-1 top-4 xl:text-start lg:text-end text-end p-2 rounded-full bg-white dark:bg-gray-900 w-45 mx-auto shadow-sm shadow-gray-100 dark:shadow-gray-800">
                                <div class="flex items-center text-start">
                                    <div
                                        class="flex items-center justify-center h-12 min-w-12 bg-primary text-center rounded-full me-2">
                                        <i class="ri-airplay-line text-xl text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-gray-400">Total Courses</span>
                                        <?php
                                        try {
                                            // Get total approved courses count - updated for your database
                                            $courseCountQuery = "SELECT COUNT(*) as total_courses FROM course_tbl WHERE course_status = 1";
                                            $courseCountResult = mysqli_query($conn, $courseCountQuery);

                                            if (!$courseCountResult) {
                                                throw new Exception("Could not fetch course count");
                                            }

                                            $courseCountData = mysqli_fetch_assoc($courseCountResult);
                                            $courseCount = $courseCountData ? $courseCountData['total_courses'] : 0;
                                        } catch (Exception $e) {
                                            $courseCount = 0;
                                        }
                                        ?>
                                        <span class="font-medium block text-lg"><span class="counter-value"
                                                data-target="<?php echo $courseCount; ?>"><?php echo $courseCount; ?></span>+</span>
                                    </div>
                                </div>
                            </div>

                            <div class="relative md:shrink-0">
                                <div class="p-2 rounded-lg rounded-ee-[60px] bg-white dark:bg-gray-900">
                                    <img class="object-cover size-48 rounded-lg rounded-ee-[60px] shadow-sm shadow-gray-100 dark:shadow-gray-800"
                                        src="assets/images/2.jpg" alt="Learning Experience">
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->

                <div class="lg:col-span-6 md:col-span-5">
                    <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Navigate success with <br>
                        our AI expertise</h4>
                    <p class="text-gray-400"><?php echo $company_name; ?> is more than just an e-learning platform –
                        it's your launchpad for growth. We bring together passionate learners and expert tutors to
                        create a space where knowledge flows seamlessly. From coding to creativity, every course is
                        built to sharpen your skills and boost your career.</p>

                    <div class="grid lg:grid-cols-2 gap-6">
                        <ul class="list-none text-gray-400">
                            <li class="mt-3 flex items-center"><span
                                    class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i
                                        class="ri-check-line"></i></span>Business Consultation</li>
                            <li class="mt-3 flex items-center"><span
                                    class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i
                                        class="ri-check-line"></i></span>Salary Scheduling</li>
                            <li class="mt-3 flex items-center"><span
                                    class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i
                                        class="ri-check-line"></i></span>Earning Reports</li>
                        </ul>

                        <ul class="list-none text-gray-400">
                            <li class="mt-3 flex items-center"><span
                                    class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i
                                        class="ri-check-line"></i></span>Archive Management</li>
                            <li class="mt-3 flex items-center"><span
                                    class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i
                                        class="ri-check-line"></i></span>Digital Solutions</li>
                            <li class="mt-3 flex items-center"><span
                                    class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i
                                        class="ri-check-line"></i></span>World-class support</li>
                        </ul>
                    </div>

                    <div class="mt-6">
                        <a href="courses.php"
                            class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary/10 hover:bg-primary text-primary hover:text-white">Explore
                            More <i class="ri-arrow-right-line align-middle ms-1"></i></a>
                    </div>
                </div><!--end col-->
            </div><!--end grid-->
        </div><!--end container-->

        <div class="container relative lg:mt-24 mt-16">
            <div class="grid grid-cols-1 pb-6 text-center">
                <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Our Course Categories</h4>
                <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> offers a more personalized
                    learning experience along with the flexibility of learning at your own pace.</p>
            </div><!--end grid-->

            <?php
            try {
                // Get all categories
                $categoryQuery = "SELECT category_id, category_name, img FROM category_tbl WHERE category_status = 1";
                $categoryResult = mysqli_query($conn, $categoryQuery);

                if (!$categoryResult) {
                    throw new Exception("Could not fetch categories");
                }

                // Get course counts for each category
                $courseCounts = array();
                $countQuery = "SELECT category_id, COUNT(*) as count FROM course_tbl WHERE course_status = 1 GROUP BY category_id";
                $countResult = mysqli_query($conn, $countQuery);

                if ($countResult) {
                    while ($row = mysqli_fetch_assoc($countResult)) {
                        $courseCounts[$row['category_id']] = $row['count'];
                    }
                }

                if (mysqli_num_rows($categoryResult) === 0) {
                    throw new Exception("No categories available at the moment");
                }
                ?>

                <style>
                    #abCatTrack {
                        scrollbar-width: none;
                    }

                    #abCatTrack::-webkit-scrollbar {
                        display: none;
                    }

                    .ab-cat-dot {
                        width: 10px;
                        height: 10px;
                        border-radius: 50%;
                        background: #d1d5db;
                        border: none;
                        cursor: pointer;
                        transition: background 0.3s, transform 0.3s;
                        padding: 0;
                    }

                    .ab-cat-dot.active {
                        background: var(--color-primary, #0ea5e9);
                        transform: scale(1.3);
                    }
                </style>

                <!-- Categories Carousel Wrapper -->
                <div style="position: relative; margin-top: 1.5rem;">

                    <!-- Prev Button -->
                    <button id="abCatPrev" type="button"
                        style="position:absolute; left:-20px; top:50%; transform:translateY(-50%); z-index:20; width:40px; height:40px; border-radius:50%; background:#fff; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; cursor:pointer; color: inherit;">
                        <i class="ri-arrow-left-s-line" style="font-size:20px;"></i>
                    </button>

                    <!-- Scrollable Track -->
                    <div style="overflow: hidden;">
                        <div id="abCatTrack" style="display:flex; gap:20px; transition: transform 0.4s ease;">
                            <?php while ($row = mysqli_fetch_assoc($categoryResult)) {
                                $name = $row['category_name'];
                                $categoryId = $row['category_id'];
                                $courseCount = isset($courseCounts[$categoryId]) ? $courseCounts[$categoryId] : 0;
                                $meta = $categoryMeta[$name] ?? ['desc' => "Explore courses in $name.", 'img' => 'default.png'];
                                ?>
                                <div class="ab-cat-card" style="flex: 0 0 calc(25% - 15px); min-width: 0;">
                                    <div
                                        class="group bg-white dark:bg-gray-900 rounded-lg shadow shadow-gray-200 dark:shadow-gray-800 p-6 flex flex-col h-auto transition hover:shadow-lg duration-300">
                                        <div class="flex-grow text-center flex flex-col justify-start">
                                            <img src="assets/images/<?php echo $row['img']; ?>"
                                                alt="<?php echo $name; ?>" class="w-16 h-16 mx-auto mb-4"
                                                onerror="this.onerror=null; this.src='assets/images/default.png';">
                                            <h3 class="text-xl font-semibold text-primary"><?php echo $name; ?></h3>
                                            <p
                                                class="text-gray-600 dark:text-gray-300 text-sm mt-2 mb-4 line-clamp-2 leading-snug">
                                                <?php echo $meta['desc']; ?>
                                            </p>
                                            <p class="text-sm text-gray-400 mb-1">
                                                <?php echo $courseCount; ?> Course<?php echo $courseCount != 1 ? 's' : ''; ?>
                                                Available
                                            </p>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <a href="courses-by-language.php?cat_id=<?php echo urlencode($categoryId); ?>"
                                                class="inline-block text-sm text-primary hover:underline">
                                                View Courses <i class="ri-arrow-right-line align-middle"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Next Button -->
                    <button id="abCatNext" type="button"
                        style="position:absolute; right:-20px; top:50%; transform:translateY(-50%); z-index:20; width:40px; height:40px; border-radius:50%; background:#fff; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; cursor:pointer; color: inherit;">
                        <i class="ri-arrow-right-s-line" style="font-size:20px;"></i>
                    </button>
                </div>

                <!-- Dot Navigation -->
                <div id="abCatDots" style="display:flex; justify-content:center; gap:8px; margin-top:20px;"></div>

                <?php
            } catch (Exception $e) {
                echo '
                <div class="section-error">
                    <div class="section-error-icon">
                        <i class="ri-book-2-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Categories Coming Soon</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        We\'re currently organizing our course offerings. Check back soon to explore our programming categories!
                    </p>
                </div>
                ';
            }
            ?>

            <div class="container relative md:mt-24 mt-16">
                <div class="grid grid-cols-1 pb-6 text-center">
                    <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Meet Our Expert Tutors</h4>
                    <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> offers a more personalized
                        learning experience along with the flexibility of learning at your own pace.</p>
                </div><!--end grid-->

                <?php
                try {
                    // Updated query to match your database structure
                    $sql = "
                    SELECT 
                        t.tutor_id,
                        t.tutor_name,
                        t.tutor_email,
                        t.tutor_status,
                        t.verification_status,
                        tp.profile_pic,
                        tp.expertise,
                        tp.bio
                    FROM tutor_tbl t
                    LEFT JOIN tutor_profile_tbl tp ON t.tutor_id = tp.tutor_id
                    WHERE t.tutor_status = 1 
                    AND t.verification_status = 'approved'
                    ORDER BY t.tutor_id ASC
                ";

                    $result = mysqli_query($conn, $sql);

                    if (!$result) {
                        throw new Exception("Could not fetch tutor information");
                    }

                    if (mysqli_num_rows($result) === 0) {
                        throw new Exception("No tutors available at the moment");
                    }
                    ?>

                    <style>
                        #abTutorTrack {
                            scrollbar-width: none;
                        }

                        #abTutorTrack::-webkit-scrollbar {
                            display: none;
                        }

                        .ab-tutor-dot {
                            width: 10px;
                            height: 10px;
                            border-radius: 50%;
                            background: #d1d5db;
                            border: none;
                            cursor: pointer;
                            transition: background 0.3s, transform 0.3s;
                            padding: 0;
                        }

                        .ab-tutor-dot.active {
                            background: var(--color-primary, #0ea5e9);
                            transform: scale(1.3);
                        }
                    </style>

                    <!-- Tutors Carousel Wrapper -->
                    <div style="position: relative; margin-top: 1.5rem;">

                        <!-- Prev Button -->
                        <button id="abTutorPrev" type="button"
                            style="position:absolute; left:-20px; top:50%; transform:translateY(-50%); z-index:20; width:40px; height:40px; border-radius:50%; background:#fff; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; cursor:pointer; color: inherit;">
                            <i class="ri-arrow-left-s-line" style="font-size:20px;"></i>
                        </button>

                        <!-- Scrollable Track -->
                        <div style="overflow: hidden;">
                            <div id="abTutorTrack" style="display:flex; gap:24px; transition: transform 0.4s ease;">
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <div class="ab-tutor-card" style="flex: 0 0 calc(25% - 18px); min-width: 0;">
                                        <div class="group text-center">
                                            <div class="relative mx-auto rounded-full overflow-hidden border-4 border-white shadow-md shadow-gray-100 dark:shadow-gray-800 duration-500"
                                                style="width: 130px; height: 130px;">
                                                <?php
                                                $profileSrc = 'assets/images/default-profile.png';
                                                if (!empty($row['profile_pic'])) {
                                                    if (filter_var($row['profile_pic'], FILTER_VALIDATE_URL)) {
                                                        $profileSrc = $row['profile_pic'];
                                                    } elseif (strpos($row['profile_pic'], 'uploads/') === 0) {
                                                        $profileSrc = $row['profile_pic'];
                                                    } else {
                                                        $profileSrc = $tutor_profile_path . $row['profile_pic'];
                                                    }
                                                }
                                                ?>
                                                <img src="<?php echo $profileSrc; ?>" class="w-full h-full object-cover"
                                                    alt="<?php echo $row['tutor_name']; ?>"
                                                    onerror="this.onerror=null; this.src='assets/images/default-profile.png';">

                                                <div
                                                    class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-900 rounded-full opacity-0 group-hover:opacity-100 duration-500">
                                                </div>

                                                <ul
                                                    class="list-none absolute start-0 end-0 -bottom-20 group-hover:bottom-5 duration-500">
                                                    <li class="inline">
                                                        <a href="#"
                                                            class="size-8 inline-flex items-center justify-center bg-primary text-white rounded-full">
                                                            <i class="ri-facebook-circle-line"></i>
                                                        </a>
                                                    </li>
                                                    <li class="inline">
                                                        <a href="#"
                                                            class="size-8 inline-flex items-center justify-center bg-primary text-white rounded-full">
                                                            <i class="ri-instagram-line"></i>
                                                        </a>
                                                    </li>
                                                    <li class="inline">
                                                        <a href="#"
                                                            class="size-8 inline-flex items-center justify-center bg-primary text-white rounded-full">
                                                            <i class="ri-linkedin-line"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="p-4">
                                                <a href="tutor-profile.php?id=<?php echo $row['tutor_id']; ?>"
                                                    class="text-lg font-medium hover:text-primary duration-500">
                                                    <?php echo $row['tutor_name']; ?>
                                                </a>
                                                <p class="text-gray-400" style="font-size: 12px;">
                                                    <?php echo !empty($row['expertise']) ? $row['expertise'] : 'Expert Tutor'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Next Button -->
                        <button id="abTutorNext" type="button"
                            style="position:absolute; right:-20px; top:50%; transform:translateY(-50%); z-index:20; width:40px; height:40px; border-radius:50%; background:#fff; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; cursor:pointer; color: inherit;">
                            <i class="ri-arrow-right-s-line" style="font-size:20px;"></i>
                        </button>
                    </div>

                    <!-- Dot Navigation -->
                    <div id="abTutorDots" style="display:flex; justify-content:center; gap:8px; margin-top:20px;"></div>

                    <?php
                } catch (Exception $e) {
                    echo '
                <div class="section-error">
                    <div class="section-error-icon">
                        <i class="ri-user-star-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Our Expert Team</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Our team of experienced tutors is preparing amazing courses for you. They\'ll be featured here soon!
                    </p>
                </div>
                ';
                }
                ?>

            </div><!--end container-->

            <!--<div class="container relative mt-16">
            <div class="grid md:grid-cols-6 grid-cols-2 justify-center gap-6">
                <div class="mx-auto pt-4">
                    <img src="assets/images/client/amazon.svg" class="h-7" alt="Amazon">
                </div>

                <div class="mx-auto pt-4">
                    <img src="assets/images/client/google.svg" class="h-7" alt="Google">
                </div>
                
                <div class="mx-auto pt-4">
                    <img src="assets/images/client/lenovo.svg" class="h-7" alt="Lenovo">
                </div>
                
                <div class="mx-auto pt-4">
                    <img src="assets/images/client/paypal.svg" class="h-7" alt="PayPal">
                </div>
                
                <div class="mx-auto pt-4">
                    <img src="assets/images/client/shopify.svg" class="h-7" alt="Shopify">
                </div>
                
                <div class="mx-auto pt-4">
                    <img src="assets/images/client/spotify.svg" class="h-7" alt="Spotify">
                </div>
            </div>
        </div>-->
    </section><!--end section-->
    <!-- End -->

    <?php
    try {
        include 'footer.php';
    } catch (Exception $e) {
        echo '<div class="text-center py-8 text-gray-500 dark:text-gray-400">© ' . date('Y') . ' ' . $company_name . '. All rights reserved.</div>';
    }
    ?>

    <!-- Back to top -->
    <a href="#" onclick="topFunction()" id="back-to-top"
        class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i
            class="ri-arrow-up-line"></i></a>
    <!-- Back to top -->

    <!-- JAVASCRIPTS -->
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        // Error handling function
        function showError(message) {
            const errorOverlay = document.getElementById('errorOverlay');
            const errorText = document.getElementById('errorText');

            if (errorOverlay && errorText) {
                errorText.textContent = message;
                errorOverlay.style.display = 'flex';
            }
        }

        // Global error handler
        window.onerror = function (msg, url, lineNo, columnNo, error) {
            showError('An unexpected error occurred. Please try again.');
            return false;
        };

        // Counter animation
        document.addEventListener('DOMContentLoaded', function () {
            const counterElements = document.querySelectorAll('.counter-value');
            counterElements.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                if (isNaN(target)) return;

                let current = 0;
                const increment = target / 50; // Adjust speed
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target + '+';
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current) + '+';
                    }
                }, 30);
            });

            // Image error handling
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.onerror = function () {
                    if (this.src.includes('profile')) {
                        this.src = 'assets/images/default-profile.png';
                    } else {
                        this.src = 'assets/images/default.png';
                    }
                    this.alt = 'Image not available';
                };
            });

            // Initialize Categories Carousel
            initCategoryCarousel();

            // Initialize Tutors Carousel
            initTutorsCarousel();
        });

        // Categories Carousel Function
        function initCategoryCarousel() {
            const track = document.getElementById('abCatTrack');
            const prevBtn = document.getElementById('abCatPrev');
            const nextBtn = document.getElementById('abCatNext');
            const dotsWrap = document.getElementById('abCatDots');
            const cards = track.querySelectorAll('.ab-cat-card');

            const visibleCount = 4;
            const total = cards.length;
            let current = 0;
            let autoplayInterval;

            if (total === 0) return;

            const firstClones = Array.from(cards).slice(0, visibleCount);
            const lastClones = Array.from(cards).slice(-visibleCount);

            lastClones.forEach(card => {
                track.insertBefore(card.cloneNode(true), track.firstChild);
            });
            firstClones.forEach(card => {
                track.appendChild(card.cloneNode(true));
            });

            track.style.transform = `translateX(-${visibleCount * (cards[0].offsetWidth + 20)}px)`;
            current = visibleCount;

            const numDots = total;
            for (let i = 0; i < numDots; i++) {
                const dot = document.createElement('button');
                dot.className = 'ab-cat-dot' + (i === 0 ? ' active' : '');
                dot.addEventListener('click', () => goTo(i));
                // dotsWrap.appendChild(dot);
            }

            function getCardWidth() {
                const allCards = track.querySelectorAll('.ab-cat-card');
                return allCards[0].offsetWidth + 20;
            }

            function updateDots() {
                const dotPosition = ((current - visibleCount) % total + total) % total;
                dotsWrap.querySelectorAll('.ab-cat-dot').forEach((d, i) => {
                    d.classList.toggle('active', i === dotPosition);
                });
            }

            function goTo(index) {
                const cardWidth = getCardWidth();
                current = visibleCount + index;
                track.style.transition = 'transform 0.4s ease';
                track.style.transform = `translateX(-${current * cardWidth}px)`;
                updateDots();
            }

            function nextSmooth() {
                const cardWidth = getCardWidth();
                current++;
                track.style.transition = 'transform 0.4s ease';
                track.style.transform = `translateX(-${current * cardWidth}px)`;

                setTimeout(() => {
                    if (current >= visibleCount + total) {
                        track.style.transition = 'none';
                        current = visibleCount;
                        track.style.transform = `translateX(-${current * cardWidth}px)`;
                    }
                }, 400);

                updateDots();
            }

            function prevSmooth() {
                const cardWidth = getCardWidth();
                current--;
                track.style.transition = 'transform 0.4s ease';
                track.style.transform = `translateX(-${current * cardWidth}px)`;

                setTimeout(() => {
                    if (current < visibleCount) {
                        track.style.transition = 'none';
                        current = visibleCount + total - 1;
                        track.style.transform = `translateX(-${current * cardWidth}px)`;
                    }
                }, 400);

                updateDots();
            }

            function startAutoplay() {
                autoplayInterval = setInterval(() => nextSmooth(), 4000);
            }

            function resetAutoplay() {
                clearInterval(autoplayInterval);
                startAutoplay();
            }

            prevBtn.addEventListener('click', () => {
                prevSmooth();
                resetAutoplay();
            });

            nextBtn.addEventListener('click', () => {
                nextSmooth();
                resetAutoplay();
            });

            dotsWrap.querySelectorAll('.ab-cat-dot').forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    goTo(index);
                    resetAutoplay();
                });
            });

            startAutoplay();

            window.addEventListener('resize', () => {
                const cardWidth = getCardWidth();
                track.style.transform = `translateX(-${current * cardWidth}px)`;
            });
        }

        // Tutors Carousel Function
        function initTutorsCarousel() {
            const track = document.getElementById('abTutorTrack');
            const prevBtn = document.getElementById('abTutorPrev');
            const nextBtn = document.getElementById('abTutorNext');
            const dotsWrap = document.getElementById('abTutorDots');
            const cards = track.querySelectorAll('.ab-tutor-card');

            const visibleCount = 4;
            const total = cards.length;
            let current = 0;
            let autoplayInterval;

            if (total === 0) return;

            const firstClones = Array.from(cards).slice(0, visibleCount);
            const lastClones = Array.from(cards).slice(-visibleCount);

            lastClones.forEach(card => {
                track.insertBefore(card.cloneNode(true), track.firstChild);
            });
            firstClones.forEach(card => {
                track.appendChild(card.cloneNode(true));
            });

            track.style.transform = `translateX(-${visibleCount * (cards[0].offsetWidth + 24)}px)`;
            current = visibleCount;

            const numDots = total;
            for (let i = 0; i < numDots; i++) {
                const dot = document.createElement('button');
                dot.className = 'ab-tutor-dot' + (i === 0 ? ' active' : '');
                dot.addEventListener('click', () => goTo(i));
                // dotsWrap.appendChild(dot);
            }

            function getCardWidth() {
                const allCards = track.querySelectorAll('.ab-tutor-card');
                return allCards[0].offsetWidth + 24;
            }

            function updateDots() {
                const dotPosition = ((current - visibleCount) % total + total) % total;
                dotsWrap.querySelectorAll('.ab-tutor-dot').forEach((d, i) => {
                    d.classList.toggle('active', i === dotPosition);
                });
            }

            function goTo(index) {
                const cardWidth = getCardWidth();
                current = visibleCount + index;
                track.style.transition = 'transform 0.4s ease';
                track.style.transform = `translateX(-${current * cardWidth}px)`;
                updateDots();
            }

            function nextSmooth() {
                const cardWidth = getCardWidth();
                current++;
                track.style.transition = 'transform 0.4s ease';
                track.style.transform = `translateX(-${current * cardWidth}px)`;

                setTimeout(() => {
                    if (current >= visibleCount + total) {
                        track.style.transition = 'none';
                        current = visibleCount;
                        track.style.transform = `translateX(-${current * cardWidth}px)`;
                    }
                }, 400);

                updateDots();
            }

            function prevSmooth() {
                const cardWidth = getCardWidth();
                current--;
                track.style.transition = 'transform 0.4s ease';
                track.style.transform = `translateX(-${current * cardWidth}px)`;

                setTimeout(() => {
                    if (current < visibleCount) {
                        track.style.transition = 'none';
                        current = visibleCount + total - 1;
                        track.style.transform = `translateX(-${current * cardWidth}px)`;
                    }
                }, 400);

                updateDots();
            }

            function startAutoplay() {
                autoplayInterval = setInterval(() => nextSmooth(), 5000);
            }

            function resetAutoplay() {
                clearInterval(autoplayInterval);
                startAutoplay();
            }

            prevBtn.addEventListener('click', () => {
                prevSmooth();
                resetAutoplay();
            });

            nextBtn.addEventListener('click', () => {
                nextSmooth();
                resetAutoplay();
            });

            dotsWrap.querySelectorAll('.ab-tutor-dot').forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    goTo(index);
                    resetAutoplay();
                });
            });

            startAutoplay();

            window.addEventListener('resize', () => {
                const cardWidth = getCardWidth();
                track.style.transform = `translateX(-${current * cardWidth}px)`;
            });
        }
    </script>

    <?php
    // Close database connection
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
    ?>
</body>

</html>