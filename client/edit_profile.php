<?php
require_once __DIR__ . "/../config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = "";

/* Update profile */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if ($name == "" || $phone == "") {
        $message = "Please fill all fields.";
    }
    else if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "Phone number must contain exactly 10 digits.";
    }
    else {

        $sql = "UPDATE users
                SET name = '$name',
                    phone = '$phone'
                WHERE user_id = $user_id";

        if (mysqli_query($conn, $sql)) {

    $_SESSION['user_name'] = $name;

    header("Location: profile.php");
    exit();

} else {

    $message = "Profile update failed.";

}
    }
}

/* Get current user details */

$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);

$user = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Profile</title>

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
        }

        .sidebar a:hover {
            background-color: #444;
        }

        /* Main */

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        .form-box {
            background-color: white;
            max-width: 600px;
            padding: 30px;
            border-radius: 10px;
        }

        .form-box h1 {
            margin-bottom: 25px;
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
        }

        .form-group input:focus {
            outline: none;
            border-color: #555;
        }

        .email-box {
            background-color: #eee;
        }

        .update-btn {
            padding: 12px 20px;
            background-color: #222;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .update-btn:hover {
            background-color: #444;
        }

        .back-btn {
            display: inline-block;
            margin-left: 10px;
            padding: 12px 20px;
            background-color: #ddd;
            color: black;
            text-decoration: none;
            border-radius: 5px;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #eee;
            border-radius: 5px;
        }

    </style>

</head>

<body>


    <?php include "includes/sidebar.php"; ?>


    <!-- Main -->

    <div class="main">

        <div class="form-box">

            <h1>✏️ Edit Profile</h1>

            <?php if ($message != "") { ?>

                <div class="message">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>


            <form method="POST">

                <div class="form-group">

                    <label>Name</label>

                    <input
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($user['name']); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        class="email-box"
                        readonly
                    >

                </div>


                <div class="form-group">

                    <label>Phone</label>

                    <input
                        type="text"
                        name="phone"
                        value="<?php echo htmlspecialchars($user['phone']); ?>"
                        maxlength="10"
                        required
                    >

                </div>


                <button type="submit" class="update-btn">
                    Update Profile
                </button>

                <a href="profile.php" class="back-btn">
                    Back
                </a>

            </form>

        </div>

    </div>

</body>

</html>