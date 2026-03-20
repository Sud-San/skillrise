<?php
include("connection.php");

// ======================= AJAX HANDLER ===========================
// Handle Delete FAQ
if(isset($_POST['action']) && $_POST['action'] == 'delete'){
    $faq_id = intval($_POST['faq_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM faq WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $faq_id);
    if(mysqli_stmt_execute($stmt)){
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// Handle Toggle Status
if(isset($_POST['action']) && $_POST['action'] == 'toggle_status'){
    $faq_id = intval($_POST['faq_id']);
    $faq_status = intval($_POST['faq_status']);
    $stmt = mysqli_prepare($conn, "UPDATE faq SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $faq_status, $faq_id);
    if(mysqli_stmt_execute($stmt)){
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ======================= DISPLAY QUERY ===========================
$display_query = "SELECT * FROM faq ORDER BY id DESC";
$result = mysqli_query($conn, $display_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Manage FAQ | <?php echo $company_name; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="assets/images/favicon.ico">
<script src="assets/js/config.js"></script>
<link href="assets/css/vendor.min.css" rel="stylesheet" />
<link href="assets/css/app.min.css" rel="stylesheet" id="app-style" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="assets/css/icons.min.css" rel="stylesheet" />
<link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" />
<link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div class="wrapper">

    <?php include_once("sidebar.php"); ?>
    <?php include_once("header.php"); ?>

    <div class="page-content">
        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed">
                            <h4 class="header-title mb-2">Manage FAQ</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-start mb-0 ms-0">
                                        <a href="add_faq.php" class="btn btn-primary">
                                            Add Faq
                                        </a>
                                    </div>
                            <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>FAQ Question</th>
                                        <th>FAQ Answer</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; while ($faq = mysqli_fetch_array($result)) { ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td style="max-width: 150px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($faq['question']); ?></td>
                                        <td style="max-width: 100px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($faq['answer']); ?></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" 
                                                       class="form-check-input toggle-switch" 
                                                       data-id="<?= $faq['id']; ?>" 
                                                       <?= ($faq['status'] == 1) ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                        <td>
                                           <a href="#" class="delete-btn" data-id="<?= $faq['id']; ?>">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                            </a>
                                            &nbsp;|&nbsp;
                                            <a href="add_faq.php?faq_update=<?= $faq['id']; ?>">
                                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once("footer.php"); ?>
    </div>
</div>

  <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- Apex Chart js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <!-- Projects Analytics Dashboard App js -->
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="assets/vendor/datatables/dataTables.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="assets/vendor/datatables/responsive.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/fixedColumns.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.fixedHeader.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/datatables/buttons.bootstrap5.min.js"></script>
    <script src="assets/vendor/datatables/buttons.html5.min.js"></script>
    <script src="assets/vendor/datatables/buttons.print.min.js"></script>
    <script src="assets/vendor/datatables/jszip.min.js"></script>
    <script src="assets/vendor/datatables/pdfmake.min.js"></script>
    <script src="assets/vendor/datatables/vfs_fonts.js"></script>
    <script src="assets/vendor/datatables/dataTables.keyTable.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.select.min.js"></script>
    <script src="assets/js/components/table-datatable.js"></script>


<script>
$(document).ready(function(){ 

    // Toggle FAQ status
    $(document).on('change', '.toggle-switch', function(){
        var faq_id = $(this).data('id');
        var faq_status = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '',
            type: 'POST',
            data: { action: 'toggle_status', faq_id: faq_id, faq_status: faq_status },
            dataType: 'json',
            success: function(response){
                if(response.status === 'success'){
                    Swal.fire({
                        title: 'Status Updated!',
                        icon: 'success',
                        text:"Faq Status updated Successfully.",
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(){
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong while updating status.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Delete FAQ with SweetAlert
    $(document).on('click', '.delete-btn', function(e){
        e.preventDefault();
        var faq_id = $(this).data('id');
        var row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: { action: 'delete', faq_id: faq_id },
                    dataType: 'json',
                    success: function(response){
                        if(response.status === 'success'){
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'FAQ deleted successfully.',
                                timer: 1200,
                                showConfirmButton: false
                            });
                            row.fadeOut(800, function(){ $(this).remove(); });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to delete FAQ.',
                            });
                        }
                    },
                    error: function(){
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong.',
                        });
                    }
                });
            }
        });
    });

});
</script>

</body>
</html>
