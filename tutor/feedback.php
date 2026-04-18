<?php
require_once('includes/init.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php' ?>

    <style>
        /* Professional Card Styling */
        .feedback-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            overflow: hidden;
        }

        .card-header {
            background: #ffffff;
            color: #111827;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.25rem;
            color: #111827;
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-dropdown {
            padding: 7px 14px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-dropdown:focus {
            outline: none;
            border-color: #6b7280;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
        }

        .filter-dropdown:hover {
            border-color: #9ca3af;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 600;
            margin: 0;
            color: #111827;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            margin: 0;
            font-weight: 500;
        }

        .stat-card.total .stat-value {
            color: #1f2937;
        }

        .stat-card.new .stat-value {
            color: #d97706;
        }

        .stat-card.reviewed .stat-value {
            color: #2563eb;
        }

        .stat-card.resolved .stat-value {
            color: #059669;
        }

        /* Table Enhancements */
        #datatable {
            margin: 0 !important;
            font-size: 14px;
            border-collapse: separate;
            border-spacing: 0;
        }

        #datatable thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 0.813rem;
            padding: 1rem;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            border-top: none;
        }

        #datatable tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease;
        }

        #datatable tbody tr:last-child {
            border-bottom: none;
        }

        #datatable tbody tr:hover {
            background-color: #f9fafb;
        }

        #datatable tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: #374151;
            border-top: none;
        }

        /* User Profile */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }

        .user-name {
            font-weight: 500;
            color: #111827;
            font-size: 14px;
        }

        /* Badges */
        .anonymous-badge {
            background: #f3f4f6;
            color: #6b7280;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .type-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            text-transform: capitalize;
        }

        .type-course {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #dbeafe;
        }

        .type-tutor {
            background: #fdf2f8;
            color: #9f1239;
            border: 1px solid #fce7f3;
        }

        .type-platform {
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #e0e7ff;
        }

        .type-bug {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .type-suggestion {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            text-transform: capitalize;
        }

        .status-new {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-reviewed {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .status-resolved {
            background: #d1fae5;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-closed {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }

        /* Rating Stars */
        .rating-stars {
            color: #f59e0b;
            font-size: 14px;
            letter-spacing: 2px;
        }

        .rating-stars .empty {
            color: #d1d5db;
        }

        /* Message Preview */
        .message-preview {
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            color: #6b7280;
            font-size: 14px;
        }

        .message-preview:hover {
            color: #111827;
        }

        /* Popover Styling */
        .popover {
            max-width: 400px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .popover-body {
            color: #374151;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-section {
                width: 100%;
            }

            .filter-dropdown {
                flex: 1;
                min-width: 120px;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="app">

    <?php include 'includes/header.php' ?>

    <div class="app-wrapper">
        <div class="app-content pt-2 p-md-3 p-lg-4">
            <div class="container-xl">

                <?php
                include 'connection.php';
                $tutor_id = $_SESSION['tutor_id'];
                // Fetch statistics
                $stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM feedback_tbl
                where tutor_id= $tutor_id";
                $stats_result = mysqli_query($conn, $stats_query);
                $stats = mysqli_fetch_assoc($stats_result);
                ?>

                <!-- Statistics Cards -->
                <!-- <div class="stats-container">
                    <div class="stat-card total">
                        <h3 class="stat-value"><?= $stats['total'] ?></h3>
                        <p class="stat-label">Total Feedback</p>
                    </div>
                    <div class="stat-card new">
                        <h3 class="stat-value"><?= $stats['new'] ?></h3>
                        <p class="stat-label">New</p>
                    </div>
                    <div class="stat-card reviewed">
                        <h3 class="stat-value"><?= $stats['reviewed'] ?></h3>
                        <p class="stat-label">Reviewed</p>
                    </div>
                    <div class="stat-card resolved">
                        <h3 class="stat-value"><?= $stats['resolved'] ?></h3>
                        <p class="stat-label">Resolved</p>
                    </div>
                </div> -->

                <!-- Main Feedback Table -->
                <div class="feedback-card">
                    <div class="card-header">
                        <h4>User Feedback</h4>
                        <div class="filter-section">
                            <!-- Type Filter -->
                            <select id="typeFilter" class="filter-dropdown">
                                <option value="">All Types</option>
                                <option value="course">Course</option>
                                <option value="tutor">Tutor</option>
                                <option value="platform">Platform</option>
                                <option value="bug">Bug</option>
                                <option value="suggestion">Suggestion</option>
                            </select>

                            <!-- Status Filter -->
                            <select id="statusFilter" class="filter-dropdown">
                                <option value="">All Status</option>
                                <option value="new">New</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Course</th>
                                        <th>Type</th>
                                        <th>Rating</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $query = "SELECT 
                                    f.feedback_id,
                                    f.user_id,
                                    f.tutor_id,
                                    f.course_id,
                                    f.feedback_type,
                                    f.rating,
                                    f.message,
                                    f.is_anonymous,
                                    f.status,
                                    f.created_at,
                                    f.resolved_at,
                                    u.user_name,
                                    u.profile_pic,
                                    COALESCE(c.course_title, '') as course_name,
                                    COALESCE(t.tutor_name, '') as tutor_name
                                    FROM feedback_tbl f
                                    LEFT JOIN user_tbl u ON f.user_id = u.user_id
                                    LEFT JOIN course_tbl c ON f.course_id = c.course_id
                                    LEFT JOIN tutor_tbl t ON f.tutor_id = t.tutor_id
                                    where f.tutor_id = $tutor_id
                                    ORDER BY f.feedback_id ASC";

                                    $result = mysqli_query($conn, $query);

                                    if (!$result) {
                                        echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . mysqli_error($conn) . "</td></tr>";
                                    } else {
                                        while ($row = mysqli_fetch_assoc($result)):
                                            ?>
                                            <tr data-type="<?= strtolower($row['feedback_type'] ?? ''); ?>"
                                                data-status="<?= strtolower($row['status'] ?? 'new'); ?>">

                                                <td><strong><?= $row['feedback_id']; ?></strong></td>

                                                <td>
                                                    <?php if (!$row['is_anonymous'] && $row['user_name']): ?>
                                                        <div class="user-info">
                                                            <img src="<?= $row['profile_pic'] ? "../" . $user_profile_path . $row['profile_pic'] : 'default-profile.png'; ?>"
                                                                class="user-avatar"
                                                                alt="<?= htmlspecialchars($row['user_name']); ?>">
                                                            <span
                                                                class="user-name"><?= htmlspecialchars($row['user_name']); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="anonymous-badge">
                                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor">
                                                                <path
                                                                    d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                                                            </svg>
                                                            Anonymous
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($row['course_name']); ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="type-badge type-<?= strtolower($row['feedback_type'] ?? ''); ?>">
                                                        <?= ucfirst($row['feedback_type'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?php if (!empty($row['rating']) && $row['rating'] > 0): ?>
                                                        <div class="rating-stars">
                                                            <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                echo $i <= $row['rating'] ? '★' : '<span class="empty">★</span>';
                                                            }
                                                            ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div class="message-preview" data-bs-toggle="popover"
                                                        data-bs-trigger="hover focus" data-bs-html="true"
                                                        data-bs-placement="left" title="Feedback Details"
                                                        data-bs-content="<strong>Message:</strong><br><?= htmlspecialchars($row['message']); ?><?php if (!empty($row['course_name'])): ?><br><br><strong>Course:</strong> <?= htmlspecialchars($row['course_name']); ?><?php endif; ?><?php if (!empty($row['tutor_name'])): ?><br><strong>Tutor:</strong> <?= htmlspecialchars($row['tutor_name']); ?><?php endif; ?>">
                                                        <?= htmlspecialchars(substr($row['message'], 0, 60)) . (strlen($row['message']) > 60 ? '...' : ''); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="status-badge status-<?= strtolower($row['status'] ?? 'new'); ?>">
                                                        <?= ucfirst($row['status'] ?? 'New'); ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <div style="font-size: 14px;">
                                                        <?= date('d M Y', strtotime($row['created_at'])); ?>
                                                        <div style="font-size: 12px; color: #94a3b8;">
                                                            <?= date('h:i A', strtotime($row['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <?php include 'includes/script.php' ?>

    <script>
        $(document).ready(function () {

            // Initialize DataTable
            let table;
            if ($.fn.DataTable.isDataTable('#datatable')) {
                table = $('#datatable').DataTable();
                table.destroy();
            }

            table = $('#datatable').DataTable({
                scrollX: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'desc']],
                dom: "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Copy'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel'
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF'
                    },
                    {
                        extend: 'print',
                        text: 'Print'
                    }
                ],
                language: {
                    search: "Search feedback:",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No feedback available",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching feedback found",
                    emptyTable: "No feedback data available"
                }
            });

            // Initialize popovers with better configuration
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    container: 'body',
                    trigger: 'hover focus',
                    delay: { "show": 200, "hide": 100 }
                });
            });

            // Custom filter function - FIXED VERSION
            $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    // Get the actual HTML row element
                    let rowNode = table.row(dataIndex).node();

                    // Get data attributes from the row
                    let type = $(rowNode).attr('data-type');
                    let status = $(rowNode).attr('data-status');

                    // Get selected filter values
                    let selectedType = $('#typeFilter').val().toLowerCase();
                    let selectedStatus = $('#statusFilter').val().toLowerCase();

                    // Apply filters
                    let typeMatch = selectedType === '' || type === selectedType;
                    let statusMatch = selectedStatus === '' || status === selectedStatus;

                    return typeMatch && statusMatch;
                }
            );

            // Filter change handlers
            $('#typeFilter, #statusFilter').on('change', function () {
                table.draw();
            });

        });
    </script>

</body>

</html>