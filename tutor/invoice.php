<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = (int) $_SESSION['tutor_id'];

// ── Fetch tutor info ───────────────────────────────────
$t_res = mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id = $tutor_id");
$tutor = mysqli_fetch_assoc($t_res);

$p_res = mysqli_query($conn, "SELECT * FROM tutor_profile_tbl WHERE tutor_id = $tutor_id");
$profile = mysqli_fetch_assoc($p_res);

// ── Fetch specific payment or list all ─────────────────
$payment_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($payment_id) {
    // Single invoice view
    $pay_res = mysqli_query($conn, "
        SELECT tp.*, p.package_name, p.valid_months, p.can_add_courses,
               p.can_add_videos, p.can_add_quiz, p.can_add_games, p.can_add_assignments,
               p.max_course, p.max_video_upload
        FROM tutor_package_tbl tp
        JOIN package_tbl p ON tp.package_id = p.package_id
        WHERE tp.purchase_id = $payment_id AND tp.tutor_id = $tutor_id AND tp.payment_status = 1
        LIMIT 1
    ");
    $payment = mysqli_fetch_assoc($pay_res);
    if (!$payment) {
        header('Location: invoice.php');
        exit;
    }
} else {
    $payment = null;
}

// ── Fetch all paid invoices ────────────────────────────
$all_res = mysqli_query($conn, "
    SELECT tp.*, p.package_name
    FROM tutor_package_tbl tp
    JOIN package_tbl p ON tp.package_id = p.package_id
    WHERE tp.tutor_id = $tutor_id AND tp.payment_status = 1
    ORDER BY tp.created_at DESC
");
$all_payments = [];
while ($r = mysqli_fetch_assoc($all_res))
    $all_payments[] = $r;

// ── Invoice number helper ──────────────────────────────
function inv_no($id)
{
    return 'CDZ-' . str_pad($id, 6, '0', STR_PAD_LEFT);
}

$avatar = (!empty($profile['profile_pic']))
    ? 'assets/uploads/profiles/' . htmlspecialchars($profile['profile_pic'])
    : 'assets/images/user.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Mulish:wght@400;500;600;700&display=swap');

        :root {
            --g600: #16a34a;
            --g500: #22c55e;
            --g100: #dcfce7;
            --g50: #f0fdf4;
            --ink: #111827;
            --ink2: #374151;
            --muted: #6b7280;
            --border: #e5e7eb;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .04);
        }

        body.app,
        .app-wrapper {
            font-family: 'Mulish', sans-serif !important;
            background: #f9fafb;
        }

        .page-title {
            font-family: 'Sora', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.4px;
            margin: 0;
        }

        .page-sub {
            font-size: .85rem;
            color: var(--muted);
            margin-top: 3px;
        }

        /* ── Invoice list card ── */
        .inv-list-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, .05);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .inv-list-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--g600);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .inv-list-header::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--g500);
            border-radius: 50%;
        }

        /* ── Invoice list rows ── */
        .inv-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
            flex-wrap: wrap;
            transition: background .12s;
        }

        .inv-row:last-child {
            border-bottom: none;
        }

        .inv-row:hover {
            background: var(--g50);
        }

        .inv-num {
            font-family: 'Sora', sans-serif;
            font-size: .82rem;
            font-weight: 700;
            color: var(--g600);
        }

        .inv-pkg {
            font-size: .875rem;
            font-weight: 600;
            color: var(--ink);
        }

        .inv-date {
            font-size: .78rem;
            color: var(--muted);
            margin-top: 1px;
        }

        .inv-amt {
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: var(--ink);
        }

        .inv-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--g100);
            color: #15803d;
            font-size: .7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .btn-view-inv {
            background: var(--g600);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: .78rem;
            font-weight: 700;
            font-family: 'Mulish', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .15s;
        }

        .btn-view-inv:hover {
            background: #15803d;
            color: #fff;
        }

        /* ── Invoice Preview Modal ── */
        .modal-invoice .modal-content {
            border-radius: 18px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-invoice .modal-header {
            border: none;
            padding: 20px 24px 0;
        }

        .modal-invoice .modal-body {
            padding: 16px 24px 24px;
        }

        /* ── The actual invoice design ── */
        #invoice-box {
            background: #fff;
            padding: 40px;
            font-family: 'Mulish', sans-serif;
            max-width: 700px;
            margin: 0 auto;
        }

        .inv-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .inv-brand .brand-name {
            font-family: 'Sora', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--g600);
            letter-spacing: -.5px;
        }

        .inv-brand .brand-tag {
            font-size: .72rem;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .inv-title-badge {
            background: var(--g600);
            color: #fff;
            font-family: 'Sora', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            padding: 6px 20px;
            border-radius: 8px;
            letter-spacing: .04em;
        }

        .inv-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .inv-meta-cell {
            padding: 14px 18px;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .inv-meta-cell:nth-child(even) {
            border-right: none;
        }

        .inv-meta-cell:nth-last-child(-n+2) {
            border-bottom: none;
        }

        .inv-meta-label {
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
            margin-bottom: 3px;
        }

        .inv-meta-value {
            font-size: .88rem;
            font-weight: 600;
            color: var(--ink);
        }

        .inv-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .inv-party-box {
            background: #f9fafb;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
        }

        .inv-party-box.from {
            border-left: 4px solid var(--g600);
        }

        .inv-party-box.to {
            border-left: 4px solid #6b7280;
        }

        .party-label {
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .party-name {
            font-family: 'Sora', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            color: var(--ink);
        }

        .party-info {
            font-size: .78rem;
            color: var(--ink2);
            margin-top: 3px;
            line-height: 1.5;
        }

        /* Items table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .inv-table thead tr {
            background: var(--g600);
            color: #fff;
        }

        .inv-table thead th {
            padding: 11px 16px;
            font-size: .75rem;
            font-weight: 700;
            text-align: left;
            letter-spacing: .04em;
        }

        .inv-table thead th:last-child {
            text-align: right;
        }

        .inv-table tbody td {
            padding: 12px 16px;
            font-size: .85rem;
            color: var(--ink2);
            border-bottom: 1px solid var(--border);
        }

        .inv-table tbody td:last-child {
            text-align: right;
            font-weight: 700;
            color: var(--ink);
        }

        .inv-table tbody tr:last-child td {
            border-bottom: none;
        }

        .inv-table tfoot td {
            padding: 10px 16px;
            font-size: .875rem;
            font-weight: 700;
        }

        .inv-table tfoot .total-row td {
            background: var(--g50);
            border-top: 2px solid var(--g100);
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
            color: var(--g600);
        }

        .inv-table tfoot td:last-child {
            text-align: right;
        }

        /* Features included */
        .inv-features {
            background: var(--g50);
            border: 1px solid var(--g100);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }

        .inv-features-title {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--g600);
            margin-bottom: 10px;
        }

        .feat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .feat-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .78rem;
            color: var(--ink2);
            font-weight: 600;
        }

        .feat-item i {
            font-size: .7rem;
        }

        /* Footer */
        .inv-footer {
            border-top: 2px solid var(--g100);
            padding-top: 16px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .inv-footer .f-brand {
            font-family: 'Sora', sans-serif;
            font-size: .8rem;
            font-weight: 700;
            color: var(--g600);
        }

        .inv-footer .f-note {
            font-size: .72rem;
            color: var(--muted);
        }

        .inv-thank {
            text-align: center;
            margin-top: 20px;
            padding: 12px;
            background: linear-gradient(135deg, var(--g600), #15803d);
            border-radius: 8px;
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
        }

        /* Action buttons */
        .inv-action-bar {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .btn-download {
            background: var(--g600);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 9px 20px;
            font-size: .85rem;
            font-weight: 700;
            font-family: 'Mulish', sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background .15s;
        }

        .btn-download:hover {
            background: #15803d;
        }

        .btn-print {
            background: transparent;
            color: var(--muted);
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 9px 20px;
            font-size: .85rem;
            font-weight: 700;
            font-family: 'Mulish', sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all .15s;
        }

        .btn-print:hover {
            background: #f3f4f6;
            color: var(--ink2);
        }

        /* Empty state */
        .empty-inv {
            text-align: center;
            padding: 40px;
            color: var(--muted);
        }

        .empty-inv i {
            font-size: 2.5rem;
            color: #d1d5db;
            display: block;
            margin-bottom: 10px;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #invoice-box,
            #invoice-box * {
                visibility: visible;
            }

            #invoice-box {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <!-- Page Header -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title">Invoices</h1>
                        <div class="page-sub">Your payment receipts from <?php echo $company_name ?></div>
                    </div>
                    <a href="settings.php#plan" class="d-inline-flex align-items-center gap-2"
                        style="font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Settings
                    </a>
                </div>

                <!-- ── Invoice List ── -->
                <div class="inv-list-card">
                    <div class="inv-list-header">All Invoices</div>

                    <?php if (empty($all_payments)): ?>
                        <div class="empty-inv">
                            <i class="fa-solid fa-receipt"></i>
                            No paid invoices found yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($all_payments as $pay): ?>
                            <div class="inv-row">
                                <div>
                                    <div class="inv-num"><?= inv_no($pay['purchase_id']) ?></div>
                                    <div class="inv-pkg"><?= htmlspecialchars($pay['package_name']) ?> Plan</div>
                                    <div class="inv-date">
                                        <i class="fa-solid fa-calendar me-1"></i>
                                        <?= $pay['created_at'] ? date('d M Y, h:i A', strtotime($pay['created_at'])) : date('d M Y', strtotime($pay['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="text-end">
                                        <div class="inv-amt">₹<?= number_format($pay['amount_paid'], 0) ?></div>
                                        <span class="inv-status"><i class="fa-solid fa-circle-check"></i> Paid</span>
                                    </div>
                                    <button class="btn-view-inv"
                                        onclick="window.open('tutor-receipt.php?id=<?= $pay['purchase_id'] ?>', '_blank')">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Invoice Modal ── -->
    <div class="modal fade modal-invoice" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-invoice" style="color:var(--g600);"></i>
                        <span
                            style="font-family:'Sora',sans-serif;font-weight:700;font-size:1rem;color:var(--ink);">Invoice</span>
                        <span id="modal-inv-num" style="font-size:.8rem;color:var(--muted);font-weight:600;"></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Action bar -->
                    <div class="inv-action-bar">
                        <button class="btn-print" onclick="printInvoice()">
                            <i class="fa-solid fa-print"></i> Print
                        </button>
                        <button class="btn-download" onclick="downloadPDF()">
                            <i class="fa-solid fa-file-arrow-down"></i> Download PDF
                        </button>
                    </div>

                    <!-- Invoice content -->
                    <div id="invoice-box">

                        <!-- Header -->
                        <div class="inv-brand">
                            <div>
                                <div class="brand-name"><?php echo $company_name ?></div>
                                <div class="brand-tag">E-Learning Platform</div>
                                <div style="font-size:.72rem;color:var(--muted);margin-top:4px;">
                                    <?php echo $company_email ?> &nbsp;|&nbsp;
                                    <?php echo $company_website ?>
                                </div>
                            </div>
                            <div class="inv-title-badge">INVOICE</div>
                        </div>

                        <!-- Invoice Meta -->
                        <div class="inv-meta-grid">
                            <div class="inv-meta-cell">
                                <div class="inv-meta-label">Invoice No.</div>
                                <div class="inv-meta-value" id="inv-number">—</div>
                            </div>
                            <div class="inv-meta-cell">
                                <div class="inv-meta-label">Payment Date</div>
                                <div class="inv-meta-value" id="inv-date">—</div>
                            </div>
                            <div class="inv-meta-cell">
                                <div class="inv-meta-label">Razorpay ID</div>
                                <div class="inv-meta-value" id="inv-rzp"
                                    style="font-family:monospace;font-size:.78rem;">—</div>
                            </div>
                            <div class="inv-meta-cell">
                                <div class="inv-meta-label">Status</div>
                                <div class="inv-meta-value">
                                    <span
                                        style="background:#dcfce7;color:#15803d;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;">
                                        <i class="fa-solid fa-circle-check me-1"></i>Paid
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- From / To -->
                        <div class="inv-parties">
                            <div class="inv-party-box from">
                                <div class="party-label">From</div>
                                <div class="party-name">
                                    <?php echo $company_name ?>
                                </div>
                                <div class="party-info">
                                    <?php echo $company_address ?><br>
                                    <?php echo $company_email ?><br>
                                    <?php echo $company_website ?>
                                </div>
                            </div>
                            <div class="inv-party-box to">
                                <div class="party-label">Bill To</div>
                                <div class="party-name"><?= htmlspecialchars($tutor['tutor_name'] ?? '—') ?></div>
                                <div class="party-info">
                                    <?= htmlspecialchars($tutor['tutor_email'] ?? '') ?><br>
                                    <?= htmlspecialchars($tutor['tutor_phone'] ?? '') ?><br>
                                    <?= htmlspecialchars($profile['country'] ?? '') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Validity</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <strong id="inv-pkg-name">—</strong><br>
                                        <span style="font-size:.75rem;color:var(--muted);">Tutor Subscription
                                            Plan</span>
                                    </td>
                                    <td id="inv-duration">—</td>
                                    <td id="inv-validity">—</td>
                                    <td id="inv-amount">—</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align:right;color:var(--muted);font-size:.8rem;">
                                        Subtotal</td>
                                    <td id="inv-subtotal">—</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="text-align:right;color:var(--muted);font-size:.8rem;">Tax
                                        (0%)</td>
                                    <td>₹0</td>
                                </tr>
                                <tr class="total-row">
                                    <td colspan="4" style="text-align:right;">Total Paid</td>
                                    <td id="inv-total">—</td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Features Included -->
                        <div class="inv-features">
                            <div class="inv-features-title"><i class="fa-solid fa-list-check me-1"></i> Features
                                Included</div>
                            <div class="feat-grid" id="inv-features-grid">
                                <!-- filled by JS -->
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="inv-footer">
                            <div>
                                <div class="f-brand"><?php echo $company_name ?></div>
                                <div class="f-note">This is a computer-generated invoice. No signature required.</div>
                            </div>
                            <div class="f-note" style="text-align:right;">
                                For queries: <?php echo $company_email ?>
                            </div>
                        </div>

                        <div class="inv-thank">
                            🎓 Thank you for being a valued <?php echo $company_name ?> tutor! Happy Teaching!
                        </div>

                    </div><!-- /invoice-box -->
                </div>
            </div>
        </div>
    </div>

    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
    <?php include 'includes/script.php'; ?>

    <script>
        // ── Payment data from PHP ──────────────────────────────
        const payments = <?= json_encode(array_column(
            array_map(function ($p) use ($conn) {
                        // Fetch package features for each payment
                        $pkg = mysqli_fetch_assoc(mysqli_query(
                            $conn,
                            "SELECT * FROM package_tbl WHERE package_id = {$p['package_id']}"
                        ));
                        $p['features'] = [
                            ['label' => 'Add Courses', 'val' => $pkg['can_add_courses'] ?? 0, 'max' => $pkg['max_course'] ?? 0],
                            ['label' => 'Upload Videos', 'val' => $pkg['can_add_videos'] ?? 0, 'max' => $pkg['max_video_upload'] ?? 0],
                            ['label' => 'Create Quizzes', 'val' => $pkg['can_add_quiz'] ?? 0, 'max' => 0],
                            ['label' => 'Add Games', 'val' => $pkg['can_add_games'] ?? 0, 'max' => 0],
                            ['label' => 'Assignments', 'val' => $pkg['can_add_assignments'] ?? 0, 'max' => 0],
                        ];
                        return $p;
                    }, $all_payments),
            null,
            'payment_id'
        ), JSON_UNESCAPED_UNICODE) ?>;

        function viewInvoice(id) {
            const p = payments[id];
            if (!p) return;

            const invNo = 'CDZ-' + String(id).padStart(6, '0');
            document.getElementById('modal-inv-num').textContent = '— ' + invNo;
            document.getElementById('inv-number').textContent = invNo;

            const dt = p.payment_date || p.created_at;
            document.getElementById('inv-date').textContent = formatDate(dt);
            document.getElementById('inv-rzp').textContent = p.razorpay_payment_id || '—';

            document.getElementById('inv-pkg-name').textContent = p.package_name + ' Plan';
            document.getElementById('inv-duration').textContent = p.valid_months + ' Month' + (p.valid_months > 1 ? 's' : '');

            const start = formatDateShort(p.start_date);
            const end = formatDateShort(p.end_date);
            document.getElementById('inv-validity').textContent = start + ' – ' + end;

            const amt = '₹' + Number(p.amount).toLocaleString('en-IN');
            document.getElementById('inv-amount').textContent = amt;
            document.getElementById('inv-subtotal').textContent = amt;
            document.getElementById('inv-total').textContent = amt;

            // Features
            const grid = document.getElementById('inv-features-grid');
            grid.innerHTML = '';
            p.features.forEach(f => {
                const enabled = parseInt(f.val) === 1;
                const color = enabled ? '#16a34a' : '#d1d5db';
                const ico = enabled ? 'fa-circle-check' : 'fa-circle-xmark';
                const extra = (enabled && f.max > 0) ? ` (up to ${f.max})` : '';
                grid.innerHTML += `
            <div class="feat-item" style="${enabled ? '' : 'color:#9ca3af;'}">
                <i class="fa-solid ${ico}" style="color:${color};"></i>
                ${f.label}${extra}
            </div>`;
            });

            new bootstrap.Modal(document.getElementById('invoiceModal')).show();
        }

        function formatDate(str) {
            if (!str) return '—';
            const d = new Date(str);
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
        function formatDateShort(str) {
            if (!str) return '—';
            const d = new Date(str);
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // ── Download PDF ────────────────────────────────────────
        function downloadPDF() {
            const el = document.getElementById('invoice-box');
            const num = document.getElementById('inv-number').textContent || 'invoice';

            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'Codezy_' + num + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            const btn = document.querySelector('.btn-download');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating…';
            btn.disabled = true;

            html2pdf().set(opt).from(el).save().then(() => {
                btn.innerHTML = '<i class="fa-solid fa-file-arrow-down"></i> Download PDF';
                btn.disabled = false;
            });
        }

        // ── Print ───────────────────────────────────────────────
        function printInvoice() {
            window.print();
        }

        // ── Auto-open if ?id= is in URL ─────────────────────────
        <?php if ($payment_id && $payment): ?>
            window.addEventListener('DOMContentLoaded', () => {
                viewInvoice(<?= $payment_id ?>);
            });
        <?php endif; ?>
    </script>
</body>

</html>