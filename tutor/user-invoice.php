<?php
session_start();
include_once "connection.php";

$user_id_session = isset($_GET['uid']) ? (int) $_GET['uid'] : 0;
$course_id = isset($_GET['cid']) ? (int) $_GET['cid'] : 0;
$tutor_id = isset($_GET['tid']) ? (int) $_GET['tid'] : 0;

if ($user_id_session <= 0 || $course_id <= 0 || $tutor_id <= 0) {
    die("Invalid Invoice parameters.");
}

// Fetch payment details
$paymentStmt = $conn->prepare("
    SELECT p.*, c.course_title, c.course_description, 
           u.user_name, u.user_email, u.mobile,
           t.tutor_name AS tutor_name, t.tutor_email AS tutor_email, t.tutor_phone
    FROM user_payment_tbl p
    JOIN course_tbl c ON p.course_id = c.course_id
    JOIN user_tbl u ON p.user_id = u.user_id
    JOIN tutor_tbl t ON t.tutor_id = p.tutor_id
    WHERE p.course_id = ? AND p.user_id = ? AND p.tutor_id = ?
");
$paymentStmt->bind_param("iii", $course_id, $user_id_session, $tutor_id);
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
    <meta charset="UTF-8" />
    <title><?= $company_name ?> - Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Remixicon -->
    <link href="../assets/libs/remixicon/fonts/remixicon.css" rel="stylesheet" type="text/css" />
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="../assets/css/tailwind.min.css" />

    <link rel="stylesheet" href="assets/css/user-invoice.css">
</head>

<body class="text-gray-900 dark:text-white dark:bg-gray-900">
    <div class="print-btn-container hidden"></div>
    <?php include_once "includes/headtag.php" ?>
    <!-- Start Hero -->
    <section class="relative bg-gray-50 dark:bg-gray-800 py-24 border border-gray-100 dark:border-gray-700">
        <div class="container relative">
            <div class="md:flex justify-center">
                <div class="lg:w-4/5 w-full">
                    <div class="p-6 bg-white dark:bg-gray-900 rounded-lg relative overflow-hidden">

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
                                            <span class="academy"><?php echo $c_name[1] ?></span>
                                        </div>
                                        <div class="flex mt-4 text-gray-400">
                                            <i class="ri-links-line text-lg me-2"></i>
                                            <a href="../index.php"
                                                class="text-primary dark:text-white font-medium">www.skillrise.com</a>
                                        </div>
                                    </div>

                                    <div class="mt-6 md:mt-0 md:w-56 text-start">
                                        <h5 class="text-md font-semibold text-gray-900 dark:text-white">Tutor
                                            Details:</h5>
                                        <ul class="list-none text-gray-400">
                                            <li class="flex mt-3">
                                                <span class="w-24 shrink-0 font-medium tracking-wide">Name:</span>
                                                <span><?= e($payment['tutor_name']) ?: 'SkillRise Tutor' ?></span>
                                            </li>
                                            <li class="flex mt-3">
                                                <span class="w-24 shrink-0 font-medium tracking-wide">Phone:</span>
                                                <span
                                                    class="break-all"><?= e($payment['tutor_phone']) ?: 'None' ?></span>
                                            </li>
                                            <li class="flex mt-3">
                                                <span class="w-24 shrink-0 font-medium tracking-wide">Email:</span>
                                                <span
                                                    class="break-all"><?= e($payment['tutor_email']) ?: 'tutor@skillrise.com' ?></span>
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
                                            <span class="ms-2"><?= e($payment['user_name']) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 shrink-0 font-medium tracking-wide">Phone:</span>
                                            <span class="ms-2 break-all"><?= e($payment['mobile']) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 shrink-0 font-medium tracking-wide">Email:</span>
                                            <span class="ms-2 break-all"><?= e($payment['user_email']) ?></span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="md:w-56 text-start">
                                    <h5 class="text-md font-semibold text-gray-900 dark:text-white">Invoice Details:
                                    </h5>
                                    <ul class="list-none text-gray-400">
                                        <li class="flex mt-3">
                                            <span class="w-24 font-medium">Inv No:</span>
                                            <span>#SR-0<?= e($payment['user_payment_id']) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 font-medium">Date:</span>
                                            <span><?= date("d M Y", strtotime($payment['payment_date'])) ?></span>
                                        </li>
                                        <li class="flex mt-3">
                                            <span class="w-24 font-medium">Ref:</span>
                                            <span class="text-xs"><?= e($payment['razorpay_id']) ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="relative dark:shadow-gray-800 rounded-lg mt-6 overflow-x-auto">
                                <table class="w-full text-start text-gray-500 dark:text-gray-400"
                                    style="table-layout: fixed;">
                                    <thead class="text-sm uppercase bg-transparent dark:bg-transparent">
                                        <tr>
                                            <th scope="col" class="text-center px-3 py-3 text-gray-900 dark:text-white"
                                                style="width:8%">
                                                No.
                                            </th>
                                            <th scope="col" class="text-start px-3 py-3 text-gray-900 dark:text-white"
                                                style="width:42%">
                                                Description
                                            </th>
                                            <th scope="col" class="text-center px-3 py-3 text-gray-900 dark:text-white"
                                                style="width:10%">
                                                Qty
                                            </th>
                                            <th scope="col" class="text-center px-3 py-3 text-gray-900 dark:text-white"
                                                style="width:20%">
                                                Rate
                                            </th>
                                            <th scope="col" class="text-end px-3 py-3 text-gray-900 dark:text-white"
                                                style="width:20%">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="bg-transparent dark:bg-transparent">
                                            <td class="text-center px-3 py-4">1</td>
                                            <td class="text-start px-3 py-4 font-medium text-gray-900 dark:text-white"
                                                style="word-wrap: break-word;">
                                                Course: <?= e($payment['course_title']) ?>
                                            </td>
                                            <td class="text-center px-3 py-4">1</td>
                                            <td class="text-center px-3 py-4">₹
                                                <?= number_format($payment['amount'], 2) ?>
                                            </td>
                                            <td class="text-end px-3 py-4">₹
                                                <?= number_format($payment['amount'], 2) ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="w-64 ms-auto p-5">
                                <ul class="list-none">
                                    <li class="text-gray-400 flex justify-between">
                                        <span>Subtotal:</span>
                                        <span>₹ <?= number_format($payment['amount'], 2) ?></span>
                                    </li>
                                    <li class="text-gray-400 flex justify-between mt-2">
                                        <span>Tax (Included):</span>
                                        <span>₹ 0.00</span>
                                    </li>
                                    <li
                                        class="flex justify-between font-bold mt-2 text-lg text-gray-900 dark:text-white border-t border-gray-100 dark:border-gray-800 pt-2">
                                        <span>Total:</span>
                                        <span>₹ <?= number_format($payment['amount'], 2) ?></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="invoice-footer border-t border-gray-100 dark:border-gray-800 pt-6 mt-6">
                                <div class="md:flex justify-between items-center">
                                    <div>
                                        <div class="text-gray-400 text-center md:text-start text-sm">
                                            <p>This is a computer generated invoice. No signature required.</p>
                                            <p class="mt-1">For any queries, contact <a
                                                    href="mailto:support@skillrise.com"
                                                    class="text-primary">support@skillrise.com</a></p>
                                        </div>
                                    </div>

                                    <div class="mt-4 md:mt-0">
                                        <div class="text-center md:text-end">
                                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <?= $company_name ?>
                                            </h5>
                                            <p class="text-gray-400 text-sm">Thank you for your purchase!</p>
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
                            </div>
                        </div><!-- /invoice-content -->
                    </div>
                </div>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->

</body>

</html>