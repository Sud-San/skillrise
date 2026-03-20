<?php
session_start();
include 'connection.php';
?>


<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<!-- dream class  team.html       -->

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
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Teachers</h3>

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
                        aria-current="page">Team</li>
                </ul>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->

    <!-- Start -->
    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <?php

            // ✅ Fetch tutors with their profile data
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
    ORDER BY t.tutor_name ASC LIMIT 8
";

            $result = mysqli_query($conn, $sql);
            $tutorCount = mysqli_num_rows($result);
            ?>

            <?php if ($tutorCount > 0): ?>
                <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 mt-6 gap-6">
                    <?php while ($row = mysqli_fetch_assoc($result)) {
                        // Handle profile picture path
                        $profilePic = 'assets/images/default-profile.png'; // Default
                
                        if (!empty($row['profile_pic'])) {
                            if (filter_var($row['profile_pic'], FILTER_VALIDATE_URL)) {
                                $profilePic = $row['profile_pic']; // Full URL
                            } elseif (strpos($row['profile_pic'], 'assets/') === 0) {
                                $profilePic = $row['profile_pic']; // Relative path starting with assets/
                            } elseif (strpos($row['profile_pic'], 'uploads/') === 0) {
                                $profilePic = $row['profile_pic']; // Relative path starting with uploads/
                            } else {
                                $profilePic = $tutor_profile_path . $row['profile_pic']; // Assume it's a filename in the tutors directory
                            }
                        }

                        ?>
                        <div class="group text-center">
                            <div
                                class="relative mx-auto w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-md shadow-gray-100 dark:shadow-gray-800 duration-500">
                                <img src="<?php echo $profilePic; ?>"
                                    class="w-full h-full object-cover rounded-full group-hover:scale-105 duration-500"
                                    alt="<?php echo htmlspecialchars($row['tutor_name']); ?>"
                                    onerror="this.onerror=null; this.src='assets/images/default-profile.png';">

                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-900 rounded-full opacity-0 group-hover:opacity-100 duration-500">
                                </div>

                                <ul class="list-none absolute start-0 end-0 -bottom-20 group-hover:bottom-5 duration-500">
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
                                    <?php echo htmlspecialchars($row['tutor_name']); ?>
                                </a>
                                <p class="text-gray-400">
                                    <?php echo !empty($row['expertise']) ? htmlspecialchars($row['expertise']) : 'Expert Tutor'; ?>
                                </p>
                                <?php if (!empty($row['bio'])): ?>
                                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">
                                        <?php echo htmlspecialchars(substr($row['bio'], 0, 80)) . (strlen($row['bio']) > 80 ? '...' : ''); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16">
                    <div
                        class="inline-flex items-center justify-center size-20 bg-primary/10 text-primary rounded-full mb-6">
                        <i class="ri-user-search-line text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-3">No Tutors Available</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Our expert teaching team is currently being assembled. Check
                        back soon to meet our amazing tutors!</p>
                    <div class="mt-8">
                        <a href="index.php"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark inline-flex items-center">
                            <i class="ri-arrow-left-line me-2"></i> Back to Home
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div><!--end container-->
        <div class="container relative md:mt-24 mt-16">
            <div class="md:flex justify-center">
                <div class="lg:w-3/4 md:w-4/5">
                    <div class="text-center pb-6">
                        <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Questions & Answers
                        </h4>
                        <p class="text-gray-400 max-w-xl mx-auto"><?php echo $company_name; ?> offers a more
                            personalized learning experience along with the flexibility of learning at your own pace.
                        </p>
                    </div>
                    <?php

                    $query = "SELECT * FROM faq WHERE status=1 ORDER BY id ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <div id="accordion-collapseone" data-accordion="collapse" class="mt-6">
                            <div
                                class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden">
                                <h2 class="font-medium" id="accordion-collapse-heading-1">
                                    <button type="button"
                                        class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer"
                                        data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
                                        aria-controls="accordion-collapse-body-1">
                                        <span><?php echo $row['question']; ?></span>
                                        <svg data-accordion-icon class="size-5 rotate-180 shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-1" class="hidden"
                                    aria-labelledby="accordion-collapse-heading-1">
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400"><?php echo $row['answer']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <!-- <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                <h2 class="font-medium" id="accordion-collapse-heading-2">
                                    <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                                        <span>Do I need a designer to use <?php echo $company_name; ?> ?</span>
                                        <svg data-accordion-icon class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                <h2 class="font-medium" id="accordion-collapse-heading-3">
                                    <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-3" aria-expanded="false" aria-controls="accordion-collapse-body-3">
                                        <span>What do I need to do to start selling ?</span>
                                        <svg data-accordion-icon class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-3" class="hidden" aria-labelledby="accordion-collapse-heading-3">
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                <h2 class="font-medium" id="accordion-collapse-heading-4">
                                    <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-4" aria-expanded="false" aria-controls="accordion-collapse-body-4">
                                        <span>What happens when I receive an order ?</span>
                                        <svg data-accordion-icon class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-4" class="hidden" aria-labelledby="accordion-collapse-heading-4">
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                    </div>
                                </div>
                            </div> -->
                    </div>
                </div>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End -->


    <?php include 'footer.php' ?>

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

<!-- dream class  team.html       -->

</html>