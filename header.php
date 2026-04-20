<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store the current page address to redirect back after login
if (!isset($_SESSION['user_id'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    $auth_pages = ['login.php', 'signup.php', 'logout.php', 'forgot_password.php'];
    if (!in_array($current_page, $auth_pages)) {
        $_SESSION['prelogin_redirect'] = $_SERVER['REQUEST_URI'];
    }
}
?>

<head>
    <link rel="stylesheet" href="assets/css/header.css">
</head>

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
                        <!-- <li>
                            <a href="notes.php" class="sub-menu-item">
                                <i class="ri-sticky-note-line me-2 text-yellow-500"></i> Notes
                            </a>
                        </li> -->
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
                    <button id="profileToggle" onclick="toggleProfileMenu(event)" type="button" style="background:none;border:none;cursor:pointer;padding:4px;">
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

<script>
    function toggleProfileMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const dropdown = document.querySelector('.profile-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('active');
        }
    }

    // Global click listener to close the dropdown when clicking outside
    window.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.profile-dropdown');
        const toggle = document.getElementById('profileToggle');
        if (dropdown && dropdown.classList.contains('active')) {
            // If the click is NOT on the dropdown and NOT on the toggle button, close it
            if (!dropdown.contains(e.target) && (!toggle || !toggle.contains(e.target))) {
                dropdown.classList.remove('active');
            }
        }
    });
</script>