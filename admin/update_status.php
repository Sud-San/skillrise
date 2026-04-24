<?php
include("connection.php");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// FOR CITY MODULE
if (isset($_POST['city_id']) && isset($_POST['city_status'])) {
    $city_id = intval($_POST['city_id']);
    $status = intval($_POST['city_status']);

    if ($city_id > 0) {
        $query = "UPDATE city_tbl SET city_status = '$status' WHERE city_id = '$city_id'";
        if (mysqli_query($conn, $query)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    exit;
}

// FOR GAMES MODULE
if (isset($_POST['game_id']) && isset($_POST['game_status'])) {
    $game_id = intval($_POST['game_id']);
    $game_status = intval($_POST['game_status']);
    if ($game_id > 0) {
        $query = "UPDATE games SET is_active = '$game_status' WHERE game_id = '$game_id'";
        if (mysqli_query($conn, $query)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    exit;
}

// FOR STATE MODULE
if (isset($_POST['state_id']) && isset($_POST['state_status'])) {
    $id = $_POST['state_id'];
    $status = $_POST['state_status'];
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE state_tbl SET state_status = '$status' WHERE state_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
    exit;
}

// FOR COURSE MODULE
if (isset($_POST['course_id']) && isset($_POST['course_status'])) {
    $id = $_POST['course_id'];
    $status = $_POST['course_status'];
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE course_tbl SET course_status = '$status' WHERE course_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
    exit;
}

// FOR ENROLLMENT MODULE
if (isset($_POST['enrollmentid']) && isset($_POST['enrollmentstatus'])) {
    $enrollment_id = (int) $_POST['enrollmentid'];
    $enrollment_status = mysqli_real_escape_string($conn, $_POST['enrollmentstatus']);

    $sql = "UPDATE enrollments_tbl SET enrollment_status = '$enrollment_status' WHERE enrollment_id = '$enrollment_id'";
    if (mysqli_query($conn, $sql)) {
        echo "ok";
    } else {
        http_response_code(500);
        echo "update error";
    }
    exit;
}

// FOR PACKAGE MODULE
if (isset($_POST['package_id']) && isset($_POST['package_id']) && isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];
    $package_id = intval($_POST['package_id']);
    $update = "UPDATE package_tbl SET $field='$value' WHERE package_id='$package_id'";
    if (mysqli_query($conn, $update)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Error updating status: ' . mysqli_error($conn)]);
    }
    exit;
}

// FOR CATEGORY MODULE
if (isset($_POST['category_id']) && isset($_POST['category_status'])) {
    $id = $_POST['category_id'];
    $update = "UPDATE category_tbl SET category_status='" . $_POST['category_status'] . "' WHERE category_id='$id'";
    mysqli_query($conn, $update);
    echo "success";
    exit;
}

// FOR USER MODULE
if (isset($_POST['user_id']) && isset($_POST['user_status'])) {
    $id = $_POST['user_id'];
    $status = $_POST['user_status'];
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE user_tbl SET user_status = '$status' WHERE user_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => "error", 'error' => "Error updating status: " . mysqli_error($conn)]);
    }
    exit;
}

// FOR TUTOR MODULE
if (isset($_POST['tutor_id']) && isset($_POST['tutor_status'])) {
    $id = $_POST['tutor_id'];
    $status = $_POST['tutor_status'];
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE tutor_tbl SET tutor_status = '$status' WHERE tutor_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => "error", 'error' => "Error updating status: " . mysqli_error($conn)]);
    }
    exit;
}

// FOR TUTOR PAYMENT
if (isset($_POST['purchase_id']) && isset($_POST['payment_status'])) {
    $id = $_POST['purchase_id'];
    $status = $_POST['payment_status'];
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE tutor_package_tbl SET payment_status = '$status' WHERE purchase_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => "error", 'error' => "Error updating status: " . mysqli_error($conn)]);
    }
    exit;
}

// FOR FAQ MODULE
if (isset($_POST['faq_id']) && isset($_POST['faq_status'])) {
    $id = $_POST['faq_id'];
    $status = $_POST['faq_status'];
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "UPDATE faq SET status = '$status' WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
    exit;
}

// FOR COURSE category MODULE
if (isset($_POST['cs_id']) && isset($_POST['cs_status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['cs_id']);
    $status = mysqli_real_escape_string($conn, $_POST['cs_status']);

    $query = "UPDATE course_category SET cs_status = '$status' WHERE cs_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
    exit;
}
// FOR Notification MODULE
if (isset($_POST['notification_id']) && isset($_POST['notification_status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['notification_id']);
    $status = mysqli_real_escape_string($conn, $_POST['notification_status']);
    $query = "UPDATE notification_tbl SET is_read = '$status' WHERE notification_id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
    exit;
}

// No valid module request
echo "Invalid request!";
