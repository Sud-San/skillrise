<?php
require_once('includes/init.php');
include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php' ?>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        .card:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        #datatable thead th {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .star-rating i {
            font-size: 14px;
        }

        .review-text-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 13px;
            color: #6c757d;
        }

        .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 4px;
        }

        .filter-btn {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .filter-btn:hover {
            background-color: #f1f3f5;
            border-color: #adb5bd;
        }

        .filter-btn .fa-filter {
            color: #6c757d;
            font-size: 12px;
        }

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            border: 1px solid #e9ecef;
            min-width: 160px;
        }

        .dropdown-item {
            padding: 9px 18px;
            font-size: 13px;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>

</head>

<body class="app">

    <?php include 'includes/header.php' ?>

    <div class="app-wrapper">
        <div class="app-content pt-2 p-md-3 p-lg-4">
            <div class="container-xl">
                <div class="row g-3 mb-4 align-items-center justify-content-between">
                    <div class="col-auto"></div>
                    <div class="main-content w-100">
                        <div class="mt-1">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header bg-white pt-3 pb-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="h3 mb-0">Reviews</h4>
                                        </div>
                                        <div class="col-auto">
                                            <div class="dropdown">
                                                <button class="btn filter-btn dropdown-toggle" type="button"
                                                    id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-filter"></i> Filter by
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item filter-option active" href="#"
                                                            data-filter="">Show All</a></li>
                                                    <li><a class="dropdown-item filter-option" href="#"
                                                            data-filter="Visible">Visible Only</a></li>
                                                    <li><a class="dropdown-item filter-option" href="#"
                                                            data-filter="Hidden">Hidden Only</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="datatable"
                                            class="table table-bordered table-hover align-middle text-center"
                                            style="width:100%">
                                            <thead class="table-light text-uppercase">
                                                <tr>
                                                    <th>Review Id</th>
                                                    <th>Student Name</th>
                                                    <th>Course Name</th>
                                                    <th>Description</th>
                                                    <th>Ratings</th>
                                                    <th>Verified</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $logged_tutor_id = $_SESSION['tutor_id'];
                                                $result = mysqli_query($conn, "
                                                SELECT 
                                                    r.*,
                                                    u.user_name,
                                                    c.course_title
                                                FROM reviews_tbl r
                                                LEFT JOIN user_tbl u ON r.user_id = u.user_id
                                                LEFT JOIN course_tbl c ON r.course_id = c.course_id
												WHERE r.tutor_id = $logged_tutor_id
                                                ORDER BY r.created_at DESC
                                            ");
                                                $counter = 1;
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $rating = (int) $row['rating'];
                                                    $isVerified = $row['is_verified'] == 1;
                                                    $isVisible = $row['status'] == 1;
                                                    $reviewText = $row['review_text'] ? htmlspecialchars($row['review_text']) : 'No review';
                                                    $dateDisplay = $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : '—';
                                                    $verifiedText = $isVerified ? 'Enrolled' : 'Not Enrolled';
                                                    $statusText = $isVisible ? 'Visible' : 'Hidden';
                                                    ?>
                                                    <!-- Store status in data-status on the <tr> itself — most reliable approach -->
                                                    <tr data-status="<?php echo $statusText; ?>">
                                                        <td><?php echo $counter++; ?></td>

                                                        <td class="text-start">
                                                            <span
                                                                class="fw-semibold"><?php echo htmlspecialchars($row['user_name'] ?? 'Unknown'); ?></span>
                                                        </td>

                                                        <td class="text-start">
                                                            <?php echo htmlspecialchars($row['course_title'] ?? '—'); ?>
                                                        </td>

                                                        <td class="text-start" style="max-width:250px;">
                                                            <div class="review-text-clamp"><?php echo $reviewText; ?></div>
                                                        </td>

                                                        <td>
                                                            <div class="star-rating">
                                                                <?php for ($i = 1; $i <= 5; $i++) {
                                                                    echo $i <= $rating
                                                                        ? '<i class="fa-solid fa-star text-warning"></i>'
                                                                        : '<i class="fa-regular fa-star text-secondary"></i>';
                                                                } ?>
                                                                <div class="small text-muted mt-1"><?php echo $rating; ?>/5
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <?php if ($isVerified): ?>
                                                                <span class="badge bg-success"><i
                                                                        class="fa-solid fa-check me-1"></i>Enrolled</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary"><i
                                                                        class="fa-solid fa-xmark me-1"></i>Not Enrolled</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <?php if ($isVisible): ?>
                                                                <span class="badge bg-success"><i
                                                                        class="fa-solid fa-eye me-1"></i>Visible</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger"><i
                                                                        class="fa-solid fa-eye-slash me-1"></i>Hidden</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-muted small"><?php echo $dateDisplay; ?></td>
                                                    </tr>
                                                <?php } ?>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
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

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {

            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            // ── Custom search using data-status on <tr> ──
            // This is the most reliable approach — no column index needed
            let currentFilter = '';

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'datatable') return true; // only apply to this table
                if (currentFilter === '') return true; // show all
                let rowNode = table.row(dataIndex).node();
                let rowStatus = $(rowNode).data('status'); // reads data-status from <tr>
                return rowStatus === currentFilter;
            });

            let table = $('#datatable').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                pageLength: 10,
                dom: "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'copy', text: 'Copy', className: 'btn btn-secondary btn-sm' },
                    { extend: 'csv', text: 'CSV', className: 'btn btn-secondary btn-sm' },
                    { extend: 'excel', text: 'Excel', className: 'btn btn-secondary btn-sm' },
                    { extend: 'pdf', text: 'PDF', className: 'btn btn-secondary btn-sm' },
                    { extend: 'print', text: 'Print', className: 'btn btn-secondary btn-sm' },
                    { extend: 'colvis', text: 'Column visibility', className: 'btn btn-secondary btn-sm' }
                ],
                language: {
                    paginate: {
                        previous: "<i class='fa-solid fa-angle-left'></i>",
                        next: "<i class='fa-solid fa-angle-right'></i>"
                    },
                    search: "Search:"
                }
            });

            // ── Filter dropdown ──
            $(document).on('click', '.filter-option', function (e) {
                e.preventDefault();
                $('.filter-option').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('filter'); // '', 'Visible', 'Hidden'
                table.draw();
            });

        });
    </script>

</body>

</html>