<?php
session_start();
include_once 'connection.php';

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Must have a reset email in session
if (empty($_SESSION['tutor_reset_email'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start again.']);
    exit();
}

$email = $_SESSION['tutor_reset_email'];

// Fetch tutor name
$stmt = mysqli_prepare($conn, "SELECT tutor_name FROM tutor_tbl WHERE tutor_email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tutor = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$tutor) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']);
    exit();
}

// Generate new OTP and update session
$otp = rand(100000, 999999);
$_SESSION['tutor_otp'] = $otp;
$_SESSION['tutor_otp_expire'] = time() + 300;

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "codezy03@gmail.com";
    $mail->Password   = "bjzmtwtfnadwxqbt"; // your app password
    $mail->SMTPSecure = "ssl";
    $mail->Port       = 465;

    $mail->setFrom("codezy03@gmail.com", "SkillRise");
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "New Password Reset OTP - SkillRise";
    $mail->Body = '
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;border:1px solid #e2e8f0;border-radius:10px;">
            <div style="background:linear-gradient(135deg,#10b981,#065f46);padding:30px;text-align:center;border-radius:10px 10px 0 0;">
                <h1 style="color:white;margin:0;font-size:24px;">New Verification Code</h1>
            </div>
            <div style="background:#f9f9f9;padding:30px;border-radius:0 0 10px 10px;">
                <h2 style="color:#333;font-size:18px;">Hello ' . htmlspecialchars($tutor['tutor_name']) . ',</h2>
                <p style="color:#666;line-height:1.6;">Your new verification code is:</p>
                <div style="text-align:center;margin:30px 0;">
                    <div style="display:inline-block;background:white;padding:20px 40px;border-radius:10px;border:2px dashed #10b981;">
                        <span style="font-size:32px;font-weight:bold;color:#10b981;letter-spacing:5px;">' . $otp . '</span>
                    </div>
                </div>
                <p style="color:#666;line-height:1.6;"><strong>⏱️ This code will expire in 5 minutes.</strong></p>
                <div style="margin-top:20px;padding:15px;background:#fff3cd;border-left:4px solid #ffc107;border-radius:5px;">
                    <p style="margin:0;color:#856404;font-size:14px;">⚠️ If you did not request this, please ignore this email.</p>
                </div>
                <p style="margin-top:30px;color:#666;">Best regards,<br><strong style="color:#10b981;">SkillRise Team</strong></p>
            </div>
        </div>
    ';

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'New OTP sent to your email!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mail error: ' . $e->getMessage()]);
}
exit();
?>