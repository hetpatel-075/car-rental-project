<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
    die("ERROR: User is not logged in. user_id session missing.");
}

$user_id = (int)$_SESSION['user_id'];

echo "<h2>DEBUG</h2>";
echo "Logged User ID: " . $user_id . "<br><br>";

$sql = "SELECT
            b.booking_id,
            b.user_id,
            b.car_id,
            b.pickup_date,
            b.return_date,
            b.total_days,
            b.total_amount,
            b.booking_status,
            c.car_name,
            c.brand,
            c.model,
            c.image
        FROM bookings b
        LEFT JOIN cars c
        ON b.car_id = c.car_id
        WHERE b.user_id = ?
        ORDER BY b.booking_id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("SQL Prepare Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);

if (!mysqli_stmt_execute($stmt)) {
    die("SQL Execute Error: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

echo "Total Bookings Found: " . mysqli_num_rows($result) . "<hr>";

if (mysqli_num_rows($result) == 0) {

    echo "<h2 style='color:red;'>NO BOOKINGS FOUND</h2>";

    echo "<p>Check your bookings table. The logged user ID is:</p>";
    echo "<h1>" . $user_id . "</h1>";

} else {

    while ($booking = mysqli_fetch_assoc($result)) {

        echo "<div style='
            background:#f5f5f5;
            padding:20px;
            margin:15px;
            border-radius:10px;
        '>";

        echo "<h2>" .
            htmlspecialchars($booking['car_name'] ?? 'Car not found')
            . "</h2>";

        echo "Booking ID: " .
            htmlspecialchars($booking['booking_id'])
            . "<br>";

        echo "User ID: " .
            htmlspecialchars($booking['user_id'])
            . "<br>";

        echo "Car ID: " .
            htmlspecialchars($booking['car_id'])
            . "<br>";

        echo "Pickup: " .
            htmlspecialchars($booking['pickup_date'])
            . "<br>";

        echo "Return: " .
            htmlspecialchars($booking['return_date'])
            . "<br>";

        echo "Days: " .
            htmlspecialchars($booking['total_days'])
            . "<br>";

        echo "Amount: ₹" .
            htmlspecialchars($booking['total_amount'])
            . "<br>";

        echo "Status: " .
            htmlspecialchars($booking['booking_status'])
            . "<br>";

        echo "Image DB value: " .
            htmlspecialchars($booking['image'] ?? 'NULL')
            . "<br>";

        echo "</div>";
    }
}

mysqli_stmt_close($stmt);
?>
