<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
include_once 'connection.php';

if (!function_exists('formatDuration')) {
    function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        if ($hours > 0) {
            return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
        }
        return $mins . 'm';
    }
}

// ── Build WHERE ──────────────────────────────────────────
$whereClauses = ["c.course_status = 1", "t.tutor_status = 1"];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $whereClauses[] = "(c.course_title LIKE '%$search%' OR t.tutor_name LIKE '%$search%')";
}

if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    $catIds = array_map('intval', $_GET['categories']);
    if (!empty($catIds)) {
        $catList = implode(',', $catIds);
        $whereClauses[] = "c.category_id IN ($catList)";
    }
}

if (isset($_GET['levels']) && is_array($_GET['levels'])) {
    $levels = array_map(function ($l) use ($conn) {
        return mysqli_real_escape_string($conn, $l);
    }, $_GET['levels']);
    if (!empty($levels)) {
        $levelList = "'" . implode("','", $levels) . "'";
        $whereClauses[] = "c.course_level IN ($levelList)";
    }
}

if (isset($_GET['prices']) && is_array($_GET['prices'])) {
    $priceConditions = [];
    if (in_array('free', $_GET['prices']))
        $priceConditions[] = "c.price = 0";
    if (in_array('paid', $_GET['prices']))
        $priceConditions[] = "c.price > 0";
    if (!empty($priceConditions))
        $whereClauses[] = "(" . implode(' OR ', $priceConditions) . ")";
}

$whereSQL = implode(' AND ', $whereClauses);

// ── Sorting ───────────────────────────────────────────────
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$orderSQL = "ORDER BY c.course_id DESC";
if ($sort == 'price_low')
    $orderSQL = "ORDER BY c.price ASC";
if ($sort == 'price_high')
    $orderSQL = "ORDER BY c.price DESC";

// ── Pagination ────────────────────────────────────────────
$limit = 6;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM course_tbl c INNER JOIN tutor_tbl t ON c.tutor_id = t.tutor_id WHERE $whereSQL";
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

$query = "SELECT 
            c.course_id, c.course_title, c.price, c.course_level, c.course_thumbnail,
            c.category_id, cat.category_name,
            t.tutor_id, t.tutor_name, tp.profile_pic,
            (SELECT COUNT(*) FROM lessons_tbl l WHERE l.course_id = c.course_id) as lesson_count
          FROM course_tbl c
          INNER JOIN tutor_tbl t ON c.tutor_id = t.tutor_id
          LEFT JOIN tutor_profile_tbl tp ON t.tutor_id = tp.tutor_id
          LEFT JOIN category_tbl cat ON c.category_id = cat.category_id
          WHERE $whereSQL AND cat.category_status = 1
          $orderSQL
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

$showStart = ($totalRows > 0) ? $start + 1 : 0;
$showEnd = min($start + $limit, $totalRows);
?>

