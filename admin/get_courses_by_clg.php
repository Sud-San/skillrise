<?php
include 'connection.php';

if (!isset($_POST['college_id'])) {
    echo "<option value=''>No Courses Found</option>";
    exit;
}

$college_id = intval($_POST['college_id']);

$sql = "
    SELECT 
        cc.college_course_id,
        ct.course_name
    FROM college_course AS cc
    JOIN course_tbl AS ct ON cc.course_id = ct.course_id
    WHERE cc.college_id = $college_id
    ORDER BY ct.course_name ASC
";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='{$row['college_course_id']}'>{$row['course_name']}</option>";
    }
} else {
    echo "<option value=''>No Courses Found</option>";
}
?>