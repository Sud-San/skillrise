<?php
session_start();
include 'connection.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════
           CSS VARIABLES
        ══════════════════════════════════════ */
        :root {
            --primary: #054b40;
            --primary-hover: #0a7a68;
            --primary-light: rgba(5, 75, 64, 0.07);
            --accent-green: #10b981;
            --gold: #f59e0b;
            --surface: #ffffff;
            --bg: #f7faf9;
            --border: #e5e7eb;
            --text: #111827;
            --text-muted: #6b7280;
            --radius-card: 14px;
            --radius-widget: 14px;
            --shadow-card: 0 2px 10px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 18px 44px rgba(5, 75, 64, 0.14);
        }

        .dark {
            --surface: #1f2937;
            --bg: #111827;
            --border: #2d3748;
            --text: #f1f5f9;
            --text-muted: #9ca3af;
            --primary-light: rgba(16, 185, 129, 0.10);
        }

        /* ══════════════════════════════════════
           SIDEBAR — Desktop
        ══════════════════════════════════════ */
        .sidebar-wrap {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-right: 20px;
            margin-left: -40px;
        }

        /* ── Tablet: remove negative margin ── */
        @media (max-width: 1023px) {
            .sidebar-wrap {
                margin-left: 0;
                margin-right: 0;
                position: static;
                /* un-stick on tablet so it flows naturally */
            }
        }

        /* ── Mobile: sidebar becomes a collapsible drawer ── */
        @media (max-width: 767px) {
            .sidebar-wrap {
                gap: 12px;
            }
        }

        .sidebar-widget {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-widget);
            padding: 20px 22px;
            box-shadow: var(--shadow-card);
            transition: box-shadow 0.2s;
        }

        .sidebar-widget:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 767px) {
            .sidebar-widget {
                padding: 14px 16px;
            }
        }

        .widget-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dark .widget-title {
            color: var(--accent-green);
        }

        .widget-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ══════════════════════════════════════
           MOBILE FILTER TOGGLE
        ══════════════════════════════════════ */
        .mobile-filter-toggle {
            display: none;
        }

        @media (max-width: 767px) {
            .mobile-filter-toggle {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                background: var(--primary);
                color: #fff;
                border: none;
                border-radius: 10px;
                padding: 12px 16px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                margin-bottom: 4px;
                gap: 8px;
                transition: background 0.2s;
            }

            .mobile-filter-toggle:hover {
                background: var(--primary-hover);
            }

            .mobile-filter-toggle .toggle-icon {
                transition: transform 0.3s ease;
                font-size: 18px;
            }

            .mobile-filter-toggle.open .toggle-icon {
                transform: rotate(180deg);
            }

            /* Collapsible sidebar on mobile */
            .sidebar-collapsible {
                overflow: hidden;
                max-height: 0;
                transition: max-height 0.4s ease, opacity 0.3s ease;
                opacity: 0;
            }

            .sidebar-collapsible.open {
                max-height: 2000px;
                opacity: 1;
            }

            /* Horizontal scroll for filter pills on mobile */
            .mobile-filter-pills {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                padding-bottom: 4px;
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-filter-pills::-webkit-scrollbar {
                display: none;
            }
        }

        @media (min-width: 768px) {
            .sidebar-collapsible {
                max-height: none !important;
                opacity: 1 !important;
                overflow: visible !important;
            }
        }

        /* ══════════════════════════════════════
           SEARCH WIDGET
        ══════════════════════════════════════ */
        .search-group {
            display: flex;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 75, 64, 0.08);
        }

        .dark .search-group:focus-within {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        }

        .search-group input {
            flex: 1;
            height: 44px;
            padding: 0 14px;
            border: none;
            outline: none;
            font-size: 13.5px;
            background: transparent;
            color: var(--text);
            font-family: inherit;
            min-width: 0;
            /* prevent overflow in flex */
        }

        .search-group input::placeholder {
            color: #b0bec5;
        }

        .search-group button {
            width: 46px;
            height: 44px;
            background: var(--primary);
            border: none;
            color: #fff;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .search-group button:hover {
            background: var(--primary-hover);
        }

        /* ══════════════════════════════════════
           FILTER CHECKBOXES
        ══════════════════════════════════════ */
        .checkbox-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .checkbox-list li label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 7px 8px;
            border-radius: 8px;
            transition: background 0.15s;
        }

        .checkbox-list li label:hover {
            background: var(--primary-light);
        }

        .checkbox-list input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }

        .filter-label {
            flex: 1;
            font-size: 13.5px;
            color: var(--text-muted);
            transition: color 0.15s;
        }

        .checkbox-list input:checked~.filter-label {
            color: var(--primary);
            font-weight: 600;
        }

        .dark .checkbox-list input:checked~.filter-label {
            color: var(--accent-green);
        }

        .filter-count {
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #9ca3af;
            padding: 1px 8px;
            border-radius: 20px;
            transition: all 0.15s;
        }

        .dark .filter-count {
            background: #2d3748;
        }

        .checkbox-list input:checked~.filter-count {
            background: var(--primary-light);
            color: var(--primary);
        }

        .dark .checkbox-list input:checked~.filter-count {
            color: var(--accent-green);
        }

        /* ══════════════════════════════════════
           HERO SECTION
        ══════════════════════════════════════ */
        .hero-section {
            position: relative;
            background: var(--primary);
            padding: 72px 0 60px;
            overflow: hidden;
        }

        @media (max-width: 767px) {
            .hero-section {
                padding: 48px 0 40px;
            }
        }

        .hero-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        .hero-glow {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 200px;
            background: radial-gradient(ellipse, rgba(16, 185, 129, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-section h3 {
            font-family: 'Playfair Display', serif !important;
            font-size: clamp(30px, 4vw, 48px) !important;
            color: #fff;
            position: relative;
        }

        /* ══════════════════════════════════════
           COURSE GRID
        ══════════════════════════════════════ */
        #course-grid-container {
            transition: opacity 0.25s ease;
        }

        #course-grid-container.loading {
            opacity: 0.45;
            pointer-events: none;
        }

        /* ══════════════════════════════════════
           LAYOUT — Main grid responsiveness
        ══════════════════════════════════════ */

        /* Ensure the main section container doesn't overflow on small screens */
        @media (max-width: 767px) {
            section.relative.py-10 {
                padding-top: 24px;
                padding-bottom: 24px;
            }

            /* Remove any horizontal overflow */
            .container {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        /* ══════════════════════════════════════
           TOUCH-FRIENDLY CHECKBOXES ON MOBILE
        ══════════════════════════════════════ */
        @media (max-width: 767px) {
            .checkbox-list li label {
                padding: 10px 8px;
                /* larger tap targets */
            }

            .checkbox-list input[type="checkbox"] {
                width: 18px;
                height: 18px;
            }

            .filter-label {
                font-size: 14px;
            }
        }

        /* ══════════════════════════════════════
           ACTIVE FILTER INDICATOR BADGE
        ══════════════════════════════════════ */
        .filter-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-green);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-left: 4px;
            line-height: 1;
        }
    </style>
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900" style="background: var(--bg);">
    <?php include 'header.php'; ?>

    <!-- ── HERO ── -->
    <section class="hero-section">
        <div class="hero-dots"></div>
        <div class="hero-glow"></div>
        <div class="container relative">
            <div class="grid grid-cols-1 text-center mt-10">
                <h3 class="text-4xl md:leading-normal leading-normal font-semibold text-white">Our Courses</h3>
                <ul class="tracking-[0.5px] inline-block mt-3">
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white/60 hover:text-white/90">
                        <a href="index.php"><?php echo $company_name; ?></a>
                    </li>
                    <li class="inline-block text-white/50 mx-1 text-sm"><i class="ri-arrow-right-s-line"></i></li>
                    <li class="inline-block font-medium uppercase duration-500 text-xs ease-in-out text-white" aria-current="page">Courses</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ── MAIN ── -->
    <section class="relative py-10 lg:py-16">
        <div class="container relative">
            <div class="grid md:grid-cols-12 grid-cols-1 gap-8">

                <!-- ══ SIDEBAR ══ -->
                <div class="lg:col-span-3 md:col-span-4 col-span-12">

                    <!-- Mobile Filter Toggle Button -->
                    <button class="mobile-filter-toggle" id="mobileFilterToggle" aria-expanded="false" aria-controls="sidebarCollapsible">
                        <span style="display:flex;align-items:center;gap:8px;">
                            <i class="ri-filter-3-line"></i>
                            <span>Filters &amp; Search</span>
                            <span class="filter-badge" id="activeFilterBadge" style="display:none;">0</span>
                        </span>
                        <i class="ri-arrow-down-s-line toggle-icon"></i>
                    </button>

                    <div class="sidebar-collapsible" id="sidebarCollapsible">
                        <div class="sidebar-wrap">

                            <!-- Search -->
                            <div class="sidebar-widget">
                                <h4 class="widget-title">Search Courses</h4>
                                <form action="courses.php" method="GET" id="searchForm">
                                    <div class="search-group">
                                        <input type="text" name="search" placeholder="Search courses..."
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <button type="submit"><i class="ri-search-line"></i></button>
                                    </div>
                                </form>
                            </div>

                            <!-- Filters -->
                            <form action="courses.php" method="GET" id="filterForm">
                                <?php if (isset($_GET['search'])): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                                <?php endif; ?>

                                <!-- Category -->
                                <div class="sidebar-widget">
                                    <h4 class="widget-title">Category</h4>
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
                                                    WHERE c.category_status = 1 OR co.course_status = 1 AND t.tutor_status = 1
                                                    GROUP BY c.category_id, c.category_name
                                                    ORDER BY c.category_name ASC";
                                        $catResult = mysqli_query($conn, $catQuery);
                                        $selected_cats = isset($_GET['categories']) ? $_GET['categories'] : [];
                                        if ($catResult && mysqli_num_rows($catResult) > 0):
                                            while ($cat = mysqli_fetch_assoc($catResult)):
                                                $isChecked = in_array($cat['category_id'], $selected_cats) ? 'checked' : '';
                                        ?>
                                                <li>
                                                    <label>
                                                        <input name="categories[]" value="<?php echo $cat['category_id']; ?>"
                                                            type="checkbox" <?php echo $isChecked; ?>>
                                                        <span class="filter-label"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                                        <span class="filter-count"><?php echo $cat['course_count']; ?></span>
                                                    </label>
                                                </li>
                                        <?php endwhile;
                                        endif; ?>
                                    </ul>
                                </div>

                                <!-- Course Level -->
                                <div class="sidebar-widget">
                                    <h4 class="widget-title">Course Level</h4>
                                    <ul class="checkbox-list">
                                        <?php
                                        $lvlQuery = "SELECT course_level, COUNT(course_id) as count FROM course_tbl WHERE course_status = 1 GROUP BY course_level ORDER BY course_level ASC";
                                        $lvlResult = mysqli_query($conn, $lvlQuery);
                                        $selected_levels = isset($_GET['levels']) ? $_GET['levels'] : [];
                                        if ($lvlResult && mysqli_num_rows($lvlResult) > 0):
                                            while ($lvlRow = mysqli_fetch_assoc($lvlResult)):
                                                $lvlName = ucfirst(strtolower($lvlRow['course_level']));
                                                if (empty($lvlName)) continue;
                                                $isChecked = in_array($lvlRow['course_level'], $selected_levels) ? 'checked' : '';
                                                $domId = 'lvl-' . md5($lvlRow['course_level']);
                                        ?>
                                                <li>
                                                    <label for="<?php echo $domId; ?>">
                                                        <input id="<?php echo $domId; ?>" name="levels[]"
                                                            value="<?php echo htmlspecialchars($lvlRow['course_level']); ?>"
                                                            type="checkbox" <?php echo $isChecked; ?>>
                                                        <span class="filter-label"><?php echo htmlspecialchars($lvlName); ?></span>
                                                        <span class="filter-count"><?php echo $lvlRow['count']; ?></span>
                                                    </label>
                                                </li>
                                        <?php endwhile;
                                        endif; ?>
                                    </ul>
                                </div>

                                <!-- Price -->
                                <div class="sidebar-widget">
                                    <h4 class="widget-title">Course Price</h4>
                                    <?php $selected_prices = isset($_GET['prices']) ? $_GET['prices'] : []; ?>
                                    <ul class="checkbox-list">
                                        <li>
                                            <label>
                                                <input id="price-all" type="checkbox"
                                                    <?php echo empty($selected_prices) ? 'checked' : ''; ?>
                                                    onchange="if(this.checked){ window.location.href='courses.php<?php echo isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : ''; ?>'; }">
                                                <span class="filter-label">All</span>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input id="price-free" name="prices[]" value="free" type="checkbox"
                                                    <?php echo in_array('free', $selected_prices) ? 'checked' : ''; ?>>
                                                <span class="filter-label">Free</span>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input id="price-paid" name="prices[]" value="paid" type="checkbox"
                                                    <?php echo in_array('paid', $selected_prices) ? 'checked' : ''; ?>>
                                                <span class="filter-label">Paid</span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>

                            </form>
                        </div>
                    </div><!-- /sidebar-collapsible -->
                </div>

                <!-- ══ COURSE GRID ══ -->
                <div class="lg:col-span-9 md:col-span-8 col-span-12" id="course-grid-container">
                    <?php include 'fetch_courses.php'; ?>
                </div>

            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <a href="#" onclick="topFunction()" id="back-to-top"
        class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9">
        <i class="ri-arrow-up-line"></i>
    </a>

    <script src="assets/js/plugins.init.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const searchForm = document.getElementById('searchForm');
            const gridContainer = document.getElementById('course-grid-container');

            /* ── Fetch & render helper ── */
            function fetchCourses(urlParams) {
                gridContainer.classList.add('loading');
                fetch('fetch_courses.php?' + urlParams + '&_=' + Date.now())
                    .then(r => r.text())
                    .then(html => {
                        gridContainer.innerHTML = html;
                        gridContainer.classList.remove('loading');
                    })
                    .catch(() => gridContainer.classList.remove('loading'));
            }

            function updateState(params) {
                window.history.pushState({}, '', window.location.pathname + '?' + params.toString());
                fetchCourses(params.toString());
            }

            /* ── Filter checkboxes ── */
            if (filterForm) {
                filterForm.querySelectorAll('input[type="checkbox"]').forEach(input => {
                    input.addEventListener('change', function() {
                        if (this.id === 'price-all') return;
                        const formData = new FormData(filterForm);
                        const params = new URLSearchParams(formData);
                        const cur = new URLSearchParams(window.location.search);
                        if (cur.has('search')) params.set('search', cur.get('search'));
                        if (cur.has('sort')) params.set('sort', cur.get('sort'));
                        params.set('page', 1);
                        updateState(params);
                        updateFilterBadge();
                    });
                });
            }

            /* ── Search form ── */
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(searchForm);
                    const params = new URLSearchParams(formData);
                    const cur = new URLSearchParams(window.location.search);
                    cur.forEach((v, k) => {
                        if (k !== 'search' && k !== 'page') params.append(k, v);
                    });
                    params.set('page', 1);
                    updateState(params);
                });
            }

            /* ── Sort select (delegated — rendered inside fetch_courses) ── */
            gridContainer.addEventListener('change', function(e) {
                if (e.target.matches('select.fc-sort-select')) {
                    const params = new URLSearchParams(window.location.search);
                    params.set('sort', e.target.value);
                    params.set('page', 1);
                    updateState(params);
                }
            });

            /* ── Pagination (global fn called from PHP output) ── */
            window.changePage = function(page) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', page);
                updateState(params);
                const y = gridContainer.getBoundingClientRect().top + window.pageYOffset - 110;
                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
            };

            /* ── Browser back/forward ── */
            window.addEventListener('popstate', function() {
                fetchCourses(new URLSearchParams(window.location.search).toString());
            });

            /* ══════════════════════════════════════
               MOBILE FILTER TOGGLE
            ══════════════════════════════════════ */
            const toggleBtn = document.getElementById('mobileFilterToggle');
            const collapsible = document.getElementById('sidebarCollapsible');

            if (toggleBtn && collapsible) {
                // On load: if filters are active, auto-open sidebar on mobile
                const hasActiveFilters = document.querySelectorAll('#filterForm input[type="checkbox"]:checked').length > 0;
                if (window.innerWidth < 768 && hasActiveFilters) {
                    collapsible.classList.add('open');
                    toggleBtn.classList.add('open');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }

                toggleBtn.addEventListener('click', function() {
                    const isOpen = collapsible.classList.toggle('open');
                    toggleBtn.classList.toggle('open', isOpen);
                    toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            /* ── Active filter badge count ── */
            function updateFilterBadge() {
                const badge = document.getElementById('activeFilterBadge');
                if (!badge) return;
                const count = document.querySelectorAll(
                    '#filterForm input[type="checkbox"]:checked:not(#price-all)'
                ).length;
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            // Init badge on load
            updateFilterBadge();
        });
    </script>
</body>

</html>