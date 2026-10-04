<?php

require_once __DIR__ . "/../config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

/* Total bookings */

$sql_total = "SELECT COUNT(*) AS total 
              FROM bookings 
              WHERE user_id = $user_id";

$result_total = mysqli_query($conn, $sql_total);
$total_bookings = mysqli_fetch_assoc($result_total)['total'];


/* Active bookings */

$sql_active = "SELECT COUNT(*) AS active 
               FROM bookings 
               WHERE user_id = $user_id
               AND booking_status != 'Cancelled'
               AND return_date >= CURDATE()";

$result_active = mysqli_query($conn, $sql_active);
$active_bookings = mysqli_fetch_assoc($result_active)['active'];


/* Completed bookings */

$sql_completed = "SELECT COUNT(*) AS completed 
                  FROM bookings 
                  WHERE user_id = $user_id
                  AND return_date < CURDATE()
                  AND booking_status != 'Cancelled'";

$result_completed = mysqli_query($conn, $sql_completed);
$completed_bookings = mysqli_fetch_assoc($result_completed)['completed'];


/* Total booking amount */

$sql_amount = "SELECT SUM(total_amount) AS amount 
               FROM bookings 
               WHERE user_id = $user_id
               AND booking_status != 'Cancelled'";

$result_amount = mysqli_query($conn, $sql_amount);
$amount_data = mysqli_fetch_assoc($result_amount);

$total_amount = $amount_data['amount'];

if ($total_amount == NULL) {
    $total_amount = 0;
}


/* Recent bookings */

$sql_recent = "SELECT
                    bookings.*,
                    cars.car_name,
                    cars.brand,
                    cars.model
               FROM bookings
               INNER JOIN cars
               ON bookings.car_id = cars.car_id
               WHERE bookings.user_id = $user_id
               ORDER BY bookings.created_at DESC
               LIMIT 5";

$result_recent = mysqli_query($conn, $sql_recent);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Client Dashboard</title>

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


        /* TOP */

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .top h1 {
            font-size: 30px;
        }

        .top p {
            color: #666;
            margin-top: 8px;
        }

        .browse-btn {
            background-color: #222;
            color: white;
            padding: 13px 20px;
            text-decoration: none;
            border-radius: 6px;
        }

        .browse-btn:hover {
            background-color: #444;
        }


        /* STAT CARDS */

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card-icon {
            font-size: 30px;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #666;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .card .number {
            font-size: 28px;
            font-weight: bold;
        }


        /* RECENT BOOKINGS */

        .section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 22px;
        }

        .view-all {
            text-decoration: none;
            color: #222;
            font-weight: bold;
        }


        /* TABLE */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f4f6f9;
            text-align: left;
            padding: 14px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }


        /* STATUS */

        .status {
            padding: 6px 10px;
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


        /* EMPTY */

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }


        /* QUICK ACTIONS */

        .quick-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .action-btn {
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            background-color: #222;
            color: white;
        }

        .action-btn:hover {
            background-color: #444;
        }


        /* RESPONSIVE */

        @media (max-width: 1000px) {

            .cards {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 700px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                padding: 20px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .top {
                display: block;
            }

            .browse-btn {
                display: inline-block;
                margin-top: 15px;
            }

        }

    </style>

</head>

<body>



<?php include "includes/sidebar.php"; ?>


<!-- MAIN -->

<div class="main">


    <!-- TOP SECTION -->

    <div class="top">

        <div>

            <h1>
                Welcome, <?php echo htmlspecialchars($user_name); ?> 👋
            </h1>

            <p>
                Manage your car rentals and bookings from your dashboard.
            </p>

        </div>

        <a href="../cars.php" class="browse-btn">
            🚘 Browse Cars
        </a>

    </div>


    <!-- STAT CARDS -->

    <div class="cards">


        <div class="card">

            <div class="card-icon">
                📅
            </div>

            <h3>
                Total Bookings
            </h3>

            <div class="number">
                <?php echo $total_bookings; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🚗
            </div>

            <h3>
                Active Bookings
            </h3>

            <div class="number">
                <?php echo $active_bookings; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ✅
            </div>

            <h3>
                Completed
            </h3>

            <div class="number">
                <?php echo $completed_bookings; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                💰
            </div>

            <h3>
                Total Booking Amount
            </h3>

            <div class="number">
                ₹<?php echo number_format($total_amount, 2); ?>
            </div>

        </div>

    </div>


    <!-- RECENT BOOKINGS -->

    <div class="section">

        <div class="section-header">

            <h2>
                🕘 Recent Bookings
            </h2>

            <a href="../my_bookings.php" class="view-all">
                View All →
            </a>

        </div>


        <?php if (mysqli_num_rows($result_recent) > 0) { ?>


            <table>

                <tr>

                    <th>
                        Car
                    </th>

                    <th>
                        Pickup
                    </th>

                    <th>
                        Return
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Status
                    </th>

                </tr>


                <?php while ($booking = mysqli_fetch_assoc($result_recent)) { ?>

                    <tr>

                        <td>

                            <strong>
                                <?php echo htmlspecialchars($booking['car_name']); ?>
                            </strong>

                            <br>

                            <small>
                                <?php echo htmlspecialchars($booking['brand']); ?>
                                <?php echo htmlspecialchars($booking['model']); ?>
                            </small>

                        </td>


                        <td>
                            <?php echo htmlspecialchars($booking['pickup_date']); ?>
                        </td>


                        <td>
                            <?php echo htmlspecialchars($booking['return_date']); ?>
                        </td>


                        <td>
                            ₹<?php echo number_format($booking['total_amount'], 2); ?>
                        </td>


                        <td>

                            <?php

                            $status = strtolower($booking['booking_status']);

                            ?>

                            <span class="status <?php echo $status; ?>">

                                <?php
                                echo htmlspecialchars(
                                    $booking['booking_status']
                                );
                                ?>

                            </span>

                        </td>

                    </tr>

                <?php } ?>


            </table>


        <?php } else { ?>


            <div class="empty">

                <h3>
                    No bookings yet 🚘
                </h3>

                <p>
                    Start by browsing our available cars.
                </p>

                <div class="quick-actions">

                    <a
                        href="../cars.php"
                        class="action-btn"
                    >
                        🚘 Browse Cars
                    </a>

                </div>

            </div>


        <?php } ?>


    </div>


    <!-- QUICK ACTIONS -->

    <div class="quick-actions">

        <a
            href="../cars.php"
            class="action-btn"
        >
            🚘 Browse Cars
        </a>

        <a
            href="../my_bookings.php"
            class="action-btn"
        >
            📅 My Bookings
        </a>

        <a
            href="profile.php"
            class="action-btn"
        >
            👤 My Profile
        </a>

    </div>


</div>

</body>

</html>