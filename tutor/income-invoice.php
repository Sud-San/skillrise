<?php
require_once('includes/init.php');
include 'connection.php';

// ── Fetch specific enrollment or list all ──────────────
$enrollment_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($enrollment_id) {
    $pay_res = mysqli_query($conn, "
        SELECT
            e.enrollment_id, e.amount, e.enrollment_status, e.enrolled_at,
            e.completed_at, e.certificate_issued,
            u.user_name, u.user_email, u.profile_pic AS user_profile_pic,
            t.tutor_name, t.tutor_email,
            c.course_title
        FROM enrollments_tbl e
        LEFT JOIN user_tbl u   ON e.user_id  = u.user_id
        LEFT JOIN tutor_tbl t  ON e.tutor_id  = t.tutor_id
        LEFT JOIN course_tbl c ON e.course_id = c.course_id
        WHERE e.enrollment_id = $enrollment_id
        LIMIT 1
    ");
    $enrollment = mysqli_fetch_assoc($pay_res);
    if (!$enrollment) {
        header('Location: student-invoice.php');
        exit;
    }
} else {
    $enrollment = null;
}
$logged_tutor_id = $_SESSION['tutor_id'];
// ── Fetch all enrollments ──────────────────────────────
$all_res = mysqli_query($conn, "
    SELECT
        e.enrollment_id, e.user_id, e.course_id, e.tutor_id,
        e.amount, e.enrollment_status, e.enrolled_at,
        e.certificate_issued,
        u.user_name, u.user_email, u.profile_pic AS user_profile_pic,
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
while ($r = mysqli_fetch_assoc($all_res))
    $all_enrollments[] = $r;

function inv_no($id)
{
    return 'SRA-ENR-' . str_pad($id, 5, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/income-invoice.css">
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
                        <div class="page-sub">Course enrollment receipts —
                            <?php echo $company_name; ?> E-Learning
                        </div>
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
                $active_cnt = count(array_filter($all_enrollments, function ($r) {
                    return $r['enrollment_status'] == 1;
                }));
                $cert_cnt = count(array_filter($all_enrollments, function ($r) {
                    return $r['certificate_issued'] == 1;
                }));
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
                        <span
                            style="font-size:.75rem;color:var(--muted);font-weight:500;text-transform:none;letter-spacing:0;">
                            <?= $total_cnt ?> records
                        </span>
                    </div>

                    <!-- Search -->
                    <div class="inv-search">
                        <input type="text" id="inv-search-input"
                            placeholder="🔍  Search by student, course or invoice number…"
                            oninput="filterRows(this.value)">
                    </div>

                    <div id="inv-rows-wrap">
                        <?php if (empty($all_enrollments)): ?>
                            <div class="empty-inv">
                                <i class="fa-solid fa-receipt"></i>
                                No enrollment records found.
                            </div>
                        <?php else: ?>
                            <?php foreach ($all_enrollments as $enr):
                                $is_active = ((int) $enr['enrollment_status'] === 1);
                                $cert = ((int) $enr['certificate_issued'] === 1);
                                $inv_num = inv_no($enr['enrollment_id']);
                                $avatar = !empty($enr['user_profile_pic'])
                                    ? '../' . $user_profile_path . htmlspecialchars($enr['user_profile_pic'])
                                    : 'assets/images/Student_Profile_Images/default-profile.png';
                                ?>
                                <div class="inv-row"
                                    data-search="<?= strtolower(htmlspecialchars($enr['user_name'] . ' ' . $enr['course_title'] . ' ' . $inv_num)) ?>">
                                    <img src="<?= $avatar ?>" class="inv-avatar"
                                        onerror="this.src='assets/images/Student_Profile_Images/default-profile.png'">

                                    <div class="inv-row-info">
                                        <div class="inv-num"><?= $inv_num ?></div>
                                        <div class="inv-student"><?= htmlspecialchars($enr['user_name'] ?? '—') ?></div>
                                        <div class="inv-course">
                                            <i
                                                class="fa-solid fa-book me-1"></i><?= htmlspecialchars($enr['course_title'] ?? '—') ?>
                                            &nbsp;·&nbsp;
                                            <i
                                                class="fa-solid fa-chalkboard-user me-1"></i><?= htmlspecialchars($enr['tutor_name'] ?? '—') ?>
                                        </div>
                                    </div>

                                    <div class="text-end" style="min-width:80px;">
                                        <div class="inv-amt">₹<?= number_format($enr['amount'], 0) ?></div>
                                        <div class="inv-date mt-1">
                                            <?= date('d M Y', strtotime($enr['enrolled_at'])) ?>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 align-items-end">
                                        <!-- <span class="inv-status-chip <?= $is_active ? 'chip-active' : 'chip-inactive' ?>">
                                            <i class="fa-solid <?= $is_active ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                                            <?= $is_active ? 'Active' : 'Inactive' ?>
                                        </span> -->
                                        <?php if ($cert): ?>
                                            <span class="inv-status-chip chip-cert">
                                                <i class="fa-solid fa-certificate"></i> Certificate Issued
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <a target="_blank"
                                        href="user-invoice.php?uid=<?= $enr['user_id'] ?>&cid=<?= $enr['course_id'] ?>&tid=<?= $enr['tutor_id'] ?>">
                                        <button class="btn-view-inv">
                                            <i class="fa-solid fa-eye"></i> Invoice
                                        </button>
                                    </a>
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
                                <div class="inv-meta-value" id="inv-enr-id"
                                    style="font-family:monospace;font-size:.8rem;">—</div>
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
                                    <td colspan="4" style="text-align:right;color:var(--muted);font-size:.78rem;">
                                        Subtotal</td>
                                    <td id="inv-subtotal">—</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="text-align:right;color:var(--muted);font-size:.78rem;">
                                        Platform Fee (0%)</td>
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
            array_map(function ($r) {
            return $r;
        }, $all_enrollments),
            null,
            'enrollment_id'
        ), JSON_UNESCAPED_UNICODE) ?>;


        // For detailed view (fetched on demand via fetch or pre-loaded if single)
        const singleEnrollment = <?= $enrollment ? json_encode($enrollment, JSON_UNESCAPED_UNICODE) : 'null' ?>;

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
                } catch (e) { }
            }

            const d = detailCache[id] || enrollments[id];
            if (!d) return;

            const invNo = 'CDZ-ENR-' + String(id).padStart(5, '0');
            document.getElementById('modal-inv-num').textContent = '— ' + invNo;
            document.getElementById('inv-number').textContent = invNo;
            document.getElementById('inv-enr-id').textContent = '#' + id;
            document.getElementById('inv-date').textContent = formatDate(d.enrolled_at);

            // Status
            const isActive = parseInt(d.enrollment_status) === 1;
            document.getElementById('inv-status').innerHTML = isActive
                ? '<span style="background:#dcfce7;color:#15803d;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;"><i class="fa-solid fa-circle-check me-1"></i>Active</span>'
                : '<span style="background:#fee2e2;color:#b91c1c;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;"><i class="fa-solid fa-circle-xmark me-1"></i>Inactive</span>';

            // Student
            document.getElementById('inv-student-name').textContent = d.user_name || '—';
            document.getElementById('inv-student-info').innerHTML = (d.user_email || '—');

            // Course
            document.getElementById('inv-course-title').textContent = d.course_title || '—';
            document.getElementById('inv-tutor').innerHTML =
                '<i class="fa-solid fa-chalkboard-user me-1"></i>Instructor: ' + (d.tutor_name || '—');
            document.getElementById('inv-category').innerHTML = '';

            // Table
            const amt = '₹' + Number(d.amount).toLocaleString('en-IN');
            document.getElementById('inv-item-title').textContent = d.course_title || '—';
            document.getElementById('inv-item-date').textContent = formatDateShort(d.enrolled_at);
            document.getElementById('inv-item-amt').textContent = amt;
            document.getElementById('inv-subtotal').textContent = amt;
            document.getElementById('inv-total').textContent = amt;

            // Certificate
            const cert = parseInt(d.certificate_issued) === 1;
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
            return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
        function formatDateShort(str) {
            if (!str) return '—';
            return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // ── PDF Download ────────────────────────────────────────
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

        // ── Search filter ────────────────────────────────────────
        function filterRows(val) {
            const q = val.toLowerCase().trim();
            document.querySelectorAll('#inv-rows-wrap .inv-row').forEach(row => {
                const text = row.dataset.search || '';
                row.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        }

        // ── Auto-open if ?id= in URL ─────────────────────────────
        <?php if ($enrollment_id && $enrollment): ?>
            window.addEventListener('DOMContentLoaded', () => {
                // Pre-load detail into cache
                detailCache[<?= $enrollment_id ?>] = <?= json_encode($enrollment, JSON_UNESCAPED_UNICODE) ?>;
                viewInvoice(<?= $enrollment_id ?>);
            });
        <?php endif; ?>
    </script>
</body>

</html>