<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    /* ===== HOVER SUBMENU STYLES ===== */

    .has-submenu .submenu {
        display: block !important;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease;
        pointer-events: none;
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 200px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        z-index: 999;
        padding: 8px 0;
    }

    .has-submenu:hover>.submenu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: all;
    }

    .has-submenu .submenu li a {
        display: flex;
        align-items: center;
        padding: 9px 20px;
        font-size: 14px;
        color: #374151;
        font-weight: 500;
        transition: background 0.18s ease, color 0.18s ease, padding-left 0.18s ease;
    }

    .has-submenu .submenu li a:hover {
        background: #f0fdf4;
        color: #054b40;
        padding-left: 26px;
    }

    .has-submenu .submenu li.has-submenu>a::after {
        content: '›';
        margin-left: auto;
        font-size: 18px;
        line-height: 1;
        color: #9ca3af;
        transition: color 0.18s ease;
    }

    .has-submenu .submenu li.has-submenu:hover>a::after {
        color: #054b40;
    }

    .has-submenu .submenu li.has-submenu {
        position: relative;
    }

    .has-submenu .submenu li.has-submenu>.submenu {
        top: 0;
        left: 100%;
        margin-left: 4px;
        border-radius: 10px;
        transform: translateX(-6px) translateY(0);
    }

    .has-submenu .submenu li.has-submenu:hover>.submenu {
        opacity: 1;
        visibility: visible;
        transform: translateX(0) translateY(0);
        pointer-events: all;
    }

    .submenu .badge-prog {
        color: #2563eb;
    }

    .submenu .badge-design {
        color: #7c3aed;
    }

    .submenu .badge-mkt {
        color: #d97706;
    }

    .submenu .badge-biz {
        color: #059669;
    }

    .navigation-menu>li>a {
        position: relative;
    }

    .navigation-menu>li>a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #054b40;
        border-radius: 2px;
        transition: width 0.22s ease;
    }

    .navigation-menu>li:hover>a::after,
    .navigation-menu>li.active>a::after {
        width: 100%;
    }

    /* ===== AUTH AREA — always far right ===== */
    #nav-auth {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-left: auto;
        /* pushes the block to the right edge */
        flex-shrink: 0;
    }

    /* Profile dropdown */
    .profile-dropdown {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .profile-dropdown .dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 180px;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        z-index: 1000;
    }

    .dark .profile-dropdown .dropdown-menu {
        background: #1f2937;
    }

    .profile-dropdown:hover .dropdown-menu {
        display: block;
        animation: dropFade 0.2s ease forwards;
    }

    @keyframes dropFade {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .profile-dropdown .dropdown-menu ul {
        list-style: none;
        padding: 8px 0;
        margin: 0;
    }

    .profile-dropdown .dropdown-menu .menu-username {
        padding: 8px 16px 10px;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        border-bottom: 1px solid #f0f0f0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dark .profile-dropdown .dropdown-menu .menu-username {
        color: #f9fafb;
        border-color: #374151;
    }

    .profile-dropdown .dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: background 0.15s, color 0.15s;
    }

    .dark .profile-dropdown .dropdown-menu a {
        color: #d1d5db;
    }

    .profile-dropdown .dropdown-menu a:hover {
        background: #f0fdf4;
        color: #054b40;
    }

    .profile-dropdown .dropdown-menu a.logout:hover {
        background: #fef2f2;
        color: #ef4444;
    }

    /* Signup hover submenu — right-aligned */
    #nav-auth .has-submenu {
        position: relative;
    }

    #nav-auth .has-submenu .submenu {
        left: auto;
        right: 0;
        /* align to right edge of button */
        top: calc(100% + 6px);
    }
</style>

