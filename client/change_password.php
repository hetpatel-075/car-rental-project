<?php
require_once __DIR__ . "/../config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get current password from database
    $sql = "SELECT password FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    // Check current password
    if (!password_verify($current_password, $user['password'])) {

        $message = "Current password is incorrect.";
        $message_type = "error";

    } 
    elseif ($new_password != $confirm_password) {

        $message = "New passwords do not match.";
        $message_type = "error";

    } 
    elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";
        $message_type = "error";

    } 
    else {

        // Create secure password hash
        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        // Update password
        $update_sql = "UPDATE users 
                       SET password = '$hashed_password'
                       WHERE user_id = $user_id";

        if (mysqli_query($conn, $update_sql)) {

            $message = "Password changed successfully!";
            $message_type = "success";

        } else {

            $message = "Password change failed.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Change Password</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        /* Sidebar */

        .sidebar {
            width: 240px;
            height: 100vh;
            background-color: #222;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
        }

        .logo {
            color: white;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            font-size: 16px;
        }

        .sidebar a:hover {
            background-color: #444;
        }

        /* Main */

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        .main h1 {
            margin-bottom: 25px;
        }

        .password-box {
            background-color: white;
            max-width: 600px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        .change-btn {
            padding: 12px 25px;
            background-color: #222;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .change-btn:hover {
            background-color: #444;
        }

        .message {
            max-width: 600px;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            color: #222;
            text-decoration: none;
        }

    </style>

</head>

<body>

   <?php include "includes/sidebar.php"; ?>


    <!-- Main Content -->

    <div class="main">

        <h1>🔒 Change Password</h1>

        <?php if ($message != "") { ?>

            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>

        <?php } ?>


        <div class="password-box">

            <form method="POST">

                <div class="form-group">

                    <label>
                        Current Password
                    </label>

                    <input
                        type="password"
                        name="current_password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        New Password
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="change-btn"
                >
                    🔒 Change Password
                </button>

            </form>

            <a href="settings.php" class="back-btn">
                ← Back to Settings
            </a>

        </div>

    </div>

</body>

</html>