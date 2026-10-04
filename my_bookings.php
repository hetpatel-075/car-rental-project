<?php

require_once __DIR__ . "/config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
        ORDER BY bookings.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>

<head>

    <title>My Bookings - CarRental</title>

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

        /* SIDEBAR */

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

        .sidebar .active {
            background-color: #444;
        }


        /* MAIN */

        .main {
            margin-left: 240px;
            padding: 35px;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .main-header h1 {
            font-size: 30px;
        }

        .main-header p {
            color: #666;
            margin-top: 8px;
        }

        .browse-btn {
            background-color: #222;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 6px;
        }

        .browse-btn:hover {
            background-color: #444;
        }


        /* BOOKING CARD */

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
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .booking-details {
            flex: 1;
        }

        .booking-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .booking-title h2 {
            font-size: 21px;
        }

        .booking-id {
            color: #777;
            font-size: 13px;
        }


        /* INFORMATION GRID */

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .info {
            padding: 10px;
            background-color: #f7f8fa;
            border-radius: 6px;
        }

        .info strong {
            display: block;
            font-size: 12px;
            color: #777;
            margin-bottom: 5px;
        }

        .info span {
            font-size: 14px;
        }


        /* STATUS */

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: bold;
        }

        .pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }


        /* AMOUNT */

        .amount {
            font-size: 20px;
            font-weight: bold;
        }


        /* EMPTY */

        .empty {
            background-color: white;
            text-align: center;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin-bottom: 10px;
        }

        .empty p {
            color: #666;
            margin-bottom: 20px;
        }


        /* RESPONSIVE */

        @media (max-width: 900px) {

            .booking-card {
                display: block;
            }

            .car-image {
                width: 100%;
                height: 200px;
                margin-bottom: 20px;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 650px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                padding: 20px;
            }

            .main-header {
                display: block;
            }

            .browse-btn {
                display: inline-block;
                margin-top: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        🚗 CarRental
    </div>

    <a href="client/dashboard.php">
        🏠 Dashboard
    </a>

    <a href="client/profile.php">
        👤 My Profile
    </a>

    <a href="cars.php">
        🚘 Browse Cars
    </a>

    <a href="my_bookings.php" class="active">
        📅 My Bookings
    </a>

    <a href="client/history.php">
        🕘 Booking History
    </a>

    <a href="client/settings.php">
        ⚙️ Settings
    </a>

    <a href="logout.php">
        🚪 Logout
    </a>

</div>


<!-- MAIN CONTENT -->

<div class="main">


    <div class="main-header">

        <div>

            <h1>
                📅 My Bookings
            </h1>

            <p>
                View and manage your car rental bookings.
            </p>

        </div>

        <a href="cars.php" class="browse-btn">
            🚘 Browse Cars
        </a>

    </div>


    <?php if (mysqli_num_rows($result) > 0) { ?>


        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>


            <div class="booking-card">


                <!-- CAR IMAGE -->

                <?php if (!empty($booking['image'])) { ?>

                    <img
                        src="images/<?php echo htmlspecialchars($booking['image']); ?>"
                        class="car-image"
                    >

                <?php } else { ?>

                    <div class="car-image">
                        No Image
                    </div>

                <?php } ?>


                <!-- BOOKING DETAILS -->

                <div class="booking-details">


                    <div class="booking-title">

                        <div>

                            <h2>
                                <?php
                                echo htmlspecialchars(
                                    $booking['car_name']
                                );
                                ?>
                            </h2>

                            <span class="booking-id">

                                Booking ID:
                                #<?php
                                echo htmlspecialchars(
                                    $booking['booking_id']
                                );
                                ?>

                            </span>

                        </div>


                        <?php

                        $status = strtolower(
                            $booking['booking_status']
                        );

                        ?>

                        <span class="status <?php echo $status; ?>">

                            <?php
                            echo htmlspecialchars(
                                $booking['booking_status']
                            );
                            ?>

                        </span>

                    </div>


                    <!-- INFORMATION -->

                    <div class="info-grid">


                        <div class="info">

                            <strong>
                                BRAND
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['brand']
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                MODEL
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['model']
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                PICKUP DATE
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['pickup_date']
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                RETURN DATE
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['return_date']
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                TOTAL DAYS
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['total_days']
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                BOOKING DATE
                            </strong>

                            <span>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $booking['created_at']
                                    )
                                );
                                ?>
                            </span>

                        </div>


                    </div>


                    <br>


                    <div>

                        <span>
                            Booking Amount:
                        </span>

                        <span class="amount">

                            ₹<?php
                            echo number_format(
                                $booking['total_amount'],
                                2
                            );
                            ?>

                        </span>

                    </div>


                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


        <div class="empty">

            <div class="empty-icon">
                🚘
            </div>

            <h2>
                No Bookings Yet
            </h2>

            <p>
                You haven't booked a car yet.
            </p>

            <a href="cars.php" class="browse-btn">
                Browse Available Cars
            </a>

        </div>


    <?php } ?>


</div>

</body>

</html>