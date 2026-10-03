<?php

include "config/db.php";

$message = "";

$name = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if ($name == "" || $email == "" || $phone == "" || $password == "" || $confirm_password == "") {

        $message = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {

        $message = "Phone number must contain exactly 10 digits.";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";

    } elseif ($password != $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        // Check if email already exists
        $stmt = mysqli_prepare(
            $conn,
            "SELECT user_id
             FROM users
             WHERE email = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $message = "Email already registered.";

            mysqli_stmt_close($stmt);

        } else {

            mysqli_stmt_close($stmt);

            // Encrypt password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user securely
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (name, email, phone, password)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $phone,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful!";

                // Clear form values after successful registration
                $name = "";
                $email = "";
                $phone = "";

            } else {

                $message = "Registration failed.";

            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Register - CarRental</title>

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

        <a href="login.php">Login</a>

    </div>

</nav>


<!-- Registration Form -->

<div class="form-container">

    <h1>Create Account</h1>


    <?php if ($message != "") { ?>

        <p class="message">

            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>

        </p>

    <?php } ?>


    <form method="POST">

        <label>Name</label>

        <input
            type="text"
            name="name"
            required
            maxlength="100"
            value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
        >


        <label>Email</label>

        <input
            type="email"
            name="email"
            required
            maxlength="100"
            value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
        >


        <label>Phone</label>

        <input
            type="text"
            name="phone"
            required
            maxlength="10"
            pattern="[0-9]{10}"
            value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            required
            minlength="6"
        >


        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm_password"
            required
            minlength="6"
        >


        <button type="submit" class="btn">

            Register

        </button>

    </form>


    <p>

        Already have an account?

        <a href="login.php">
            Login here
        </a>

    </p>

</div>


</body>

</html>