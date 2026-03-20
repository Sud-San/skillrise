<?php

// Database configuration
$host = "127.0.0.1";     // Server name or IP
$username = "root";      // Database username
$password = "";          // Database password
$database = "codezy"; // Change to your DB name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);
$time = date("Y-m-d H:i:s");
$sql = "select * from settings_tbl where setting_id = 1";
$result = mysqli_query($conn, $sql);
$company_name = mysqli_fetch_assoc($result)['setting_value'];

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}