<?php
// Add any PHP logic here if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-center mb-6">Register As</h2>

    <!-- Role Selection Buttons -->
    <div class="flex justify-center gap-4 mb-6">
        <button id="studentBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Student</button>
        <button id="teacherBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Teacher</button>
    </div>

    <!-- Student Form -->
    <form id="studentForm" class="space-y-4 hidden" action="student_register_process.php" method="POST">
        <h3 class="text-lg font-semibold text-center mb-2">Student Registration</h3>
        <input type="text" name="sname" placeholder="Full Name"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        <input type="email" name="semail" placeholder="Email"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        <input type="password" name="spassword" placeholder="Password" minlength="6"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        <input type="password" name="sconfirm_password" placeholder="Confirm Password" minlength="6"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition">Register as Student
        </button>
    </form>

    <!-- Teacher Form -->
    <form id="teacherForm" class="space-y-4 hidden" action="teacher_register_process.php" method="POST">
        <h3 class="text-lg font-semibold text-center mb-2">Teacher Registration</h3>
        <input type="text" name="tname" placeholder="Full Name"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-400">
        <input type="email" name="temail" placeholder="Email"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-400">
        <input type="password" name="tpassword" placeholder="Password" minlength="6"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-400">
        <input type="password" name="tconfirm_password" placeholder="Confirm Password" minlength="6"
               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-400">
        <button type="submit"
                class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600 transition">Register as Teacher
        </button>
    </form>
</div>

<!-- Toggle Script -->
<script>
    $(document).ready(function () {
        $('#studentBtn').click(function () {
            $('#teacherForm').hide();
            $('#studentForm').fadeIn();
        });

        $('#teacherBtn').click(function () {
            $('#studentForm').hide();
            $('#teacherForm').fadeIn();
        });
    });
</script>

</body>
</html>
