<?php
session_start();
include 'connection.php';

// Helper function to format duration (kept for potential other uses, though logic moved to fetch_courses.php)
function formatDuration($minutes)
{
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 0) {
        return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
    }
    return $mins . 'm';
}
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <?php include 'headtag.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        /* Custom Styles Bridge */
        .sidebar-widget {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            margin-bottom: 1.5rem;
        }

        .dark .sidebar-widget {
            background-color: #1f2937;
            border-color: #374151;
            box-shadow: none;
        }

        .widget-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .checkbox-list li {
            margin-bottom: 0.75rem;
        }

        /* Course Card */
        .course-item {
            margin-right: 10px;
            margin-bottom: 10px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            min-width: 240px;
        }

        .dark .course-item {
            background: #1f2937;
            border-color: #374151;
        }

        .course-item:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transform: translateY(-5px);
        }

        .course-img {
            position: relative;
            overflow: hidden;
            aspect-ratio: 16/10;
        }

        .course-img img {
            width: 100%;
            /* min-width: 200px; */
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .course-item:hover .course-img img {
            transform: scale(1.1);
        }

        .course-tag {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            z-index: 1;
            text-transform: capitalize;
        }

        .tag-beginner {
            background: #00d09c;
        }

        .tag-intermediate {
            background: #ffc107;
            color: #000;
        }

        .tag-advanced {
            background: #ff5252;
        }

        .course-content {
            padding: 20px;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .course-category {
            color: #054b40;
            background: rgba(5, 75, 64, 0.1);
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 12px;
        }

        .course-rating {
            color: #ffb91d;
            font-size: 12px;
        }

        .course-title {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 50px;
        }

        .course-info-list {
            display: flex;
            gap: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }

        .dark .course-info-list {
            border-color: #374151;
            color: #9ca3af;
        }

        .course-info-list li {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .course-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .instructor {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .instructor img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .instructor h6 {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            color: #111;
        }

        .dark .instructor h6 {
            color: #eee;
        }

        .price {
            font-size: 18px;
            font-weight: 700;
            color: #054b40;
        }

        .search-form-group {
            position: relative;
            display: flex;
        }

        .search-form-group input {
            width: 100%;
            height: 50px;
            padding: 10px 55px 10px 20px;
            border: 1px solid #eee;
            border-radius: 5px;
            outline: none;
            transition: 0.3s;
        }

        .search-form-group input:focus {
            border-color: #054b40;
        }

        .dark .search-form-group input {
            background: #374151;
            border-color: #4b5563;
            color: #fff;
        }

        .search-form-group button {
            position: absolute;
            right: 5px;
            top: 5px;
            width: 40px;
            height: 40px;
            border: none;
            background: #054b40;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .search-form-group button:hover {
            background: #043931;
        }
    </style>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900 bg-gray-50 dark:bg-gray-900">
    <?php include 'header.php'; ?>

    <!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.php')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Our Courses</h3>
                <ul class="tracking-[0.5px] inline-block mt-2">
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                    <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page">Courses</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <div class="grid md:grid-cols-12 grid-cols-1 gap-[30px]">

                <!-- SIDEBAR -->
                <div class="lg:col-span-3 md:col-span-4">
                    <div class="sticky top-24 m-4">

                        <!-- Search (Separate Form) -->
                        <div class="sidebar-widget">
                            <h4 class="widget-title dark:text-white">Search Courses</h4>
                            <form action="courses.php" method="GET" id="searchForm">
                                <div class="search-form-group">
                                    <input type="text" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <button type="submit"><i class="ri-search-line"></i></button>
                                </div>
                            </form>
                        </div>

                        <!-- Filters Form -->
                        <form action="courses.php" method="GET" id="filterForm">
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>

                            <!-- Category Filter -->
                            <div class="sidebar-widget">
                                <h4 class="widget-title dark:text-white">Category</h4>
                                <ul class="checkbox-list">
                                    <?php
                                    $catQuery = "SELECT 
                                                c.category_id, 
                                                c.category_name, 
                                                COUNT(co.course_id) AS course_count
                                            FROM category_tbl c
                                            LEFT JOIN course_tbl co 
                                                ON c.category_id = co.category_id 
                                                AND co.course_status = 1
                                            LEFT JOIN tutor_tbl t 
                                                ON co.tutor_id = t.tutor_id 
                                                AND t.tutor_status = 1
                                            WHERE c.category_status = 1 AND co.course_status = 1 AND t.tutor_status = 1
                                            GROUP BY c.category_id, c.category_name
                                            ORDER BY c.category_name ASC;";
                                    $catResult = mysqli_query($conn, $catQuery);
                                    $selected_cats = isset($_GET['categories']) ? $_GET['categories'] : [];
                                    if ($catResult && mysqli_num_rows($catResult) > 0) {
                                        while ($cat = mysqli_fetch_assoc($catResult)) {
                                            $isChecked = in_array($cat['category_id'], $selected_cats) ? 'checked' : '';
                                    ?>
                                            <li>
                                                <div class="flex items-center">
                                                    <input id="cat-<?php echo $cat['category_id']; ?>"
                                                        name="categories[]"
                                                        value="<?php echo $cat['category_id']; ?>"
                                                        type="checkbox"
                                                        class="form-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                                        <?php echo $isChecked; ?>>
                                                    <label for="cat-<?php echo $cat['category_id']; ?>" class="ms-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer flex-1 flex justify-between">
                                                        <span><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                                        <span class="text-xs text-gray-400">(<?php echo $cat['course_count']; ?>)</span>
                                                    </label>
                                                </div>
                                            </li>
                                    <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>

                            <!-- Course Level Filter (Dynamic) -->
                            <div class="sidebar-widget">
                                <h4 class="widget-title dark:text-white">Course Level</h4>
                                <ul class="checkbox-list">
                                    <?php
                                    $lvlQuery = "SELECT course_level, COUNT(course_id) as count FROM course_tbl WHERE course_status = 1 GROUP BY course_level ORDER BY course_level ASC";
                                    $lvlResult = mysqli_query($conn, $lvlQuery);
                                    $selected_levels = isset($_GET['levels']) ? $_GET['levels'] : [];

                                    if (mysqli_num_rows($lvlResult) > 0) {
                                        while ($lvlRow = mysqli_fetch_assoc($lvlResult)) {
                                            $lvlName = ucfirst(strtolower($lvlRow['course_level']));
                                            if (empty($lvlName)) continue;

                                            $isChecked = in_array($lvlRow['course_level'], $selected_levels) ? 'checked' : '';
                                            $domId = 'lvl-' . md5($lvlRow['course_level']);
                                    ?>
                                            <li>
                                                <div class="flex items-center">
                                                    <input id="<?php echo $domId; ?>"
                                                        name="levels[]"
                                                        value="<?php echo htmlspecialchars($lvlRow['course_level']); ?>"
                                                        type="checkbox"
                                                        class="form-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                                        <?php echo $isChecked; ?>>
                                                    <label for="<?php echo $domId; ?>" class="ms-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer flex-1 flex justify-between">
                                                        <span><?php echo htmlspecialchars($lvlName); ?></span>
                                                        <span class="text-xs text-gray-400">(<?php echo $lvlRow['count']; ?>)</span>
                                                    </label>
                                                </div>
                                            </li>
                                    <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>

                            <!-- Price Filter -->
                            <div class="sidebar-widget">
                                <h4 class="widget-title dark:text-white">Course Price</h4>
                                <ul class="checkbox-list">
                                    <?php
                                    $selected_prices = isset($_GET['prices']) ? $_GET['prices'] : [];
                                    ?>
                                    <li>
                                        <!-- <div class="flex items-center"> -->
                                            <!-- <input id="price-all" type="hiddden"
                                                class="form-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                                <?php echo (empty($selected_prices)) ? 'checked' : ''; ?>
                                                onchange="if(this.checked) { window.location.href='courses.php?<?php echo isset($_GET['search']) ? 'search=' . urlencode($_GET['search']) : ''; ?>'; }"> This reload is fine for 'reset' -->
                                            <!-- <label for="price-all" class="ms-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">All</label> -->
                                        <!-- </div> -->
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <input id="price-free" name="prices[]" value="free" type="checkbox"
                                                class="form-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                                <?php echo in_array('free', $selected_prices) ? 'checked' : ''; ?>>
                                            <label for="price-free" class="ms-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">Free</label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <input id="price-paid" name="prices[]" value="paid" type="checkbox"
                                                class="form-checkbox size-4 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer"
                                                <?php echo in_array('paid', $selected_prices) ? 'checked' : ''; ?>>
                                            <label for="price-paid" class="ms-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">Paid</label>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- MAIN CONTENT -->
                <div class="lg:col-span-9 md:col-span-8" id="course-grid-container">
                    <?php include 'fetch_courses.php'; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php' ?>
    <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- AJAX Filtering Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const searchForm = document.getElementById('searchForm');
            const gridContainer = document.getElementById('course-grid-container');

            // Function to fetch courses
            function fetchCourses(urlParams) {
                gridContainer.style.opacity = '0.5';

                // Add timestamp to prevent caching
                const fetchUrl = 'fetch_courses.php?' + urlParams + '&_=' + new Date().getTime();

                fetch(fetchUrl)
                    .then(response => response.text())
                    .then(html => {
                        gridContainer.innerHTML = html;
                        gridContainer.style.opacity = '1';
                    })
                    .catch(err => {
                        console.error('Error fetching courses:', err);
                        gridContainer.style.opacity = '1';
                    });
            }

            // Update URL without reload and fetch
            function updateState(params) {
                const newUrl = window.location.pathname + '?' + params.toString();
                window.history.pushState({}, '', newUrl);
                fetchCourses(params.toString());
            }

            // Handle Filter Form Changes (Checkbox changes)
            if (filterForm) {
                const inputs = filterForm.querySelectorAll('input[type="checkbox"]');
                inputs.forEach(input => {
                    input.addEventListener('change', function(e) {
                        if (this.id === 'price-all') return; // Handled by inline onclick reload

                        const formData = new FormData(filterForm);
                        const params = new URLSearchParams(formData);

                        // Merge with search param
                        const currentParams = new URLSearchParams(window.location.search);
                        if (currentParams.has('search')) {
                            params.set('search', currentParams.get('search'));
                        }

                        params.set('page', 1);
                        updateState(params);
                    });
                });
            }

            // Handle Search Form Submission
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(searchForm);
                    const params = new URLSearchParams(formData); // This gets 'search'

                    // Merge with existing filters (categories, levels, prices)
                    const currentParams = new URLSearchParams(window.location.search);
                    currentParams.forEach((value, key) => {
                        if (key !== 'search' && key !== 'page') {
                            params.append(key, value);
                        }
                    });

                    params.set('page', 1);
                    updateState(params);
                });
            }

            // Delegated Event Handlers for loaded content (Sort & Pagination)
            gridContainer.addEventListener('change', function(e) {
                if (e.target.matches('select')) {
                    // Sorting
                    const sortValue = e.target.value;
                    const params = new URLSearchParams(window.location.search);
                    params.set('sort', sortValue);
                    params.set('page', 1);
                    updateState(params);
                }
            });

            // Global Page Change Function (called by onclick in fetch_courses.php)
            window.changePage = function(page) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', page);
                updateState(params);
                // Scroll top of grid
                const yOffset = -100;
                const y = gridContainer.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
            };

            // Handle Browser Back/Forward
            window.addEventListener('popstate', function() {
                const params = new URLSearchParams(window.location.search);
                fetchCourses(params.toString());
            });
        });
    </script>
</body>

</html>