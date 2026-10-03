<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">

    <div class="logo">
        🚗 CarRental
    </div>

    <div class="nav-links">

        <a href="index.php">Home</a>

        <a href="cars.php">Cars</a>

        <a href="index.php#about">About</a>

        <a href="index.php#contact">Contact</a>


        <?php if (isset($_SESSION['user_id'])) { ?>

            <span>
                Welcome, <?php echo $_SESSION['user_name']; ?>
            </span>

            <a href="my_bookings.php">My Bookings</a>

            <a href="logout.php">Logout</a>

        <?php } else { ?>

            <a href="login.php">Login</a>

        <?php } ?>

    </div>

</nav>