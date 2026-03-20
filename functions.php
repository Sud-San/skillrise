<?php
// functions.php
require_once 'connection.php';
require __DIR__ . '/vendor/autoload.php'; // composer autoload for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * send_email - send HTML/Plain email using PHPMailer (Gmail SMTP)
 */
function send_email($to, $subject, $bodyHtml, $bodyPlain = '') {
    // *** REPLACE with your Gmail & App Password ***
    $smtpHost = 'smtp.gmail.com';
    $smtpUser = 'codezy03@gmail.com';               // <-- put your gmail here
    $smtpPass = 'utcd obzc begu popi';     // <-- put your app password here
    $smtpPort = 587;
    $smtpSecure = 'tls';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port       = $smtpPort;

        $mail->setFrom($smtpUser, 'Codezy Support');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = $bodyPlain ?: strip_tags($bodyHtml);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: '.$mail->ErrorInfo);
        return false;
    }
}

/**
 * get_user_by_email - returns assoc array or null
 */
function get_user_by_email($email) {
    global $conn;
    $emailEsc = mysqli_real_escape_string($conn, $email);
    $sql = "SELECT * FROM users_tbl WHERE user_email = '$emailEsc' LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) return mysqli_fetch_assoc($res);
    return null;
}
