<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
include_once 'connection.php';

// Helper function (re-declared here if needed, or check if exists)
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

// Build Query
$whereClauses = ["c.course_status = 1", "t.tutor_status = 1"];

// Search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $whereClauses[] = "(c.course_title LIKE '%$search%' OR t.tutor_name LIKE '%$search%')";
}

// Categories
if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    $catIds = array_map('intval', $_GET['categories']);
    if (!empty($catIds)) {
        $catList = implode(',', $catIds);
        $whereClauses[] = "c.category_id IN ($catList)";
    }
}

// Levels
if (isset($_GET['levels']) && is_array($_GET['levels'])) {
    $levels = array_map(function ($l) use ($conn) {
        return mysqli_real_escape_string($conn, $l);
    }, $_GET['levels']);
    if (!empty($levels)) {
        $levelList = "'" . implode("','", $levels) . "'";
        $whereClauses[] = "c.course_level IN ($levelList)";
    }
}

// Prices
if (isset($_GET['prices']) && is_array($_GET['prices'])) {
    $priceConditions = [];
    if (in_array('free', $_GET['prices']))
        $priceConditions[] = "c.price = 0";
    if (in_array('paid', $_GET['prices']))
        $priceConditions[] = "c.price > 0";

    if (!empty($priceConditions)) {
        $whereClauses[] = "(" . implode(' OR ', $priceConditions) . ")";
    }
}

$whereSQL = implode(' AND ', $whereClauses);

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$orderSQL = "ORDER BY c.course_id DESC";
if ($sort == 'price_low')
    $orderSQL = "ORDER BY c.price ASC";
if ($sort == 'price_high')
    $orderSQL = "ORDER BY c.price DESC";

// Pagination
$limit = 9;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM course_tbl c INNER JOIN tutor_tbl t ON c.tutor_id = t.tutor_id WHERE $whereSQL";
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

$query = "SELECT 
            c.course_id, c.course_title, c.price, c.course_level, c.course_thumbnail,
            c.category_id, cat.category_name,
            t.tutor_id,t.tutor_name, tp.profile_pic,
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

<div class="flex flex-col sm:flex-row justify-between items-center bg-transparent mb-6" style="align-items: end;">
    <div class="mb-4 sm:mb-0">
        <span class="text-gray-600 dark:text-gray-300">Showing <span
                class="font-semibold text-gray-900 dark:text-white">
                <?php echo $showStart; ?>-
                <?php echo $showEnd; ?>
            </span> of
            <?php echo $totalRows; ?> Results
        </span>
    </div>

    <div class="flex items-center">
        <select
            class="form-select w-48 py-2 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md outline-none focus:border-primary text-sm"
            onchange="filterCourses(this.value.replace('?sort=', ''))">
            <!-- Update to use JS function helper if needed, or catch change event -->
            <option value="default" <?php echo ($sort == 'default') ? 'selected' : ''; ?>>Sort By Default</option>
            <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Sort By Low Price</option>
            <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Sort By High Price
            </option>
        </select>
    </div>
</div>

