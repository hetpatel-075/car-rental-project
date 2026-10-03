<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            bookings.*,
            cars.car_name,
            cars.brand,
            cars.model,
            cars.image
        FROM bookings
        INNER JOIN cars
        ON bookings.car_id = cars.car_id
        WHERE bookings.user_id = $user_id
        AND (
            bookings.return_date < CURDATE()
            OR bookings.booking_status = 'Cancelled'
        )
        ORDER BY bookings.return_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Booking History</title>

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

        /* Booking Card */

        .booking-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .car-image {
            width: 220px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
        }

        .booking-info {
            flex: 1;
        }

        .booking-info h2 {
            margin-bottom: 8px;
        }

        .booking-info p {
            margin: 7px 0;
        }

        .status {
            font-weight: bold;
        }

        .book-again {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 18px;
            background-color: #222;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .book-again:hover {
            background-color: #444;
        }

        .empty {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

    </style>

</head>

<body>

    <?php include "includes/sidebar.php"; ?>


    <!-- Main Content -->

    <div class="main">

        <h1>🕘 Booking History</h1>

        <?php if (mysqli_num_rows($result) > 0) { ?>

            <?php while ($booking = mysqli_fetch_assoc($result)) { ?>

                <div class="booking-card">

                    <?php if (!empty($booking['image'])) { ?>

                        <img
                            src="../images/<?php echo htmlspecialchars($booking['image']); ?>"
                            class="car-image"
                        >

                    <?php } else { ?>

                        <div class="car-image">
                            No Image
                        </div>

                    <?php } ?>


                    <div class="booking-info">

                        <h2>
                            <?php echo htmlspecialchars($booking['car_name']); ?>
                        </h2>

                        <p>
                            <strong>Brand:</strong>
                            <?php echo htmlspecialchars($booking['brand']); ?>
                        </p>

                        <p>
                            <strong>Model:</strong>
                            <?php echo htmlspecialchars($booking['model']); ?>
                        </p>

                        <p>
                            <strong>Pickup Date:</strong>
                            <?php echo htmlspecialchars($booking['pickup_date']); ?>
                        </p>

                        <p>
                            <strong>Return Date:</strong>
                            <?php echo htmlspecialchars($booking['return_date']); ?>
                        </p>

                        <p>
                            <strong>Total Days:</strong>
                            <?php echo htmlspecialchars($booking['total_days']); ?>
                        </p>

                        <p>
                            <strong>Booking Amount:</strong>
                            ₹<?php echo htmlspecialchars($booking['total_amount']); ?>
                        </p>

                        <p class="status">
                            <strong>Status:</strong>
                            <?php echo htmlspecialchars($booking['booking_status']); ?>
                        </p>

                        <a
                            href="../booking.php?car_id=<?php echo $booking['car_id']; ?>"
                            class="book-again"
                        >
                            🔁 Book Again
                        </a>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty">

                <h2>No Booking History</h2>

                <p>You have not completed any bookings yet.</p>

                <br>

                <a href="../cars.php" class="book-again">
                    🚘 Browse Cars
                </a>

            </div>

        <?php } ?>

    </div>

</body>

</html>
