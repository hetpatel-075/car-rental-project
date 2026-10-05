<?php

require_once __DIR__ . "/config/session.php";

include "config/db.php";


// -------------------------
// GET FILTER VALUES
// -------------------------

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$car_type = isset($_GET['car_type'])
    ? trim($_GET['car_type'])
    : '';

$fuel_type = isset($_GET['fuel_type'])
    ? trim($_GET['fuel_type'])
    : '';

$sort = isset($_GET['sort'])
    ? $_GET['sort']
    : '';


// -------------------------
// BUILD QUERY
// -------------------------

$sql = "SELECT * FROM cars WHERE 1=1";

if ($search != '') {

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql .= " AND (
        car_name LIKE '%$search_safe%'
        OR brand LIKE '%$search_safe%'
        OR model LIKE '%$search_safe%'
    )";
}


if ($car_type != '') {

    $car_type_safe = mysqli_real_escape_string(
        $conn,
        $car_type
    );

    $sql .= " AND car_type = '$car_type_safe'";
}


if ($fuel_type != '') {

    $fuel_type_safe = mysqli_real_escape_string(
        $conn,
        $fuel_type
    );

    $sql .= " AND fuel_type = '$fuel_type_safe'";
}


// -------------------------
// SORTING
// -------------------------

if ($sort == 'price_low') {

    $sql .= " ORDER BY price_per_day ASC";

} elseif ($sort == 'price_high') {

    $sql .= " ORDER BY price_per_day DESC";

} elseif ($sort == 'name_az') {

    $sql .= " ORDER BY car_name ASC";

} elseif ($sort == 'name_za') {

    $sql .= " ORDER BY car_name DESC";

} else {

    $sql .= " ORDER BY car_id DESC";
}


$result = mysqli_query($conn, $sql);


// -------------------------
// GET CAR TYPES
// -------------------------

$type_result = mysqli_query(
    $conn,
    "SELECT DISTINCT car_type
     FROM cars
     WHERE car_type IS NOT NULL
     AND car_type != ''
     ORDER BY car_type"
);


// -------------------------
// GET FUEL TYPES
// -------------------------

