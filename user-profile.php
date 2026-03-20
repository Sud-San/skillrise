<?php
session_start();
include 'connection.php';

// USER MUST BE LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ---------------------------------------------
   FETCH USER PERSONAL DETAILS
------------------------------------------------*/
$user_query = $conn->prepare("SELECT * FROM users_tbl WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

/* ---------------------------------------------
   FETCH USER ENROLLED COURSES
------------------------------------------------*/
$course_query = $conn->prepare("
    SELECT c.courses_id, c.title, c.thumbnail, c.description
    FROM enrollments_tbl e 
    JOIN course_tbl c ON e.rcourse_id = c.courses_id
    WHERE e.ruser_id = ?
");
$course_query->bind_param("i", $user_id);
$course_query->execute();
$course_result = $course_query->get_result();
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
            <div class="container relative">
                <div class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800">
                    <div class="grid md:grid-cols-12 items-center gap-6">
                        <div class="lg:col-span-4 md:col-span-6">
                            <div class="md:flex md:items-center md:text-start text-center">
                                <div class="profile-pic">
                                    <input id="pro-img" name="profile-image" type="file" class="hidden" onchange="loadFile(event)" />
                                    <div class="relative h-28 w-28 mx-auto">
                                        <img src="assets/images/team/1.jpg" class="rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800 ring-4 ring-gray-50 dark:ring-gray-800" id="profile-image" alt="">
                                        <label class="absolute inset-0 cursor-pointer" for="pro-img"></label>
                                    </div>
                                </div>
        
                                <div class="md:mt-0 md:ms-4 mt-4">
                                    <h5 class="text-lg font-semibold">Calvin Carlo</h5>
                                    <p class="text-gray-400">calvin@hotmail.com</p>
                                </div>
                            </div>
                        </div>

                        <!--<div class="lg:col-span-8 md:col-span-6 md:text-end text-center">
                            <div class="grid lg:grid-cols-12 grid-cols-1 items-center gap-6">
                                <div class="lg:col-span-7">
                                    <div class="grid grid-cols-3 mx-auto gap-4">
                                        <div class="counter-box text-center">
                                            <h1 class="text-lg font-semibold mb-1"><span class="counter-value" data-target="457">123</span>+</h1>
                                            <h5 class="counter-head text-gray-400 text-sm font-medium">Projects</h5>
                                        </div>
                                        
                                        <div class="counter-box text-center">
                                            <h1 class="text-lg font-semibold mb-1">$<span class="counter-value" data-target="4667">1246</span></h1>
                                            <h5 class="counter-head text-gray-400 text-sm font-medium">Earnings</h5>
                                        </div>
                                        
                                        <div class="counter-box text-center">
                                            <h1 class="text-lg font-semibold mb-1"><span class="counter-value" data-target="93">0</span>%</h1>
                                            <h5 class="counter-head text-gray-400 text-sm font-medium">Success Rate</h5>
                                        </div>
                                    </div>
                                </div>
        
                                <div class="lg:col-span-5">
                                    <a href="" class="h-9 px-4 tracking-wider inline-flex items-center justify-center text-sm font-medium rounded-lg bg-primary text-white me-1">Follow</a>
                                    <a href="" class="h-9 px-4 tracking-wider inline-flex items-center justify-center text-sm font-medium rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white">Hire Me</a>
                                </div>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div><!--end container-->

            <div class="container relative mt-6">
                <div class="grid md:grid-cols-12 grid-cols-1 gap-6">
                    <div class="lg:col-span-4 md:col-span-4">
                        <div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                            <h5 class="font-semibold">Personal Details :</h5>
                            <p class="text-gray-400 text-sm mt-2">I have started my career as a trainee and prove my self and achieve all the milestone with good guidance and reach up to the project manager. In this journey, I understand all the procedure which make me a good developer, team leader, and a project manager.</p>

                            <div class="mt-4">
                                <div class="flex items-center">
                                    <i class="ri-mail-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Email :</h6>
                                        <a href="" class="text-gray-400 text-sm">calvin@hotmail.com</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-bookmark-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Skills :</h6>
                                        <a href="" class="text-gray-400 text-sm">html</a>, <a href="" class="text-gray-400 text-sm">css</a>, <a href="" class="text-gray-400 text-sm">js</a>, <a href="" class="text-gray-400 text-sm">mysql</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-italic text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Language :</h6>
                                        <a href="" class="text-gray-400 text-sm">English</a>, <a href="" class="text-gray-400 text-sm">Japanese</a>, <a href="" class="text-gray-400 text-sm">Chinese</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-globe-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Website :</h6>
                                        <a href="" class="text-gray-400 text-sm">www.calvincarlo.com</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-gift-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Birthday :</h6>
                                        <p class="text-gray-400 text-sm mb-0">2nd March, 1996</p>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-map-pin-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Location :</h6>
                                        <a href="" class="text-gray-400 text-sm">Beijing, China</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-phone-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Cell No :</h6>
                                        <a href="" class="text-gray-400 text-sm">(+12) 1254-56-4896</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--end col-->

                    <div class="lg:col-span-8 md:col-span-8">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="assets/images/team/1.jpg" class="size-12 rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                        <div class="ms-3">
                                            <a href="" class="font-medium hover:text-primary">Calvin Carlo</a>
                                            <p class="text-gray-400 text-sm">Online</p>
                                        </div>
                                    </div>

                                    <div class="dropdown relative">
                                        <button data-dropdown-toggle="dropdown" class="dropdown-toggle size-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-[20px] text-center bg-transparent" type="button">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div class="dropdown-menu absolute end-0 m-0 mt-4 z-10 w-36 rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-sm shadow-gray-100 dark:shadow-gray-800 hidden" onclick="event.stopPropagation();">
                                            <ul class="py-2 text-start">
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Profile</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Archive Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Delete Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-red-600">Block user</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="form mt-6">
                                    <div class="grid grid-cols-1 gap-2">
                                        <div>
                                            <label for="comments" class="font-medium hidden text-sm"></label>
                                            <textarea name="comments" id="comments" class="form-input py-2 px-3 h-28 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 outline-none textarea" placeholder="What's on your mind, Calvin?"></textarea>
                                        </div>

                                        <div>
                                            <button type="submit" id="submit" name="send" class="h-11 px-5 tracking-wider inline-flex justify-center items-center text-sm font-medium rounded-lg bg-primary text-white cursor-pointer">Post Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="assets/images/team/1.jpg" class="size-12 rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                        <div class="ms-3">
                                            <a href="" class="font-medium hover:text-primary">Calvin Carlo</a>
                                            <p class="text-gray-400 text-sm">Online</p>
                                        </div>
                                    </div>

                                    <div class="dropdown relative">
                                        <button data-dropdown-toggle="dropdown" class="dropdown-toggle size-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-[20px] text-center bg-transparent" type="button">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div class="dropdown-menu absolute end-0 m-0 mt-4 z-10 w-36 rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-sm shadow-gray-100 dark:shadow-gray-800 hidden" onclick="event.stopPropagation();">
                                            <ul class="py-2 text-start">
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Profile</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Archive Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Delete Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-red-600">Block user</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <p class="text-gray-400 text-sm mb-1">My First Blog Post</p>
                                    <img src="assets/images/blogs/2.jpg" class="rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                <a href="" class="text-gray-400 hover:text-red-600"><i class="ri-thumb-up-line align-middle text-xl"></i><span class="text-sm ms-1">Like</span></a>
                                            </div>
                                            
                                            <div class="flex items-center ms-3">
                                                <a href="" class="text-gray-400 hover:text-primary"><i class="ri-chat-1-line align-middle text-xl"></i><span class="text-sm ms-1">Comments</span></a>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <a href="" class="text-gray-400 hover:text-primary"><i class="ri-share-forward-line align-middle text-xl"></i><span class="text-sm ms-1">Share</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="assets/images/team/1.jpg" class="size-12 rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                        <div class="ms-3">
                                            <a href="" class="font-medium hover:text-primary">Calvin Carlo</a>
                                            <p class="text-gray-400 text-sm">Online</p>
                                        </div>
                                    </div>

                                    <div class="dropdown relative">
                                        <button data-dropdown-toggle="dropdown" class="dropdown-toggle size-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-[20px] text-center bg-transparent" type="button">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div class="dropdown-menu absolute end-0 m-0 mt-4 z-10 w-36 rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-sm shadow-gray-100 dark:shadow-gray-800 hidden" onclick="event.stopPropagation();">
                                            <ul class="py-2 text-start">
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Profile</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Archive Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Delete Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-red-600">Block user</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <p class="text-gray-400 text-sm mb-1">My First Blog Post</p>
                                    <img src="assets/images/blogs/3.jpg" class="rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                <a href="" class="text-gray-400 hover:text-red-600"><i class="ri-thumb-up-line align-middle text-xl"></i><span class="text-sm ms-1">Like</span></a>
                                            </div>
                                            
                                            <div class="flex items-center ms-3">
                                                <a href="" class="text-gray-400 hover:text-primary"><i class="ri-chat-1-line align-middle text-xl"></i><span class="text-sm ms-1">Comments</span></a>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <a href="" class="text-gray-400 hover:text-primary"><i class="ri-share-forward-line align-middle text-xl"></i><span class="text-sm ms-1">Share</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="assets/images/team/1.jpg" class="size-12 rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                        <div class="ms-3">
                                            <a href="" class="font-medium hover:text-primary">Calvin Carlo</a>
                                            <p class="text-gray-400 text-sm">Online</p>
                                        </div>
                                    </div>

                                    <div class="dropdown relative">
                                        <button data-dropdown-toggle="dropdown" class="dropdown-toggle size-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-[20px] text-center bg-transparent" type="button">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div class="dropdown-menu absolute end-0 m-0 mt-4 z-10 w-36 rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-sm shadow-gray-100 dark:shadow-gray-800 hidden" onclick="event.stopPropagation();">
                                            <ul class="py-2 text-start">
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Profile</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Archive Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-gray-400 dark:text-white/70 hover:text-gray-900 dark:hover:text-white">Delete Post</a>
                                                </li>
                                                <li>
                                                    <a href="" class="block text-sm py-1 px-4 text-red-600">Block user</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <p class="text-gray-400 text-sm mb-1">My First Blog Post</p>
                                    <img src="assets/images/blogs/1.jpg" class="rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800" alt="">

                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                <a href="" class="text-gray-400 hover:text-red-600"><i class="ri-thumb-up-line align-middle text-xl"></i><span class="text-sm ms-1">Like</span></a>
                                            </div>
                                            
                                            <div class="flex items-center ms-3">
                                                <a href="" class="text-gray-400 hover:text-primary"><i class="ri-chat-1-line align-middle text-xl"></i><span class="text-sm ms-1">Comments</span></a>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <a href="" class="text-gray-400 hover:text-primary"><i class="ri-share-forward-line align-middle text-xl"></i><span class="text-sm ms-1">Share</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <a href="" class="text-gray-400 hover:text-primary">See More Posts <i class="ri-arrow-right-line"></i></a>
                            </div>
                        </div>
                    </div><!--end col-->
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section--><!--end section-->
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