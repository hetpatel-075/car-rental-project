<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="sidebar">

    <div class="logo">
        🚗 CarRental
    </div>

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="profile.php">
        👤 My Profile
    </a>

    <a href="../cars.php">
        🚘 Browse Cars
    </a>

    <a href="../my_bookings.php">
        📅 My Bookings
    </a>

    <a href="history.php">
        🕘 Booking History
    </a>

    <a href="settings.php">
        ⚙️ Settings
    </a>

    <a href="../logout.php">
        🚪 Logout
    </a>

</div>