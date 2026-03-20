<?php
error_reporting(E_ALL & ~E_NOTICE);
ob_start();

require_once('includes/init.php');
require('connection.php');
require('./vendor/autoload.php');

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

if (!isset($_SESSION['tutor_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$tutor_id = (int) $_SESSION['tutor_id'];
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
$razorpay_signature = $_POST['razorpay_signature'] ?? '';
$payment_row_id = (int) ($_POST['payment_row_id'] ?? 0);
$package_id = (int) ($_POST['package_id'] ?? 0);
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

if ($payment_row_id <= 0 || empty($razorpay_payment_id)) {
    echo json_encode(['error' => 'Missing required payment data']);
    exit;
}

// Handle dismissed / failed payment
if ($razorpay_payment_id === 'dismissed') {
    mysqli_query($conn, "UPDATE tutor_package_tbl SET payment_status = 2 WHERE purchase_id = $payment_row_id");
    echo json_encode(['success' => false, 'error' => 'Payment dismissed']);
    exit;
}

// Verify signature
$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

try {
    // Note: Signature verification usually requires order_id, payment_id and signature.
    // However, if the user didn't pass signature (e.g. from frontend), we might need to fetch payment details.
    // But typically Razorpay handler provides the signature.

    if (!empty($razorpay_signature)) {
        $attributes = [
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } else {
        // Fallback: Verify payment status by fetching from API if signature is missing
        $payment = $api->payment->fetch($razorpay_payment_id);
        if ($payment->status !== 'captured') {
            throw new Exception("Payment not captured. Status: " . $payment->status);
        }
    }

    // Payment successful — Activate package
    // 1. Deactivate other active packages for this tutor
    mysqli_query($conn, "UPDATE tutor_package_tbl SET payment_status = 0 WHERE tutor_id = $tutor_id AND payment_status = 1");

    // 2. Update the current record as Paid and Active
    $stmt = $conn->prepare("
        UPDATE tutor_package_tbl 
        SET payment_status = 1, 
            razorpay_id = ?, 
            created_at = NOW() 
        WHERE purchase_id = ?
    ");
    $stmt->bind_param("si", $razorpay_payment_id, $payment_row_id);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(['success' => true, 'end_date' => date('d M Y', strtotime($end_date))]);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Database update failed: ' . $conn->error]);
    }

} catch (Throwable $e) {
    // Payment verification failed
    mysqli_query($conn, "UPDATE tutor_package_tbl SET payment_status = 2 WHERE purchase_id = $payment_row_id");
    error_log("Razorpay Verify Error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Verification failed: ' . $e->getMessage()]);
}
ob_end_flush();
?>