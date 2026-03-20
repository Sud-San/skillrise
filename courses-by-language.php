<?php
session_start();
include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <?php include 'headtag.php'; ?>
    <style>
        /* Page section header */
        .courses-page-header {
            margin-bottom: 2rem;
        }
        .courses-page-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #111827;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }
        .courses-page-header p {
            color: #6b7280;
            font-size: 1rem;
        }
        
        /* Search and Filter - Improved */
        .search-filter-container {
            background: linear-gradient(to bottom, #f8faf9 0%, #ffffff 100%);
            border: 1px solid rgba(5, 75, 64, 0.08);
            border-radius: 16px;
            padding: 1.75rem 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 24px rgba(5, 75, 64, 0.04);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 1.25rem;
        }
        
        .search-box input {
            width: 100%;
            padding: 1rem 1.25rem 1rem 3.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.25s ease;
            background: #fff;
        }
        
        .search-box input:hover {
            border-color: rgba(5, 75, 64, 0.2);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #054b40;
            box-shadow: 0 0 0 3px rgba(5, 75, 64, 0.08);
        }
        
        .search-box input::placeholder {
            color: #9ca3af;
        }
        
        .search-box i {
            position: absolute;
            left: 1.125rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.25rem;
            transition: color 0.25s ease;
        }
        
        .search-box:focus-within i {
            color: #054b40;
        }
        
        .search-box i {
            pointer-events: none;
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .filter-btn {
            padding: 0.625rem 1.25rem;
            border: 1px solid #e5e7eb;
            background: #fff;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        
        .filter-btn:hover {
            border-color: #054b40;
            color: #054b40;
            background: rgba(5, 75, 64, 0.04);
        }
        
        .filter-btn.active {
            background: #054b40;
            color: white;
            border-color: #054b40;
            box-shadow: 0 2px 8px rgba(5, 75, 64, 0.25);
        }
        
        /* Course cards - Enhanced */
        .card.course-card {
            display: flex;
            margin: 1rem;
            flex-direction: column;
            justify-content: flex-start;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: white;
            height: auto;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card.course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(5, 75, 64, 0.12);
            border-color: rgba(5, 75, 64, 0.15);
        }

        .card-thumbnail-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
            background: #f3f4f6;
        }
        
        .card-thumbnail-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(5, 75, 64, 0.15) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        
        .card.course-card:hover .card-thumbnail-container::after {
            opacity: 1;
        }
        
        .card-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card.course-card:hover .card-thumbnail {
            transform: scale(1.08);
        }
        
        .tutor-profile {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid #f3f4f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .card-content {
            padding: 1.5rem 1.5rem 0;
            flex-grow: 1;
        }

        .description-container {
            position: relative;
            margin: 0.75rem 0 1rem;
            min-height: 56px;
        }
        
        .short-description {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .full-description {
            display: none;
            padding: 1rem;
            background: #f8faf9;
            border-radius: 10px;
            margin-top: 0.5rem;
            color: #4b5563;
            font-size: 0.875rem;
            line-height: 1.6;
            border-left: 4px solid #054b40;
        }

        .read-more-btn {
            color: #054b40;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-top: 0.5rem;
        }
        
        .read-more-btn i {
            margin-left: 4px;
            transition: transform 0.25s ease;
        }
        
        .read-more-btn:hover i {
            transform: translateX(4px);
        }

        .read-more-btn:hover {
            text-decoration: underline;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: #054b40;
            letter-spacing: -0.02em;
        }
        
        .level-badge {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        
        .level-beginner { background-color: #d1fae5; color: #065f46; }
        .level-intermediate { background-color: #fef3c7; color: #92400e; }
        .level-advanced { background-color: #fee2e2; color: #991b1b; }
        
        .course-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 0.5rem;
            line-height: 1.4;
            min-height: 48px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .tutor-info {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-top: 1px solid #f3f4f6;
        }
        
        .tutor-info span.block {
            color: #374151;
        }
        
        .stats {
            display: flex;
            gap: 1.5rem;
            padding: 1rem 0;
            border-top: 1px solid #f3f4f6;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-size: 0.8125rem;
        }
        
        .stat-item i {
            margin-right: 6px;
            color: #054b40;
            font-size: 1rem;
        }
        
        .view-course-btn {
            display: block;
            width: 100%;
            padding: 0.875rem 1.25rem;
            background: linear-gradient(135deg, #054b40 0%, #033d35 100%);
            color: white;
            text-align: center;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9375rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .view-course-btn:hover {
            background: linear-gradient(135deg, #0a9984 0%, #054b40 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(5, 75, 64, 0.3);
        }
        
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <?php include 'header.php'; ?>

    <?php
    // Get category_id from URL parameter
    $cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

    // Get category details
    $getCatQuery = mysqli_query($conn, "SELECT category_name FROM category_tbl WHERE category_id = $cat_id AND category_status = 1");

    if (!$getCatQuery) {
        echo "<h2 class='text-center py-20 text-red-600'>Database Error: " . mysqli_error($conn) . "</h2>";
        exit;
    }

    $catRow = mysqli_fetch_assoc($getCatQuery);

    if (!$catRow) {
        echo "<h2 class='text-center py-20 text-red-600'>Category not found</h2>";
        exit;
    }

    $category_name = $catRow['category_name'];

    // Get filter parameters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : 'all';
    ?>

    <!-- Start Hero -->
    <section class="relative table bg-primary w-full py-24">
        <div class="absolute inset-0 bg-[url('../assets/images/bg/box.php')] bg-no-repeat bg-center bg-cover"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white"><?php echo htmlspecialchars($category_name); ?> Courses</h3>

                <ul class="tracking-[0.5px] inline-block mt-2">
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/70 dark:text-white/50 hover:text-white dark:hover:text-white"><a href="index.php"><?php echo $company_name; ?></a></li>
                    <li class="inline-block text-white/70 dark:text-white/50 mx-0.5 text-sm ltr:rotate-0 rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white dark:text-white" aria-current="page"><?php echo htmlspecialchars($category_name); ?> Courses</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="relative lg:py-24 py-16">
        <div class="container relative">
            <div class="text-center courses-page-header">
                <h2><?php echo htmlspecialchars($category_name); ?> Courses</h2>
                <p>Choose a course below to start learning!</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-container">
                <form method="GET" action="" id="filterForm">
                    <input type="hidden" name="cat_id" id="catId" value="<?php echo $cat_id; ?>">
                    <input type="hidden" name="cat_name" id="catName" value="<?php echo htmlspecialchars($category_name); ?>">
                    
                    <div class="search-box">
                        <i class="ri-search-line"></i>
                        <input type="text" 
                               name="search" 
                               placeholder="Search courses by title or tutor..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               id="searchInput">
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="button" class="filter-btn <?php echo $level == 'all' ? 'active' : ''; ?>" data-level="all">
                            All Levels
                        </button>
                        <button type="button" class="filter-btn <?php echo $level == 'beginner' ? 'active' : ''; ?>" data-level="beginner">
                            Beginner
                        </button>
                        <button type="button" class="filter-btn <?php echo $level == 'intermediate' ? 'active' : ''; ?>" data-level="intermediate">
                            Intermediate
                        </button>
                        <button type="button" class="filter-btn <?php echo $level == 'advanced' ? 'active' : ''; ?>" data-level="advanced">
                            Advanced
                        </button>
                        <input type="hidden" name="level" id="levelInput" value="<?php echo $level; ?>">
                    </div>
                </form>
            </div>

            <div class="grid-container" id="gridContainer">
                <!-- Content loaded via AJAX -->
                <?php include 'fetch_courses_by_language.php'; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    
    <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>

    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            
            // Re-initialize "Read More" functionality for dynamic content
            function initReadMore() {
                $('.description-container').each(function() {
                    const $container = $(this);
                    
                    // Check if already initialized to avoid duplicate bindings if called multiple times
                    if($container.data('initialized')) return;
                    $container.data('initialized', true);

                    const $fullDesc = $container.find('.full-description');
                    const $shortDesc = $container.find('.short-description');
                    const $btn = $container.find('.read-more-btn');

                    const fullText = $fullDesc.text().trim();
                    const maxLength = 100;

                    if (fullText.length <= maxLength) {
                        $btn.hide();
                        $shortDesc.text(fullText);
                    } else {
                        const shortText = fullText.substring(0, maxLength) + '...';
                        $shortDesc.text(shortText);

                        // Unbind previous click events just in case
                        $btn.off('click').on('click', function() {
                            if ($fullDesc.is(':visible')) {
                                $fullDesc.hide();
                                $shortDesc.show();
                                $btn.find('span').text('Read More');
                                $btn.find('i').removeClass('ri-arrow-up-s-line').addClass('ri-arrow-right-s-line');
                            } else {
                                $fullDesc.show();
                                $shortDesc.hide();
                                $btn.find('span').text('Read Less');
                                $btn.find('i').removeClass('ri-arrow-right-s-line').addClass('ri-arrow-up-s-line');
                            }
                        });
                    }
                });
            }

            // AJAX Fetch Function
            function fetchCourses() {
                const catId = $('#catId').val();
                const catName = $('#catName').val();
                const search = $('#searchInput').val();
                const level = $('#levelInput').val();
                
                $('#gridContainer').css('opacity', '0.5');

                $.ajax({
                    url: 'fetch_courses_by_language.php',
                    type: 'GET',
                    data: {
                        cat_id: catId,
                        cat_name: catName,
                        search: search,
                        level: level
                    },
                    success: function(response) {
                        $('#gridContainer').html(response).css('opacity', '1');
                        initReadMore(); // Re-init JS for new content via callback
                        
                        // Update URL
                        const newUrl = window.location.pathname + '?' + $.param({
                            cat_id: catId,
                            search: search,
                            level: level
                        });
                        window.history.pushState({path: newUrl}, '', newUrl);
                    },
                    error: function() {
                        $('#gridContainer').css('opacity', '1');
                        alert('Error loading courses.');
                    }
                });
            }

            // Filter buttons
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                $('#levelInput').val($(this).data('level'));
                fetchCourses();
            });
            
            // Search with debounce
            let searchTimer;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    fetchCourses();
                }, 500);
            });
            
            // Clear search on Escape
            $('#searchInput').on('keyup', function(e) {
                if (e.key === 'Escape') {
                    $(this).val('');
                    fetchCourses();
                }
            });

            // Handle Broken Images (Delegated event works for dynamic content automatically)
            $(document).on('error', 'img.tutor-profile', function() {
                const $img = $(this);
                // Prevent infinite loop
                if($img.data('error-handled')) return;
                $img.data('error-handled', true);

                const tutorName = $img.attr('alt') || '';
                const tutorNameLower = tutorName.toLowerCase().replace(/\s+/g, '_');
                
                // Fallback sequence logic can be complex in JS or handled server-side.
                // Simple fallback:
                $img.attr('src', 'assets/images/default-profile.png');
            });

            // Initial call to setup Read More for first load
            initReadMore();
            
            // Handle Browser Back/Forward buttons
            $(window).on('popstate', function() {
                // Determine params from URL or just reload? 
                // Reloading is safest for PHP rendered pages unless we parse URL params back to inputs.
                location.reload(); 
            });
        });
    </script>
</body>
</html>