<?php
session_start();
include 'connection.php';

?>




<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    
<!-- dream class  testimonial.html       -->
	<head>
        <?php include 'headtag.php';?>
		<style>
			.testimonial-card {
		  min-height: 250px; /* adjust as needed */
		  display: flex;
		  flex-direction: column;
		  justify-content: space-between;
		}

		</style>
    </head>
    
    <body class="text-gray-900 dark:text-white dark:bg-gray-900">
	<?php include 'header.php';?>

        <!-- Start Hero -->
        <section class="relative table bg-primary w-full py-24">
            <div class="absolute inset-0 bg-[url('../assets/images/bg/box.html')] bg-no-repeat bg-center bg-cover"></div>
            <div class="container relative">
                <div class="grid grid-cols-1 text-center mt-10">
                    <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Testimonials / Reviews</h3>
                    
                    <ul class="tracking-[0.5px] inline-block mt-2">
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php">Codezy</a></li>
                        <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                        <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page">Testimonials</li>
                    </ul>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Hero -->

        <!-- Start -->
        <section class="relative lg:py-24 py-16">
            <div class="container relative">
                    <div class="grid lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-6">
    <?php

    // First, let's check what columns exist in feedback_tbl
    $check_columns = "SHOW COLUMNS FROM feedback_tbl";
    $column_result = mysqli_query($conn, $check_columns);
    $columns = array();
    while ($col = mysqli_fetch_assoc($column_result)) {
        $columns[] = $col['Field'];
    }
    
    // Determine which columns to use based on what exists
    $comment_column = '';
    $rating_column = '';
    $user_column = '';
    $date_column = '';
    
    // Check for comment column (might be called message, feedback, review, etc.)
    $possible_comment_cols = ['comment', 'message', 'feedback', 'review', 'description', 'text'];
    foreach ($possible_comment_cols as $col) {
        if (in_array($col, $columns)) {
            $comment_column = $col;
            break;
        }
    }
    
    // Check for rating column
    $possible_rating_cols = ['ratings', 'rating', 'stars', 'score'];
    foreach ($possible_rating_cols as $col) {
        if (in_array($col, $columns)) {
            $rating_column = $col;
            break;
        }
    }
    
    // Check for user column
    $possible_user_cols = ['ruser_id', 'user_id', 'student_id', 'tutor_id'];
    foreach ($possible_user_cols as $col) {
        if (in_array($col, $columns)) {
            $user_column = $col;
            break;
        }
    }
    
    // Check for date column
    $possible_date_cols = ['feedback_timestamp', 'created_at', 'date', 'timestamp'];
    foreach ($possible_date_cols as $col) {
        if (in_array($col, $columns)) {
            $date_column = $col;
            break;
        }
    }
    
    // Build query based on available columns
    if (!empty($comment_column) && !empty($rating_column)) {
        $select_fields = "f.$comment_column, f.$rating_column";
        $order_by = !empty($date_column) ? "ORDER BY f.$date_column DESC" : "";
        
        $sql = "SELECT $select_fields 
                FROM feedback_tbl f 
                WHERE f.$comment_column IS NOT NULL 
                AND f.$comment_column != ''
                AND f.$rating_column > 0
                $order_by
                LIMIT 9";
                
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($fb = mysqli_fetch_assoc($result)) { 
                $profile_pic = 'assets/images/default-profile.png';
        ?>
        <div class="space-y-8">
            <div class="testimonial-card relative overflow-hidden rounded-lg shadow-md shadow-gray-100 dark:shadow-gray-800 p-6 bg-white dark:bg-gray-900 h-64">
			<div class="relative z-1">
				<p class="text-gray-400"><?php echo htmlspecialchars($fb[$comment_column]); ?></p>
                    <ul class="list-none mb-0 text-amber-400 mt-2">
                        <?php 
                        $rating = intval($fb[$rating_column]);
                        for ($i=1; $i<=5; $i++): ?>
                            <li class="inline">
                                <i class="<?php echo $i <= $rating ? 'ri-star-fill' : 'ri-star-line'; ?>"></i>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <i class="ri-double-quotes-l text-primary/5 text-9xl absolute -top-5 start-0 z-0"></i>
            </div>

            <div class="flex items-center mt-4">
                <div class="size-10">
                    <img src="<?php echo $profile_pic; ?>" 
                         class="size-full rounded-full" 
                         alt="User"
                         onerror="this.onerror=null; this.src='assets/images/default-profile.png';">
                </div>
                <div class="ps-3">
                    <a href="#" class="font-semibold hover:text-primary duration-500">
                        Codezy User
                    </a>
                    <p class="text-sm text-gray-400">Student</p>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
            // Show sample testimonials if no real data
            show_sample_testimonials();
        }
    } else {
        // Show sample testimonials if required columns don't exist
        show_sample_testimonials();
    }
    
    // Function to show sample testimonials
    function show_sample_testimonials() {
        $sample_testimonials = [
            [
                'comment' => '"Codezy transformed my learning experience. The courses are well-structured and the tutors are incredibly supportive!"',
                'rating' => 5,
                'name' => 'Happy Student',
                'role' => 'Student'
            ],
            [
                'comment' => '"The platform is user-friendly and the content is top-notch. I\'ve learned so much in just a few weeks!"',
                'rating' => 5,
                'name' => 'Satisfied Learner',
                'role' => 'Student'
            ],
            [
                'comment' => '"As a working professional, the flexible schedule was perfect for me. Highly recommended!"',
                'rating' => 4,
                'name' => 'Busy Professional',
                'role' => 'Developer'
            ],
            [
                'comment' => '"The quality of instruction is outstanding. The tutors are knowledgeable and patient."',
                'rating' => 5,
                'name' => 'Aspiring Developer',
                'role' => 'Student'
            ],
            [
                'comment' => '"Best online learning platform I\'ve used. The projects helped me build a strong portfolio."',
                'rating' => 5,
                'name' => 'Career Changer',
                'role' => 'Student'
            ],
            [
                'comment' => '"The community support is amazing. Always someone ready to help when you\'re stuck."',
                'rating' => 4,
                'name' => 'Community Member',
                'role' => 'Learner'
            ]
        ];
        
        foreach ($sample_testimonials as $testimonial) {
    ?>
        <div class="space-y-8">
            <div class="testimonial-card relative overflow-hidden rounded-lg shadow-md shadow-gray-100 dark:shadow-gray-800 p-6 bg-white dark:bg-gray-900 h-64">
			<div class="relative z-1">
				<p class="text-gray-400"><?php echo $testimonial['comment']; ?></p>
                    <ul class="list-none mb-0 text-amber-400 mt-2">
                        <?php 
                        $rating = $testimonial['rating'];
                        for ($i=1; $i<=5; $i++): ?>
                            <li class="inline">
                                <i class="<?php echo $i <= $rating ? 'ri-star-fill' : 'ri-star-line'; ?>"></i>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <i class="ri-double-quotes-l text-primary/5 text-9xl absolute -top-5 start-0 z-0"></i>
            </div>

            <div class="flex items-center mt-4">
                <div class="size-10">
                    <img src="assets/images/default-profile.png" 
                         class="size-full rounded-full" 
                         alt="<?php echo $testimonial['name']; ?>"
                         onerror="this.onerror=null; this.src='assets/images/default-profile.png';">
                </div>
                <div class="ps-3">
                    <a href="#" class="font-semibold hover:text-primary duration-500">
                        <?php echo $testimonial['name']; ?>
                    </a>
                    <p class="text-sm text-gray-400"><?php echo $testimonial['role']; ?></p>
                </div>
            </div>
        </div>
    <?php
        }
    }
    ?>
</div>

            <div class="container relative md:mt-24 mt-16">
                <div class="md:flex justify-center">
                    <div class="lg:w-3/4 md:w-4/5">
                        <div class="text-center pb-6">
                            <h4 class="mb-4 md:leading-normal text-3xl leading-normal font-semibold">Questions & Answers</h4>
                            <p class="text-gray-400 max-w-xl mx-auto">Codezy offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                        </div>           

                        <div id="accordion-collapseone" data-accordion="collapse" class="mt-6">
                            <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden">
                                <h2 class="font-medium" id="accordion-collapse-heading-1">
                                    <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1">
                                        <span>How does it work ?</span>
                                        <svg data-accordion-icon class="size-5 rotate-180 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
                                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-gray-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative shadow-sm shadow-gray-200 dark:shadow-gray-700 bg-white dark:bg-gray-900 rounded-lg overflow-hidden mt-4">
                                <h2 class="font-medium" id="accordion-collapse-heading-2">
                                    <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start cursor-pointer" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                                        <span>Do I need a designer to use Codezy ?</span>
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
                            </div>
                        </div>
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End -->
		
		<?php include 'footer.php'?>

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
        <!-- Back to top -->

        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>

<!-- dream class  testimonial.html       -->
</html>