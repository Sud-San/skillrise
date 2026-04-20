<?php
require_once('includes/init.php');
include 'connection.php';

$tutor_id = (int) $_SESSION['tutor_id'];

// ── Fetch all active packages ──────────────────────────
$pkgs_res = mysqli_query($conn, "SELECT * FROM package_tbl WHERE package_status = 1 ORDER BY price ASC");
$packages = [];
while ($p = mysqli_fetch_assoc($pkgs_res))
    $packages[] = $p;

// ── Fetch tutor's current active package ──────────────
$cur_res = mysqli_query($conn, "
    SELECT tp.*, p.package_name, p.price
    FROM tutor_package_tbl tp
    JOIN package_tbl p ON tp.package_id = p.package_id
    WHERE tp.tutor_id = $tutor_id AND tp.payment_status = 1
    ORDER BY tp.created_at DESC LIMIT 1
");
$current = mysqli_fetch_assoc($cur_res);

$pkg_days_left = 0;
if ($current) {
    $end = new DateTime($current['end_date']);
    $now = new DateTime();
    $diff = $now->diff($end);
    $pkg_days_left = ($diff->invert === 0) ? $diff->days : 0;
}

// ── Fetch tutor info ───────────────────────────────────
$t_res = mysqli_query($conn, "SELECT * FROM tutor_tbl WHERE tutor_id = $tutor_id");
$tutor = mysqli_fetch_assoc($t_res);

// Package tier visuals
$tier_styles = [
    0 => ['color' => '#6b7280', 'bg' => '#f9fafb', 'border' => '#e5e7eb', 'icon' => 'fa-seedling'],
    1 => ['color' => '#16a34a', 'bg' => '#f0fdf4', 'border' => '#86efac', 'icon' => 'fa-bolt'],
    2 => ['color' => '#7c3aed', 'bg' => '#faf5ff', 'border' => '#c4b5fd', 'icon' => 'fa-crown'],
];

function feature_row($label, $val, $max = null)
{
    $enabled = (bool) $val;
    $color = $enabled ? '#16a34a' : '#d1d5db';
    $ico = $enabled ? 'fa-circle-check' : 'fa-circle-xmark';
    $txt_cls = $enabled ? '' : 'style="color:#9ca3af;"';
    $extra = ($enabled && $max) ? " <span style='font-size:.72rem;color:#6b7280;'>(up to $max)</span>" : '';
    return "
    <div style='display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6;'>
        <i class='fa-solid $ico' style='color:$color;font-size:.85rem;width:16px;'></i>
        <span style='font-size:.85rem;font-weight:500;' $txt_cls>$label$extra</span>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php'; ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link rel="stylesheet" href="assets/css/packages.css">
</head>

<body class="app">
    <?php include 'includes/header.php'; ?>

    <!-- Processing overlay -->
    <div id="pay-processing">
        <div class="pay-spinner"></div>
        <div style="color:#fff;font-family:'Sora',sans-serif;font-weight:600;font-size:1rem;">Processing Payment…</div>
    </div>

    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <!-- Page header -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">Packages &amp; Plans</h1>
                        <div class="page-sub">Choose a plan that fits your teaching goals</div>
                    </div>
                    <a href="account.php" class="d-inline-flex align-items-center gap-2"
                        style="font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Account
                    </a>
                </div>

                <!-- Flash from URL -->
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert-success-custom mb-4">
                        <i class="fa-solid fa-circle-check fa-lg"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert-error-custom mb-4">
                        <i class="fa-solid fa-circle-xmark fa-lg"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <!-- ── Current Active Plan ── -->
                <?php if ($current && $pkg_days_left > 0):
                    $total_days = (new DateTime($current['start_date']))->diff(new DateTime($current['end_date']))->days;
                    $used_days = max(0, $total_days - $pkg_days_left);
                    $pct = $total_days > 0 ? min(100, round($used_days / $total_days * 100)) : 0;
                    ?>
                    <div class="current-plan-card mb-4">
                        <div style="flex:1;position:relative;z-index:1;">
                            <div class="cp-label"><i class="fa-solid fa-circle-dot me-1"></i> Currently Active</div>
                            <div class="cp-name"><?= htmlspecialchars($current['package_name']) ?></div>
                            <div class="cp-dates">
                                <i class="fa-solid fa-calendar-days me-1"></i>
                                <?= date('d M Y', strtotime($current['start_date'])) ?>
                                &nbsp;→&nbsp;
                                <?= date('d M Y', strtotime($current['end_date'])) ?>
                            </div>
                            <div class="cp-bar-wrap">
                                <div class="cp-bar" style="width:<?= $pct ?>%"></div>
                            </div>
                            <div style="font-size:.72rem;opacity:.75;margin-top:5px;"><?= $pct ?>% of plan used</div>
                            <div class="prepaid-badge">
                                <i class="fa-solid fa-gift"></i>
                                <?= $pkg_days_left ?> days will carry over if you upgrade
                            </div>
                        </div>
                        <div class="cp-days" style="position:relative;z-index:1;">
                            <div class="d-num"><?= $pkg_days_left ?></div>
                            <div class="d-lbl">Days Left</div>
                        </div>
                    </div>
                <?php elseif ($current && $pkg_days_left === 0): ?>
                    <div class="alert-error-custom mb-4">
                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                        Your <strong><?= htmlspecialchars($current['package_name']) ?></strong> plan has expired. Renew
                        below to keep teaching!
                    </div>
                <?php endif; ?>

                <!-- ── Pricing Cards ── -->
                <div class="pricing-grid mb-5">
                    <?php foreach ($packages as $i => $pkg):
                        $style = $tier_styles[min($i, 2)];
                        $is_current = ($current && $current['package_id'] == $pkg['package_id'] && $pkg_days_left > 0);
                        $is_popular = ($i === 1);
                        $card_cls = $is_current ? 'pkg-card active-pkg' : ($is_popular ? 'pkg-card highlighted' : 'pkg-card');

                        // Preview end date with prepaid days
                        $preview_end = date('d M Y', strtotime("+{$pkg['valid_months']} months +{$pkg_days_left} days"));
                        ?>
                        <div class="<?= $card_cls ?>">

                            <?php if ($is_current): ?>
                                <div class="pkg-badge active-badge"><i class="fa-solid fa-circle-check me-1"></i> Your Plan
                                </div>
                            <?php elseif ($is_popular): ?>
                                <div class="pkg-badge"><i class="fa-solid fa-bolt me-1"></i> Most Popular</div>
                            <?php endif; ?>

                            <div class="pkg-icon"
                                style="background:<?= $style['bg'] ?>;border:1px solid <?= $style['border'] ?>;">
                                <i class="fa-solid <?= $style['icon'] ?>" style="color:<?= $style['color'] ?>;"></i>
                            </div>

                            <div class="pkg-name-title"><?= htmlspecialchars($pkg['package_name']) ?></div>
                            <div class="pkg-validity">
                                <i class="fa-solid fa-clock me-1"></i>
                                <?= $pkg['valid_months'] ?> month
                                <!-- <?= $pkg['valid_months'] > 1 ? 's' : '' ?> -->
                                <?php if (!$is_current && $pkg_days_left > 0): ?>
                                    <!-- + <?= $pkg_days_left ?> prepaid days -->
                                <?php endif; ?>
                            </div>

                            <div class="pkg-price">
                                <sup>₹</sup><?= number_format($pkg['price'], 0) ?>
                                <span>/ plan</span>
                            </div>

                            <?php if (!$is_current && $pkg_days_left > 0): ?>
                                <!-- <div class="prepaid-info-card">
                                    <i class="fa-solid fa-gift"></i>
                                    Expires <?= $preview_end ?> (includes <?= $pkg_days_left ?> carry-over days)
                                </div> -->
                            <?php endif; ?>

                            <hr class="pkg-divider">

                            <div>
                                <?= feature_row('Add Courses', $pkg['can_add_courses'], $pkg['max_course'] ?: null) ?>
                                <?= feature_row('Upload Videos', $pkg['can_add_videos'], $pkg['max_video_upload'] ?: null) ?>
                                <!-- f -->
                            </div>

                            <?php if ($is_current): ?>
                                <button class="btn-buy btn-buy-active">
                                    <i class="fa-solid fa-circle-check"></i> Current Plan
                                </button>
                            <?php else: ?>
                                <button class="btn-buy <?= $is_popular ? 'btn-buy-primary' : 'btn-buy-outline' ?>" onclick="openPayModal(
                                    <?= $pkg['package_id'] ?>,
                                    '<?= htmlspecialchars(addslashes($pkg['package_name'])) ?>',
                                    <?= $pkg['price'] ?>,
                                    <?= $pkg['valid_months'] ?>,
                                    <?= $pkg_days_left ?>,
                                    '<?= $preview_end ?>'
                                )">
                                    <i class="fa-solid fa-indian-rupee-sign"></i>
                                    <?= $current ? 'Switch / Renew' : 'Buy Now' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($packages)): ?>
                        <div class="text-center py-5" style="color:var(--muted);grid-column:1/-1;">
                            <i class="fa-solid fa-box-open fa-3x mb-3 d-block" style="color:#d1d5db;"></i>
                            No packages available at the moment.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ── Payment History (from tutor_payment_tbl) ── -->
                <div
                    style="background:#fff;border-radius:16px;border:1px solid rgba(0,0,0,.04);box-shadow:var(--card-shadow);overflow:hidden;margin-bottom:30px;">
                    <div style="padding:20px 24px 16px;">
                        <div
                            style="font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--g600);padding-bottom:8px;border-bottom:2px solid var(--g100);display:flex;align-items:center;gap:7px;">
                            <span
                                style="width:6px;height:6px;background:var(--g500);border-radius:50%;display:inline-block;"></span>
                            Payment History
                        </div>
                    </div>

                    <?php
                    $hist_res = mysqli_query($conn, "
                    SELECT tp.*, p.package_name, p.valid_months
                    FROM  tutor_package_tbl tp
                    JOIN package_tbl p ON tp.package_id = p.package_id
                    WHERE tp.tutor_id = $tutor_id
                    ORDER BY tp.created_at DESC
                    LIMIT 15
                ");
                    $history = [];
                    while ($h = mysqli_fetch_assoc($hist_res))
                        $history[] = $h;
                    ?>

                    <?php if (empty($history)): ?>
                        <div class="text-center py-4" style="color:var(--muted);font-size:.88rem;padding-bottom:24px;">
                            <i class="fa-solid fa-receipt fa-2x d-block mb-2" style="color:#d1d5db;"></i>
                            No payment history yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="history-table w-100" style="border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Package</th>
                                        <th>Razorpay ID</th>
                                        <th>Amount</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Payment Date & Time</th>
                                        <th>Invoice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $idx => $h):
                                        $paid = ($h['payment_status'] == 1);
                                        $chip_bg = $paid ? '#dcfce7' : '#fee2e2';
                                        $chip_clr = $paid ? '#15803d' : '#b91c1c';
                                        $chip_lbl = $paid ? 'Paid' : 'Failed';
                                        ?>
                                        <tr>
                                            <td style="color:var(--muted);"><?= $idx + 1 ?></td>
                                            <td>
                                                <span
                                                    style="font-weight:600;color:var(--ink);"><?= htmlspecialchars($h['package_name']) ?></span>
                                                <span
                                                    style="font-size:.75rem;color:var(--muted);display:block;"><?= $h['valid_months'] ?>
                                                    months</span>
                                            </td>
                                            <td style="font-size:.78rem;color:var(--muted);font-family:monospace;">
                                                <?= $h['razorpay_id'] ? htmlspecialchars($h['razorpay_id']) : '—' ?>
                                            </td>
                                            <td style="font-weight:700;color:var(--g600);font-family:'Sora',sans-serif;">
                                                ₹<?= number_format($h['amount_paid'], 0) ?>
                                            </td>
                                            <td><?= date('d M Y', strtotime($h['start_date'])) ?></td>
                                            <td><?= date('d M Y', strtotime($h['end_date'])) ?></td>
                                            <td style="font-size:.8rem;">
                                                <?= $h['created_at'] ? date('d M Y, h:i A', strtotime($h['created_at'])) : '—' ?>
                                            </td>
                                            <td>
                                                <a href="tutor-receipt.php?id=<?= $h['purchase_id'] ?>"
                                                    class="btn btn-sm btn-primary">View Invoice</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Payment Modal ── -->
    <div class="modal fade modal-pay" id="payModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5
                        style="font-family:'Sora',sans-serif;font-weight:700;font-size:1.05rem;color:var(--ink);margin:0;">
                        <i class="fa-solid fa-indian-rupee-sign me-2" style="color:var(--g600);"></i>
                        Complete Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:.88rem;color:var(--muted);margin:0;">Review your plan before proceeding to
                        payment:</p>

                    <div class="pay-preview">
                        <div class="pp-name" id="pay-pkg-name">—</div>
                        <div class="pp-detail" id="pay-pkg-detail">—</div>
                        <div class="pp-price" id="pay-pkg-price">—</div>
                    </div>

                    <!-- Prepaid row — shown only if days > 0 -->
                    <div class="prepaid-row" id="prepaid-row" style="display:none;">
                        <i class="fa-solid fa-gift fa-lg"></i>
                        <div>
                            <span id="prepaid-days-text">0 carry-over days</span> added from your current plan.<br>
                            <span style="font-weight:500;font-size:.78rem;">New expiry: <strong
                                    id="prepaid-end-date">—</strong></span>
                        </div>
                    </div>

                    <?php if ($current && $pkg_days_left > 0): ?>
                        <div
                            style="margin-top:10px;padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:.8rem;color:#92400e;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Your current <strong><?= htmlspecialchars($current['package_name']) ?></strong> plan will be
                            deactivated, but remaining days carry over.
                        </div>
                    <?php endif; ?>

                    <!-- Razorpay secure note -->
                    <div
                        style="margin-top:12px;display:flex;align-items:center;gap:8px;font-size:.75rem;color:var(--muted);">
                        <i class="fa-solid fa-lock" style="color:var(--g600);"></i>
                        Secured by Razorpay · UPI, Cards, Net Banking accepted
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-pay-now" id="btn-pay-now" onclick="startPayment()">
                        <i class="fa-solid fa-bolt"></i> Pay Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
    <?php include 'includes/script.php'; ?>

    <script>
        let _selectedPkg = {};

        function openPayModal(pkgId, pkgName, pkgPrice, pkgMonths, prepaidDays, previewEnd) {
            _selectedPkg = { pkgId, pkgName, pkgPrice, pkgMonths, prepaidDays, previewEnd };

            document.getElementById('pay-pkg-name').textContent = pkgName;
            document.getElementById('pay-pkg-detail').textContent =
                pkgMonths + ' month' + (pkgMonths > 1 ? 's' : '') +
                (prepaidDays > 0 ? ' + ' + prepaidDays + ' carry-over days' : '');
            document.getElementById('pay-pkg-price').textContent = '₹' + Number(pkgPrice).toLocaleString('en-IN');

            const prepaidRow = document.getElementById('prepaid-row');
            if (prepaidDays > 0) {
                document.getElementById('prepaid-days-text').textContent = prepaidDays + ' carry-over days';
                document.getElementById('prepaid-end-date').textContent = previewEnd;
                prepaidRow.style.display = 'flex';
            } else {
                prepaidRow.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('payModal')).show();
        }

        function startPayment() {
            document.getElementById('btn-pay-now').disabled = true;
            document.getElementById('btn-pay-now').innerHTML = '<span class="pay-spinner" style="width:18px;height:18px;border-width:2px;"></span> Creating order…';

            // Step 1: Create Razorpay order
            fetch('razorpay_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'package_id=' + _selectedPkg.pkgId
            })
                .then(r => r.json())
                .then(data => {
                    if (data.error) { showError(data.error); return; }

                    // Step 2: Open Razorpay checkout
                    const options = {
                        key: '<?= RAZORPAY_KEY_ID ?>',
                        amount: data.amount,
                        currency: data.currency,
                        name: '<?php echo $company_name; ?>',
                        description: _selectedPkg.pkgName + ' Plan',
                        order_id: data.order_id,
                        prefill: {
                            name: data.tutor_name,
                            email: data.tutor_email,
                            contact: data.tutor_phone,
                        },
                        theme: { color: '#16a34a' },

                        handler: function (response) {
                            // Payment captured — verify on server
                            document.getElementById('payModal').querySelector('.btn-close').click();
                            document.getElementById('pay-processing').classList.add('show');

                            fetch('razorpay_verify.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id ?? data.order_id,
                                    razorpay_signature: response.razorpay_signature,
                                    payment_row_id: data.payment_row_id,
                                    package_id: _selectedPkg.pkgId,
                                    start_date: data.start_date,
                                    end_date: data.end_date,
                                })
                            })
                                .then(r => r.json())
                                .then(res => {
                                    document.getElementById('pay-processing').classList.remove('show');
                                    if (res.success) {
                                        window.location.href = 'packages.php?success=' +
                                            encodeURIComponent('🎉 ' + _selectedPkg.pkgName + ' activated! Valid until ' + res.end_date);
                                    } else {
                                        window.location.href = 'packages.php?error=' +
                                            encodeURIComponent('Payment received but activation failed: ' + res.error);
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('pay-processing').classList.remove('show');
                                    window.location.href = 'packages.php?error=' + encodeURIComponent('Verification failed. Contact support.');
                                });
                        },

                        modal: {
                            ondismiss: function () {
                                // Mark as failed if dismissed
                                fetch('razorpay_verify.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: new URLSearchParams({
                                        razorpay_payment_id: 'dismissed',
                                        razorpay_order_id: data.order_id,
                                        payment_row_id: data.payment_row_id,
                                        package_id: _selectedPkg.pkgId,
                                        start_date: data.start_date,
                                        end_date: data.end_date,
                                    })
                                });
                                resetPayBtn();
                            }
                        }
                    };

                    const rzp = new Razorpay(options);
                    rzp.open();
                    bootstrap.Modal.getInstance(document.getElementById('payModal'))?.hide();
                })
                .catch(() => showError('Network error. Please try again.'));
        }

        function showError(msg) {
            resetPayBtn();
            alert('Error: ' + msg);
        }

        function resetPayBtn() {
            const btn = document.getElementById('btn-pay-now');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-bolt"></i> Pay Now';
        }
    </script>
</body>

</html>