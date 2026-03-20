<?php
include "connection.php";
$alert_js = "";
// ✅ DB connection check
if (!$conn) {
    $alert_js = "Swal.fire('Error', 'Database connection failed.', 'error');";
}

// Default vars
$name = $address = $email = $logo = $slug = $website = $description = $status = $city_id = $contact = "";

// ✅ Fetch record for editing
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM cllg_tbl WHERE clg_id='$id'");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $name = $row['clg_name'];
        $slug = $row['clg_slug'];
        $city_id = $row['city_id'];
        $email = $row['clg_email'];
        $contact = $row['clg_contact'];
        $address = $row['clg_address'];
        $website = $row['clg_website'];
        $description = $row['clg_description'];
        $status = $row['clg_status'];
        $logo = $row['clg_logo'];
    }
}

// ✅ Handle Insert/Update
if (isset($_POST['btn_add_clg'])) {

    // File upload
    $logo_name = $logo;
    if (!empty($_FILES['clg_logo']['name'])) {
        $upload_dir = "img/clg_img/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $logo_name = time() . "_" . basename($_FILES['clg_logo']['name']);
        move_uploaded_file($_FILES['clg_logo']['tmp_name'], $upload_dir . $logo_name);
    }

    // Sanitize inputs
    $clg_name = trim($_POST['clg_name']);
    $clg_slug = trim($_POST['clg_slug']);
    $clg_email = trim($_POST['clg_email']);
    $clg_contact = trim($_POST['clg_contact']);
    $clg_address = trim($_POST['clg_address']);
    $city_id = trim($_POST['city_id']);
    $clg_website = trim($_POST['clg_website']);
    $clg_description = trim($_POST['clg_description']);
    $clg_status = trim($_POST['clg_status']);

    // Escape for query
    $clg_name = mysqli_real_escape_string($conn, $clg_name);
    $clg_slug = mysqli_real_escape_string($conn, $clg_slug);
    $clg_email = mysqli_real_escape_string($conn, strtolower($clg_email)); // lowercase emails
    $clg_contact = mysqli_real_escape_string($conn, $clg_contact);
    $clg_address = mysqli_real_escape_string($conn, $clg_address);
    $city_id = mysqli_real_escape_string($conn, $city_id);
    $clg_website = mysqli_real_escape_string($conn, strtolower($clg_website)); // lowercase
    $clg_description = mysqli_real_escape_string($conn, $clg_description);
    $clg_status = mysqli_real_escape_string($conn, $clg_status);

    $duplicate_fields = [];

    // ✅ Define helper function for duplicate checking
    function is_duplicate($conn, $field, $value, $exclude_id = null) {
        if (empty($value)) return false;
        $field = mysqli_real_escape_string($conn, $field);
        $value = mysqli_real_escape_string($conn, $value);
        $where = $exclude_id ? "AND clg_id != '$exclude_id'" : "";
        $sql = "SELECT COUNT(*) AS c FROM cllg_tbl WHERE LOWER($field)=LOWER('$value') $where";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        return $row['c'] > 0;
    }

    // ✅ Update mode
    if (!empty($_GET['id'])) {
        $id = mysqli_real_escape_string($conn, $_GET['id']);

        if (is_duplicate($conn, 'clg_name', $clg_name, $id)) $duplicate_fields[] = 'Name';
        if (is_duplicate($conn, 'clg_email', $clg_email, $id)) $duplicate_fields[] = 'Email';
        if (is_duplicate($conn, 'clg_website', $clg_website, $id)) $duplicate_fields[] = 'Website';
        if (is_duplicate($conn, 'clg_contact', $clg_contact, $id)) $duplicate_fields[] = 'Contact';
        if (is_duplicate($conn, 'clg_slug', $clg_slug, $id)) $duplicate_fields[] = 'Slug';

        if (!empty($duplicate_fields)) {
            $msg = implode(' & ', $duplicate_fields) . " already exist. Please use unique values.";
            $alert_js = "Swal.fire('Duplicate Entry!', " . json_encode($msg) . ", 'warning');";
        } else {
            $update = "UPDATE cllg_tbl SET
                clg_name='$clg_name',
                clg_slug='$clg_slug',
                clg_email='$clg_email',
                clg_contact='$clg_contact',
                clg_address='$clg_address',
                city_id='$city_id',
                clg_website='$clg_website',
                clg_description='$clg_description',
                clg_status='$clg_status',
                clg_logo='$logo_name'
                WHERE clg_id='$id'";

            if (mysqli_query($conn, $update)) {
                $alert_js = "Swal.fire({icon:'success', title:'Updated!', text:'College updated successfully.'})
                             .then(()=>{window.location='admin-all-college.php';});";
            } else {
                $alert_js = "Swal.fire('Error', 'Failed to update college. " . addslashes(mysqli_error($conn)) . "', 'error');";
            }
        }

    } else {
        // ✅ Insert mode
        if (is_duplicate($conn, 'clg_name', $clg_name)) $duplicate_fields[] = 'Name';
        if (is_duplicate($conn, 'clg_email', $clg_email)) $duplicate_fields[] = 'Email';
        if (is_duplicate($conn, 'clg_website', $clg_website)) $duplicate_fields[] = 'Website';
        if (is_duplicate($conn, 'clg_contact', $clg_contact)) $duplicate_fields[] = 'Contact';
        if (is_duplicate($conn, 'clg_slug', $clg_slug)) $duplicate_fields[] = 'Slug';

        if (!empty($duplicate_fields)) {
            $msg = implode(' & ', $duplicate_fields) . " already exist. Please use unique values.";
            $alert_js = "Swal.fire('Duplicate Entry!', " . json_encode($msg) . ", 'warning');";
        } else {
            $insert = "INSERT INTO cllg_tbl 
                (clg_name, clg_slug, clg_email, clg_contact, clg_address, city_id, clg_website, clg_logo, clg_description, clg_status)
                VALUES (
                '$clg_name', '$clg_slug', '$clg_email', '$clg_contact', '$clg_address', '$city_id',
                '$clg_website', '$logo_name', '$clg_description', '$clg_status')";

            if (mysqli_query($conn, $insert)) {
                $alert_js = "Swal.fire({icon:'success', title:'Inserted!', text:'College inserted successfully.'})
                             .then(()=>{window.location='admin-add-college.php';});";
            } else {
                $alert_js = "Swal.fire('Error', 'Failed to insert college. " . addslashes(mysqli_error($conn)) . "', 'error');";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
        <title>Adminto | Manage State</title>
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

        <div class="card shadow">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <?= isset($_GET['id']) ? 'Update College' : 'Add College' ?>
                </h1>
            </div>

            <div class="card-body">

                <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">College Name</label>
                            <input type="text" class="form-control" name="clg_name" id="clg_name"
                                value="<?= htmlspecialchars($name) ?>" required pattern="^[A-Za-z ]+$">
                            <div class="invalid-feedback" id="nameError">
                                Enter College Name and only in alphabet
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">College Slug</label>
                            <input type="text" class="form-control" name="clg_slug" id="clg_slug"
                                value="<?= htmlspecialchars($slug) ?>" required pattern="^[A-Za-z]+$">
                            <div class="invalid-feedback" id="slugError">
                                Enter College Slug only in alphabet
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">College Email</label>
                            <input type="email" class="form-control" name="clg_email"
                                value="<?= htmlspecialchars($email) ?>" required>
                            <div class="invalid-feedback">Enter College Email</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">College Contact</label>
                            <input type="text" class="form-control" name="clg_contact"
                                value="<?= htmlspecialchars($contact) ?>" required pattern="^[0-9]{10}$">
                            <div class="invalid-feedback">Enter 10 digit contact number</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">College Address</label>
                            <input type="text" class="form-control" name="clg_address"
                                value="<?= htmlspecialchars($address) ?>" required>
                            <div class="invalid-feedback">Enter College Address</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <select class="form-control" name="city_id" required>
                                <option value="">Select City</option>
                                <?php
                                $q = "SELECT city_id, city_name FROM city_tbl WHERE city_status = 1";
                                $res = mysqli_query($conn, $q);
                                while($r = mysqli_fetch_assoc($res)) {
                                    $selected = ($city_id == $r['city_id']) ? 'selected' : '';
                                    echo "<option value='".htmlspecialchars($r['city_id'])."' $selected>".
                                        htmlspecialchars($r['city_name'])."</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Please select a city.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="clg_website"
                                value="<?= htmlspecialchars($website) ?>" placeholder="https://example.com"
                                required pattern="https?://.+">
                            <div class="invalid-feedback">
                                Enter Website with http:// or https://
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="clg_status" required>
                                <option value="1" <?= ($status == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($status == 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                         <label class="form-label">Description</label>
                         <textarea class="form-control" name="clg_description" rows="4" required><?= htmlspecialchars($description) ?></textarea>
                         <div class="invalid-feedback">Enter College Description</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" name="clg_logo"
                            <?= empty($logo) ? 'required' : '' ?> accept="image/*">

                        <?php if ($logo): ?>
                            <img src="img/clg_img/<?= htmlspecialchars($logo) ?>" width="100"
                                class="mt-2 border rounded">
                        <?php endif; ?>

                        <div class="invalid-feedback">Select College Logo</div>
                    </div>

                    <button class="btn btn-primary" type="submit" name="btn_add_clg">Submit</button>

                </form>

            </div>
        </div>

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
<script>
(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
<?php if (!empty($alert_js)) echo "<script>$alert_js</script>"; ?>
                <!-- Footer Start -->
                <?php include_once("footer.php");?>
                <!-- end Footer -->
                </div> <!-- container -->
            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->
        </div>
</body>
</html>