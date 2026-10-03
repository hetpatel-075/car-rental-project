<?php

require_once __DIR__ . "/session.php";
include "../config/db.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   CREATE CSRF TOKEN
========================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


/* =========================
   UPDATE BOOKING STATUS
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid security token.");
    }

    $booking_id = isset($_POST['booking_id'])
        ? intval($_POST['booking_id'])
        : 0;

    $new_status = isset($_POST['booking_status'])
        ? trim($_POST['booking_status'])
        : '';

    $allowed_statuses = array(
        'Pending',
        'Confirmed',
        'Cancelled'
    );

    if (
        $booking_id > 0 &&
        in_array($new_status, $allowed_statuses, true)
    ) {

        $update_sql = "UPDATE bookings
                       SET booking_status = ?
                       WHERE booking_id = ?";

        $update_stmt = mysqli_prepare($conn, $update_sql);

        if ($update_stmt) {

            mysqli_stmt_bind_param(
                $update_stmt,
                "si",
                $new_status,
                $booking_id
            );

            mysqli_stmt_execute($update_stmt);

            mysqli_stmt_close($update_stmt);

            header("Location: bookings.php?updated=1");
            exit();
        }
    }
}


/* =========================
   SEARCH AND FILTER
========================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$status_filter = isset($_GET['status'])
    ? trim($_GET['status'])
    : '';


/* =========================
   BOOKING COUNTS
========================= */

$total_bookings_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM bookings"
);

$total_bookings_data = mysqli_fetch_assoc($total_bookings_result);
$total_bookings = (int)$total_bookings_data['total'];


$pending_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Pending'"
);

$pending_data = mysqli_fetch_assoc($pending_result);
$pending_bookings = (int)$pending_data['total'];


$confirmed_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Confirmed'"
);

$confirmed_data = mysqli_fetch_assoc($confirmed_result);
$confirmed_bookings = (int)$confirmed_data['total'];


$cancelled_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Cancelled'"
);

$cancelled_data = mysqli_fetch_assoc($cancelled_result);
$cancelled_bookings = (int)$cancelled_data['total'];


/* =========================
   FETCH BOOKINGS
========================= */

$sql = "SELECT
    bookings.booking_id,
    bookings.pickup_date,
    bookings.return_date,
    bookings.total_days,
    bookings.total_amount,
    bookings.booking_status,
    bookings.created_at,

    users.name AS user_name,
    users.email AS user_email,
    users.phone AS user_phone,

    cars.car_name,
    cars.brand,
    cars.model

FROM bookings

INNER JOIN users
    ON bookings.user_id = users.user_id

INNER JOIN cars
    ON bookings.car_id = cars.car_id

WHERE 1=1";


$params = array();
$types = "";


/* =========================
   SEARCH
========================= */

if ($search != '') {

    $sql .= " AND (
        users.name LIKE ?
        OR users.email LIKE ?
        OR cars.car_name LIKE ?
        OR cars.brand LIKE ?
        OR bookings.booking_id LIKE ?
    )";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sssss";
}


/* =========================
   STATUS FILTER
========================= */

if ($status_filter != '') {

    $sql .= " AND bookings.booking_status = ?";

    $params[] = $status_filter;

    $types .= "s";
}


/* =========================
   ORDER
========================= */

$sql .= " ORDER BY bookings.created_at DESC";