<style>
    /* ── Top bar ──────────────────────────────────────── */
    .fc-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .fc-result-text {
        font-size: 14px;
        color: #6b7280;
    }

    .fc-result-text strong {
        color: #111827;
        font-weight: 700;
    }

    .dark .fc-result-text {
        color: #9ca3af;
    }

    .dark .fc-result-text strong {
        color: #f9fafb;
    }

    .fc-sort-select {
        height: 42px;
        padding: 0 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 13.5px;
        color: #374151;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 36px;
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s;
        min-width: 180px;
    }

    .fc-sort-select:focus {
        border-color: #054b40;
    }

    .dark .fc-sort-select {
        background-color: #1f2937;
        border-color: #374151;
        color: #d1d5db;
    }

    /* ── Grid ─────────────────────────────────────────── */
    .fc-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }

    @media (max-width: 1180px) {
        .fc-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .fc-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── Card ─────────────────────────────────────────── */
    .fc-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .dark .fc-card {
        background: #1f2937;
        border-color: #2d3748;
        box-shadow: none;
    }

    .fc-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 42px rgba(0, 0, 0, 0.13);
    }

    /* Thumbnail */
    .fc-thumb {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .fc-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.45s ease;
    }

    .fc-card:hover .fc-thumb img {
        transform: scale(1.08);
    }

    /* Level badge */
    .fc-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 13px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        text-transform: capitalize;
        letter-spacing: 0.5px;
        z-index: 2;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
    }

    .fc-badge-beginner {
        background: #10b981;
    }

    .fc-badge-intermediate {
        background: #f59e0b;
        color: #000 !important;
    }

    .fc-badge-advanced {
        background: #ef4444;
    }

    /* Card body */
    .fc-body {
        padding: 18px 20px 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    /* Category + Rating row */
    .fc-meta-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .fc-cat {
        font-size: 10.5px;
        font-weight: 700;
        color: #054b40;
        background: rgba(5, 75, 64, 0.08);
        padding: 3px 10px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dark .fc-cat {
        color: #10b981;
        background: rgba(16, 185, 129, 0.12);
    }

    .fc-stars {
        display: flex;
        align-items: center;
        gap: 1px;
        font-size: 11.5px;
        color: #f59e0b;
    }

    .fc-stars span {
        color: #9ca3af;
        font-size: 11px;
        margin-left: 3px;
    }

    /* Title */
    .fc-title {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        line-height: 1.5;
        margin: 0 0 14px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 45px;
        text-decoration: none;
        transition: color 0.2s;
    }

    .fc-title:hover {
        color: #054b40;
    }

    .dark .fc-title {
        color: #f1f5f9;
    }

    .dark .fc-title:hover {
        color: #10b981;
    }

    /* Info pills */
    .fc-info {
        display: flex;
        gap: 14px;
        font-size: 12.5px;
        color: #6b7280;
        padding-bottom: 14px;
        border-bottom: 1px solid #f0f0f0;
        margin-bottom: 14px;
        list-style: none;
        padding-left: 0;
        flex-wrap: wrap;
    }

    .dark .fc-info {
        border-color: #2d3748;
        color: #9ca3af;
    }

    .fc-info li {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .fc-info li i {
        font-size: 14px;
        color: #054b40;
    }

    .dark .fc-info li i {
        color: #10b981;
    }

    /* Footer */
    .fc-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        gap: 8px;
    }

    .fc-instructor {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        text-decoration: none;
    }

    .fc-instructor img {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        flex-shrink: 0;
    }

    .fc-instructor-name {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 110px;
        display: block;
        transition: color 0.2s;
    }

    .fc-instructor:hover .fc-instructor-name {
        color: #054b40;
    }

    .dark .fc-instructor-name {
        color: #cbd5e1;
    }

    .fc-price {
        font-size: 17px;
        font-weight: 800;
        color: #054b40;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .dark .fc-price {
        color: #10b981;
    }

    .fc-price-free {
        font-size: 15px;
        font-weight: 700;
        color: #10b981;
    }

    /* ── Empty state ─────────────────────────────────── */
    .fc-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 70px 20px;
    }

    .fc-empty i {
        font-size: 54px;
        color: #d1d5db;
        margin-bottom: 16px;
        display: block;
    }

    .fc-empty h3 {
        font-size: 18px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 8px;
    }

    .dark .fc-empty h3 {
        color: #f1f5f9;
    }

    .fc-empty p {
        font-size: 14px;
        color: #9ca3af;
    }

    /* ── Container wrapper ──────────────────────────── */
    .fc-container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .fc-content {
        flex: 1;
    }

    /* ── Pagination ──────────────────────────────────── */
    .fc-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin-top: auto;
        padding: 40px 20px;
        flex-wrap: wrap;
        border-top: 1px solid #e5e7eb;
    }

    .dark .fc-pagination {
        border-color: #2d3748;
    }

    .fc-page-btn {
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        text-decoration: none;
        font-family: inherit;
    }

    .dark .fc-page-btn {
        background: #1f2937;
        border-color: #374151;
        color: #d1d5db;
    }

    .fc-page-btn:hover,
    .fc-page-btn.active {
        background: #054b40;
        border-color: #054b40;
        color: #fff;
    }

    .fc-page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>

