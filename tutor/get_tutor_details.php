<?php
include 'connection.php';

$tutor_id = intval($_GET['tutor_id']);

$sql = "
SELECT 
    degree_name,
    clg_name,
    degree_image,
    passing_year,
    certificate_name,
    certificate_image,
    institute_name
FROM tutors_details
WHERE tutor_id = $tutor_id
AND status = 1
ORDER BY passing_year DESC
";

$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>