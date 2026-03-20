<?php
// Error reporting settings
//error_reporting(0); // Turn off error reporting for production
ini_set('display_errors', 0);

session_start();

// Start output buffering to catch any output before headers
ob_start();

try {
    include 'connection.php';
    
    if (!$conn) {
        throw new Exception("We're experiencing technical difficulties. Please try again later.");
    }
    
    // Fetch categories for language section
    $str = "SELECT * FROM category_tbl WHERE category_status = 1";
    $data = mysqli_query($conn, $str);
    
    if (!$data) {
        throw new Exception("Unable to load categories. Please try again.");
    }
    
    $errorOccurred = false;
    $errorMessage = '';
    
} catch (Exception $e) {
    $errorOccurred = true;
    $errorMessage = $e->getMessage();
}

// Clear any unwanted output
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    
<!-- dream class  index.html -->
    <head>
        <?php include 'headtag.php';?>
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
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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

            /* ===== TUTOR PROFILE - SMALLER SIZE ===== */
            .tutor-profile-circle {
                width: 130px !important;
                height: 130px !important;
            }

            .tutor-card-wrapper {
                max-width: 170px;
                margin: 0 auto;
            }

            .tutor-card-wrapper .p-4 a {
                font-size: 14px !important;
            }

            .tutor-card-wrapper .p-4 p {
                font-size: 12px !important;
            }
            /* ======================================== */
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
        // If there's a critical error, show it and stop execution
        if ($errorOccurred && !$conn) {
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
        
        <!-- Hero Start -->
        <section class="relative overflow-hidden md:py-48 py-36 bg-primary">
            <div class="container relative">
                <div class="grid md:grid-cols-12 grid-cols-1 items-center mt-10 gap-6">
                    <div class="lg:col-span-7 md:col-span-6">
                        <h1 class="font-semibold lg:leading-normal leading-normal tracking-wide text-4xl lg:text-5xl text-white mb-5">
                            <?php 
                            try {
                                if (isset($_SESSION['user_name']) && 
                                    isset($_SESSION['user_role']) && 
                                    ($_SESSION['user_role'] === 'student' || $_SESSION['user_role'] === 'tutor')
                                ): ?>
                                    Welcome, 
                                    <span style="color: white;"><i><?php echo htmlspecialchars($_SESSION['user_name']); ?></i></span>!
                                    <br>
                                    Continue Your Online Learning Journey
                                <?php else: ?>
                                    Welcome to Your Online <br> Learning Journey
                                <?php endif;
                            } catch (Exception $e) {
                                echo 'Welcome to Your Online Learning Journey';
                            }
                            ?>
                        </h1>
                        
                        <p class="text-white/50 text-lg max-w-xl"><?php echo $company_name; ?> offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                        
                        <div class="mt-6">
                            <form class="relative max-w-xl" onsubmit="event.preventDefault(); window.location.href='signup.php';">
                                <input type="email" id="subemail" name="name" class="p-4 pe-40 w-full h-[50px] outline-none bg-transparent border border-gray-200/20 text-gray-100 rounded-lg" placeholder="Enter your email id..">
                                <button type="submit" onclick="window.location.href='signup.php'" class="h-11 px-4 tracking-wider justify-center items-center text-sm font-medium rounded-lg bg-yellow-500 text-white absolute top-0.75 end-0.75 cursor-pointer">Subscribe Now</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-5 md:col-span-6">
                        <div class="grid grid-cols-2 relative md:gap-4 gap-2">
                            <div class="grid grid-cols-1 md:gap-4 gap-2 text-center h-fit mt-14">
                                <div class="p-2 rounded-full bg-white/10 md:w-45 w-40 mx-auto">
                                    <div class="flex items-center text-start">
                                        <div class="flex items-center justify-center h-14 min-w-14 bg-red-500 text-center rounded-full me-2">
                                            <i class="ri-user-fill text-2xl text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-white/70">Best</span>
                                            <span class="text-white font-medium block text-lg">Mentor</span>
                                        </div>
                                    </div>
                                </div>

                                <img src="assets/images/h2.jpg" class="rounded-full" alt="">
                            </div>

                            <div class="grid grid-cols-1 md:gap-4 gap-2 text-center h-fit">
                                <img src="assets/images/h1.jpg" class="rounded-full" alt="">

                                <div class="p-2 rounded-full bg-white/10 md:w-45 w-40 mx-auto">
                                    <div class="flex items-center text-start">
                                        <div class="flex items-center justify-center h-14 min-w-14 bg-sky-500 text-center rounded-full me-2">
                                            <i class="ri-honour-fill text-2xl text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-white/70">Popular</span>
                                            <span class="text-white font-medium block text-lg">Courses</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="absolute top-0 start-1/2 ltr:-trangray-1/2 rtl:trangray-1/2 mover">
                                <span class="after:absolute after:start-0 after:bottom-1/2 after:trangray-y-1/2 after:h-2.5 after:w-10 after:rounded-md after:bg-yellow-500/20 relative after:z-10"></span>
                                <span class="after:absolute after:start-0 after:bottom-1/2 after:trangray-y-1/2 after:rotate-90 after:h-2.5 after:w-10 after:rounded-md after:bg-yellow-500/20 relative after:z-10"></span>
                            </div>

                            <div class="absolute bottom-0 start-1/2 ltr:-trangray-1/2 rtl:trangray-1/2 mover-2">
                                <span class="flex size-12 bg-yellow-500/20 rounded-full"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Start -->
        <section class="relative lg:py-24 py-16">
            <div class="container relative">
                <div class="container relative md:mt-15 mt-16">
                    <div class="grid md:grid-cols-12 grid-cols-1 items-center gap-6">
                        <div class="lg:col-span-6 md:col-span-7">
                            <div class="relative">
                                <div class="relative md:shrink-0 lg:me-0 me-10">
                                    <img class="object-cover md:w-96 w-84 h-full rounded-lg rounded-ss-[60px] shadow-sm shadow-gray-100 dark:shadow-gray-800" src="assets/images/1.jpg" alt="">
                                </div>

                                <div class="absolute bottom-15 lg:end-6 end-0">
                                    <div class="relative z-1 top-4 xl:text-start lg:text-end text-end p-2 rounded-full bg-white dark:bg-gray-900 w-45 mx-auto shadow-sm shadow-gray-100 dark:shadow-gray-800">
                                        <div class="flex items-center text-start">
                                            <div class="flex items-center justify-center h-12 min-w-12 bg-primary text-center rounded-full me-2">
                                                <i class="ri-airplay-line text-xl text-white"></i>
                                            </div>
                                            <div class="flex-1">
                                                <span class="text-gray-400">Total Courses</span>
                                                <?php
                                                try {
                                                    $courseCountQuery = "SELECT COUNT(*) as total FROM course_tbl WHERE course_status = 1";
                                                    $courseCountResult = mysqli_query($conn, $courseCountQuery);
                                                    if (!$courseCountResult) {
                                                        throw new Exception();
                                                    }
                                                    $courseCount = mysqli_fetch_assoc($courseCountResult)['total'];
                                                } catch (Exception $e) {
                                                    $courseCount = 0;
                                                }
                                                ?>
                                                <span class="font-medium block text-lg"><span class="counter-value" data-target="<?php echo $courseCount; ?>"><?php echo $courseCount; ?></span></span>
                                            </div>
                                        </div>
                                    </div>
    
                                    <div class="relative md:shrink-0">
                                        <div class="p-2 rounded-lg rounded-ee-[60px] bg-white dark:bg-gray-900">
                                            <img class="object-cover size-48 rounded-lg rounded-ee-[60px] shadow-sm shadow-gray-100 dark:shadow-gray-800" src="assets/images/2.jpg" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-6 md:col-span-5">
                            <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Navigate success with <br> our AI expertise</h4>
                            <p class="text-gray-400"><?php echo $company_name; ?> is more than just an e-learning platform – it's your launchpad for growth. We bring together passionate learners and expert tutors to create a space where knowledge flows seamlessly. From coding to creativity, every course is built to sharpen your skills and boost your career.</p>

                            <div class="grid lg:grid-cols-2 gap-6">
                                <ul class="list-none text-gray-400">
                                    <li class="mt-3 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span>Business Consultation</li>
                                    <li class="mt-3 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span>Salary Scheduling</li>
                                    <li class="mt-3 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span>Earning Reports</li>
                                </ul>

                                <ul class="list-none text-gray-400">
                                    <li class="mt-3 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span>Archive Management</li>
                                    <li class="mt-3 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span>Digital Solutions</li>
                                    <li class="mt-3 flex items-center"><span class="inline-flex justify-center items-center size-5 rounded-full bg-primary/10 text-primary border border-primary/20 me-2"><i class="ri-check-line"></i></span>World-class support</li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <a href="courses.php" class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary/10 hover:bg-primary text-primary hover:text-white">Explore More <i class="ri-arrow-right-line align-middle ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular Categories Section - Slideshow -->
                <div class="container relative md:mt-24 mt-16">
                    <div class="grid grid-cols-1 pb-6 text-center">
                        <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Popular Categories</h4>
                        <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                    </div>

                    <?php
                    try {
                        $categoryQuery = "SELECT c.category_id, c.category_name, c.category_code,
                                        (SELECT COUNT(*) FROM course_tbl co WHERE co.category_id = c.category_id AND co.course_status = 1) AS course_count 
                                        FROM category_tbl c WHERE c.category_status = 1";
                        $categoryResult = mysqli_query($conn, $categoryQuery);
                        
                        if (!$categoryResult || mysqli_num_rows($categoryResult) === 0) {
                            throw new Exception("Categories currently unavailable");
                        }

                        $categoryMeta = [
                            'HTML' => [
                                'desc' => 'HTML is the foundation of web design. Start structuring your site now.',
                                'img'  => 'html.png'
                            ],
                            'PHP' => [
                                'desc' => 'PHP powers the web. Learn to create interactive and dynamic websites.',
                                'img'  => 'php.png'
                            ],
                            'JAVA' => [
                                'desc' => 'Java is robust and scalable. Ideal for mobile and enterprise software.',
                                'img'  => 'java.png'
                            ],
                            'PYTHON' => [
                                'desc' => 'Python is simple yet powerful. Perfect for AI, ML, and web development.',
                                'img'  => 'python.png'
                            ],
                            'UNIX' => [
                                'desc' => 'Master Linux and shell scripting to automate tasks and manage systems.',
                                'img'  => 'unix.png'
                            ]
                        ];
                    ?>

                    <style>
                        #catTrack { scrollbar-width: none; }
                        #catTrack::-webkit-scrollbar { display: none; }
                        .cat-dot { width: 10px; height: 10px; border-radius: 50%; background: #d1d5db; border: none; cursor: pointer; transition: background 0.3s, transform 0.3s; padding: 0; }
                        .cat-dot.active { background: var(--color-primary, #0ea5e9); transform: scale(1.3); }
                    </style>

                    <!-- Slider Wrapper -->
                    <div style="position: relative; margin-top: 1.5rem;">

                        <!-- Prev Button -->
                        <button id="catPrev" type="button" style="position:absolute; left:-20px; top:50%; transform:translateY(-50%); z-index:20; width:40px; height:40px; border-radius:50%; background:#fff; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; cursor:pointer; color: inherit;">
                            <i class="ri-arrow-left-s-line" style="font-size:20px;"></i>
                        </button>

                        <!-- Scrollable Track: overflow hidden so only 4 cards show -->
                        <div style="overflow: hidden;">
                            <div id="catTrack" style="display:flex; gap:20px; transition: transform 0.4s ease;">
                                <?php while ($row = mysqli_fetch_assoc($categoryResult)) { 
                                    $name = $row['category_name'];
                                    $meta = $categoryMeta[$name] ?? ['desc' => "Explore courses in $name.", 'img' => 'default.png'];
                                ?>
                                <div class="cat-card" style="flex: 0 0 calc(25% - 15px); min-width: 0;">
                                    <div class="group bg-white dark:bg-gray-900 rounded-xl shadow shadow-gray-200 dark:shadow-gray-800 p-6 flex flex-col h-full min-h-[280px] transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-transparent hover:border-primary/20">
                                        <div class="flex-grow text-center flex flex-col justify-start">
                                            <img src="assets/images/<?php echo $meta['img']; ?>" alt="<?php echo $name; ?>" class="w-16 h-16 mx-auto mb-4 object-contain">
                                            <h3 class="text-xl font-semibold text-primary"><?php echo $name; ?></h3>
                                            <p class="text-gray-600 dark:text-gray-300 text-sm mt-2 mb-4 leading-snug flex-grow">
                                                <?php echo $meta['desc']; ?>
                                            </p>
                                            <p class="text-sm text-gray-400 mb-1"><?php echo $row['course_count']; ?> Courses Available</p>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <a href="courses-by-language.php?cat_id=<?php echo urlencode($row['category_id']); ?>"
                                               class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                                                View Courses <i class="ri-arrow-right-line align-middle"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Next Button -->
                        <button id="catNext" type="button" style="position:absolute; right:-20px; top:50%; transform:translateY(-50%); z-index:20; width:40px; height:40px; border-radius:50%; background:#fff; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; cursor:pointer; color: inherit;">
                            <i class="ri-arrow-right-s-line" style="font-size:20px;"></i>
                        </button>
                    </div>

                    <!-- Dot Navigation -->
                    <div id="catDots" style="display:flex; justify-content:center; gap:8px; margin-top:20px;"></div>

                    <script>
                    (function () {
                        const track    = document.getElementById('catTrack');
                        const prevBtn  = document.getElementById('catPrev');
                        const nextBtn  = document.getElementById('catNext');
                        const dotsWrap = document.getElementById('catDots');
                        const cards    = track.querySelectorAll('.cat-card');

                        const visibleCount = 4; // show 4 at a time
                        const total        = cards.length;
                        const maxIndex     = Math.max(0, total - visibleCount);
                        let   current      = 0;

                        // Build dots
                        const numDots = maxIndex + 1;
                        for (let i = 0; i < numDots; i++) {
                            const dot = document.createElement('button');
                            dot.className = 'cat-dot' + (i === 0 ? ' active' : '');
                            dot.addEventListener('click', () => goTo(i));
                            dotsWrap.appendChild(dot);
                        }

                        function getCardWidth() {
                            // card width + gap (20px)
                            return cards[0].offsetWidth + 20;
                        }

                        function updateDots() {
                            dotsWrap.querySelectorAll('.cat-dot').forEach((d, i) => {
                                d.classList.toggle('active', i === current);
                            });
                        }

                        function goTo(index) {
                            current = Math.max(0, Math.min(index, maxIndex));
                            track.style.transform = 'translateX(-' + (current * getCardWidth()) + 'px)';
                            updateDots();
                        }

                        prevBtn.addEventListener('click', () => goTo(current - 1));
                        nextBtn.addEventListener('click', () => goTo(current + 1));

                        // Auto-play every 4s
                        setInterval(() => goTo(current >= maxIndex ? 0 : current + 1), 4000);

                        // Recalculate on resize
                        window.addEventListener('resize', () => goTo(current));
                    })();
                    </script>
                    
                    <?php
                    } catch (Exception $e) {
                        echo '
                        <div class="text-center py-10">
                            <div class="inline-flex items-center justify-center size-16 bg-red-50 text-red-500 rounded-full mb-4">
                                <i class="ri-error-warning-line text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Categories Temporarily Unavailable</h3>
                            <p class="text-gray-500 mb-4">We\'re working to restore this feature. Please check back soon.</p>
                            <button onclick="location.reload()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Refresh Page</button>
                        </div>
                        ';
                    }
                    ?>
                </div>

                <!-- Expert Tutors Section -->
                <div class="container relative md:mt-24 mt-16">
                    <div class="grid grid-cols-1 pb-6 text-center">
                        <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Our Expert Tutors</h4>
                        <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                    </div>

                    <?php
                    try {
                        $tutorQuery = "
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
                            LIMIT 4
                        ";

                        $tutorResult = mysqli_query($conn, $tutorQuery);
                        
                        if (!$tutorResult || mysqli_num_rows($tutorResult) === 0) {
                            throw new Exception("Tutor information currently unavailable");
                        }
                    ?>
                    
                    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 mt-6 gap-6">
                        <?php while ($row = mysqli_fetch_assoc($tutorResult)) { ?>

                        <!-- ✅ tutor-card-wrapper limits overall card width -->
                        <div class="group text-center tutor-card-wrapper">

                            <!-- ✅ tutor-profile-circle controls the image circle size (130x130px) -->
                            <div class="relative mx-auto rounded-full overflow-hidden border-4 border-white shadow-md shadow-gray-100 dark:shadow-gray-800 duration-500 tutor-profile-circle">
                                <?php
                                $profilePic = !empty($row['profile_pic']) ? $tutor_profile_path . $row['profile_pic'] : 'assets/images/default-profile.png';
                                if (filter_var($row['profile_pic'], FILTER_VALIDATE_URL)) {
                                    $profileSrc = $tutor_profile_path . $row['profile_pic'];
                                } else {
                                    $profileSrc = $profilePic;
                                }
                                ?>
                                <img src="<?php echo $profileSrc; ?>" 
                                     class="w-full h-full object-cover"
                                     alt="<?php echo $row['tutor_name']; ?>"
                                     onerror="this.onerror=null; this.src='assets/images/default-profile.png';">
                                
                                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-900 rounded-full opacity-0 group-hover:opacity-100 duration-500"></div>

                                <ul class="list-none absolute start-0 end-0 -bottom-20 group-hover:bottom-5 duration-500">
                                    <li class="inline">
                                        <a href="#" class="size-8 inline-flex items-center justify-center bg-primary text-white rounded-full">
                                            <i class="ri-facebook-circle-line"></i>
                                        </a>
                                    </li>
                                    <li class="inline">
                                        <a href="#" class="size-8 inline-flex items-center justify-center bg-primary text-white rounded-full">
                                            <i class="ri-instagram-line"></i>
                                        </a>
                                    </li>
                                    <li class="inline">
                                        <a href="#" class="size-8 inline-flex items-center justify-center bg-primary text-white rounded-full">
                                            <i class="ri-linkedin-line"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="p-4">
                                <a href="tutor-profile.php?id=<?php echo $row['tutor_id']; ?>" class="text-lg font-medium hover:text-primary duration-500">
                                    <?php echo $row['tutor_name']; ?>
                                </a>
                                <p class="text-gray-400">
                                    <?php echo !empty($row['expertise']) ? $row['expertise'] : 'Expert Tutor'; ?>
                                </p>
                            </div>
                        </div>

                        <?php } ?>
                    </div>
                    
                    <?php
                    } catch (Exception $e) {
                        echo '
                        <div class="text-center py-10">
                            <div class="inline-flex items-center justify-center size-16 bg-yellow-50 text-yellow-500 rounded-full mb-4">
                                <i class="ri-user-search-line text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Meet Our Tutors Soon</h3>
                            <p class="text-gray-500">Our expert tutors are currently being featured. Check back shortly.</p>
                        </div>
                        ';
                    }
                    ?>
                </div>

                <!-- Testimonials Section -->
                <div class="container relative md:mt-24 mt-16">
                    <div class="grid grid-cols-1 pb-6 text-center">
                        <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">What Our Students Say?</h4>
                        <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> is a full-service brand agency for purpose driven companies. We build brands that look good, and sound good.</p>
                    </div>

                    <div class="grid grid-cols-1 relative mt-6">
                        <div class="tiny-three-item-icon">
                            <!-- Sample testimonial -->
                            <div class="tiny-slide">
                                <div class="m-2 mx-3">
                                    <div class="relative overflow-hidden rounded-lg shadow-md shadow-gray-100 dark:shadow-gray-800 p-6 bg-white dark:bg-gray-900">
                                        <div class="relative z-1">
                                            <p class="text-gray-400">"<?php echo $company_name; ?> transformed my learning experience. The tutors are amazing!"</p>
                                            <ul class="list-none mb-0 text-amber-400 mt-2">
                                                <li class="inline"><i class="ri-star-fill"></i></li>
                                                <li class="inline"><i class="ri-star-fill"></i></li>
                                                <li class="inline"><i class="ri-star-fill"></i></li>
                                                <li class="inline"><i class="ri-star-fill"></i></li>
                                                <li class="inline"><i class="ri-star-fill"></i></li>
                                            </ul>
                                        </div>
                                        <i class="ri-double-quotes-l text-primary/5 text-9xl absolute -top-5 start-0 z-0"></i>
                                    </div>
                                    
                                    <div class="flex items-center mt-4">
                                        <div class="size-10">
                                            <img src="assets/images/default-profile.png" 
                                                 class="size-full rounded-full" 
                                                 alt="Student">
                                        </div>
                                        <div class="ps-3">
                                            <a href="#" class="font-semibold hover:text-primary duration-500">
                                                Happy Student
                                            </a>
                                            <p class="text-sm text-gray-400">Student</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Start Footer -->
        <?php 
        try {
            include 'footer.php';
        } catch (Exception $e) {
            echo '<div class="text-center py-8 text-gray-500">© ' . date('Y') . ' ' . $company_name . '. All rights reserved.</div>';
        }
        ?>
        
        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>

        <!-- JAVASCRIPTS -->
        <script src="assets/libs/tiny-slider/min/tiny-slider.js"></script>
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        
        <script>
            // Error handling function
            function showError(message) {
                document.getElementById('errorText').textContent = message;
                document.getElementById('errorOverlay').style.display = 'flex';
            }
            
            // Global error handler
            window.onerror = function(msg, url, lineNo, columnNo, error) {
                showError('An unexpected error occurred. Please try again.');
                return false; // Prevents default browser error handling
            };
            
            // Handle image loading errors
            document.addEventListener('DOMContentLoaded', function() {
                const images = document.querySelectorAll('img');
                images.forEach(img => {
                    img.onerror = function() {
                        this.src = 'assets/images/default-placeholder.png';
                        this.alt = 'Image not available';
                    };
                });
            });

            // Initialize testimonials slider
            try {
                var testimonialSlider = tns({
                    container: '.tiny-three-item-icon',
                    items: 3,
                    slideBy: 1,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    autoplayButtonOutput: false,
                    controls: true,
                    nav: false,
                    mouseDrag: true,
                    gutter: 20,
                    controlsText: ['<', '>'],
                    responsive: {
                        0: { items: 1 },
                        768: { items: 2 },
                        1024: { items: 3 }
                    }
                });
            } catch (e) {
                console.log('Testimonial slider init failed');
            }

            // Categories slider is handled inline with native scroll JS
        </script>
        
        <?php 
        try {
            include 'popup_login.php';
        } catch (Exception $e) {
            // Silently fail for popup
        }
        
        // Close database connection
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
        ?>
    </body>
</html>