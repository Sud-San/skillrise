<?php
session_start();
require 'vendor/autoload.php'; // if installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Get email from form
    $email = $_POST['email'];

    // 2. Generate 6-digit OTP
    $otp = rand(100000, 999999);

    // 3. Save OTP in session (you can also save in DB)
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expiry'] = time() + 300; // valid for 5 minutes

    // 4. Send Email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';          // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = $_SESSION['reset_email'];     // your email
        $mail->Password   = 'your_app_password';       // app password, not normal password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('yourgmail@gmail.com', 'College Finder'); // from name
        $mail->addAddress($email); // user email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "
            <h3>Your OTP Code</h3>
            <p>Your OTP is: <strong>$otp</strong></p>
            <p>This code is valid for 5 minutes.</p>
        ";
        $mail->AltBody = "Your OTP is: $otp";

        $mail->send();

        echo "OTP sent to your email.";
    } catch (Exception $e) {
        echo "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
