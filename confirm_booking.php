<?php

session_start();

include "config/db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Check POST data
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: cars.php");
    exit();
}


// Get data
$user_id = (int) $_SESSION['user_id'];

$car_id = isset($_POST['car_id'])
    ? (int) $_POST['car_id']
    : 0;

$pickup_date = isset($_POST['pickup_date'])
    ? trim($_POST['pickup_date'])
    : '';

$return_date = isset($_POST['return_date'])
    ? trim($_POST['return_date'])
    : '';


// Basic validation
if ($car_id <= 0 || $pickup_date == '' || $return_date == '') {

    die("Invalid booking information.");

}


// Validate dates
$pickup = DateTime::createFromFormat(
    'Y-m-d',
    $pickup_date
);

$return = DateTime::createFromFormat(
    'Y-m-d',
    $return_date
);


if (
    !$pickup ||
    !$return ||
    $pickup->format('Y-m-d') !== $pickup_date ||
    $return->format('Y-m-d') !== $return_date
) {

    die("Invalid date format.");

}


// Pickup date cannot be in the past
$today = new DateTime('today');

if ($pickup < $today) {

    die("Pickup date cannot be in the past.");

}


// Return date must be after pickup date
if ($return <= $pickup) {

    die("Return date must be after pickup date.");

}


// Get car information securely
$car_stmt = mysqli_prepare(
    $conn,
    "SELECT car_id, car_name, brand, model, car_type,
            fuel_type, seats, price_per_day, image, status
     FROM cars
     WHERE car_id = ?"
);

mysqli_stmt_bind_param(
    $car_stmt,
    "i",
    $car_id
);

mysqli_stmt_execute($car_stmt);

$car_result = mysqli_stmt_get_result($car_stmt);


if (
    !$car_result ||
    mysqli_num_rows($car_result) == 0
) {

    mysqli_stmt_close($car_stmt);

    die("Car not found.");

}


$car = mysqli_fetch_assoc($car_result);

mysqli_stmt_close($car_stmt);


// Check car availability
if (strtolower($car['status']) != 'available') {

    die("Sorry! This car is currently unavailable.");

}


// Check for double booking
$check_stmt = mysqli_prepare(
    $conn,
    "SELECT booking_id
     FROM bookings
     WHERE car_id = ?
     AND booking_status != 'Cancelled'
     AND pickup_date < ?
     AND return_date > ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check_stmt,
    "iss",
    $car_id,
    $return_date,
    $pickup_date
);

mysqli_stmt_execute($check_stmt);

$check_result = mysqli_stmt_get_result($check_stmt);


if (mysqli_num_rows($check_result) > 0) {

    mysqli_stmt_close($check_stmt);

    die("
        <div style='
            font-family: Arial;
            text-align:center;
            margin-top:80px;
        '>

            <h2>
                ❌ Sorry! This car is already booked
                for the selected dates.
            </h2>

            <p>
                <a href='cars.php'>
                    ← Back to Cars
                </a>
            </p>

        </div>
    ");

}

mysqli_stmt_close($check_stmt);


// Calculate total days
$interval = $pickup->diff($return);

$total_days = (int) $interval->days;


// Make sure days are valid
if ($total_days <= 0) {

    die("Invalid booking duration.");

}


// Calculate total amount
$price_per_day = (float) $car['price_per_day'];

$total_amount = $total_days * $price_per_day;


// Create booking
$booking_status = "Pending";


// Insert booking securely
$insert_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO bookings
    (
        user_id,
        car_id,
        pickup_date,
        return_date,
        total_days,
        total_amount,
        booking_status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $insert_stmt,
    "iissids",
    $user_id,
    $car_id,
    $pickup_date,
    $return_date,
    $total_days,
    $total_amount,
    $booking_status
);


// Insert booking
if (mysqli_stmt_execute($insert_stmt)) {

    $booking_id = mysqli_insert_id($conn);

    mysqli_stmt_close($insert_stmt);

} else {

    mysqli_stmt_close($insert_stmt);

    die("Booking failed. Please try again.");

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

    <title>Booking Confirmed - CarRental</title>

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

        .container {
            width: 90%;
            max-width: 650px;
            margin: 70px auto;
        }

        .success-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .success-icon {
            font-size: 65px;
            margin-bottom: 15px;
        }

        h1 {
            color: #16a34a;
            margin-bottom: 10px;
        }

        .message {
            color: #666;
            margin-bottom: 30px;
        }

        .booking-info {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            text-align: left;
            margin-bottom: 25px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .row:last-child {
            border-bottom: none;
        }

        .label {
            color: #666;
        }

        .value {
            font-weight: bold;
            text-align: right;
        }

        .status {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 20px;
            background: #fef3c7;
            color: #92400e;
            font-size: 13px;
            font-weight: bold;
        }

        .amount {
            color: #2563eb;
            font-size: 22px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            padding: 13px 20px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }

        .primary {
            background: #2563eb;
            color: white;
        }

        .secondary {
            background: #e5e7eb;
            color: #222;
        }

        .primary:hover {
            background: #1d4ed8;
        }

        .secondary:hover {
            background: #d1d5db;
        }

        @media (max-width: 550px) {

            .container {
                width: 94%;
            }

            .success-card {
                padding: 25px;
            }

            .row {
                flex-direction: column;
                gap: 5px;
            }

            .value {
                text-align: left;
            }

            .buttons {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="success-card">

        <div class="success-icon">
            ✅
        </div>

        <h1>
            Booking Created Successfully!
        </h1>

        <p class="message">
            Your car booking has been submitted.
            It is currently waiting for confirmation.
        </p>


        <div class="booking-info">

            <div class="row">

                <span class="label">
                    Booking ID
                </span>

                <span class="value">
                    #<?php echo (int) $booking_id; ?>
                </span>

            </div>


            <div class="row">

                <span class="label">
                    Car
                </span>

                <span class="value">
                    <?php
                    echo htmlspecialchars(
                        $car['car_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </span>

            </div>


            <div class="row">

                <span class="label">
                    Pickup Date
                </span>

                <span class="value">
                    <?php
                    echo htmlspecialchars(
                        $pickup_date,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </span>

            </div>


            <div class="row">

                <span class="label">
                    Return Date
                </span>

                <span class="value">
                    <?php
                    echo htmlspecialchars(
                        $return_date,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </span>

            </div>


            <div class="row">

                <span class="label">
                    Total Days
                </span>

                <span class="value">
                    <?php echo $total_days; ?> day(s)
                </span>

            </div>


            <div class="row">

                <span class="label">
                    Total Amount
                </span>

                <span class="value amount">
                    ₹<?php
                    echo number_format(
                        $total_amount,
                        2
                    );
                    ?>
                </span>

            </div>


            <div class="row">

                <span class="label">
                    Booking Status
                </span>

                <span class="value">

                    <span class="status">
                        🟡 Pending
                    </span>

                </span>

            </div>

        </div>


        <div class="buttons">

            <a
                href="my_bookings.php"
                class="btn primary"
            >
                📅 My Bookings
            </a>

            <a
                href="cars.php"
                class="btn secondary"
            >
                🚗 Browse Cars
            </a>

        </div>

    </div>

</div>

</body>

</html>