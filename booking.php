<?php
session_start();

echo "<pre>";
print_r($_SESSION);
echo "</pre>";
exit();

include "config/db.php";
// Get car ID
$car_id = isset($_GET['car_id']) ? (int) $_GET['car_id'] : 0;

if ($car_id <= 0) {
    die("Invalid car selected.");
}

// Get car details securely
$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM cars
     WHERE car_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $car_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {

    mysqli_stmt_close($stmt);

    die("Car not found.");
}

$car = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Check availability
if (strtolower($car['status']) != 'available') {
    die("Sorry! This car is currently unavailable.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Car - CarRental</title>

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

        /* Navbar */

        .navbar {
            background: #111827;
            color: white;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #60a5fa;
        }

        /* Main */

        .container {
            width: 88%;
            max-width: 1100px;
            margin: 45px auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            margin: 0 0 8px;
            font-size: 34px;
        }

        .page-title p {
            color: #666;
            margin: 0;
        }

        /* Booking Card */

        .booking-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);

            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* Car Section */

        .car-section {
            background: #eef2f7;
            padding: 35px;
        }

        .car-image {
            width: 100%;
            height: 280px;
            object-fit: contain;
            margin-bottom: 20px;
        }

        .no-image {
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 90px;
            margin-bottom: 20px;
        }

        .car-section h2 {
            margin: 0 0 5px;
            font-size: 27px;
        }

        .brand-model {
            color: #777;
            margin-bottom: 20px;
        }

        .car-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .info-box {
            background: white;
            padding: 12px;
            border-radius: 7px;
            border: 1px solid #e5e7eb;
        }

        .info-label {
            color: #777;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: bold;
            font-size: 14px;
        }

        .price {
            margin-top: 22px;
            font-size: 27px;
            font-weight: bold;
            color: #2563eb;
        }

        .price span {
            font-size: 13px;
            color: #777;
            font-weight: normal;
        }

        /* Form Section */

        .form-section {
            padding: 40px;
        }

        .form-section h2 {
            margin: 0 0 25px;
            font-size: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 15px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }

        /* Summary */

        .summary {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 18px;
            margin: 25px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: #555;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .total-row {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
            color: #111;
            font-size: 20px;
            font-weight: bold;
        }

        .total-amount {
            color: #2563eb;
        }

        /* Buttons */

        .buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 13px 18px;
            border: none;
            border-radius: 7px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }

        .confirm-btn {
            background: #16a34a;
            color: white;
            flex: 1;
        }

        .confirm-btn:hover {
            background: #15803d;
        }

        .back-btn {
            background: #e5e7eb;
            color: #222;
        }

        .back-btn:hover {
            background: #d1d5db;
        }

        .note {
            margin-top: 18px;
            padding: 12px;
            background: #eff6ff;
            border-radius: 7px;
            color: #1d4ed8;
            font-size: 13px;
        }

        /* Responsive */

        @media (max-width: 800px) {

            .booking-card {
                grid-template-columns: 1fr;
            }

            .form-section,
            .car-section {
                padding: 30px;
            }

        }

        @media (max-width: 550px) {

            .container {
                width: 92%;
            }

            .navbar {
                justify-content: center;
                text-align: center;
            }

            .nav-links {
                justify-content: center;
            }

            .car-info {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
            }

            .page-title h1 {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

<!-- Navbar -->

<nav class="navbar">

    <div class="logo">
        🚗 CarRental
    </div>

    <div class="nav-links">

        <a href="index.php">Home</a>

        <a href="cars.php">Cars</a>

        <a href="index.php#about">About</a>

        <a href="index.php#contact">Contact</a>

        <span>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?>
        </span>

        <a href="my_bookings.php">My Bookings</a>

        <a href="logout.php">Logout</a>

    </div>

</nav>


<!-- Page Title -->

<div class="container">

    <div class="page-title">

        <h1>📅 Book Your Car</h1>

        <p>
            Select your pickup and return dates
        </p>

    </div>


    <div class="booking-card">

        <!-- Car Information -->

        <div class="car-section">

            <?php if (!empty($car['image']) && file_exists("images/" . $car['image'])) { ?>

                <img
                    src="images/<?php echo htmlspecialchars($car['image'], ENT_QUOTES, 'UTF-8'); ?>"
                    class="car-image"
                    alt="<?php echo htmlspecialchars($car['car_name'], ENT_QUOTES, 'UTF-8'); ?>"
                >

            <?php } else { ?>

                <div class="no-image">
                    🚗
                </div>

            <?php } ?>


            <h2>
                <?php echo htmlspecialchars($car['car_name'], ENT_QUOTES, 'UTF-8'); ?>
            </h2>

            <div class="brand-model">

                <?php echo htmlspecialchars($car['brand'], ENT_QUOTES, 'UTF-8'); ?>

                -

                <?php echo htmlspecialchars($car['model'], ENT_QUOTES, 'UTF-8'); ?>

            </div>


            <div class="car-info">

                <div class="info-box">

                    <div class="info-label">
                        🚙 Car Type
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['car_type'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        ⛽ Fuel
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['fuel_type'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        👥 Seats
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['seats'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        🟢 Status
                    </div>

                    <div class="info-value">
                        Available
                    </div>

                </div>

            </div>


            <div class="price">

                ₹<?php echo number_format((float) $car['price_per_day'], 2); ?>

                <span>
                    / day
                </span>

            </div>

        </div>


        <!-- Booking Form -->

        <div class="form-section">

            <h2>Booking Details</h2>

            <form
                method="POST"
                action="confirm_booking.php"
                onsubmit="return validateBooking()"
            >

                <input
                    type="hidden"
                    name="car_id"
                    value="<?php echo (int) $car['car_id']; ?>"
                >


                <div class="form-group">

                    <label for="pickup_date">
                        📅 Pickup Date
                    </label>

                    <input
                        type="date"
                        id="pickup_date"
                        name="pickup_date"
                        required
                        onchange="calculateTotal()"
                    >

                </div>


                <div class="form-group">

                    <label for="return_date">
                        📅 Return Date
                    </label>

                    <input
                        type="date"
                        id="return_date"
                        name="return_date"
                        required
                        onchange="calculateTotal()"
                    >

                </div>


                <!-- Booking Summary -->

                <div class="summary">

                    <div class="summary-row">

                        <span>
                            Price per day
                        </span>

                        <strong>
                            ₹<?php echo number_format((float) $car['price_per_day'], 2); ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Total Days
                        </span>

                        <strong id="totalDays">
                            0
                        </strong>

                    </div>


                    <div class="summary-row total-row">

                        <span>
                            Total Amount
                        </span>

                        <span class="total-amount">
                            ₹<span id="totalAmount">0.00</span>
                        </span>

                    </div>

                </div>


                <div class="buttons">

                    <a href="cars.php" class="btn back-btn">
                        ← Back
                    </a>

                    <button
                        type="submit"
                        class="btn confirm-btn"
                    >
                        Confirm Booking
                    </button>

                </div>


                <div class="note">
                    ℹ️ Your booking will be created with
                    <strong>Pending</strong> status until confirmed.
                </div>

            </form>

        </div>

    </div>

</div>


<script>

    // Price from database
    const pricePerDay = <?php echo json_encode((float) $car['price_per_day']); ?>;


    // Set minimum date to today
    const today = new Date();

    const year = today.getFullYear();

    const month = String(today.getMonth() + 1).padStart(2, '0');

    const day = String(today.getDate()).padStart(2, '0');

    const todayString = year + "-" + month + "-" + day;

    document.getElementById("pickup_date").min = todayString;

    document.getElementById("return_date").min = todayString;


    function calculateTotal() {

        const pickup =
            document.getElementById("pickup_date").value;

        const returnDate =
            document.getElementById("return_date").value;

        const totalDaysElement =
            document.getElementById("totalDays");

        const totalAmountElement =
            document.getElementById("totalAmount");


        if (pickup === "" || returnDate === "") {

            totalDaysElement.innerText = "0";

            totalAmountElement.innerText = "0.00";

            return;

        }


        const pickupDate = new Date(pickup);

        const returnDateValue = new Date(returnDate);


        const difference =
            returnDateValue - pickupDate;


        const days =
            Math.ceil(
                difference / (1000 * 60 * 60 * 24)
            );


        if (days > 0) {

            const totalAmount =
                days * pricePerDay;

            totalDaysElement.innerText = days;

            totalAmountElement.innerText =
                totalAmount.toFixed(2);

        } else {

            totalDaysElement.innerText = "0";

            totalAmountElement.innerText = "0.00";

        }

    }


    function validateBooking() {

        const pickup =
            document.getElementById("pickup_date").value;

        const returnDate =
            document.getElementById("return_date").value;


        if (pickup === "" || returnDate === "") {

            alert("Please select both pickup and return dates.");

            return false;

        }


        const pickupDate = new Date(pickup);

        const returnDateValue = new Date(returnDate);


        if (returnDateValue <= pickupDate) {

            alert("Return date must be after pickup date.");

            return false;

        }


        return true;

    }


    // Automatically update return-date minimum
    document.getElementById("pickup_date").addEventListener(
        "change",
        function () {

            document.getElementById("return_date").min =
                this.value;

            calculateTotal();

        }
    );

</script>

</body>

</html>
