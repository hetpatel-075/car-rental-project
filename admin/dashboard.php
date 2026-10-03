<?php

require_once __DIR__ . "/session.php";
include "../config/db.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


/* =========================
   DASHBOARD STATISTICS
========================= */

// Total users
$user_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

$total_users = 0;

if ($user_query) {
    $user_data = mysqli_fetch_assoc($user_query);
    $total_users = (int)$user_data['total'];
}


// Total cars
$car_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM cars"
);

$total_cars = 0;

if ($car_query) {
    $car_data = mysqli_fetch_assoc($car_query);
    $total_cars = (int)$car_data['total'];
}


// Available cars
$available_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM cars
     WHERE LOWER(status) = 'available'"
);

$available_cars = 0;

if ($available_query) {
    $available_data = mysqli_fetch_assoc($available_query);
    $available_cars = (int)$available_data['total'];
}


// Total bookings
$booking_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM bookings"
);

$total_bookings = 0;

if ($booking_query) {
    $booking_data = mysqli_fetch_assoc($booking_query);
    $total_bookings = (int)$booking_data['total'];
}


// Pending bookings
$pending_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Pending'"
);

$pending_bookings = 0;

if ($pending_query) {
    $pending_data = mysqli_fetch_assoc($pending_query);
    $pending_bookings = (int)$pending_data['total'];
}


// Confirmed bookings
$confirmed_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Confirmed'"
);

$confirmed_bookings = 0;

if ($confirmed_query) {
    $confirmed_data = mysqli_fetch_assoc($confirmed_query);
    $confirmed_bookings = (int)$confirmed_data['total'];
}


// Cancelled bookings
$cancelled_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Cancelled'"
);

$cancelled_bookings = 0;

if ($cancelled_query) {
    $cancelled_data = mysqli_fetch_assoc($cancelled_query);
    $cancelled_bookings = (int)$cancelled_data['total'];
}


// Total revenue
$revenue_query = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM bookings
     WHERE booking_status != 'Cancelled'"
);

$total_revenue = 0;

if ($revenue_query) {
    $revenue_data = mysqli_fetch_assoc($revenue_query);
    $total_revenue = (float)$revenue_data['total'];
}


/* =========================
   RECENT BOOKINGS
========================= */

