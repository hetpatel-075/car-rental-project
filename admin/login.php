<?php
require_once __DIR__ . "/session.php";

include "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username == "" || $password == "") {

        $message = "Please enter username and password.";

    } else {

        // Prepared statement for secure login
        $stmt = mysqli_prepare(
            $conn,
            "SELECT admin_id, username, password
             FROM admin
             WHERE username = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $admin = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $admin['password'])) {

                // Regenerate session ID after successful login
                session_regenerate_id(true);

                $_SESSION['admin_id'] = (int)$admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];

                // Save the session before redirecting to dashboard.
                session_write_close();

                mysqli_stmt_close($stmt);

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "Admin username not found.";

        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Admin Login</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <div class="form-container">

        <h2>Admin Login</h2>

        <?php if ($message != "") { ?>

            <p style="color:red;">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Username</label>

            <input
                type="text"
                name="username"
                required
                autocomplete="username"
                value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >

            <button type="submit">Login</button>

        </form>

    </div>

</body>
</html>