<!-- Start Navbar -->
<nav id="topnav" class="defaultscroll is-sticky">
    <div class="container relative" style="display:flex; align-items:center; justify-content:space-between;">

        <!-- Logo -->
        <a class="logo" href="index.php" style="flex-shrink:0;">
            <span class="inline-block dark:hidden">
                <img src="<?php echo $logo1; ?>" class="l-dark" height="60px" width="120px" alt="">
                <img src="<?php echo $logo; ?>" class="l-light" height="60px" width="120px" alt="">
            </span>
            <img src="assets/images/logo-light.png" height="24" class="hidden dark:inline-block" alt="">
        </a>

        <!-- Mobile Toggle -->
        <div class="menu-extras">
            <div class="menu-item">
                <a class="navbar-toggle" id="isToggle" onclick="toggleMenu()">
                    <div class="lines">
                        <span></span><span></span><span></span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Center nav links — sits between logo and auth -->
        <div id="navigation">
            <ul class="navigation-menu nav-light">

                <li><a href="index.php" class="sub-menu-item">Home</a></li>

                <li><a href="aboutus.php" class="sub-menu-item">About Us</a></li>

                <li class="has-submenu parent-menu-item">
                    <a href="javascript:void(0)">Courses <span class="menu-arrow"></span></a>
                    <ul class="submenu">
                        <li class="has-submenu">
                            <a href="courses.php">
                                <i class="ri-book-2-line me-2 text-blue-500"></i> Courses
                            </a>
                            <ul class="submenu">
                                <li><a href="courses.php" class="sub-menu-item">
                                        <i class="ri-book-2-line me-2"></i> All Courses
                                    </a></li>
                                <?php
                                // Fetch distinct categories from the database
                                include 'connection.php';
                                $categoryQuery = "SELECT DISTINCT category_name, category_id FROM category_tbl";
                                $categoryResult = mysqli_query($conn, $categoryQuery);
                                // Loop through categories and create submenu items
                                while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {
                                    $category = htmlspecialchars($categoryRow['category_name']);
                                    $cat_id = $categoryRow['category_id'];

                                    $iconClass = '';
                                    switch ($category) {
                                        case 'programming':
                                            $iconClass = 'ri-code-s-slash-line me-2 badge-prog';
                                            break;
                                        case 'design':
                                            $iconClass = 'ri-palette-line me-2 badge-design';
                                            break;
                                        case 'marketing':
                                            $iconClass = 'ri-bar-chart-line me-2 badge-mkt';
                                            break;
                                        case 'business':
                                            $iconClass = 'ri-briefcase-line me-2 badge-biz';
                                            break;
                                        default:
                                            $iconClass = 'ri-book-2-line me-2';
                                    }

                                    ?>
                                    <li><a href="courses-by-language.php?cat_id=<?= $cat_id ?>" class="sub-menu-item">
                                            <i class="<?= $iconClass ?>"></i> <?= $category ?>
                                        </a></li>
                                <?php } ?>
                            </ul>
                        </li>
                        <li>
                            <a href="game/index.php" class="sub-menu-item">
                                <i class="ri-gamepad-line me-2 text-purple-500"></i> Games
                            </a>
                        </li>
                        <li>
                            <a href="notes.php" class="sub-menu-item">
                                <i class="ri-sticky-note-line me-2 text-yellow-500"></i> Notes
                            </a>
                        </li>
                    </ul>
                </li>

                <li><a href="team.php" class="sub-menu-item">Tutor</a></li>

                <li><a href="contactus.php" class="sub-menu-item">Contact Us</a></li>

            </ul>
        </div>
        <!-- End navigation -->

        <!-- ===== AUTH — RIGHT SIDE (outside #navigation, direct child of container) ===== -->
        <div id="nav-auth">

            <?php if (isset($_SESSION['user_name']) && isset($_SESSION['user_role'])): ?>

                <div class="profile-dropdown">
                    <button type="button" style="background:none;border:none;cursor:pointer;padding:4px;">
                        <span
                            style="width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,0.4);">
                            <img src="<?php echo $_SESSION['user_profile_pic']; ?>"
                                style="width:100%;height:100%;object-fit:cover;" alt="Profile">
                        </span>
                    </button>
                    <div class="dropdown-menu">
                        <ul>
                            <li class="menu-username"><?php echo htmlspecialchars($_SESSION['user_name']); ?></li>

                            <?php if ($_SESSION['user_role'] === 'student'): ?>
                                <li><a href="user-mycourses.php"><i class="ri-book-open-line"></i> My Courses</a></li>
                                <li><a href="user-setting.php"><i class="ri-settings-2-line"></i> Setting</a></li>

                            <?php elseif ($_SESSION['user_role'] === 'tutor'): ?>
                                <li><a href="tutor-dashboard.php"><i class="ri-dashboard-line"></i> Dashboard</a></li>

                            <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                                <li><a href="admin/dashboard.php"><i class="ri-shield-user-line"></i> Admin Panel</a></li>
                            <?php endif; ?>

                            <li><a href="logout.php" class="logout"><i class="ri-logout-circle-r-line"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>

            <?php else: ?>

                <a href="login.php" class="sub-menu-item"
                    style="height:40px;padding:0 18px;display:inline-flex;align-items:center;font-size:13px;font-weight:600;border-radius:6px;background:#fff;border:1.5px solid #fff;color:#054b40;transition:all 0.2s;margin-right:8px;"
                    onmouseover="this.style.background='#fff';this.style.color='#054b40';"
                    onmouseout="this.style.background='#fff';this.style.color='#054b40';">
                    Login
                </a>

                <div class="has-submenu parent-menu-item" style="position:relative;">
                    <a href="javascript:void(0)"
                        style="height:40px;padding:0 18px;display:inline-flex;align-items:center;font-size:13px;font-weight:600;border-radius:6px;background:#fff;color:#054b40;gap:5px;transition:all 0.2s;"
                        onmouseover="this.style.background='#e6f4f1';" onmouseout="this.style.background='#fff';">
                        Signup <i class="ri-arrow-down-s-line" style="font-size:15px;"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="signup.php" class="sub-menu-item">
                                <i class="ri-user-add-line me-2"></i> Signup as Student
                            </a></li>
                        <li><a href="tutor_register.php" class="sub-menu-item">
                                <i class="ri-user-star-line me-2"></i> Signup as Tutor
                            </a></li>
                    </ul>
                </div>

            <?php endif; ?>

        </div>
        <!-- End auth -->

    </div>
</nav>
<!-- End Navbar -->