<div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-[30px]">
    <?php
    if ($totalRows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $thumbnail = !empty($row['course_thumbnail']) ? "assets/images/thumbnail/" . $row['course_thumbnail'] : "assets/images/default-course.png";
            if (!file_exists($thumbnail) && file_exists($row['course_thumbnail']))
                $thumbnail = $row['course_thumbnail'];

            $tutor_img = !empty($row['profile_pic']) ? $row['profile_pic'] : "assets/images/default-profile.png";
            if (!file_exists($tutor_img) && file_exists($tutor_profile_path . $tutor_img)) {
                $tutor_img = $tutor_profile_path . $tutor_img;
            }

            $badgeClass = 'tag-beginner';
            if (strtolower($row['course_level']) == 'intermediate')
                $badgeClass = 'tag-intermediate';
            if (strtolower($row['course_level']) == 'advanced')
                $badgeClass = 'tag-advanced';

            $rating = 4.5;
            $reviews = rand(10, 100);
            ?>
            <div class="course-item group">
                <span class="course-tag <?php echo $badgeClass; ?>">
                    <?php echo ucfirst($row['course_level']); ?>
                </span>
                <div class="course-img">
                    <a href="course-detail.php?id=<?php echo $row['course_id']; ?>">
                        <img src="<?php echo $thumbnail; ?>" alt="" onerror="this.src='assets/images/default-course.png'">
                    </a>
                </div>
                <div class="course-content">
                    <div class="course-meta">
                        <span class="course-category">
                            <?php echo htmlspecialchars($row['category_name']); ?>
                        </span>
                        <div class="course-rating">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-half-fill"></i>
                            <span class="text-gray-400 text-xs ms-1">(
                                <?php echo $reviews; ?>)
                            </span>
                        </div>
                    </div>

                    <h4 class="course-title">
                        <a href="course-detail.php?id=<?php echo $row['course_id']; ?>"
                            class="group-hover:text-primary transition">
                            <?php echo htmlspecialchars($row['course_title']); ?>
                        </a>
                    </h4>

                    <ul class="course-info-list">
                        <li><i class="ri-book-open-line text-primary"></i>
                            <?php echo $row['lesson_count']; ?> Lectures
                        </li>
                        <li><i class="ri-time-line text-primary"></i>
                            <?php echo formatDuration($row['lesson_count'] * 30); ?>
                        </li>
                    </ul>

                    <div class="course-bottom">
                        <a href="#" class="instructor">
                            <img src="<?php echo $tutor_img; ?>" alt="" onerror="this.src='assets/images/default-profile.png'">
                            <h6>
                                <a
                                    href="tutor-profile.php?id=<?php echo $row['tutor_id']; ?>"><?php echo htmlspecialchars($row['tutor_name']); ?></a>
                            </h6>
                        </a>
                        <div class="price">
                            <?php if ($row['price'] > 0): ?>
                                ₹
                                <?php echo number_format($row['price']); ?>
                            <?php else: ?>
                                <span class="text-green-500">Free</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-span-full py-16 text-center">
                <div class="mb-4"><i class="ri-search-line text-5xl text-gray-200"></i></div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">No Courses Found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your filters.</p>
                </div>';
    }
    ?>
</div>

<?php if ($totalPages > 1): ?>
    <div class="grid md:grid-cols-12 grid-cols-1 mt-8">
        <div class="md:col-span-12 text-center">
            <nav aria-label="Page navigation">
                <ul class="inline-flex items-center -space-x-px">
                    <li>
                        <a href="javascript:void(0)" onclick="changePage(<?php echo max(1, $page - 1); ?>)"
                            style="width: 40px; height: 40px; border-radius: 9999px 0px 0px 9999px;"
                            class="size-[40px] inline-flex justify-center items-center text-slate-400 bg-white dark:bg-gray-800 hover:text-white border border-gray-100 dark:border-gray-700 hover:border-primary hover:bg-primary rounded-s-lg transition-all duration-500">
                            <i class="ri-arrow-left-s-line leading-none text-lg"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a href="javascript:void(0)" onclick="changePage(<?php echo $i; ?>)"
                            style="margin: 0 5px; width: 40px; height: 40px;"
                                class="size-[40px] inline-flex justify-center items-center <?php echo ($i == $page) ? 'text-white bg-primary border-primary' : 'text-slate-400 bg-white dark:bg-gray-800 hover:text-white border-gray-100 dark:border-gray-700 hover:border-primary hover:bg-primary'; ?> border transition-all duration-500">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li>
                        <a href="javascript:void(0)" onclick="changePage(<?php echo min($totalPages, $page + 1); ?>)"
                            style="width: 40px; height: 40px; border-radius: 0 9999px 9999px 0;"
                            class="size-[40px] inline-flex justify-center items-center text-slate-400 bg-white dark:bg-gray-800 hover:text-white border border-gray-100 dark:border-gray-700 hover:border-primary hover:bg-primary rounded-e-lg transition-all duration-500">
                            <i class="ri-arrow-right-s-line leading-none text-lg"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>