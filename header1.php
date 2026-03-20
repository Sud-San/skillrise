<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<head>
    <style>
        .nav-right {
            display: flex !important;
            justify-content: flex-end !important;
            margin-left: auto !important;
        }
    </style>
</head>

<!-- Start Navbar -->
<nav id="topnav" class="defaultscroll is-sticky">
    <div class="container relative">
        <!-- Start Logo container-->
        <a class="logo" href="index.php">
            <span class="inline-block dark:hidden">
                <img src="<?php echo $logo1; ?>" class="l-dark" height="60px" width="120px" alt="">
                <img src="<?php echo $logo; ?>" class="l-light" height="60px" width="120px" alt="">

            </span>
            <img src="assets/images/logo-light.png" height="24" class="hidden dark:inline-block" alt="">
        </a>
        <!-- End Logo container-->

        <!-- Start Mobile Toggle -->
        <div class="menu-extras">
            <div class="menu-item">
                <a class="navbar-toggle" id="isToggle" onclick="toggleMenu()">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
            </div>
        </div>
        <!-- End Mobile Toggle -->



        <div id="navigation">
            <!-- Navigation Menu-->
            <ul class="navigation-menu nav-left !justify-start nav-light">
                <li class="sub-menu-item">
                    <a href="index.php">Home</a><span class="menu-arrow"></span>
                </li>

                <li><a href="aboutus.php" class="sub-menu-item"> About Us</a></li>

                <li class="has-submenu parent-menu-item">
                    <a href="javascript:void(0)">Courses</a><span class="menu-arrow"></span>
                    <ul class="submenu">
                        <li><a href="courses.php" class="sub-menu-item">Courses</a></li>
                        <li><a href="notes.php" class="sub-menu-item">Notes</a></li>
                    </ul>
                </li>


                <!--<li class="has-submenu parent-parent-menu-item">
                            <a href="javascript:void(0)">Pages</a><span class="menu-arrow"></span>
                            <ul class="submenu">
                               <li><a href="services.php" class="sub-menu-item">Services</a></li>
                                <li><a href="team.php" class="sub-menu-item"> Team</a></li>
                                 <li><a href="pricing.php" class="sub-menu-item">Pricing</a></li>
                                <li><a href="testimonial.php" class="sub-menu-item">Testimonial</a></li>
                                <li><a href="faqs.php" class="sub-menu-item">FAQs</a></li>
                               <li class="has-submenu parent-menu-item"><a href="javascript:void(0)"> User Profile</a><span class="submenu-arrow"></span>
                                    <ul class="submenu">
                                        <li><a href="user-profile.php" class="sub-menu-item">Profile</a></li>
                                        <li><a href="user-billing.php" class="sub-menu-item">Billing</a></li>
                                        <li><a href="user-payment.php" class="sub-menu-item">Payment</a></li>
                                        <li><a href="user-invoice.php" class="sub-menu-item">Invoice</a></li>
                                        <li><a href="user-social.php" class="sub-menu-item">Social</a></li>
                                        <li><a href="user-notification.php" class="sub-menu-item">Notification</a></li>
                                        <li><a href="user-setting.php" class="sub-menu-item">Setting</a></li>
                                    </ul> 
                                </li>
                                <li class="has-submenu parent-menu-item"><a href="javascript:void(0)"> Auth Pages </a><span class="submenu-arrow"></span>
                                    <ul class="submenu">
                                        <li><a href="login.php" class="sub-menu-item">Login</a></li>
                                        <li><a href="signup.php" class="sub-menu-item">Signup</a></li>
                                        <li><a href="forgot_password.php" class="sub-menu-item">Reset Password</a></li>
                                    </ul>  
                                </li>
                                <li class="has-submenu parent-menu-item"><a href="javascript:void(0)"> Utility </a><span class="submenu-arrow"></span>
                                    <ul class="submenu">
                                        <li><a href="terms.php" class="sub-menu-item">Terms of Services</a></li>
                                        <li><a href="privacy.php" class="sub-menu-item">Privacy Policy</a></li>
                                    </ul>  
                                </li>
                                <li class="has-submenu parent-menu-item"><a href="javascript:void(0)"> Special</a><span class="submenu-arrow"></span>
                                    <ul class="submenu">
                                        <li><a href="comingsoon.php" class="sub-menu-item">Coming Soon</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>-->

                <li><a href="team.php" class="sub-menu-item"> Tutor</a></li>

                <li><a href="contactus.php" class="sub-menu-item">Contact Us</a></li>


                <!--Login button Start-->

                <ul class="navigation-menu nav-right !justify-end nav-light">

                    <?php
                    // USER IS LOGGED IN
                    if (isset($_SESSION['user_name']) && isset($_SESSION['user_role'])):
                    ?>

                        <!-- PROFILE DROPDOWN -->
                        <li class="dropdown inline-block relative">
                            <button data-dropdown-toggle="dropdown" class="dropdown-toggle items-center mt-4" type="button">
                                <span class="size-11 inline-flex items-center justify-center rounded-full">
                                    <img src="assets/images/profile.png" class="rounded-full" alt="">
                                </span>
                            </button>

                            <div class="dropdown-menu absolute end-0 mt-2 w-44 rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow hidden">
                                <ul class="py-2 text-start">

                                    <!-- 👤 User Name Display -->
                                    <li class="px-4 py-2 text-sm font-semibold text-gray-700 border-b">
                                        <?php echo $_SESSION['user_name']; ?>
                                    </li>

                                    <?php if ($_SESSION['user_role'] === 'student'): ?>
                                        <!-- 🎓 STUDENT OPTIONS -->
                                        <li>
                                            <a href="user-mycourses.php" class="flex items-center font-semibold py-1.5 px-4 hover:text-primary uppercase">
                                                <i class="ri-book-open-line me-1"></i> My Courses
                                            </a>
                                        </li>

                                        <li>
                                            <a href="user-setting.php" class="flex items-center font-semibold py-1.5 px-4 hover:text-primary uppercase">
                                                <i class="ri-settings-2-line me-1"></i> Setting
                                            </a>
                                        </li>

                                    <?php elseif ($_SESSION['user_role'] === 'tutor'): ?>
                                        <!-- 👨‍🏫 TUTOR OPTIONS -->
                                        <li>
                                            <a href="tutor-dashboard.php" class="flex items-center font-semibold py-1.5 px-4 hover:text-primary uppercase">
                                                <i class="ri-dashboard-line me-1"></i> Dashboard
                                            </a>
                                        </li>

                                        <!-- <li>
                                            <a href="tutor-setting.php" class="flex items-center font-semibold py-1.5 px-4 hover:text-primary uppercase">
                                                <i class="ri-settings-2-line me-1"></i> Setting
                                            </a>
                                        </li> -->

                                    <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                                        <!-- 🛠 ADMIN OPTION -->
                                        <li>
                                            <a href="admin/dashboard.php" class="flex items-center font-semibold py-1.5 px-4 hover:text-primary uppercase">
                                                <i class="ri-shield-user-line me-1"></i> Admin Panel
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- 🚪 LOGOUT FOR ALL -->
                                    <li>
                                        <a href="logout.php" class="flex items-center font-semibold py-1.5 px-4 hover:text-red-500 uppercase">
                                            <i class="ri-logout-circle-r-line me-1"></i> Logout
                                        </a>
                                    </li>

                                </ul>

                            </div>
                        </li>

                    <?php else: ?>

                        <!-- USER IS NOT LOGGED IN → Show Login + Signup -->
                        <li class="inline-block">
                            <a href="login.php" class="h-11 px-6 inline-flex items-center text-sm font-semibold hover:bg-[#054b40] hover:text-white">
                                Login
                            </a>
                        </li>

                        <!--<li class="inline-block ms-2">
										<a href="signup.php" class="h-11 px-6 inline-flex items-center text-sm font-semibold hover:bg-[#3B82F6] hover:text-white">
											Signup
										</a>
									</li>-->

                        <li class="has-submenu parent-menu-item"><a href="javascript:void(0)"> Signup </a><span class="submenu-arrow"></span>
                            <ul class="submenu">
                                <li><a href="signup.php" class="sub-menu-item">Signup as Student</a></li>
                                <li><a href="tutor_register.php" class="sub-menu-item">Signup as Tutor</a></li>
                            </ul>
                        </li>

                    <?php endif; ?>

                </ul>

                <!--Login button End-->
            </ul><!--end navigation menu-->
        </div><!--end navigation-->
    </div><!--end container-->
</nav><!--end header-->
<!-- End Navbar -->