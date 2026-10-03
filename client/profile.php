<?php
session_start();

include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>

    <title>My Profile</title>

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

        .profile-box {
            background-color: white;
            max-width: 700px;
            padding: 30px;
            border-radius: 10px;
        }

        .profile-box h1 {
            margin-bottom: 25px;
        }

        .profile-item {
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .profile-item strong {
            display: inline-block;
            width: 180px;
        }

        .edit-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background-color: #222;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .edit-btn:hover {
            background-color: #444;
        }

    </style>

</head>

<body>

    <?php include "includes/sidebar.php"; ?>


    <!-- Main Content -->

    <div class="main">

        <div class="profile-box">

            <h1>👤 My Profile</h1>

            <div class="profile-item">
                <strong>Name:</strong>
                <?php echo htmlspecialchars($user['name']); ?>
            </div>

            <div class="profile-item">
                <strong>Email:</strong>
                <?php echo htmlspecialchars($user['email']); ?>
            </div>

            <div class="profile-item">
                <strong>Phone:</strong>
                <?php echo htmlspecialchars($user['phone']); ?>
            </div>

            <div class="profile-item">
                <strong>Account Created:</strong>
                <?php echo htmlspecialchars($user['created_at']); ?>
            </div>

            <a href="edit_profile.php" class="edit-btn">
                ✏️ Edit Profile
            </a>

        </div>

    </div>

</body>
</html>