<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
include_once 'connection.php';

// Get and sanitize parameters
$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : 'all';

// Build query
$query = "SELECT 
    c.course_id, 
    c.course_title AS title, 
    c.course_description AS description, 
    c.price, 
    c.course_level AS level, 
    c.course_thumbnail AS thumbnail,
    c.tutor_id,
    tp.profile_pic,
    t.tutor_name,
    (SELECT COUNT(*) FROM lessons_tbl l WHERE l.course_id = c.course_id) AS lesson_count
FROM course_tbl c
INNER JOIN tutor_tbl t ON c.tutor_id = t.tutor_id
LEFT JOIN tutor_profile_tbl tp ON t.tutor_id = tp.tutor_id
WHERE c.category_id = $cat_id 
AND c.course_status = 1
AND t.tutor_status = 1";

// Add search filter
if (!empty($search)) {
    $query .= " AND (c.course_title LIKE '%$search%' OR t.tutor_name LIKE '%$search%')";
}

// Add level filter
if ($level != 'all') {
    $query .= " AND c.course_level = '$level'";
}

$query .= " ORDER BY c.course_id ASC";

$result = mysqli_query($conn, $query);
$total_courses = ($result) ? mysqli_num_rows($result) : 0;

// Determine category name for no-results message (optional, passed or fetched?)
// To save a query, we might rely on generic message or just 'this category'.
// Ideally, courses-by-language.php passes it, but for AJAX, we might skip or fetch.
// Let's pass it if needed, or just say "this category". 
// Or fetch it quickly if not expensive.
$category_name = "this category";
if (isset($_GET['cat_name'])) {
    $category_name = htmlspecialchars($_GET['cat_name']);
} else {
    // Quick fetch if needed, but 'this category' is safe fallback
}

?>

<div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-8" id="coursesGrid">
    <?php
    if (!$result) {
        echo "<div class='col-span-3 text-center py-10 text-red-500'>Error loading courses: " . mysqli_error($conn) . "</div>";
    } elseif ($total_courses > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine level badge class
            $level_class = '';
            switch (strtolower($row['level'])) {
                case 'beginner':
                    $level_class = 'level-beginner';
                    break;
                case 'intermediate':
                    $level_class = 'level-intermediate';
                    break;
                case 'advanced':
                    $level_class = 'level-advanced';
                    break;
                default:
                    $level_class = 'level-beginner';
            }

            // Fix thumbnail image path
            $thumbnail_path = 'assets/images/thumbnail/' . $row['thumbnail'];
            if (!file_exists($thumbnail_path) || empty($row['thumbnail'])) {
                $thumbnail_path = 'assets/images/default-course.png';
            }

            // Tutor image logic
            $profile_pic_path = 'assets/images/default-profile.png';
            // Simple check (can be enhanced if complex logic needed)
            if (!empty($row['profile_pic'])) {
                if (file_exists($row['profile_pic']))
                    $profile_pic_path = $row['profile_pic'];
                elseif (file_exists($tutor_profile_path . $row['profile_pic']))
                    $profile_pic_path = $tutor_profile_path . $row['profile_pic'];
                elseif (file_exists('uploads/tutors/' . $row['profile_pic']))
                    $profile_pic_path = 'uploads/tutors/' . $row['profile_pic'];
            }
            ?>
            <div class="card course-card" data-level="<?php echo strtolower($row['level']); ?>">
                <div class="card-thumbnail-container">
                    <img src="<?php echo $thumbnail_path; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>"
                        class="card-thumbnail" onerror="this.onerror=null; this.src='assets/images/default-course.png';">
                </div>

                <div class="card-content">
                    <div class="course-meta">
                        <span class="price-tag">₹
                            <?php echo number_format($row['price'], 2); ?>
                        </span>
                        <span class="level-badge <?php echo $level_class; ?>">
                            <?php echo ucfirst($row['level']); ?>
                        </span>
                    </div>

                    <h5 class="course-title">
                        <?php echo $row['title']; ?>
                    </h5>

                    <div class="description-container">
                        <div class="short-description"></div>
                        <div class="full-description">
                            <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                        </div>
                        <button class="read-more-btn">
                            <span>Read More</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </button>
                    </div>

                    <div class="tutor-info">
                        <img src="<?php echo $profile_pic_path; ?>" class="tutor-profile"
                            alt="<?php echo htmlspecialchars($row['tutor_name']); ?>"
                            onerror="this.onerror=null; this.src='assets/images/default-profile.png';">
                        <div>
                            <span class="block font-semibold text-sm text-gray-800">
                                <a
                                    href="tutor-profile.php?id=<?php echo $row['tutor_id']; ?>"><?php echo $row['tutor_name']; ?></a>
                            </span>
                            <span class="text-xs text-gray-500">Instructor</span>
                        </div>
                    </div>

                    <div class="stats">
                        <div class="stat-item">
                            <i class="ri-book-line"></i>
                            <span>
                                <?php echo $row['lesson_count']; ?> Lessons
                            </span>
                        </div>
                        <div class="stat-item">
                            <i class="ri-time-line"></i>
                            <span>
                                <?php echo $row['lesson_count'] * 30; ?> min
                            </span>
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-0">
                    <a href="course-detail.php?id=<?php echo $row['course_id']; ?>" class="view-course-btn">
                        View Course Details
                    </a>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="no-results">
            <i class="ri-search-line"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Found</h3>
            <p class="text-gray-500 mb-6">
                <?php if (!empty($search)): ?>
                    No courses found for "
                    <?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    No courses available for
                    <?php echo htmlspecialchars($category_name); ?> yet.
                <?php endif; ?>
            </p>
            <a href="courses.php"
                class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                <i class="ri-arrow-left-line mr-2"></i> Browse All Categories
            </a>
        </div>
    <?php } ?>
</div>

<?php if ($total_courses > 0): ?>
    <div class="mt-10 text-center text-gray-500">
        Showing
        <?php echo $total_courses; ?> course
        <?php echo $total_courses > 1 ? 's' : ''; ?>
        <?php if (!empty($search)): ?>
            for "
            <?php echo htmlspecialchars($search); ?>"
        <?php endif; ?>
    </div>
<?php endif; ?>