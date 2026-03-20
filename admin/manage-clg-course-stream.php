
<?php
    include "connection.php";
    // Show Php code 
    $display_stream = "SELECT ccs.*, ct.course_name, st.stream_name 
                       FROM college_course_stream_tbl ccs 
                       JOIN course_tbl ct ON ccs.college_course_id = ct.course_id 
                       JOIN stream_tbl st ON ccs.stream_id = st.stream_id";
    $result = mysqli_query($conn, $display_stream);

    // Delete code
    if(isset($_GET['id']) && is_numeric($_GET['id']))
    {
        $id = $_GET['id'];

        $delete_stream = "DELETE FROM college_course_stream_tbl WHERE id='$id'";
        mysqli_query($conn, $delete_stream);

        // Very important: redirect with success flag
        header("Location: manage-clg-course-stream.php?deleted=1");
        exit;
    }

    $i=1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Font Awesome 6.6.0 CDN -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      <meta charset="utf-8" />
        <title>Adminto | Manage Stream</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- Theme Config Js -->
        <script src="assets/js/config.js"></script>

        <!-- Vendor css -->
        <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

        <!-- App css -->
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

        <!-- Icons css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- Datatables css -->
        <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uD1O7M2V3Xw2o4rU6+z3HQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
             /* Default Switch (Full Size) */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 25px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 19px;
    width: 19px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #0d6efd;
}

input:checked + .slider:before {
    transform: translateX(24px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}


/* ----------------------------------------
   EXTRA SMALL SWITCH (For DataTable)
-----------------------------------------*/
.switch.xs {
    width: 32px;
    height: 16px;
}

.switch.xs .slider {
    background-color: #ccc;
    border-radius: 20px;
}

.switch.xs .slider:before {
    height: 12px;
    width: 12px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    border-radius: 50%;
}

.switch.xs input:checked + .slider {
    background-color: #0d6efd;
}

.switch.xs input:checked + .slider:before {
    transform: translateX(16px);
}

 </style>
</head>
<body>
             <div class="wrapper">

            <!-- Menu -->
            <!-- Sidenav Menu Start -->
          
            <?php  include_once("sidebar.php");?>

            <!-- Sidenav Menu End -->

            
            <!-- Topbar Start -->
          
            <?php include_once("header.php");?>
          
            <!-- Topbar End -->

            <!-- Search Modal -->
            <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-transparent">
                        <form>
                            <div class="card mb-1">
                                <div class="px-3 py-2 d-flex flex-row align-items-center" id="top-search">
                                    <i class="ri-search-line fs-22"></i>
                                    <input type="search" class="form-control border-0" id="search-modal-input"
                                        placeholder="Search for actions, people,">
                                    <button type="submit" class="btn p-0" data-bs-dismiss="modal" aria-label="Close">[esc]</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <div class="page-content">
                <div class="page-container">

                    
                    <!-- <h1>Manage State</h1> -->
                    <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="header-title mb-2">Manage Stream</h4>
                            </div>
                            <div class="card-body">
                              <div class="d-flex justify-content-start mb-0 ms-0">
                                        <a href="clg-course-stream.php" class="btn btn-primary">
                                            Add College Course Stream
                                        </a>
                                    </div>                              
                                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>college course</th>
                                            <th>stream</th>
                                            <th>Eligibility</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                            
                                            <!-- <th>Start date</th>
                                            <th>Salary</th> -->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php while($row=mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $i++?></td>
                                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['stream_name']); ?></td>
                                                <td><?php echo $row['eligibility']?></td>
                                                <td>
                                                    <label class="switch xs">
                                                        <input type="checkbox" class="statusToggle" data-id="<?php echo $row['id']; ?>" 
                                                            <?php echo ($row['ccs_status'] == 1) ? "checked" : ""; ?>>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </td>
                                                <td><a href="javascript:void(0);" onclick="deleteStream(<?php echo $row['id']; ?>)">
                                                   <i class="fa-solid fa-trash text-danger"></i></a>
                                                    &nbsp;&nbsp;
                                                    <a href="clg-course-stream.php?id=<?php echo $row['id']?>"><i class="fa-solid fa-pen-to-square text-primary"></i></a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>



                                </table>
                                
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div> <!-- end row-->
                   

                   

                   

                </div> <!-- container -->

                <!-- Footer Start -->
                <?php include_once("footer.php");?>
                <!-- end Footer -->

            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

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
                document.querySelectorAll('.statusToggle').forEach(function(toggle) {
                    toggle.addEventListener('change', function() {
                        let streamId = this.getAttribute('data-id');
                        let status = this.checked ? 1 : 0;

                        // Define SweetAlert message
                        let msg = status == 1 ? "Status Activated" : "Status Deactivated";
                        let txt = status == 1 ? "Status Updated successfully." : "This stream is now inactive.";
                        // SweetAlert popup
                        Swal.fire({
                            icon: status == 1 ? 'success' : 'warning',
                            title: msg,
                            text: txt,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // AJAX to stream.php
                        // Ajax request
                        let xhr = new XMLHttpRequest();
                        xhr.open("POST", "update_status.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.send("id=" + streamId + "&status=" + status);
                    });
                });
        </script>
        <script>
            function deleteStream(stream_id) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to delete this stream?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to delete URL
                        window.location.href = "manage-clg-course-stream.php?id=" + stream_id;
                    }
                });
            }
        </script>
<?php if(isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'College stream deleted successfully.',
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true
    });
});
</script>
<?php endif; ?>

</body>
</html>
                        
