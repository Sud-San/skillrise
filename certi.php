<?php
include 'connection.php';
session_start();

// Fetch user and course details
$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'];

$query = "SELECT user_tbl.user_name, course_tbl.course_title, enrollments_tbl.completed_at
          FROM enrollments_tbl
          INNER JOIN user_tbl ON enrollments_tbl.user_id = user_tbl.user_id
          INNER JOIN course_tbl ON enrollments_tbl.course_id = course_tbl.course_id
          WHERE enrollments_tbl.user_id = '$user_id' 
          AND enrollments_tbl.course_id = '$course_id'";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $student_name = $row['user_name'];
    $course_title = $row['course_title'];
    $completion_date = $row['completed_at'];
} else {
    header("Location: user-mycourses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="icon" sizes="180x180" href="codez3.png" />
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans|Pinyon+Script|Rochester');

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #eee;
        }

        .certificate-container {
            width: 900px;
            height: 500px;
            background-color: #fff;
            padding: 40px;
            color: #333;
            font-family: 'Open Sans', sans-serif;
            text-align: center;
            border: 10px solid lightblue;

            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 250px;
            opacity: 0.2;
        }

        .pm-certificate-title h2 {
            font-size: 50px;
            font-weight: bold;
            color: #222;
        }

        .pm-certificate-body p {
            font-size: 25px;
            font-weight: bold;
            margin: 15px 0;
            color: black;
            background: rgba(255, 255, 255, 0.7);
            padding: 5px;
            border-radius: 5px;
        }

        .pm-earned-text {
            font-size: 45px;
            font-weight: bold;
            font-family: 'Pinyon Script', cursive;
            color: #444;
        }

        .pm-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .pm-footer .date {
            width: 40%;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-top: 10px;
            font-size: 18px;
        }

        .download-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .download-btn:hover {
            background: #218838;
        }

        .close-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .close-btn:hover {
            background: #218838;
        }
    </style>
</head>

<body>

    <div id="certificate" class="certificate-container">
        <img class="watermark" src="<?php echo $logo; ?>" alt="Website Logo">

        <div class="pm-certificate-title">
            <h2>Certificate of Completion</h2>
        </div>

        <div class="pm-certificate-body">
            <p>This certificate is proudly presented to:</p>
            <p class="pm-earned-text font-bold text-2xl">
                <?php echo htmlspecialchars($student_name); ?>
            </p>
            <p>For outstanding performance and dedication in <br> successful completion of</p>
            <p class="pm-earned-text font-bold text-2xl">
                <?php echo htmlspecialchars($course_title); ?>
            </p>
            <p>Under the guidance of <?php echo $company_name; ?></p>
        </div>

        <div class="pm-footer">
            <div></div>
            <div class="date">Date of Completion:
                <?php echo $completion_date; ?>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px;">
        <button class="download-btn" onclick="downloadCertificate() ">Download Certificate</button>
        <button class="close-btn" onclick="window.location.href='user-mycourses.php'">Close</button>
    </div>
    <script>
        async function downloadCertificate() {
            const { jsPDF } = window.jspdf;
            const certificate = document.getElementById("certificate");

            html2canvas(certificate).then(canvas => {
                let imgData = canvas.toDataURL("image/png");

                let pdf = new jsPDF("l", "mm", [900, 500]);
                pdf.addImage(imgData, "PNG", 0, 0, 900, 500);

                let studentName = "<?php echo $student_name; ?>";
                pdf.save(`Certificate_${studentName}.pdf`);
            });
        }
    </script>
</body>

</html>