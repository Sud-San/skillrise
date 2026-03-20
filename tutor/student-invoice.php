<?php
require_once('includes/init.php');
include 'connection.php';

// ── Fetch specific enrollment or list all ──────────────
$enrollment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($enrollment_id) {
    $pay_res = mysqli_query($conn, "
        SELECT
            e.enrollment_id, e.amount, e.status, e.enrolled_at,
            e.completed_at, e.certificate_issued,
            u.user_name, u.user_email, u.profile_pic AS user_profile_pic,
            t.tutor_name, t.tutor_email,
            c.course_title
        FROM enrollments_tbl e
        LEFT JOIN user_tbl u   ON e.user_id   = u.user_id
        LEFT JOIN tutor_tbl t  ON e.tutor_id  = t.tutor_id
        LEFT JOIN course_tbl c ON e.course_id = c.course_id
        WHERE e.enrollment_id = $enrollment_id
        LIMIT 1
    ");
    $enrollment = mysqli_fetch_assoc($pay_res);
    if (!$enrollment) {
        header('Location: student-invoice.php'); exit;
    }
} else {
    $enrollment = null;
}
$logged_tutor_id = $_SESSION['tutor_id'];
// ── Fetch all enrollments ──────────────────────────────
$all_res = mysqli_query($conn, "
    SELECT
        e.enrollment_id, e.amount, e.status, e.enrolled_at,
        e.certificate_issued,
        u.user_name, u.profile_pic AS user_profile_pic,
        t.tutor_name,
        c.course_title
    FROM enrollments_tbl e
    LEFT JOIN user_tbl u    ON e.user_id  = u.user_id
    LEFT JOIN tutor_tbl t   ON e.tutor_id = t.tutor_id
    LEFT JOIN course_tbl c  ON e.course_id = c.course_id
	WHERE e.tutor_id = $logged_tutor_id
    ORDER BY e.enrollment_id DESC
");
$all_enrollments = [];
while ($r = mysqli_fetch_assoc($all_res)) $all_enrollments[] = $r;

function inv_no($id) {
    return 'CDZ-ENR-' . str_pad($id, 5, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/headtag.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Mulish:wght@400;500;600;700&display=swap');

        :root {
            --g600:#16a34a; --g500:#22c55e;
            --g100:#dcfce7; --g50:#f0fdf4;
            --ink:#111827; --ink2:#374151;
            --muted:#6b7280; --border:#e5e7eb;
            --card-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
        }

        body.app, .app-wrapper { font-family:'Mulish',sans-serif !important; background:#f9fafb; }
        .page-title { font-family:'Sora',sans-serif; font-size:1.4rem; font-weight:700; color:var(--ink); letter-spacing:-.4px; margin:0; }
        .page-sub   { font-size:.85rem; color:var(--muted); margin-top:3px; }

        /* ── Stats row ── */
        .stat-chips { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:24px; }
        .stat-chip {
            background:#fff; border:1px solid var(--border); border-radius:12px;
            padding:14px 20px; flex:1; min-width:120px;
            box-shadow:var(--card-shadow);
        }
        .stat-chip .sc-val { font-family:'Sora',sans-serif; font-size:1.4rem; font-weight:800; color:var(--g600); }
        .stat-chip .sc-lbl { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin-top:2px; }

        /* ── Invoice list card ── */
        .inv-list-card {
            background:#fff; border-radius:16px;
            border:1px solid rgba(0,0,0,.05);
            box-shadow:var(--card-shadow); overflow:hidden;
        }
        .inv-list-header {
            padding:18px 24px; border-bottom:1px solid var(--border);
            font-size:.68rem; font-weight:800; letter-spacing:.12em;
            text-transform:uppercase; color:var(--g600);
            display:flex; align-items:center; justify-content:space-between;
        }
        .inv-list-header-left { display:flex; align-items:center; gap:7px; }
        .inv-list-header-left::before {
            content:''; width:6px; height:6px;
            background:var(--g500); border-radius:50%;
        }

        /* ── Search bar ── */
        .inv-search {
            padding:14px 24px; border-bottom:1px solid var(--border);
            background:#fafafa;
        }
        .inv-search input {
            width:100%; max-width:340px; padding:8px 14px;
            border:1.5px solid var(--border); border-radius:8px;
            font-size:.85rem; font-family:'Mulish',sans-serif;
            color:var(--ink); outline:none;
            transition:border-color .15s;
        }
        .inv-search input:focus { border-color:var(--g500); }

        /* ── Invoice rows ── */
        .inv-row {
            display:flex; align-items:center;
            padding:14px 24px; border-bottom:1px solid var(--border);
            gap:14px; flex-wrap:wrap; transition:background .12s;
        }
        .inv-row:last-child  { border-bottom:none; }
        .inv-row:hover       { background:var(--g50); }
        .inv-avatar {
            width:42px; height:42px; border-radius:50%;
            object-fit:cover; border:2px solid var(--g100);
            flex-shrink:0;
        }
        .inv-row-info { flex:1; min-width:0; }
        .inv-num      { font-family:'Sora',sans-serif; font-size:.78rem; font-weight:700; color:var(--g600); }
        .inv-student  { font-size:.9rem; font-weight:700; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .inv-course   { font-size:.75rem; color:var(--muted); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .inv-date     { font-size:.72rem; color:var(--muted); white-space:nowrap; }
        .inv-amt      { font-family:'Sora',sans-serif; font-size:.95rem; font-weight:800; color:var(--ink); white-space:nowrap; }
        .inv-status-chip {
            display:inline-flex; align-items:center; gap:5px;
            font-size:.7rem; font-weight:700; padding:3px 10px; border-radius:99px;
            white-space:nowrap;
        }
        .chip-active   { background:var(--g100); color:#15803d; }
        .chip-inactive { background:#fee2e2; color:#b91c1c; }
        .chip-cert     { background:#ede9fe; color:#7c3aed; }

        .btn-view-inv {
            background:var(--g600); color:#fff; border:none;
            border-radius:8px; padding:7px 14px;
            font-size:.78rem; font-weight:700;
            font-family:'Mulish',sans-serif;
            display:inline-flex; align-items:center; gap:6px;
            transition:background .15s; cursor:pointer; white-space:nowrap;
        }
        .btn-view-inv:hover { background:#15803d; }

        /* ── Invoice Modal ── */
        .modal-invoice .modal-content {
            border-radius:18px; border:none;
            box-shadow:0 20px 60px rgba(0,0,0,.15);
            max-height:92vh; overflow-y:auto;
        }
        .modal-invoice .modal-header { border:none; padding:20px 24px 0; }
        .modal-invoice .modal-body   { padding:16px 24px 24px; }

        /* ── Action bar ── */
        .inv-action-bar {
            display:flex; gap:10px; justify-content:flex-end;
            margin-bottom:16px; flex-wrap:wrap;
        }
        .btn-download {
            background:var(--g600); color:#fff; border:none;
            border-radius:9px; padding:9px 20px;
            font-size:.85rem; font-weight:700;
            font-family:'Mulish',sans-serif; cursor:pointer;
            display:inline-flex; align-items:center; gap:7px;
            transition:background .15s;
        }
        .btn-download:hover { background:#15803d; }
        .btn-print {
            background:transparent; color:var(--muted);
            border:1.5px solid var(--border); border-radius:9px;
            padding:9px 20px; font-size:.85rem; font-weight:700;
            font-family:'Mulish',sans-serif; cursor:pointer;
            display:inline-flex; align-items:center; gap:7px;
            transition:all .15s;
        }
        .btn-print:hover { background:#f3f4f6; }

        /* ═══════════════════════════════════════════
           THE INVOICE ITSELF
        ═══════════════════════════════════════════ */
        #invoice-box {
            background:#fff; padding:40px;
            font-family:'Mulish',sans-serif;
            max-width:700px; margin:0 auto;
        }

        /* Header */
        .inv-brand { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px; }
        .inv-brand .brand-name { font-family:'Sora',sans-serif; font-size:1.6rem; font-weight:800; color:var(--g600); letter-spacing:-.5px; }
        .inv-brand .brand-tag  { font-size:.72rem; color:var(--muted); font-weight:600; letter-spacing:.08em; text-transform:uppercase; margin-top:2px; }
        .inv-brand .brand-contact { font-size:.72rem; color:var(--muted); margin-top:4px; }
        .inv-title-badge {
            background:var(--g600); color:#fff;
            font-family:'Sora',sans-serif; font-size:1.1rem; font-weight:700;
            padding:6px 20px; border-radius:8px; letter-spacing:.04em;
        }

        /* Meta grid */
        .inv-meta-grid {
            display:grid; grid-template-columns:1fr 1fr;
            border:1px solid var(--border); border-radius:12px;
            overflow:hidden; margin-bottom:24px;
        }
        .inv-meta-cell {
            padding:13px 16px; border-right:1px solid var(--border);
            border-bottom:1px solid var(--border);
        }
        .inv-meta-cell:nth-child(even)      { border-right:none; }
        .inv-meta-cell:nth-last-child(-n+2) { border-bottom:none; }
        .inv-meta-label { font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:3px; }
        .inv-meta-value { font-size:.85rem; font-weight:600; color:var(--ink); }

        /* Parties */
        .inv-parties { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px; }
        .inv-party-box { background:#f9fafb; border:1px solid var(--border); border-radius:10px; padding:14px 16px; }
        .inv-party-box.from { border-left:4px solid var(--g600); }
        .inv-party-box.to   { border-left:4px solid #6b7280; }
        .party-label { font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:6px; }
        .party-name  { font-family:'Sora',sans-serif; font-size:.92rem; font-weight:700; color:var(--ink); }
        .party-info  { font-size:.76rem; color:var(--ink2); margin-top:3px; line-height:1.55; }

        /* Course detail card */
        .course-card {
            background:var(--g50); border:1px solid var(--g100);
            border-radius:10px; padding:16px; margin-bottom:24px;
            display:flex; gap:14px; align-items:flex-start;
        }
        .course-card .cc-icon {
            width:44px; height:44px; flex-shrink:0;
            background:var(--g600); border-radius:10px;
            display:flex; align-items:center; justify-content:center;
        }
        .course-card .cc-icon i { color:#fff; font-size:1rem; }
        .course-card .cc-title  { font-family:'Sora',sans-serif; font-size:.95rem; font-weight:700; color:var(--ink); }
        .course-card .cc-tutor  { font-size:.78rem; color:var(--muted); margin-top:3px; }
        .course-card .cc-cat    { font-size:.72rem; background:var(--g100); color:#15803d; padding:2px 10px; border-radius:99px; font-weight:700; display:inline-block; margin-top:5px; }

        /* Items table */
        .inv-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        .inv-table thead tr { background:var(--g600); }
        .inv-table thead th {
            padding:10px 14px; font-size:.74rem; color:#fff;
            font-weight:700; text-align:left; letter-spacing:.04em;
        }
        .inv-table thead th:last-child { text-align:right; }
        .inv-table tbody td {
            padding:12px 14px; font-size:.84rem;
            color:var(--ink2); border-bottom:1px solid var(--border);
        }
        .inv-table tbody td:last-child { text-align:right; font-weight:700; color:var(--ink); }
        .inv-table tfoot td { padding:9px 14px; font-size:.84rem; font-weight:700; }
        .inv-table tfoot .total-row td {
            background:var(--g50); border-top:2px solid var(--g100);
            font-family:'Sora',sans-serif; font-size:.95rem; color:var(--g600);
        }
        .inv-table tfoot td:last-child { text-align:right; }

        /* Certificate status */
        .cert-row {
            display:flex; align-items:center; gap:10px;
            padding:12px 16px; border-radius:10px; margin-bottom:20px;
        }
        .cert-row.issued    { background:#ede9fe; border:1px solid #c4b5fd; }
        .cert-row.pending   { background:#f3f4f6; border:1px solid var(--border); }
        .cert-row i         { font-size:1.1rem; }
        .cert-row .cert-txt { font-size:.84rem; font-weight:700; }

        /* Footer */
        .inv-footer {
            border-top:2px solid var(--g100); padding-top:14px;
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:8px; margin-top:4px;
        }
        .inv-footer .f-brand { font-family:'Sora',sans-serif; font-size:.8rem; font-weight:700; color:var(--g600); }
        .inv-footer .f-note  { font-size:.7rem; color:var(--muted); }
        .inv-thank {
            text-align:center; margin-top:18px; padding:12px;
            background:linear-gradient(135deg,var(--g600),#15803d);
            border-radius:8px; color:#fff; font-size:.82rem; font-weight:600;
        }

        /* Empty state */
        .empty-inv { text-align:center; padding:40px; color:var(--muted); }
        .empty-inv i { font-size:2.5rem; color:#d1d5db; display:block; margin-bottom:10px; }

        @media print {
            body * { visibility:hidden; }
            #invoice-box, #invoice-box * { visibility:visible; }
            #invoice-box { position:absolute; left:0; top:0; width:100%; padding:20px; }
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
                    <h1 class="page-title">Income Details</h1>
                    <div class="page-sub">Course enrollment receipts — Codezy E-Learning</div>
                </div>
                <a href="user-payment.php" class="d-inline-flex align-items-center gap-2"
                   style="font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Payments
                </a>
            </div>

            <!-- Stats -->
            <?php
            $total_amt = array_sum(array_column($all_enrollments, 'amount'));
            $total_cnt = count($all_enrollments);
            $active_cnt = count(array_filter($all_enrollments, fn($r) => $r['status'] == 1));
            $cert_cnt   = count(array_filter($all_enrollments, fn($r) => $r['certificate_issued'] == 1));
            ?>
            <div class="stat-chips">
                <div class="stat-chip">
                    <div class="sc-val"><?= $total_cnt ?></div>
                    <div class="sc-lbl">Total Enrollments</div>
                </div>
                <div class="stat-chip">
                    <div class="sc-val">₹<?= number_format($total_amt, 0) ?></div>
                    <div class="sc-lbl">Total Revenue</div>
                </div>
                <div class="stat-chip">
                    <div class="sc-val"><?= $active_cnt ?></div>
                    <div class="sc-lbl">Active</div>
                </div>
                <div class="stat-chip">
                    <div class="sc-val"><?= $cert_cnt ?></div>
                    <div class="sc-lbl">Certs Issued</div>
                </div>
            </div>

            <!-- Invoice List -->
            <div class="inv-list-card">
                <div class="inv-list-header">
                    <div class="inv-list-header-left">All Student Invoices</div>
                    <span style="font-size:.75rem;color:var(--muted);font-weight:500;text-transform:none;letter-spacing:0;">
                        <?= $total_cnt ?> records
                    </span>
                </div>

                <!-- Search -->
                <div class="inv-search">
                    <input type="text" id="inv-search-input" placeholder="🔍  Search by student, course or invoice number…"
                           oninput="filterRows(this.value)">
                </div>

                <div id="inv-rows-wrap">
                <?php if (empty($all_enrollments)) : ?>
                    <div class="empty-inv">
                        <i class="fa-solid fa-receipt"></i>
                        No enrollment records found.
                    </div>
                <?php else : ?>
                    <?php foreach ($all_enrollments as $enr) :
                        $is_active = ((int)$enr['status'] === 1);
                        $cert      = ((int)$enr['certificate_issued'] === 1);
                        $inv_num   = inv_no($enr['enrollment_id']);
                        $avatar    = !empty($enr['user_profile_pic'])
                            ? 'assets/images/Student_Profile_Images/' . htmlspecialchars($enr['user_profile_pic'])
                            : 'assets/images/Student_Profile_Images/default-profile.png';
                    ?>
                    <div class="inv-row" data-search="<?= strtolower(htmlspecialchars($enr['user_name'] . ' ' . $enr['course_title'] . ' ' . $inv_num)) ?>">
                        <img src="<?= $avatar ?>" class="inv-avatar"
                             onerror="this.src='assets/images/Student_Profile_Images/default-profile.png'">

                        <div class="inv-row-info">
                            <div class="inv-num"><?= $inv_num ?></div>
                            <div class="inv-student"><?= htmlspecialchars($enr['user_name'] ?? '—') ?></div>
                            <div class="inv-course">
                                <i class="fa-solid fa-book me-1"></i><?= htmlspecialchars($enr['course_title'] ?? '—') ?>
                                &nbsp;·&nbsp;
                                <i class="fa-solid fa-chalkboard-user me-1"></i><?= htmlspecialchars($enr['tutor_name'] ?? '—') ?>
                            </div>
                        </div>

                        <div class="text-end" style="min-width:80px;">
                            <div class="inv-amt">₹<?= number_format($enr['amount'], 0) ?></div>
                            <div class="inv-date mt-1">
                                <?= date('d M Y', strtotime($enr['enrolled_at'])) ?>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-1 align-items-end">
                            <span class="inv-status-chip <?= $is_active ? 'chip-active' : 'chip-inactive' ?>">
                                <i class="fa-solid <?= $is_active ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                                <?= $is_active ? 'Active' : 'Inactive' ?>
                            </span>
                            <?php if ($cert) : ?>
                            <span class="inv-status-chip chip-cert">
                                <i class="fa-solid fa-certificate"></i> Cert Issued
                            </span>
                            <?php endif; ?>
                        </div>

                        <button class="btn-view-inv"
                                onclick="viewInvoice(<?= $enr['enrollment_id'] ?>)">
                            <i class="fa-solid fa-eye"></i> Invoice
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══ Invoice Modal ══ -->
<div class="modal fade modal-invoice" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-invoice" style="color:var(--g600);"></i>
                    <span style="font-family:'Sora',sans-serif;font-weight:700;font-size:1rem;color:var(--ink);">
                        Enrollment Invoice
                    </span>
                    <span id="modal-inv-num" style="font-size:.8rem;color:var(--muted);font-weight:600;"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="inv-action-bar">
                    <button class="btn-print" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                    <button class="btn-download" onclick="downloadPDF()">
                        <i class="fa-solid fa-file-arrow-down"></i> Download PDF
                    </button>
                </div>

                <!-- ══════════════ INVOICE DESIGN ══════════════ -->
                <div id="invoice-box">

                    <!-- Brand header -->
                    <div class="inv-brand">
                        <div>
                            <div class="brand-name">Codezy</div>
                            <div class="brand-tag">E-Learning Platform</div>
                            <div class="brand-contact">support@codezy.in &nbsp;|&nbsp; www.codezy.in</div>
                        </div>
                        <div class="inv-title-badge">INVOICE</div>
                    </div>

                    <!-- Meta -->
                    <div class="inv-meta-grid">
                        <div class="inv-meta-cell">
                            <div class="inv-meta-label">Invoice No.</div>
                            <div class="inv-meta-value" id="inv-number">—</div>
                        </div>
                        <div class="inv-meta-cell">
                            <div class="inv-meta-label">Enrollment Date</div>
                            <div class="inv-meta-value" id="inv-date">—</div>
                        </div>
                        <div class="inv-meta-cell">
                            <div class="inv-meta-label">Enrollment ID</div>
                            <div class="inv-meta-value" id="inv-enr-id" style="font-family:monospace;font-size:.8rem;">—</div>
                        </div>
                        <div class="inv-meta-cell">
                            <div class="inv-meta-label">Payment Status</div>
                            <div class="inv-meta-value" id="inv-status">—</div>
                        </div>
                    </div>

                    <!-- From / To -->
                    <div class="inv-parties">
                        <div class="inv-party-box from">
                            <div class="party-label">From</div>
                            <div class="party-name">Codezy E-Learning</div>
                            <div class="party-info">
                                Online Education Platform<br>
                                support@codezy.in<br>
                                www.codezy.in
                            </div>
                        </div>
                        <div class="inv-party-box to">
                            <div class="party-label">Bill To (Student)</div>
                            <div class="party-name" id="inv-student-name">—</div>
                            <div class="party-info" id="inv-student-info">—</div>
                        </div>
                    </div>

                    <!-- Course card -->
                    <div class="course-card">
                        <div class="cc-icon"><i class="fa-solid fa-book-open"></i></div>
                        <div>
                            <div class="cc-title" id="inv-course-title">—</div>
                            <div class="cc-tutor" id="inv-tutor">—</div>
                            <div id="inv-category"></div>
                        </div>
                    </div>

                    <!-- Items table -->
                    <table class="inv-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Enrolled On</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>
                                    <strong id="inv-item-title">—</strong><br>
                                    <span style="font-size:.75rem;color:var(--muted);">Course Enrollment</span>
                                </td>
                                <td>One-time</td>
                                <td id="inv-item-date">—</td>
                                <td id="inv-item-amt">—</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right;color:var(--muted);font-size:.78rem;">Subtotal</td>
                                <td id="inv-subtotal">—</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align:right;color:var(--muted);font-size:.78rem;">Platform Fee (0%)</td>
                                <td>₹0</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" style="text-align:right;">Total Paid</td>
                                <td id="inv-total">—</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Certificate status -->
                    <div class="cert-row" id="cert-row">
                        <i class="fa-solid fa-certificate" id="cert-icon"></i>
                        <div>
                            <div class="cert-txt" id="cert-txt">—</div>
                            <div style="font-size:.74rem;color:var(--muted);" id="cert-sub">—</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="inv-footer">
                        <div>
                            <div class="f-brand">Codezy E-Learning</div>
                            <div class="f-note">This is a computer-generated invoice. No signature required.</div>
                        </div>
                        <div class="f-note" style="text-align:right;">
                            For queries: support@codezy.in
                        </div>
                    </div>

                    <div class="inv-thank">
                        🎓 Thank you for learning with Codezy! Keep growing, keep learning.
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
// ── All enrollment data from PHP ──────────────────────
const enrollments = <?= json_encode(array_column(
    array_map(function($r) {
        return $r;
    }, $all_enrollments),
    null, 'enrollment_id'
), JSON_UNESCAPED_UNICODE) ?>;

// For detailed view (fetched on demand via fetch or pre-loaded if single)
<?php if ($enrollment) : ?>
const singleEnrollment = <?= json_encode($enrollment, JSON_UNESCAPED_UNICODE) ?>;
<?php else : ?>
const singleEnrollment = null;
<?php endif; ?>

// ── Full data cache (loaded on first view) ─────────────
const detailCache = {};

async function viewInvoice(id) {
    // If we don't have full detail, fetch it
    if (!detailCache[id]) {
        try {
            const resp = await fetch('student-invoice.php?id=' + id + '&ajax=1');
            // We use a simpler approach: embed all needed data in list query
            // For now use list data and enrich what we have
            detailCache[id] = enrollments[id] || {};
        } catch(e) {}
    }

    const d = detailCache[id] || enrollments[id];
    if (!d) return;

    const invNo = 'CDZ-ENR-' + String(id).padStart(5, '0');
    document.getElementById('modal-inv-num').textContent = '— ' + invNo;
    document.getElementById('inv-number').textContent    = invNo;
    document.getElementById('inv-enr-id').textContent    = '#' + id;
    document.getElementById('inv-date').textContent      = formatDate(d.enrolled_at);

    // Status
    const isActive = parseInt(d.status) === 1;
    document.getElementById('inv-status').innerHTML = isActive
        ? '<span style="background:#dcfce7;color:#15803d;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;"><i class="fa-solid fa-circle-check me-1"></i>Active</span>'
        : '<span style="background:#fee2e2;color:#b91c1c;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;"><i class="fa-solid fa-circle-xmark me-1"></i>Inactive</span>';

    // Student
    document.getElementById('inv-student-name').textContent = d.user_name || '—';
    document.getElementById('inv-student-info').innerHTML   = (d.user_email || '—');

    // Course
    document.getElementById('inv-course-title').textContent = d.course_title || '—';
    document.getElementById('inv-tutor').innerHTML =
        '<i class="fa-solid fa-chalkboard-user me-1"></i>Instructor: ' + (d.tutor_name || '—');
    document.getElementById('inv-category').innerHTML = '';

    // Table
    const amt = '₹' + Number(d.amount).toLocaleString('en-IN');
    document.getElementById('inv-item-title').textContent = d.course_title || '—';
    document.getElementById('inv-item-date').textContent  = formatDateShort(d.enrolled_at);
    document.getElementById('inv-item-amt').textContent   = amt;
    document.getElementById('inv-subtotal').textContent   = amt;
    document.getElementById('inv-total').textContent      = amt;

    // Certificate
    const cert    = parseInt(d.certificate_issued) === 1;
    const certRow = document.getElementById('cert-row');
    const certIco = document.getElementById('cert-icon');
    certRow.className = 'cert-row ' + (cert ? 'issued' : 'pending');
    certIco.style.color = cert ? '#7c3aed' : '#9ca3af';
    document.getElementById('cert-txt').textContent = cert
        ? 'Certificate of Completion Issued'
        : 'Certificate Pending';
    document.getElementById('cert-sub').textContent = cert
        ? 'Student has successfully completed this course.'
        : 'Certificate will be issued upon course completion.';

    new bootstrap.Modal(document.getElementById('invoiceModal')).show();
}

function formatDate(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}
function formatDateShort(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' });
}

// ── PDF Download ────────────────────────────────────────
function downloadPDF() {
    const el  = document.getElementById('invoice-box');
    const num = document.getElementById('inv-number').textContent || 'invoice';

    const opt = {
        margin:      [10, 10, 10, 10],
        filename:    'Codezy_' + num + '.pdf',
        image:       { type:'jpeg', quality:0.98 },
        html2canvas: { scale:2, useCORS:true, logging:false },
        jsPDF:       { unit:'mm', format:'a4', orientation:'portrait' }
    };

    const btn = document.querySelector('.btn-download');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating…';
    btn.disabled  = true;

    html2pdf().set(opt).from(el).save().then(() => {
        btn.innerHTML = '<i class="fa-solid fa-file-arrow-down"></i> Download PDF';
        btn.disabled  = false;
    });
}

// ── Search filter ────────────────────────────────────────
function filterRows(val) {
    const q = val.toLowerCase().trim();
    document.querySelectorAll('#inv-rows-wrap .inv-row').forEach(row => {
        const text = row.dataset.search || '';
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// ── Auto-open if ?id= in URL ─────────────────────────────
<?php if ($enrollment_id && $enrollment) : ?>
window.addEventListener('DOMContentLoaded', () => {
    // Pre-load detail into cache
    detailCache[<?= $enrollment_id ?>] = <?= json_encode($enrollment, JSON_UNESCAPED_UNICODE) ?>;
    viewInvoice(<?= $enrollment_id ?>);
});
<?php endif; ?>
</script>
</body>
</html>