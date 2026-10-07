<?php
require_once __DIR__ . "/../config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = "Settings saved successfully!";
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Settings</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #222;
            transition: 0.3s;
        }

        /* DARK MODE */

        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background-color: #222;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
        }

        body.dark-mode .sidebar {
            background-color: #000;
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

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        .main h1 {
            margin-bottom: 25px;
        }

        .settings-box {
            background-color: white;
            max-width: 700px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        body.dark-mode .settings-box {
            background-color: #1e1e1e;
            box-shadow: 0 2px 8px rgba(255,255,255,0.05);
        }

        .setting {
            padding: 20px 0;
            border-bottom: 1px solid #ddd;
        }

        body.dark-mode .setting {
            border-bottom: 1px solid #444;
        }

        .setting:last-child {
            border-bottom: none;
        }

        .setting h3 {
            margin-bottom: 8px;
        }

        .setting p {
            color: #666;
            margin-bottom: 10px;
        }

        body.dark-mode .setting p {
            color: #aaa;
        }

        .setting input {
            margin-right: 8px;
        }

        .save-btn {
            margin-top: 25px;
            padding: 12px 25px;
            border: none;
            background-color: #222;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .save-btn:hover {
            background-color: #444;
        }

        body.dark-mode .save-btn {
            background-color: #ffffff;
            color: #000;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            max-width: 700px;
        }

        .password-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 18px;
            background-color: #222;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .password-btn:hover {
            background-color: #444;
        }

        body.dark-mode .password-btn {
            background-color: #ffffff;
            color: #000;
        }

    </style>

</head>

<body>

    <?php include "includes/sidebar.php"; ?>

    <div class="main">

        <h1>⚙️ Settings</h1>

        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo $message; ?>
            </div>

        <?php } ?>

        <div class="settings-box">

            <form method="POST">

                <!-- Notifications -->

                <div class="setting">

                    <h3>🔔 Email Notifications</h3>

                    <p>
                        Receive notifications about your bookings.
                    </p>

                    <label>

                        <input
                            type="checkbox"
                            name="notifications"
                            checked
                        >

                        Enable Email Notifications

                    </label>

                </div>


                <!-- Dark Mode -->

                <div class="setting">

                    <h3>🌙 Dark Mode</h3>

                    <p>
                        Change the appearance of your dashboard.
                    </p>

                    <label>

                        <input
                            type="checkbox"
                            id="darkMode"
                            name="dark_mode"
                        >

                        Enable Dark Mode

                    </label>

                </div>


                <!-- Password -->

                <div class="setting">

                    <h3>🔒 Password</h3>

                    <p>
                        Change your account password.
                    </p>

                    <a
                        href="change_password.php"
                        class="password-btn"
                    >
                        Change Password
                    </a>

                </div>


                <button
                    type="submit"
                    class="save-btn"
                >
                    💾 Save Settings
                </button>

            </form>

        </div>

    </div>


    <!-- DARK MODE JAVASCRIPT -->

    <script>

        const darkMode = document.getElementById("darkMode");

        // Load saved setting
        if (localStorage.getItem("darkMode") === "ON") {

            document.body.classList.add("dark-mode");

            darkMode.checked = true;

        }


        // When checkbox is changed
        darkMode.addEventListener("change", function() {

            if (this.checked) {

                document.body.classList.add("dark-mode");

                localStorage.setItem("darkMode", "ON");

            } else {

                document.body.classList.remove("dark-mode");

                localStorage.setItem("darkMode", "OFF");

            }

        });

    </script>

</body>

</html>
