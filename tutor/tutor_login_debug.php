<?php
session_start();
include_once 'connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize flags for alerts
$login_error = '';
$account_deactivated = false;
$account_not_verified = false;
$debug_info = [];

// If already logged in, redirect to tutor dashboard
if (!empty($_SESSION['tutor_logged']) && $_SESSION['tutor_logged'] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    $debug_info[] = "Email entered: " . $email;
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    if (empty($errors)) {
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT tutor_id, tutor_name, tutor_email, tutor_phone, password, tutor_status, verification_status FROM tutor_tbl WHERE tutor_email = ?");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $debug_info[] = "Query executed. Rows found: " . mysqli_num_rows($result);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $tutor = mysqli_fetch_assoc($result);
                
                $debug_info[] = "Tutor found: " . $tutor['tutor_name'];
                $debug_info[] = "Password in DB length: " . strlen($tutor['password']);
                $debug_info[] = "Password starts with: " . substr($tutor['password'], 0, 4);
                $debug_info[] = "Tutor Status: " . $tutor['tutor_status'];
                $debug_info[] = "Verification Status: " . $tutor['verification_status'];
                
                // Verify password - check if it's hashed or plain text
                $password_valid = false;
                
                // Check if password appears to be hashed (bcrypt hashes start with $2y$ and are 60 chars)
                if (strlen($tutor['password']) == 60 && substr($tutor['password'], 0, 4) === '$2y$') {
                    // Password is hashed - use password_verify
                    $password_valid = password_verify($password, $tutor['password']);
                    $debug_info[] = "Using password_verify (hashed): " . ($password_valid ? 'MATCH' : 'NO MATCH');
                } else {
                    // Password is plain text - direct comparison
                    $password_valid = ($password === $tutor['password']);
                    $debug_info[] = "Using direct comparison (plain text): " . ($password_valid ? 'MATCH' : 'NO MATCH');
                    $debug_info[] = "Entered password: '" . $password . "'";
                    $debug_info[] = "DB password: '" . $tutor['password'] . "'";
                }
                
                if ($password_valid) {
                    $debug_info[] = "Password verification: SUCCESS";
                    
                    // Check verification status
                    if ($tutor['verification_status'] != 'verified' && $tutor['verification_status'] != 1) {
                        $account_not_verified = true;
                        $debug_info[] = "Account not verified";
                    }
                    // Check if account is active
                    elseif ($tutor['tutor_status'] != 1) {
                        $account_deactivated = true;
                        $debug_info[] = "Account deactivated";
                    }
                    // Login successful
                    else {
                        $debug_info[] = "All checks passed - setting session";
                        
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);
                        
                        $_SESSION['tutor_id']        = $tutor['tutor_id'];
                        $_SESSION['tutor_email']     = $tutor['tutor_email'];
                        $_SESSION['tutor_name']      = $tutor['tutor_name'];
                        $_SESSION['tutor_phone']     = $tutor['tutor_phone'];
                        $_SESSION['tutor_logged']    = true;
                        $_SESSION['login_time']      = time();
                        
                        $debug_info[] = "Session variables set";
                        $debug_info[] = "Attempting redirect to index.php";
                        
                        // Show debug info before redirect (comment out redirect for debugging)
                        // Uncomment the lines below to see debug info, comment out header redirect
                        
                        // echo "<pre>";
                        // print_r($debug_info);
                        // echo "\n\nSESSION:";
                        // print_r($_SESSION);
                        // echo "</pre>";
                        // exit();
                        
                        header('Location: index.php');
                        exit();
                    }
                } else {
                    $login_error = 'Invalid email or password';
                    $debug_info[] = "Password verification: FAILED";
                }
            } else {
                $login_error = 'Invalid email or password';
                $debug_info[] = "No tutor found with this email";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $login_error = 'Database error. Please try again later.';
            $debug_info[] = "Statement preparation failed: " . mysqli_error($conn);
        }
    } else {
        $login_error = implode(', ', $errors);
        $debug_info[] = "Validation errors: " . implode(', ', $errors);
    }
}

// Get company name from connection.php if it exists
$company_name = isset($company_name) ? $company_name : 'TutorPanel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Tutor Login (Debug) | <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* Same styles as before - abbreviated for space */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --emerald: #10b981;
            --emerald-mid: #059669;
            --forest: #065f46;
            --forest-deep: #022c22;
            --white: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(150deg, var(--forest-deep) 0%, #044533 38%, #047a55 70%, var(--emerald-mid) 100%);
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
        }
        .container {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 100vh;
            padding: 2rem 1.5rem;
        }
        .login-card {
            background: var(--white);
            border-radius: 26px;
            padding: 2.6rem 2.4rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 30px 80px rgba(2,44,34,.55);
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 11.5px; font-weight: 600; margin-bottom: 5px; }
        .form-input {
            width: 100%;
            padding: 11px 13px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
        }
        .btn-login {
            width: 100%;
            padding: 12.5px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--emerald) 0%, var(--forest) 100%);
            margin-top: 1rem;
        }
        .debug-box {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
        .debug-box h4 {
            color: #d63384;
            margin-bottom: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .debug-item {
            padding: 4px 0;
            border-bottom: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Tutor Login (Debug Mode)</h1>
            <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 1.5rem;">
                This debug version will show you what's happening during login
            </p>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="you@institution.edu" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"/>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Enter your password" required/>
                </div>

                <button class="btn-login" type="submit">Login & Show Debug Info</button>
            </form>

            <?php if (!empty($debug_info)) : ?>
                <div class="debug-box">
                    <h4>🔍 Debug Information:</h4>
                    <?php foreach ($debug_info as $info) : ?>
                        <div class="debug-item"><?php echo htmlspecialchars($info); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($login_error)) : ?>
                <div style="background: #fee; border: 1px solid #fcc; padding: 10px; border-radius: 8px; margin-top: 15px; color: #c33; font-size: 13px;">
                    <strong>Error:</strong> <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <p style="font-size: 11px; color: #999; margin-top: 20px; text-align: center;">
                ⚠️ Delete this debug file after troubleshooting!
            </p>
        </div>
    </div>
</body>
</html>
