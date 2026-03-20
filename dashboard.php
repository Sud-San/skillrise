<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Dashboard</title></head>
<body style="font-family:'Segoe UI', sans-serif; padding:30px;">
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
  <p>Role: <?php echo htmlspecialchars($_SESSION['user_role']); ?></p>
  <p><a href="logout.php">Logout</a></p>
</body>
</html>