<!-- Top Bar -->
<div class="fc-container">
    <div class="fc-content">
        <div class="fc-topbar">
            <p class="fc-result-text">
                Showing <strong><?php echo $showStart; ?>–<?php echo $showEnd; ?></strong> of
                <strong><?php echo $totalRows; ?></strong> Results
            </p>
            <select class="fc-sort-select"
                onchange="(function(v){ var p=new URLSearchParams(window.location.search); p.set('sort',v); p.set('page',1); window.history.pushState({},'',window.location.pathname+'?'+p.toString()); document.getElementById('course-grid-container') && fetch('fetch_courses.php?'+p.toString()+'&_='+Date.now()).then(r=>r.text()).then(h=>{document.getElementById('course-grid-container').innerHTML=h;}); })(this.value)">
                <option value="default" <?php echo ($sort == 'default') ? 'selected' : ''; ?>>Sort By Default</option>
                <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>

        <!-- Course Grid -->
        <div class="fc-grid">
    <?php
    if ($totalRows > 0):
        while ($row = mysqli_fetch_assoc($result)):
            $thumbnail = !empty($row['course_thumbnail'])
                ? "assets/images/thumbnail/" . $row['course_thumbnail']
                : "assets/images/default-course.png";

            $tutor_img = !empty($row['profile_pic'])
                ? $row['profile_pic']
                : "assets/images/default-profile.png";

            // Badge class
            $level = strtolower($row['course_level']);
            $badgeClass = 'fc-badge-beginner';
            if ($level == 'intermediate')
                $badgeClass = 'fc-badge-intermediate';
            if ($level == 'advanced')
                $badgeClass = 'fc-badge-advanced';

            $reviews = rand(10, 100);
    ?>
            <div class="fc-card">

                <!-- Thumbnail -->
                <div class="fc-thumb">
                    <span class="fc-badge <?php echo $badgeClass; ?>"><?php echo ucfirst($level); ?></span>
                    <a href="course-detail.php?id=<?php echo $row['course_id']; ?>">
                        <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                            alt="<?php echo htmlspecialchars($row['course_title']); ?>"
                            onerror="this.src='assets/images/default-course.png'">
                    </a>
                </div>

                <!-- Body -->
                <div class="fc-body">

                    <!-- Category + Stars -->
                    <div class="fc-meta-top">
                        <span class="fc-cat"><?php echo htmlspecialchars($row['category_name']); ?></span>
                        <div class="fc-stars">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-half-fill"></i>
                            <span>(<?php echo $reviews; ?>)</span>
                        </div>
                    </div>

                    <!-- Title -->
                    <a href="course-detail.php?id=<?php echo $row['course_id']; ?>" class="fc-title">
                        <?php echo htmlspecialchars($row['course_title']); ?>
                    </a>

                    <!-- Info -->
                    <ul class="fc-info">
                        <li>
                            <i class="ri-book-open-line"></i>
                            <?php echo $row['lesson_count']; ?> Lectures
                        </li>
                        <li>
                            <i class="ri-time-line"></i>
                            <?php echo formatDuration($row['lesson_count'] * 30); ?>
                        </li>
                    </ul>

                    <!-- Footer: Instructor + Price -->
                    <div class="fc-footer">
                        <a href="tutor-profile.php?id=<?php echo $row['tutor_id']; ?>" class="fc-instructor">
                            <img src="<?php echo $tutor_profile_path . htmlspecialchars($tutor_img); ?>"
                                alt="<?php echo htmlspecialchars($row['tutor_name']); ?>"
                                onerror="this.src='assets/images/default-profile.png'">
                            <span class="fc-instructor-name"><?php echo htmlspecialchars($row['tutor_name']); ?></span>
                        </a>
                        <div>
                            <?php if ($row['price'] > 0): ?>
                                <span class="fc-price">₹<?php echo number_format($row['price']); ?></span>
                            <?php else: ?>
                                <span class="fc-price-free">Free</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /.fc-body -->
            </div><!-- /.fc-card -->

        <?php
        endwhile;
    else:
        ?>
        <div class="fc-empty">
            <i class="ri-search-line"></i>
            <h3>No Courses Found</h3>
            <p>Try adjusting your search or filters.</p>
        </div>
    <?php endif; ?>
    </div><!-- /.fc-grid -->
    </div><!-- /.fc-content -->

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="fc-pagination">
        <!-- Prev -->
        <button class="fc-page-btn" onclick="changePage(<?php echo max(1, $page - 1); ?>)" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>
            <i class="ri-arrow-left-s-line"></i>
        </button>

        <!-- Page numbers -->
        <?php
        $range = 2;
        $start_page = max(1, $page - $range);
        $end_page = min($totalPages, $page + $range);

        if ($start_page > 1): ?>
            <button class="fc-page-btn" onclick="changePage(1)">1</button>
            <?php if ($start_page > 2): ?>
                <span style="padding:0 4px;color:#9ca3af;">…</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <button class="fc-page-btn <?php echo ($i == $page) ? 'active' : ''; ?>" onclick="changePage(<?php echo $i; ?>)">
                <?php echo $i; ?>
            </button>
        <?php endfor; ?>

        <?php if ($end_page < $totalPages): ?>
            <?php if ($end_page < $totalPages - 1): ?>
                <span style="padding:0 4px;color:#9ca3af;">…</span>
            <?php endif; ?>
            <button class="fc-page-btn" onclick="changePage(<?php echo $totalPages; ?>)"><?php echo $totalPages; ?></button>
        <?php endif; ?>

        <!-- Next -->
        <button class="fc-page-btn" onclick="changePage(<?php echo min($totalPages, $page + 1); ?>)" <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>>
            <i class="ri-arrow-right-s-line"></i>
        </button>
        </div>
    <?php endif; ?>
</div><!-- /.fc-container -->