$fuel_result = mysqli_query(
    $conn,
    "SELECT DISTINCT fuel_type
     FROM cars
     WHERE fuel_type IS NOT NULL
     AND fuel_type != ''
     ORDER BY fuel_type"
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

    <title>Browse Cars - CarRental</title>

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

        /* -------------------------
           NAVBAR
        ------------------------- */

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

        .welcome-text {
            color: #d1d5db;
            font-size: 15px;
        }


        /* -------------------------
           PAGE HEADER
        ------------------------- */

        .page-header {
            text-align: center;
            padding: 45px 20px 30px;
        }

        .page-header h1 {
            margin: 0 0 10px;
            font-size: 36px;
        }

        .page-header p {
            color: #666;
            margin: 0;
        }


        /* -------------------------
           FILTERS
        ------------------------- */

        .filter-box {
            width: 86%;
            max-width: 1200px;
            margin: 0 auto 35px;
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto auto;
            gap: 12px;
            align-items: center;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
        }


        /* -------------------------
           BUTTONS
        ------------------------- */

        .btn {
            border: none;
            padding: 12px 18px;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 14px;
        }

        .search-btn {
            background: #2563eb;
            color: white;
        }

        .search-btn:hover {
            background: #1d4ed8;
        }

        .reset-btn {
            background: #e5e7eb;
            color: #222;
        }

        .reset-btn:hover {
            background: #d1d5db;
        }


        /* -------------------------
           CARS
        ------------------------- */

        .cars-container {
            width: 86%;
            max-width: 1200px;
            margin: auto;
            padding-bottom: 50px;
        }

        .results-text {
            margin-bottom: 20px;
            color: #555;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .car-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 22px rgba(0,0,0,0.12);
        }

        .car-image {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: #e5e7eb;
        }

        .no-image {
            width: 100%;
            height: 210px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }

        .car-content {
            padding: 20px;
        }

        .car-content h2 {
            margin: 0 0 5px;
            font-size: 21px;
        }

        .brand-model {
            color: #777;
            margin-bottom: 15px;
        }

        .car-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 18px;
        }

        .info-item {
            background: #f8fafc;
            padding: 9px;
            border-radius: 6px;
            font-size: 13px;
        }

        .price {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 15px;
        }

        .price span {
            font-size: 13px;
            font-weight: normal;
            color: #777;
        }


        /* -------------------------
           STATUS
        ------------------------- */

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .available {
            background: #dcfce7;
            color: #15803d;
        }

        .unavailable {
            background: #fee2e2;
            color: #dc2626;
        }


        /* -------------------------
           CARD BUTTONS
        ------------------------- */

        .card-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .details-btn {
            background: #e5e7eb;
            color: #222;
        }

        .details-btn:hover {
            background: #d1d5db;
        }

        .book-btn {
            background: #16a34a;
            color: white;
        }

        .book-btn:hover {
            background: #15803d;
        }

        .disabled-btn {
            background: #d1d5db;
            color: #666;
            cursor: not-allowed;
        }


        /* -------------------------
           NO CARS
        ------------------------- */

        .no-cars {
            background: white;
            padding: 50px;
            text-align: center;
            border-radius: 12px;
            color: #666;
        }


        /* -------------------------
           RESPONSIVE
        ------------------------- */

        @media (max-width: 1000px) {

            .filter-form {
                grid-template-columns: 1fr 1fr;
            }

            .cars-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 650px) {

            .navbar {
                justify-content: center;
                text-align: center;
            }

            .nav-links {
                justify-content: center;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .cars-grid {
                grid-template-columns: 1fr;
            }

            .cars-container,
            .filter-box {
                width: 92%;
            }

            .page-header h1 {
                font-size: 28px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <div class="logo">
        🚗 CarRental
    </div>


    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="cars.php">
            Cars
        </a>

        <a href="index.php#about">
            About
        </a>

        <a href="index.php#contact">
            Contact
        </a>


        <?php if (isset($_SESSION['user_id'])) { ?>

            <span class="welcome-text">
                Welcome,
                <?php
                echo htmlspecialchars(
                    $_SESSION['user_name'] ?? 'User',
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </span>


            <a href="my_bookings.php">
                My Bookings
            </a>


            <a href="logout.php">
                Logout
            </a>


        <?php } else { ?>


            <a href="login.php">
                Login
            </a>


        <?php } ?>

    </div>

</nav>


<!-- =========================
     PAGE HEADER
========================= -->

<div class="page-header">

    <h1>
        🚘 Browse Our Cars
    </h1>

    <p>
        Find the perfect car for your next journey
    </p>

</div>


<!-- =========================
     FILTERS
========================= -->

<div class="filter-box">

    <form
        method="GET"
        action="cars.php"
        class="filter-form"
    >

        <input
            type="text"
            name="search"
            placeholder="Search car, brand or model..."
            value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
        >


        <select name="car_type">

            <option value="">
                All Car Types
            </option>


            <?php while ($type = mysqli_fetch_assoc($type_result)) { ?>

                <option
                    value="<?php echo htmlspecialchars($type['car_type'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php
                    if ($car_type == $type['car_type']) {
                        echo 'selected';
                    }
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $type['car_type'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </option>

            <?php } ?>

        </select>


        <select name="fuel_type">

            <option value="">
                All Fuel Types
            </option>


            <?php while ($fuel = mysqli_fetch_assoc($fuel_result)) { ?>

                <option
                    value="<?php echo htmlspecialchars($fuel['fuel_type'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php
                    if ($fuel_type == $fuel['fuel_type']) {
                        echo 'selected';
                    }
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $fuel['fuel_type'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </option>

            <?php } ?>

        </select>


        <select name="sort">

            <option value="">
                Sort By
            </option>


            <option
                value="price_low"
                <?php
                if ($sort == 'price_low') {
                    echo 'selected';
                }
                ?>
            >
                Price: Low to High
            </option>


            <option
                value="price_high"
                <?php
                if ($sort == 'price_high') {
                    echo 'selected';
                }
                ?>
            >
                Price: High to Low
            </option>


            <option
                value="name_az"
                <?php
                if ($sort == 'name_az') {
                    echo 'selected';
                }
                ?>
            >
                Name: A to Z
            </option>


            <option
                value="name_za"
                <?php
                if ($sort == 'name_za') {
                    echo 'selected';
                }
                ?>
            >
                Name: Z to A
            </option>

        </select>


        <button
            type="submit"
            class="btn search-btn"
        >
            🔍 Search
        </button>


        <a
            href="cars.php"
            class="btn reset-btn"
        >
            Reset
        </a>

    </form>

</div>


<!-- =========================
     CARS
========================= -->

<div class="cars-container">

    <?php

    $car_count = mysqli_num_rows($result);

    ?>


    <div class="results-text">

        Showing
        <strong>
            <?php echo $car_count; ?>
        </strong>
        car(s)

    </div>


    <?php if ($car_count > 0) { ?>


        <div class="cars-grid">


            <?php while ($car = mysqli_fetch_assoc($result)) { ?>


                <div class="car-card">


                    <?php
                    $image = trim($car['image'] ?? '');
                    
                    if ($image != '') {
                    
                        // If image is already a full URL (Cloudinary)
                        if (
                            strpos($image, 'http://') === 0 ||
                            strpos($image, 'https://') === 0
                        ) {
                    
                            $image_url = $image;
                    
                        } else {
                    
                            // For old images stored as local filenames
                            $image_url = 'images/' . $image;
                    
                        }
                    ?>
                    
                        <img
                            src="<?php echo htmlspecialchars(
                                $image_url,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            class="car-image"
                            alt="<?php echo htmlspecialchars(
                                $car['car_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >
                    
                    <?php } else { ?>
                    
                        <div class="no-image">
                            🚗
                        </div>
                    
                    <?php } ?>


                    <div class="car-content">


                        <h2>

                            <?php
                            echo htmlspecialchars(
                                $car['car_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </h2>


                        <div class="brand-model">

                            <?php
                            echo htmlspecialchars(
                                $car['brand'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $car['model'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </div>


                        <div class="car-info">


                            <div class="info-item">

                                🚙

                                <?php
                                echo htmlspecialchars(
                                    $car['car_type'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </div>


                            <div class="info-item">

                                ⛽

                                <?php
                                echo htmlspecialchars(
                                    $car['fuel_type'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </div>


                            <div class="info-item">

                                👥

                                <?php
                                echo htmlspecialchars(
                                    $car['seats'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                                Seats

                            </div>


                            <div class="info-item">

                                📅

                                <?php
                                echo htmlspecialchars(
                                    $car['model'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </div>


                        </div>


                        <div class="price">

                            ₹<?php
                            echo number_format(
                                (float) $car['price_per_day'],
                                2
                            );
                            ?>

                            <span>
                                / day
                            </span>

                        </div>


                        <?php
                        if (
                            strtolower($car['status']) == 'available'
                        ) {
                        ?>

                            <div class="status available">
                                ● Available
                            </div>

                        <?php } else { ?>

                            <div class="status unavailable">

                                ●

                                <?php
                                echo htmlspecialchars(
                                    $car['status'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <div class="card-buttons">


                            <a
                                href="car_details.php?car_id=<?php echo (int) $car['car_id']; ?>"
                                class="btn details-btn"
                            >
                                👁 View Details
                            </a>


                            <?php
                            if (
                                strtolower($car['status']) == 'available'
                            ) {
                            ?>

                                <a
                                    href="booking.php?car_id=<?php echo (int) $car['car_id']; ?>"
                                    class="btn book-btn"
                                >
                                    📅 Book Now
                                </a>

                            <?php } else { ?>

                                <span class="btn disabled-btn">
                                    Not Available
                                </span>

                            <?php } ?>


                        </div>


                    </div>

                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <div class="no-cars">

            <h2>
                😔 No Cars Found
            </h2>

            <p>
                Try changing your search or filter options.
            </p>


            <a
                href="cars.php"
                class="btn search-btn"
            >
                View All Cars
            </a>

        </div>


    <?php } ?>


</div>


</body>

</html>
