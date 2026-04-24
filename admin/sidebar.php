<!-- Session and Admin Data Retrieval -->

<?php
// ini_set('session.cookie_lifetime', 500);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    $admin = $_SESSION['admin'];
    $admin_name = $_SESSION['admin_name'];
    $admin_image = $_SESSION['admin_image'];
} else {
    header("Location: login.php");
    exit();
}

?>

<style>
    /* Responsive Logo Header Override */
    .sidenav-menu .logo {
        height: auto !important;
        min-height: 70px;
        line-height: normal !important;
        padding: 12px 0 8px 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidenav-menu .logo-lg {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        width: 100%;
        overflow: hidden;
    }

    .sidenav-menu .logo-lg img {
        height: 35px;
        max-width: 100%;
        object-fit: contain;
        margin-bottom: 4px;
    }

    .sidenav-menu .logo-text {
        font-size: clamp(16px, 1.2vw, 20px);
        font-weight: 600;
        font-family: 'Outfit', sans-serif;
        white-space: nowrap;
    }

    .sidenav-menu .logo-text-accent {
        color: #2eb85c;
    }

    /* Make sure small logo acts normal */
    .sidenav-menu .logo-sm {
        display: none;
    }

    .sidebar-enable .sidenav-menu .logo-lg {
        display: none !important;
    }

    .sidebar-enable .sidenav-menu .logo-sm {
        display: block !important;
    }
</style>

<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="index.php" class="logo">
        <span class="logo-light">
            <?php $c = explode(" ", $company_name); ?>
            <span class="logo-lg drop-logo">
                <img src="../SkillRise_logo1.png" alt="logo">
                <span class="logo-text" style="color: #2b3342;"><?php echo $c[0] ?><span
                        class="logo-text-accent"><?php echo isset($c[1]) ? $c[1] : '' ?></span></span>
            </span>
            <span class="logo-sm">
                <img src="../SkillRise_logo1.png" alt="small logo" height="26">
            </span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg drop-logo">
                <img src="../SkillRise_logo1.png" alt="dark logo">
                <span class="logo-text" style="color: #000000;"><?php echo $c[0] ?><span
                        class="logo-text-accent"><?php echo isset($c[1]) ? $c[1] : '' ?></span></span>
            </span>
            <span class="logo-sm">
                <img src="../SkillRise_logo1.png" alt="small logo" height="26">
            </span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-sm-hover">
        <i class="ri-circle-line align-middle"></i>
    </button>

    <!-- Sidebar Menu Toggle Button -->
    <button class="sidenav-toggle-button">
        <i class="ri-menu-5-line fs-20"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
        <i class="ti ti-x align-middle"></i>

    </button>

    <div data-simplebar>

        <!--- Sidenav Menu -->
        <ul class="side-nav">

            <li class="side-nav-item">
                <a href="index.php" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-dashboard-line"></i></span>
                    <span class="menu-text"> Dashboard </span>
                    <span class="badge bg-danger rounded-pill">9+</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#user" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-user-line"></i></span>
                    <span class="menu-text">User</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="user">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-user.php" class="side-nav-link"><span
                                    class="menu-text">All User</span></a></li>
                    </ul>
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="active-user.php" class="side-nav-link"><span
                                    class="menu-text">Active User</span></a></li>
                    </ul>
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="deactive-user.php" class="side-nav-link"><span
                                    class="menu-text">Deactive User</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#tutor" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-user-line"></i></span>
                    <span class="menu-text">Tutors</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="tutor">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-tutor.php" class="side-nav-link"><span
                                    class="menu-text">All Tutors</span></a></li>
                    </ul>
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="active-tutor.php" class="side-nav-link"><span
                                    class="menu-text">Active Tutors</span></a></li>
                    </ul>
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="deactive-tutor.php" class="side-nav-link"><span
                                    class="menu-text">Deactive Tutors</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#category" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-menu-search-line"></i></span>
                    <span class="menu-text">Category</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="category">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-category.php" class="side-nav-link"><span
                                    class="menu-text">Manage Category</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCourses" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-book-open-line"></i></span>
                    <span class="menu-text">Courses</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarCourses">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-course.php" class="side-nav-link"><span
                                    class="menu-text">Manage Course</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#package" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-stack-line"></i></span>
                    <span class="menu-text">Packages</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="package">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-packages.php" class="side-nav-link"><span
                                    class="menu-text">Manage Packages</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarenrollment" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-school-line"></i></span>
                    <span class="menu-text">Enrollment</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarenrollment">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-enrollment.php" class="side-nav-link"><span
                                    class="menu-text">Manage Enrollment</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebargames" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-gamepad-line"></i></span>
                    <span class="menu-text">Games</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebargames">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-games.php" class="side-nav-link"><span
                                    class="menu-text">Manage Games</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarNotification" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-notification-3-line"></i></span>
                    <span class="menu-text"> Notifications </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarNotification">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="notification.php" class="side-nav-link"><span
                                    class="menu-text">Manage Notifications</span></a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#payment" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-wallet-line"></i></span>
                    <span class="menu-text"> Payments Management </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="payment">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-user-payment.php" class="side-nav-link"><span
                                    class="menu-text">User Payment</span></a></li>
                        <li class="side-nav-item"><a href="manage-tutor-payment.php" class="side-nav-link"><span
                                    class="menu-text">Tutor Payment</span></a></li>
                    </ul>
                </div>
            </li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#place" aria-expanded="false" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-map-pin-line"></i></span>
                    <span class="menu-text">Place Management</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="place">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage-state.php" class="side-nav-link"><span
                                    class="menu-text">State</span></a></li>
                        <li class="side-nav-item"><a href="manage-city.php" class="side-nav-link"><span
                                    class="menu-text">City</span></a></li>
                    </ul>
                </div>
            </li>



            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarfaq" class="side-nav-link">
                    <span class="menu-icon"><i class="ri-question-line"></i></span>
                    <span class="menu-text"> FAQ </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarfaq">
                    <ul class="sub-menu">
                        <li class="side-nav-item"><a href="manage_faq.php" class="side-nav-link"><span
                                    class="menu-text">Manage FAQ</span></a></li>
                    </ul>
                </div>
            </li>
        </ul>

        <div class="clearfix"></div>
    </div>
</div>