/* =========================
   PREPARE QUERY
========================= */

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($params)) {

        /*
         * mysqli_stmt_bind_param requires parameters
         * to be passed by reference.
         */

        $bind_names = array();
        $bind_names[] = $types;

        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }

        call_user_func_array(
            array($stmt, 'bind_param'),
            $bind_names
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = false;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Bookings - Admin</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #1e293b;
            color: white;
            padding-top: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 14px 25px;
            font-size: 15px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #334155;
            color: white;
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .top-bar h1 {
            font-size: 28px;
            color: #1e293b;
        }

        .admin-name {
            background: white;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 28px;
            font-weight: bold;
            color: #1e293b;
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .search-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-form input,
        .search-form select {
            padding: 11px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        .search-form input {
            flex: 1;
            min-width: 250px;
        }

        .search-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .reset-btn {
            background: #64748b;
            color: white;
            text-decoration: none;
            padding: 11px 20px;
            border-radius: 6px;
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .booking-id {
            font-weight: bold;
            color: #2563eb;
        }

        .user-info strong,
        .car-info strong {
            display: block;
            margin-bottom: 4px;
        }

        .user-info small,
        .car-info small {
            color: #64748b;
        }

        .date-info {
            line-height: 1.7;
        }

        .amount {
            font-weight: bold;
            color: #16a34a;
        }

        .status-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .status-form select {
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background: white;
        }

        .update-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .update-btn:hover {
            background: #1d4ed8;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }

        @media (max-width: 1000px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 768px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                padding: 20px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

        }

        @media (max-width: 600px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
            }

            .stats {
                grid-template-columns: 1fr;
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

    <a href="dashboard.php">
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

    <a href="bookings.php" class="active">
        📅 Bookings
    </a>

    <a href="logout.php">
        🚪 Logout
    </a>

</div>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main">


    <!-- TOP BAR -->

    <div class="top-bar">

        <h1>
            Manage Bookings
        </h1>

        <div class="admin-name">

            👤
            <?php
            echo htmlspecialchars(
                isset($_SESSION['admin_username'])
                    ? $_SESSION['admin_username']
                    : 'Admin'
            );
            ?>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['updated'])) { ?>

        <div class="success-message">
            ✅ Booking status updated successfully.
        </div>

    <?php } ?>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">

        <div class="stat-card">

            <h3>
                Total Bookings
            </h3>

            <p>
                <?php echo $total_bookings; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Pending
            </h3>

            <p>
                <?php echo $pending_bookings; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Confirmed
            </h3>

            <p>
                <?php echo $confirmed_bookings; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Cancelled
            </h3>

            <p>
                <?php echo $cancelled_bookings; ?>
            </p>

        </div>

    </div>


    <!-- =========================
         SEARCH / FILTER
    ========================= -->

    <div class="search-box">

        <form
            method="GET"
            action="bookings.php"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Search by user, email, car or booking ID..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <select name="status">

                <option value="">
                    All Status
                </option>

                <option
                    value="Pending"
                    <?php
                    if ($status_filter == 'Pending') {
                        echo 'selected';
                    }
                    ?>
                >
                    Pending
                </option>

                <option
                    value="Confirmed"
                    <?php
                    if ($status_filter == 'Confirmed') {
                        echo 'selected';
                    }
                    ?>
                >
                    Confirmed
                </option>

                <option
                    value="Cancelled"
                    <?php
                    if ($status_filter == 'Cancelled') {
                        echo 'selected';
                    }
                    ?>
                >
                    Cancelled
                </option>

            </select>


            <button
                type="submit"
                class="search-btn"
            >
                🔍 Search
            </button>


            <a
                href="bookings.php"
                class="reset-btn"
            >
                Reset
            </a>

        </form>

    </div>


    <!-- =========================
         BOOKINGS TABLE
    ========================= -->

    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>
                        Booking ID
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
                        Days
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Booked On
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php

                if ($result && mysqli_num_rows($result) > 0) {

                    while ($booking = mysqli_fetch_assoc($result)) {

                ?>

                    <tr>


                        <!-- BOOKING ID -->

                        <td>

                            <span class="booking-id">

                                #
                                <?php
                                echo (int)$booking['booking_id'];
                                ?>

                            </span>

                        </td>


                        <!-- CUSTOMER -->

                        <td>

                            <div class="user-info">

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['user_name']
                                    );
                                    ?>
                                </strong>

                                <small>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['user_email']
                                    );
                                    ?>
                                </small>

                                <br>

                                <small>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['user_phone']
                                    );
                                    ?>
                                </small>

                            </div>

                        </td>


                        <!-- CAR -->

                        <td>

                            <div class="car-info">

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['car_name']
                                    );
                                    ?>
                                </strong>

                                <small>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking['brand']
                                    );
                                    ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking['model']
                                    );
                                    ?>

                                </small>

                            </div>

                        </td>


                        <!-- DATES -->

                        <td>

                            <div class="date-info">

                                <strong>
                                    Pickup:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $booking['pickup_date']
                                );
                                ?>

                                <br>

                                <strong>
                                    Return:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $booking['return_date']
                                );
                                ?>

                            </div>

                        </td>


                        <!-- DAYS -->

                        <td>

                            <?php
                            echo (int)$booking['total_days'];
                            ?>

                        </td>


                        <!-- AMOUNT -->

                        <td>

                            <span class="amount">

                                ₹
                                <?php
                                echo number_format(
                                    (float)$booking['total_amount'],
                                    2
                                );
                                ?>

                            </span>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <form
                                method="POST"
                                action="bookings.php"
                                class="status-form"
                            >

                                <input
                                    type="hidden"
                                    name="booking_id"
                                    value="<?php
                                    echo (int)$booking['booking_id'];
                                    ?>"
                                >


                                <!-- CSRF TOKEN -->

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_SESSION['csrf_token']
                                    );
                                    ?>"
                                >


                                <select name="booking_status">

                                    <option
                                        value="Pending"
                                        <?php
                                        if (
                                            $booking['booking_status']
                                            == 'Pending'
                                        ) {
                                            echo 'selected';
                                        }
                                        ?>
                                    >
                                        Pending
                                    </option>


                                    <option
                                        value="Confirmed"
                                        <?php
                                        if (
                                            $booking['booking_status']
                                            == 'Confirmed'
                                        ) {
                                            echo 'selected';
                                        }
                                        ?>
                                    >
                                        Confirmed
                                    </option>


                                    <option
                                        value="Cancelled"
                                        <?php
                                        if (
                                            $booking['booking_status']
                                            == 'Cancelled'
                                        ) {
                                            echo 'selected';
                                        }
                                        ?>
                                    >
                                        Cancelled
                                    </option>

                                </select>


                                <button
                                    type="submit"
                                    class="update-btn"
                                >
                                    Update
                                </button>

                            </form>

                        </td>


                        <!-- CREATED DATE -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $booking['created_at']
                            );
                            ?>

                        </td>


                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="8"
                            class="no-data"
                        >
                            📭 No bookings found.
                        </td>

                    </tr>

                <?php

                }

                ?>

            </tbody>

        </table>

    </div>


</div>


</body>

</html>

<?php

if ($stmt) {
    mysqli_stmt_close($stmt);
}

?>