<?php
   include "connection.php";
    // DB connection check
    if (!$conn) {
        die("Database connection failed.");
    }

    // Handle Delete Request (Secure with prepared statement)
    if (isset($_GET['id'])) {
        $id = intval($_GET['delete_id']);

        $delete = "DELETE FROM cllg_tbl WHERE clg_id = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                Swal.fire('Success', 'College deleted successfully!', 'success').then(function() {
                    window.location='admin-all-college.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire('Error', 'Error deleting record: " . mysqli_error($conn) . "', 'error').then(function() {
                    window.location='admin-all-college.php';
                });
            </script>";
        }
        mysqli_stmt_close($stmt);
        exit;
    }
            // DISPLAY CODE
            $display_query="
                                                    SELECT c.*, ct.city_name 
                                                FROM cllg_tbl AS c
                                                LEFT JOIN city_tbl AS ct ON c.city_id = ct.city_id
                                                ORDER BY c.clg_id DESC";
            $result=mysqli_query($conn, $display_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
        <title>Adminto | Manage College</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- Theme Config Js -->
        <script src="assets/js/config.js"></script>

        <!-- Vendor css -->
        <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

        <!-- App css -->
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
        <!-- Sweet alert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <!-- Icons css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- Datatables css -->
        <link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/fixedColumns.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />
        <!-- Font Awseome cdn -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
             img.logo {
                width: 100px;
                height: 100px;
                border-radius: 5px;
                object-fit: cover;
                border: 1px solid #ddd;
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
                                <h1 class="header-title mb-2">Manage College</h1>
                            </div>
                            <div class="card-body">
                               <table id="collegeTable" class="table table-bordered table-striped display nowrap" style="width:100%">    
                                 <thead>
                                        <tr>     <th>Logo</th>
                                                <th>Name</th>
                                                <th>Slug</th>
                                                <th>City</th>
                                                <th>Email</th>
                                                <th>Contact</th>
                                                <th>Address</th>
                                                <th>Website</th>
                                        <th><abbr title="College Description">Desc</abbr></th>
                                                <th>Status</th>
                                                <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($state = mysqli_fetch_array($result))  { ?>
                                        <tr>
                                            <td><img src="img/clg_img/<?php echo $state['clg_logo']; ?>"  class="logo"  alt="College Logo"></td>
                                            <td><?php echo $state['clg_name']; ?></td>
                                            <td><?php echo $state['clg_slug']; ?></td>
                                            <td><?php echo $state['city_name']; ?></td>
                                            <td><?php echo $state['clg_email']; ?></td>
                                            <td><?php echo $state['clg_contact']; ?></td>
                                            <td><?php echo $state['clg_address']; ?></td>
                                            <td><?php echo $state['clg_website']; ?></td>
                                            <td><?php echo $state['clg_description']; ?></td>

                                                <!-- Status Switch -->
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox"
                                                            class="form-check-input toggle-switch"
                                                            id="switch_<?php echo $state['clg_id']; ?>"
                                                            data-id="<?php echo $state['clg_id']; ?>"
                                                            <?php echo ($state['clg_status'] == 1) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>


                                                <!-- Action Buttons -->
                                                <td>
                                                    <a href="admin-add-college.php?id=<?php echo $state['clg_id']; ?>" title="Edit">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <a href="admin-all-college.php?id=<?php echo $state['clg_id']; ?>" 
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this college?');">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
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

</body>
   <script>
$(document).ready(function(){

  $(document).on('change', '.toggle-switch', function(){

    var clg_id = $(this).data('id');  // FIXED
    var clg_status = $(this).is(':checked') ? 1 : 0;

    $.ajax({
      url: 'update_status.php',
      type: 'POST',
      data: { id: clg_id, status: clg_status },
      success: function(response){
        Swal.fire({
          title: 'Status Updated!',
          text: 'College status changed successfully.',
          icon: 'success',
          timer: 1000,
          showConfirmButton: false
        });
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

});
</script>

</html>