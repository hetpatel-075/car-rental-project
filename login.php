<?php

require_once __DIR__ . "/config/session.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email == "" || $password == "") {

        $message = "Please enter email and password.";

    } else {

        // Secure query using prepared statement
        $stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, name, email, password
             FROM users
             WHERE email = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            // Check password
            if (password_verify($password, $user['password'])) {

                // Regenerate session ID after successful login
                session_regenerate_id(true);

                // Create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                mysqli_stmt_close($stmt);

                // Go to client dashboard
                header("Location: client/dashboard.php");
                exit();

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "Email not registered.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Login - CarRental</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<!-- Navigation -->

<nav class="navbar">

    <div class="logo">
        🚗 CarRental
    </div>

    <div class="nav-links">

        <a href="index.php">Home</a>

        <a href="cars.php">Cars</a>

        <a href="register.php">Register</a>

    </div>

</nav>


<!-- Login Form -->

<div class="form-container">

    <h1>Login</h1>


    <?php if ($message != "") { ?>

        <p class="message">

            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>

        </p>

    <?php } ?>


    <form method="POST">

        <label>Email</label>

        <input
            type="email"
            name="email"
            required
            autocomplete="email"
            value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            required
            autocomplete="current-password"
        >


        <button type="submit" class="btn">

            Login

        </button>

    </form>


    <p>

        Don't have an account?

        <a href="register.php">
            Register here
        </a>

    </p>

</div>


</body>

</html>