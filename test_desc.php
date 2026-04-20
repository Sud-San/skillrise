<?php
include 'tutor/connection.php';
$q = mysqli_query($conn, 'SELECT course_id, tutor_id FROM course_tbl WHERE course_id=17');
while($r=mysqli_fetch_assoc($q)) {
  echo json_encode($r)."\n";
}
