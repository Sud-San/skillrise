<?php
error_reporting(E_ALL & ~E_NOTICE);
ob_start();

require_once('includes/init.php');
require('connection.php');
require('./vendor/autoload.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');

// DEBUG: Log the call
$logData = date('Y-m-d H:i:s') . " - Call to razorpay_order.php. Session tutor_id: " . ($_SESSION['tutor_id'] ?? 'NONE') . " POST package_id: " . ($_POST['package_id'] ?? 'NONE') . "\n";
file_put_contents('debug_log.txt', $logData, FILE_APPEND);

if (!isset($_SESSION['tutor_id'])) {
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Unauthenticated access attempt.\n", FILE_APPEND);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$tutor_id = (int) $_SESSION['tutor_id'];
$package_id = (int) ($_POST['package_id'] ?? 0);

if ($package_id <= 0) {
    echo json_encode(['error' => 'Invalid package']);
    exit;
}

// Fetch package details
$pkg_res = mysqli_query($conn, "SELECT * FROM package_tbl WHERE package_id = $package_id AND package_status = 1");
$package = mysqli_fetch_assoc($pkg_res);

if (!$package) {
    echo json_encode(['error' => 'Package not found']);
    exit;
}

// Fetch tutor details
$tutor_res = mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id = $tutor_id");
$tutor = mysqli_fetch_assoc($tutor_res);

if (!$tutor) {
    echo json_encode(['error' => 'Tutor not found']);
    exit;
}

// Initialize Razorpay
$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

$package_price = str_replace(',', '', $package['price']);
$amount = (int) round((float) $package_price * 100); // Razorpay requires integer in paise
$currency = 'INR';
$receipt = 'rcpt_' . time() . '_' . $tutor_id;

try {
    $order = $api->order->create([
        'receipt' => $receipt,
        'amount' => $amount,
        'currency' => $currency,
        'payment_capture' => 1 // auto capture
    ]);

    if (!isset($order['id'])) {
        throw new Exception("Failed to create Razorpay order ID.");
    }

    // Calculate start and end date
    $start_date = date('Y-m-d H:i:s');
    // Fetch tutor's current active package to check for carry-over days
    $cur_res = mysqli_query($conn, "
        SELECT end_date FROM tutor_package_tbl 
        WHERE tutor_id = $tutor_id AND payment_status = 1 
        ORDER BY created_at DESC LIMIT 1
    ");
    $current = mysqli_fetch_assoc($cur_res);

    $carry_over_days = 0;
    if ($current) {
        $end_curr = new DateTime($current['end_date']);
        $now = new DateTime();
        if ($end_curr > $now) {
            $diff = $now->diff($end_curr);
            $carry_over_days = $diff->days;
        }
    }

    $end_date = date('Y-m-d H:i:s', strtotime("+{$package['valid_months']} months +{$carry_over_days} days"));

    // Record the payment attempt in tutor_package_tbl (status 0 = pending)
    $stmt = $conn->prepare("
        INSERT INTO tutor_package_tbl (tutor_id, package_id, razorpay_id, amount_paid, start_date, end_date, payment_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");

    $amount_decimal = (float) $package_price;
    $order_id = $order['id'];
    $stmt->bind_param("iisdss", $tutor_id, $package_id, $order_id, $amount_decimal, $start_date, $end_date);

    if (!$stmt->execute()) {
        throw new Exception("Database Insert Failed: " . $stmt->error);
    }

    $payment_row_id = $stmt->insert_id;

    ob_clean();
    echo json_encode([
        'order_id' => $order['id'],
        'amount' => $amount,
        'currency' => $currency,
        'tutor_name' => $tutor['tutor_name'] ?? '',
        'tutor_email' => $tutor['tutor_email'] ?? '',
        'tutor_phone' => $tutor['tutor_phone'] ?? '',
        'payment_row_id' => $payment_row_id,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);

} catch (Throwable $e) {
    error_log("Razorpay Order Error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['error' => 'Order creation failed: ' . $e->getMessage()]);
}
ob_end_flush();
?>