$recent_query = mysqli_query(
    $conn,
    "SELECT
        bookings.booking_id,
        bookings.pickup_date,
        bookings.return_date,
        bookings.total_amount,
        bookings.booking_status,
        users.name,
        cars.car_name
     FROM bookings
     INNER JOIN users
        ON bookings.user_id = users.user_id
     INNER JOIN cars
        ON bookings.car_id = cars.car_id
     ORDER BY bookings.created_at DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - CarRental</title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #222;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;

            width: 240px;
            height: 100vh;

            background: #111827;
            color: white;

            padding: 25px 15px;

            overflow-y: auto;
        }


        .logo {
            font-size: 24px;
            font-weight: bold;

            padding: 0 12px 30px;
        }


        .admin-title {
            font-size: 12px;
            color: #9ca3af;

            padding: 0 12px;
            margin-bottom: 10px;

            text-transform: uppercase;
            letter-spacing: 1px;
        }


        .sidebar a {
            display: block;

            color: #d1d5db;
            text-decoration: none;

            padding: 13px 14px;
            margin-bottom: 5px;

            border-radius: 7px;

            font-size: 14px;
        }


        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }


        .logout {
            margin-top: 25px;
            border-top: 1px solid #374151;
            padding-top: 15px;
        }


        /* =========================
           MAIN CONTENT
        ========================= */

        .main {
            margin-left: 240px;
            padding: 30px;
        }


        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 30px;
        }


        .topbar h1 {
            margin: 0;
            font-size: 30px;
        }


        .welcome {
            color: #666;
            font-size: 14px;
        }


        /* =========================
           STAT CARDS
        ========================= */

        .stats-grid {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }


        .stat-card {
            background: white;

            padding: 22px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.06);
        }


        .stat-top {
            display: flex;

            justify-content: space-between;
            align-items: center;
        }


        .stat-icon {
            width: 45px;
            height: 45px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #eff6ff;

            font-size: 22px;
        }


        .stat-title {
            color: #777;
            font-size: 13px;

            margin-top: 15px;
        }


        .stat-number {
            font-size: 28px;
            font-weight: bold;

            margin-top: 5px;
        }


        .stat-subtitle {
            color: #777;
            font-size: 12px;

            margin-top: 5px;
        }


        /* =========================
           STATUS CARDS
        ========================= */

        .status-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .status-card {
            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.06);
        }


        .status-card h3 {
            margin: 0 0 10px;

            font-size: 15px;
        }


        .status-number {
            font-size: 28px;
            font-weight: bold;
        }


        .pending {
            color: #d97706;
        }


        .confirmed {
            color: #16a34a;
        }


        .cancelled {
            color: #dc2626;
        }


        /* =========================
           CONTENT GRID
        ========================= */

        .content-grid {
            display: grid;

            grid-template-columns:
                2fr 1fr;

            gap: 25px;
        }


        .panel {
            background: white;

            border-radius: 12px;

            padding: 22px;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.06);
        }


        .panel-header {
            display: flex;

            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }


        .panel-header h2 {
            margin: 0;
            font-size: 20px;
        }


        .view-link {
            color: #2563eb;
            text-decoration: none;

            font-size: 13px;
        }


        /* =========================
           TABLE
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;

            min-width: 600px;
        }


        th {
            text-align: left;

            background: #f8fafc;

            color: #666;

            font-size: 12px;

            padding: 12px;
        }


        td {
            padding: 13px 12px;

            border-bottom:
                1px solid #eee;

            font-size: 13px;
        }


        tr:last-child td {
            border-bottom: none;
        }


        .booking-id {
            font-weight: bold;
            color: #2563eb;
        }


        .amount {
            font-weight: bold;
        }


        /* =========================
           STATUS BADGES
        ========================= */

        .badge {
            display: inline-block;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;
        }


        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }


        .badge-confirmed {
            background: #dcfce7;
            color: #15803d;
        }


        .badge-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }


        /* =========================
           QUICK ACTIONS
        ========================= */

        .action-grid {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 12px;
        }


        .action {
            padding: 18px 12px;

            border: 1px solid #e5e7eb;

            border-radius: 9px;

            text-decoration: none;

            color: #222;

            text-align: center;

            font-size: 13px;
        }


        .action:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
        }


        .action-icon {
            display: block;

            font-size: 25px;

            margin-bottom: 7px;
        }


        /* =========================
           SYSTEM SUMMARY
        ========================= */

        .summary-item {
            display: flex;

            justify-content: space-between;

            padding: 13px 0;

            border-bottom:
                1px solid #eee;

            font-size: 14px;
        }


        .summary-item:last-child {
            border-bottom: none;
        }


        .summary-label {
            color: #666;
        }


        .summary-value {
            font-weight: bold;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .stats-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 800px) {

            .sidebar {
                position: relative;

                width: 100%;
                height: auto;

                padding: 15px;
            }


            .sidebar .logo {
                padding-bottom: 15px;
            }


            .main {
                margin-left: 0;
                padding: 20px;
            }


            .sidebar a {
                display: inline-block;
                margin-right: 5px;
            }


            .logout {
                border: none;
                margin-top: 5px;
                padding-top: 0;
            }


            .status-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 550px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }


            .main {
                padding: 15px;
            }


            .topbar {
                align-items: flex-start;
                flex-direction: column;

                gap: 8px;
            }


            .topbar h1 {
                font-size: 25px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="logo">
        🚗 CarRental
    </div>


    <div class="admin-title">
        Admin Panel
    </div>


    <a
        href="dashboard.php"
        class="active"
    >
        📊 Dashboard
    </a>


    <a href="cars.php">
        🚘 Manage Cars
    </a>


    <a href="add_car.php">
        ➕ Add Car
    </a>


    <a href="users.php">
        👥 Users
    </a>


    <a href="bookings.php">
        📅 Bookings
    </a>


    <div class="logout">

        <a href="logout.php">
            🚪 Logout
        </a>

    </div>

</div>


<!-- =========================
     MAIN
========================= -->

<div class="main">


    <!-- Topbar -->

    <div class="topbar">

        <div>

            <h1>
                Admin Dashboard
            </h1>

            <div class="welcome">
                Manage your CarRental system from here.
            </div>

        </div>


        <div class="welcome">

            👤

            <?php

            echo isset($_SESSION['admin_username'])
                ? htmlspecialchars(
                    $_SESSION['admin_username'],
                    ENT_QUOTES,
                    'UTF-8'
                )
                : "Administrator";

            ?>

        </div>

    </div>


    <!-- =========================
         MAIN STATISTICS
    ========================= -->

    <div class="stats-grid">


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <div class="stat-title">
                        TOTAL USERS
                    </div>

                    <div class="stat-number">
                        <?php echo $total_users; ?>
                    </div>

                </div>


                <div class="stat-icon">
                    👥
                </div>

            </div>


            <div class="stat-subtitle">
                Registered customers
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <div class="stat-title">
                        TOTAL CARS
                    </div>

                    <div class="stat-number">
                        <?php echo $total_cars; ?>
                    </div>

                </div>


                <div class="stat-icon">
                    🚘
                </div>

            </div>


            <div class="stat-subtitle">

                <?php echo $available_cars; ?>

                currently available

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <div class="stat-title">
                        TOTAL BOOKINGS
                    </div>

                    <div class="stat-number">
                        <?php echo $total_bookings; ?>
                    </div>

                </div>


                <div class="stat-icon">
                    📅
                </div>

            </div>


            <div class="stat-subtitle">
                All booking records
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <div class="stat-title">
                        TOTAL REVENUE
                    </div>

                    <div class="stat-number">

                        ₹<?php
                        echo number_format(
                            $total_revenue,
                            2
                        );
                        ?>

                    </div>

                </div>


                <div class="stat-icon">
                    💰
                </div>

            </div>


            <div class="stat-subtitle">
                Excluding cancelled bookings
            </div>

        </div>


    </div>


    <!-- =========================
         BOOKING STATUS
    ========================= -->

    <div class="status-grid">


        <div class="status-card">

            <h3>
                🟡 Pending Bookings
            </h3>

            <div class="status-number pending">
                <?php echo $pending_bookings; ?>
            </div>

        </div>


        <div class="status-card">

            <h3>
                🟢 Confirmed Bookings
            </h3>

            <div class="status-number confirmed">
                <?php echo $confirmed_bookings; ?>
            </div>

        </div>


        <div class="status-card">

            <h3>
                🔴 Cancelled Bookings
            </h3>

            <div class="status-number cancelled">
                <?php echo $cancelled_bookings; ?>
            </div>

        </div>


    </div>


    <!-- =========================
         LOWER CONTENT
    ========================= -->

    <div class="content-grid">


        <!-- Recent Bookings -->

        <div class="panel">

            <div class="panel-header">

                <h2>
                    📋 Recent Bookings
                </h2>


                <a
                    href="bookings.php"
                    class="view-link"
                >
                    View All →
                </a>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Car
                            </th>

                            <th>
                                Dates
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php

                        if (
                            $recent_query &&
                            mysqli_num_rows($recent_query) > 0
                        ) {

                        ?>


                            <?php

                            while (
                                $booking =
                                mysqli_fetch_assoc(
                                    $recent_query
                                )
                            ) {

                            ?>


                                <tr>


                                    <td class="booking-id">

                                        #<?php

                                        echo (int)
                                            $booking['booking_id'];

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $booking['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $booking['car_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $booking['pickup_date'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>

                                        <br>

                                        ↓

                                        <br>

                                        <?php

                                        echo htmlspecialchars(
                                            $booking['return_date'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>

                                    </td>


                                    <td class="amount">

                                        ₹<?php

                                        echo number_format(
                                            (float)
                                            $booking['total_amount'],
                                            2
                                        );

                                        ?>

                                    </td>


                                    <td>


                                        <?php

                                        $status = strtolower(
                                            trim(
                                                $booking[
                                                    'booking_status'
                                                ]
                                            )
                                        );


                                        if (
                                            $status == 'pending'
                                        ) {

                                            echo '
                                                <span
                                                    class="badge badge-pending"
                                                >
                                                    Pending
                                                </span>
                                            ';

                                        } elseif (
                                            $status == 'confirmed'
                                        ) {

                                            echo '
                                                <span
                                                    class="badge badge-confirmed"
                                                >
                                                    Confirmed
                                                </span>
                                            ';

                                        } else {

                                            echo '
                                                <span
                                                    class="badge badge-cancelled"
                                                >
                                                    Cancelled
                                                </span>
                                            ';

                                        }

                                        ?>

                                    </td>

                                </tr>


                            <?php

                            }

                            ?>


                        <?php

                        } else {

                        ?>


                            <tr>

                                <td
                                    colspan="6"
                                    style="
                                        text-align:center;
                                        color:#777;
                                    "
                                >
                                    No bookings found.
                                </td>

                            </tr>


                        <?php

                        }

                        ?>


                    </tbody>

                </table>

            </div>

        </div>


        <!-- Right Side -->

        <div>


            <!-- Quick Actions -->

            <div
                class="panel"
                style="margin-bottom:25px;"
            >

                <div class="panel-header">

                    <h2>
                        ⚡ Quick Actions
                    </h2>

                </div>


                <div class="action-grid">


                    <a
                        href="add_car.php"
                        class="action"
                    >

                        <span class="action-icon">
                            ➕
                        </span>

                        Add Car

                    </a>


                    <a
                        href="cars.php"
                        class="action"
                    >

                        <span class="action-icon">
                            🚘
                        </span>

                        Manage Cars

                    </a>


                    <a
                        href="users.php"
                        class="action"
                    >

                        <span class="action-icon">
                            👥
                        </span>

                        Users

                    </a>


                    <a
                        href="bookings.php"
                        class="action"
                    >

                        <span class="action-icon">
                            📅
                        </span>

                        Bookings

                    </a>


                </div>

            </div>


            <!-- System Summary -->

            <div class="panel">

                <div class="panel-header">

                    <h2>
                        📈 System Summary
                    </h2>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Available Cars
                    </span>


                    <span class="summary-value">

                        <?php echo $available_cars; ?>

                        /

                        <?php echo $total_cars; ?>

                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Pending
                    </span>


                    <span class="summary-value pending">

                        <?php echo $pending_bookings; ?>

                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Confirmed
                    </span>


                    <span class="summary-value confirmed">

                        <?php echo $confirmed_bookings; ?>

                    </span>

                </div>


                <div class="summary-item">

                    <span class="summary-label">
                        Cancelled
                    </span>


                    <span class="summary-value cancelled">

                        <?php echo $cancelled_bookings; ?>

                    </span>

                </div>


            </div>


        </div>

    </div>


</div>


</body>

</html>