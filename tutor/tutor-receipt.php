<?php
require_once('includes/init.php');
include 'connection.php';

// Require login for tutor
if (!isset($_SESSION['tutor_id'])) {
    header("Location: login.php");
    exit;
}

$tutor_id_session = (int) $_SESSION['tutor_id'];
$payment_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($payment_id <= 0) {
    die("Invalid Invoice ID.");
}

// Fetch tutor payment details
$paymentStmt = $conn->prepare("
    SELECT tp.*, p.package_name, p.valid_months, p.price,
           t.tutor_name, t.tutor_email, t.tutor_phone
    FROM tutor_package_tbl tp
    JOIN package_tbl p ON tp.package_id = p.package_id
    JOIN tutor_tbl t ON tp.tutor_id = t.tutor_id
    WHERE tp.purchase_id = ? AND tp.tutor_id = ? AND tp.payment_status = 1
");
$paymentStmt->bind_param("ii", $payment_id, $tutor_id_session);
$paymentStmt->execute();
$payment = $paymentStmt->get_result()->fetch_assoc();
$paymentStmt->close();

if (!$payment) {
    die("Invoice not found or access denied.");
}

// Helper to escape output
function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">

<head>
    <title>Tutor Receipt</title>
    <link rel="icon" sizes="180x180" href="../codez3.png" />
    <link href="../assets/libs/remixicon/fonts/remixicon.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../assets/css/tailwind.min.css" />
    <link rel="stylesheet" href="../assets/css/tutor-receipt.css" />
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <div class="print-btn-container hidden">
        <!-- Kept just for any specific layout hooks if needed, but removed fixed floating button -->
    </div>

    <!-- For tutor receipt we might not include user-header since it's tutor side, but let's just mimic user-invoice structure -->

    <!-- Start Hero -->
    <section class="relative bg-gray-50 dark:bg-gray-800 py-24 border border-gray-100 dark:border-gray-700 h-screen">
        <div class="container relative">
            <div class="md:flex justify-center">
                <div class="lg:w-4/5 w-full">
                    <div
                        class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-100 dark:border-gray-800 relative overflow-hidden">

                        <!-- Watermark -->
                        <div class="watermark absolute inset-0"></div>

                        <div class="invoice-content">
                            <div class="border-b border-gray-100 dark:border-gray-800 pb-6">
                                <div class="md:flex justify-between">
                                    <div>
                                        <div class="logo">
                                            <img src="../SkillRise_logo1.png" width="100" alt="SkillRise Logo">
                                            <?php $c_name = explode(" ", $company_name) ?>
                                            <span class="skill"><?php echo $c_name[0] ?></span>
                                            <span
                                                class="academy"><?php echo isset($c_name[1]) ? $c_name[1] : '' ?></span>
                                        </div>
                                        <div class="flex mt-4 text-gray-400">
                                            <i class="ri-links-line text-lg me-2"></i>
                                            <a href="index.php"
                                                class="text-primary dark:text-white font-medium">www.skillrise.com</a>
                                        </div>
                                    </div>

                                    <div class="mt-6 md:mt-0 md:w-56 text-start">
                                        <h5 class="text-md font-semibold text-gray-900 dark:text-white">Platform
                                            Details:</h5>
                                        <ul class="list-none text-gray-400">
                                            <li class="flex mt-3">
                                                <span class="w-24 shrink-0 font-medium tracking-wide">Name:</span>
                                                <span class=""><?php echo $company_name ?></span>
                                            </li>
                                            <li class="flex mt-3">
                                                <span class="w-24 shrink-0 font-medium tracking-wide">Email:</span>
                                                <span class=" break-all"><?php echo "skillrise@gmail.com " ?></span>
                                            </li>
                                            <li class="flex mt-3">
                                                <span class="w-24 shrink-0 font-medium tracking-wide">Website:</span>
                                                <span class=" break-all"><?php echo "www.skillrise.com" ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="md:flex justify-between mt-6">
                                <div>
                                    <h5 class="text-md font-semibold text-gray-900 dark:text-white">Bill To:</h5>
                                    <ul class="list-none text-gray-400">
                                        <li class="flex mt-3">
                                            <span class="w-24 shrink-0 font-medium tracking-wide">Name:</span>
                                            <span class="ms-2"><?= e($payment['tutor_name']) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 shrink-0 font-medium tracking-wide">Phone:</span>
                                            <span class="ms-2 break-all"><?= e($payment['tutor_phone']) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 shrink-0 font-medium tracking-wide">Email:</span>
                                            <span class="ms-2 break-all"><?= e($payment['tutor_email']) ?></span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="md:w-56 text-start">
                                    <h5 class="text-md font-semibold text-gray-900 dark:text-white">Invoice Details:
                                    </h5>
                                    <ul class="list-none text-gray-400">
                                        <li class="flex mt-3">
                                            <span class="w-24 font-medium">Inv No:</span>
                                            <span>#CDZ-0<?= e($payment['purchase_id']) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 font-medium">Date:</span>
                                            <span><?= date("d M Y", strtotime($payment['created_at'])) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 font-medium">Ref:</span>
                                            <span class="text-xs"><?= e($payment['razorpay_id']) ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="relative overflow-x-auto dark:shadow-gray-800 rounded-lg mt-6">
                                <table class="w-full text-start text-gray-500 dark:text-gray-400">
                                    <thead class="text-sm uppercase bg-transparent dark:bg-transparent">
                                        <tr>
                                            <th scope="col"
                                                class="text-center px-6 py-3 w-16 text-gray-900 dark:text-white">
                                                No.
                                            </th>
                                            <th scope="col" class="text-start px-6 py-3 text-gray-900 dark:text-white">
                                                Package Description
                                            </th>
                                            <th scope="col"
                                                class="text-center px-6 py-3 w-20 text-gray-900 dark:text-white">
                                                Validity
                                            </th>
                                            <th scope="col"
                                                class="text-center px-6 py-3 w-32 text-gray-900 dark:text-white">
                                                Rate
                                            </th>
                                            <th scope="col"
                                                class="text-end px-6 py-3 w-32 text-gray-900 dark:text-white">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="bg-transparent dark:bg-transparent">
                                            <td class="text-center px-6 py-4">1</td>
                                            <th scope="row"
                                                class="text-start px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                Tutor Subscription Plan: <?= e($payment['package_name']) ?> Plan
                                            </th>
                                            <td class="text-center px-6 py-4"><?= e($payment['valid_months']) ?>
                                                <?= $payment['valid_months'] > 1 ? 'Months' : 'Month' ?>
                                            </td>
                                            <td class="text-center px-6 py-4">₹
                                                <?= number_format($payment['amount_paid'], 2) ?>
                                            </td>
                                            <td class="text-end px-6 py-4">₹
                                                <?= number_format($payment['amount_paid'], 2) ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="w-64 ms-auto p-5">
                                <ul class="list-none">
                                    <li class="text-gray-400 flex justify-between">
                                        <span>Subtotal:</span>
                                        <span>₹ <?= number_format($payment['amount_paid'], 2) ?></span>
                                    </li>
                                    <li class="text-gray-400 flex justify-between mt-2">
                                        <span>Tax (Included):</span>
                                        <span>₹ 0.00</span>
                                    </li>
                                    <li
                                        class="flex justify-between font-bold mt-2 text-lg text-gray-900 dark:text-white border-t border-gray-100 dark:border-gray-800 pt-2">
                                        <span>Total:</span>
                                        <span>₹ <?= number_format($payment['amount_paid'], 2) ?></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="invoice-footer border-t border-gray-100 dark:border-gray-800 pt-6 mt-6">
                                <div class="md:flex justify-between items-center">
                                    <div>
                                        <div class="text-gray-400 text-center md:text-start text-sm">
                                            <p>This is a computer generated invoice. No signature required.</p>
                                            <p class="mt-1">For any queries, contact <a
                                                    href="mailto:skillrise@gmail.com"
                                                    class="text-primary">skillrise@gmail.com</a></p>
                                        </div>
                                    </div>

                                    <div class="mt-4 md:mt-0">
                                        <div class="text-center md:text-end">
                                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <?= $company_name ?>
                                            </h5>
                                            <p class="text-gray-400 text-sm">Thank you for teaching with us!</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 text-center print-btn-container">
                                    <button onclick="window.print()"
                                        class="bg-primary text-white px-6 py-2.5 rounded-lg shadow-lg hover:bg-primary-dark transition-all inline-flex items-center gap-2 font-medium">
                                        <i class="ri-printer-line text-lg"></i>
                                        Print Invoice
                                    </button>
                                </div>
                            </div><!-- /invoice-content -->
                        </div>
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->

    <?php
    // Usually tutors don't have the same footer, but we'll include it or script tags manually.
    ?>

    <!-- JAVASCRIPTS -->
    <script src="../assets/js/plugins.init.js"></script>
    <script src="../assets/js/app.js"></script>
</body>

</html>