<?php
include "config/db.php";

$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($car_id <= 0) {
    die("Invalid car selected.");
}

$sql = "SELECT * FROM cars WHERE car_id = $car_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Car not found.");
}

$car = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($car['car_name']); ?> - CarRental
    </title>

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
            width: 86%;
            max-width: 1100px;
            margin: 45px auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .details-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);

            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* Image */

        .image-section {
            background: #eef2f7;
            min-height: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .car-image {
            width: 100%;
            max-height: 430px;
            object-fit: contain;
        }

        .no-image {
            font-size: 100px;
        }

        /* Details */

        .details-section {
            padding: 45px;
        }

        .details-section h1 {
            margin: 0 0 8px;
            font-size: 36px;
        }

        .brand-model {
            color: #777;
            font-size: 17px;
            margin-bottom: 25px;
        }

        .status {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .available {
            background: #dcfce7;
            color: #15803d;
        }

        .unavailable {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Price */

        .price {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 30px;
        }

        .price span {
            font-size: 15px;
            color: #777;
            font-weight: normal;
        }

        /* Information */

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 15px;
        }

        .info-label {
            color: #777;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 16px;
            font-weight: bold;
        }

        /* Buttons */

        .buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 13px 22px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            display: inline-block;
        }

        .book-btn {
            background: #16a34a;
            color: white;
            flex: 1;
        }

        .book-btn:hover {
            background: #15803d;
        }

        .disabled-btn {
            background: #d1d5db;
            color: #666;
            flex: 1;
            cursor: not-allowed;
        }

        .cars-btn {
            background: #e5e7eb;
            color: #222;
        }

        .cars-btn:hover {
            background: #d1d5db;
        }

        /* Responsive */

        @media (max-width: 800px) {

            .details-card {
                grid-template-columns: 1fr;
            }

            .image-section {
                min-height: 300px;
            }

            .details-section {
                padding: 30px;
            }

            .details-section h1 {
                font-size: 28px;
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

            .info-grid {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
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

        <?php if (isset($_SESSION['user_id'])) { ?>

            <span>
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>

            <a href="my_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>

        <?php } else { ?>

            <a href="login.php">Login</a>

        <?php } ?>

    </div>

</nav>


<!-- Main -->

<div class="container">

    <a href="cars.php" class="back-link">
        ← Back to Cars
    </a>


    <div class="details-card">

        <!-- Car Image -->

        <div class="image-section">

            <?php if (!empty($car['image']) && file_exists("images/" . $car['image'])) { ?>

                <img
                    src="images/<?php echo htmlspecialchars($car['image']); ?>"
                    alt="<?php echo htmlspecialchars($car['car_name']); ?>"
                    class="car-image"
                >

            <?php } else { ?>

                <div class="no-image">
                    🚗
                </div>

            <?php } ?>

        </div>


        <!-- Car Details -->

        <div class="details-section">

            <h1>
                <?php echo htmlspecialchars($car['car_name']); ?>
            </h1>

            <div class="brand-model">

                <?php echo htmlspecialchars($car['brand']); ?>

                -

                <?php echo htmlspecialchars($car['model']); ?>

            </div>


            <!-- Status -->

            <?php if (strtolower($car['status']) == 'available') { ?>

                <div class="status available">
                    ● Available
                </div>

            <?php } else { ?>

                <div class="status unavailable">
                    ● <?php echo htmlspecialchars($car['status']); ?>
                </div>

            <?php } ?>


            <!-- Price -->

            <div class="price">

                ₹<?php echo number_format($car['price_per_day'], 2); ?>

                <span>
                    / day
                </span>

            </div>


            <!-- Information -->

            <div class="info-grid">

                <div class="info-box">

                    <div class="info-label">
                        🚙 Car Type
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['car_type']); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        ⛽ Fuel Type
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['fuel_type']); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        👥 Seats
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['seats']); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="info-label">
                        📅 Model
                    </div>

                    <div class="info-value">
                        <?php echo htmlspecialchars($car['model']); ?>
                    </div>

                </div>

            </div>


            <!-- Buttons -->

            <div class="buttons">

                <a href="cars.php" class="btn cars-btn">
                    ← Browse Cars
                </a>


                <?php if (strtolower($car['status']) == 'available') { ?>

                    <a
                        href="booking.php?car_id=<?php echo $car['car_id']; ?>"
                        class="btn book-btn"
                    >
                        📅 Book This Car
                    </a>

                <?php } else { ?>

                    <span class="btn disabled-btn">
                        Not Available
                    </span>

                <?php } ?>

            </div>

        </div>

    </div>

</div>

</body>

</html>