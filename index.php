<?php
session_start();
require_once __DIR__ . '/config/db.php';

function getCarImage(array $car): string
{
    $imageDir = __DIR__ . '/images/';

    $carName = strtolower(trim((string)($car['car_name'] ?? '')));
    $brand = strtolower(trim((string)($car['brand'] ?? '')));
    $model = strtolower(trim((string)($car['model'] ?? '')));

    /*
     * 1. FIRST USE THE IMAGE SAVED IN DATABASE
     */
    $dbImage = basename(trim((string)($car['image'] ?? '')));

    if ($dbImage !== '' && is_file($imageDir . $dbImage)) {
        return 'images/' . $dbImage;
    }

    /*
     * 2. CAR NAME -> IMAGE
     */
    $imageMap = [
        'thar' => 'thar.jpg',
        'innova' => 'innova.jpg',
        'endeavour' => 'endeavour.jpg',
        'fortuner' => 'fortuner.jpg',
        'creta' => 'creta.jpg',
        'venue' => 'venue.jpg',
        'swift' => 'swift.jpg',
        'city' => 'city.jpg',
        'nexon' => 'nexon.jpg',
        'mercedes c-class' => 'mercedes.jpg',
        'mercedes c class' => 'mercedes.jpg',
        'c-class' => 'mercedes.jpg',
        'c class' => 'mercedes.jpg',
        'm340i' => 'm340i.jpg',
        'bmw m340i' => 'm340i.jpg',
        'urus' => 'urus.jpg'
    ];

    if (isset($imageMap[$carName])) {
        $file = $imageMap[$carName];

        if (is_file($imageDir . $file)) {
            return 'images/' . $file;
        }
    }

    /*
     * 3. SEARCH USING CAR NAME + BRAND + MODEL
     */
    $search = $carName . ' ' . $brand . ' ' . $model;

    $aliases = [
        'thar' => 'thar.jpg',
        'innova' => 'innova.jpg',
        'endeavour' => 'endeavour.jpg',
        'fortuner' => 'fortuner.jpg',
        'creta' => 'creta.jpg',
        'venue' => 'venue.jpg',
        'swift' => 'swift.jpg',
        'city' => 'city.jpg',
        'nexon' => 'nexon.jpg',
        'mercedes' => 'mercedes.jpg',
        'c-class' => 'mercedes.jpg',
        'c class' => 'mercedes.jpg',
        'm340i' => 'm340i.jpg',
        'bmw m340i' => 'm340i.jpg',
        'urus' => 'urus.jpg'
    ];

    foreach ($aliases as $keyword => $file) {
        if (
            strpos($search, $keyword) !== false &&
            is_file($imageDir . $file)
        ) {
            return 'images/' . $file;
        }
    }

    /*
     * 4. AUTOMATICALLY SEARCH IMAGE FILES
     */
    $cleanName = preg_replace('/[^a-z0-9]+/', '', $carName);

    if ($cleanName !== '') {
        $files = glob($imageDir . '*');

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            $fileName = strtolower(pathinfo($file, PATHINFO_FILENAME));
            $cleanFileName = preg_replace('/[^a-z0-9]+/', '', $fileName);

            if (
                $cleanFileName === $cleanName ||
                strpos($cleanFileName, $cleanName) !== false ||
                strpos($cleanName, $cleanFileName) !== false
            ) {
                return 'images/' . basename($file);
            }
        }
    }

    /*
     * 5. DO NOT USE BMW AS FALLBACK
     *
     * Return an empty value.
     * JavaScript below will create a neutral placeholder.
     */
    return '';
}

/*
 * GET FEATURED CARS
 */
$featured = [];

$result = mysqli_query(
    $conn,
    "SELECT * FROM cars ORDER BY car_id DESC LIMIT 6"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured[] = $row;
    }
}

/*
 * TOTAL CARS
 */
$totalCars = 0;

$countResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM cars"
);

if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalCars = (int)$countRow['total'];
}

/*
 * CAR TYPES
 */
$types = [];

foreach ($featured as $c) {
    $t = ucfirst(
        strtolower(
            trim(
                $c['car_type'] ?? ''
            )
        )
    );

    if ($t !== '') {
        $types[$t] = ($types[$t] ?? 0) + 1;
    }
}

ksort($types);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Drive-Me | Premium Car Rental</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >
</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar modern-nav">

    <a
        class="logo"
        href="index.php"
    >
        <span>🚘</span>
        Car<b>Rental</b>
    </a>

    <div class="nav-links">

        <a
            class="active"
            href="index.php"
        >
            Home
        </a>

        <a href="cars.php">
            Cars
        </a>

        <a href="#about">
            About
        </a>

        <a href="#contact">
            Contact
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="client/dashboard.php">
                Dashboard
            </a>

            <a
                href="logout.php"
                class="nav-register"
            >
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

            <a
                href="register.php"
                class="nav-register"
            >
                Register
            </a>

        <?php endif; ?>

    </div>

</nav>


<!-- ================= HERO ================= -->

<section class="hero upgraded-hero home-hero">

    <div class="hero-overlay"></div>

    <div class="home-hero-content">

        <p class="hero-small">
            SIMPLE &bull; FAST &bull; RELIABLE
        </p>

        <h1>
            Rent Your
            <span>Dream Car</span>
        </h1>

        <p class="hero-description">
            Find the perfect car for your journey.
            Affordable prices, easy booking and a wide range
            of vehicles.
        </p>

        <a
            href="cars.php"
            class="hero-browse-btn"
        >
            🚘 Browse Cars &nbsp;&rsaquo;
        </a>

    </div>


    <div class="hero-features">

        <div class="hf-item">

            <span class="hf-icon">
                🏷️
            </span>

            <div>
                <strong>Best Prices</strong>
                <small>Great deals, every day</small>
            </div>

        </div>


        <div class="hf-item">

            <span class="hf-icon">
                🛡️
            </span>

            <div>
                <strong>Verified Cars</strong>
                <small>Safe &amp; reliable</small>
            </div>

        </div>


        <div class="hf-item">

            <span class="hf-icon">
                📅
            </span>

            <div>
                <strong>Easy Booking</strong>
                <small>Quick &amp; hassle-free</small>
            </div>

        </div>


        <div class="hf-item">

            <span class="hf-icon">
                🎧
            </span>

            <div>
                <strong>24/7 Support</strong>
                <small>We're always here</small>
            </div>

        </div>

    </div>

</section>


<!-- ================= CHOOSE CAR ================= -->

<section class="choose-car">

    <div class="choose-box">

        <div class="choose-head">

            <div>

                <h2>
                    Choose Your Car
                </h2>

                <p>
                    Pick a category or search by name
                    &mdash; results update instantly.
                </p>

            </div>

            <a
                href="cars.php"
                class="choose-viewall"
            >
                View All Cars &rarr;
            </a>

        </div>


        <div class="filter-row">

            <input
                type="text"
                id="carSearch"
                placeholder="🔍 Search car, brand or model..."
                autocomplete="off"
            >

            <select id="carSort">

                <option value="new">
                    Newest
                </option>

                <option value="low">
                    Price: Low to High
                </option>

                <option value="high">
                    Price: High to Low
                </option>

            </select>

        </div>


        <!-- CATEGORY CARDS -->

        <div class="category-grid">

            <button
                type="button"
                class="category-card cat"
                data-type="sedan"
                data-label="Sedan"
            >

                <img
                    src="images/cat-sedan.png"
                    alt="Sedan"
                >

                <strong>
                    Sedan
                </strong>

                <small>
                    Comfort &amp; Style
                </small>

            </button>


            <button
                type="button"
                class="category-card cat"
                data-type="suv"
                data-label="SUV"
            >

                <img
                    src="images/cat-suv.png"
                    alt="SUV"
                >

                <strong>
                    SUV
                </strong>

                <small>
                    Space &amp; Power
                </small>

            </button>


            <button
                type="button"
                class="category-card cat"
                data-type="bike,2 wheeler,2-wheeler,two wheeler,scooter,motorcycle"
                data-label="2 Wheeler"
            >

                <img
                    src="images/cat-bike.png"
                    alt="2 Wheeler"
                >

                <strong>
                    2 Wheeler
                </strong>

                <small>
                    Freedom on Wheels
                </small>

            </button>


            <button
                type="button"
                class="category-card cat"
                data-type="sports,sport"
                data-label="Sports"
            >

                <img
                    src="images/cat-sports.png"
                    alt="Sports"
                >

                <strong>
                    Sports
                </strong>

                <small>
                    Thrill &amp; Performance
                </small>

            </button>


            <button
                type="button"
                class="category-card cat"
                data-type="luxury"
                data-label="Luxury"
            >

                <img
                    src="images/cat-luxury.png"
                    alt="Luxury"
                >

                <strong>
                    Luxury
                </strong>

                <small>
                    Premium Experience
                </small>

            </button>

        </div>


        <!-- TYPE CHIPS -->

        <div
            class="chip-row"
            id="chipRow"
        >

            <button
                type="button"
                class="chip active"
                data-type="all"
            >
                All
                <em>
                    <?php echo count($featured); ?>
                </em>
            </button>


            <?php
            foreach ($types as $t => $n):

                if (
                    in_array(
                        strtolower($t),
                        ['sedan', 'suv']
                    )
                ) {
                    continue;
                }
            ?>

                <button
                    type="button"
                    class="chip"
                    data-type="<?php echo htmlspecialchars(strtolower($t)); ?>"
                >

                    <?php echo htmlspecialchars($t); ?>

                    <em>
                        <?php echo $n; ?>
                    </em>

                </button>

            <?php endforeach; ?>

        </div>

    </div>

</section>


<!-- ================= POPULAR CARS ================= -->

<section
    class="featured-section"
    id="cars"
>

    <div class="section-heading left-heading">

        <div>

            <p>
                OUR COLLECTION
            </p>

            <h2>
                Popular Cars
            </h2>

        </div>


        <a
            href="cars.php"
            class="view-all"
        >
            View All Cars →
        </a>

    </div>


    <div
        class="featured-grid"
        id="carGrid"
    >

        <?php if ($featured): ?>

            <?php foreach ($featured as $car): ?>

                <?php

                $carImage = getCarImage($car);

                $carName = htmlspecialchars(
                    $car['car_name'] ?? ''
                );

                $carType = htmlspecialchars(
                    $car['car_type'] ?? ''
                );

                $fuelType = htmlspecialchars(
                    $car['fuel_type'] ?? ''
                );

                $brand = htmlspecialchars(
                    $car['brand'] ?? ''
                );

                $model = htmlspecialchars(
                    $car['model'] ?? ''
                );

                $seats = (int)(
                    $car['seats'] ?? 0
                );

                $price = (float)(
                    $car['price_per_day'] ?? 0
                );

                $carId = (int)(
                    $car['car_id'] ?? 0
                );

                $searchText = strtolower(
                    ($car['car_name'] ?? '') . ' ' .
                    ($car['brand'] ?? '') . ' ' .
                    ($car['model'] ?? '')
                );

                ?>

                <article
                    class="premium-car-card reveal"
                    data-type="<?php echo htmlspecialchars(strtolower(trim($car['car_type'] ?? ''))); ?>"
                    data-price="<?php echo $price; ?>"
                    data-order="<?php echo $carId; ?>"
                    data-text="<?php echo htmlspecialchars($searchText); ?>"
                >

                    <!-- CAR IMAGE -->

                    <div class="premium-car-image">

                        <?php if ($carImage !== ''): ?>

                            <img
                                src="<?php echo htmlspecialchars($carImage); ?>"
                                alt="<?php echo $carName; ?>"
                                onerror="showCarPlaceholder(this);"
                            >

                        <?php else: ?>

                            <div class="car-image-placeholder">

                                <span>🚘</span>

                                <small>
                                    Image unavailable
                                </small>

                            </div>

                        <?php endif; ?>


                        <span class="status-pill available">
                            Available
                        </span>

                    </div>


                    <!-- CAR DETAILS -->

                    <div class="premium-car-content">

                        <div class="car-topline">

                            <span>
                                <?php echo $carType; ?>
                            </span>

                            <span>
                                <?php echo $fuelType; ?>
                            </span>

                        </div>


                        <h3>
                            <?php echo $carName; ?>
                        </h3>


                        <p>
                            <?php echo $brand . ' ' . $model; ?>

                            •
                            
                            <?php echo $seats; ?>
                            Seats
                        </p>


                        <div class="price-row">

                            <strong>
                                ₹<?php echo number_format($price, 0); ?>
                            </strong>

                            <span>
                                / day
                            </span>

                            <a
                                href="car_details.php?car_id=<?php echo $carId; ?>"
                            >
                                View →
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-state">

                No cars are available yet.
                Add cars from the admin panel.

            </div>

        <?php endif; ?>

    </div>


    <div
        class="empty-state"
        id="noResults"
        style="display:none"
    >
        No cars match your selection right now.
        Try another category.
    </div>

</section>


<!-- ================= STATS ================= -->

<section class="stats-band">

    <div class="stat">

        <strong
            data-count="<?php echo $totalCars; ?>"
        >
            0
        </strong>

        <span>
            Cars Available
        </span>

    </div>


    <div class="stat">

        <strong
            data-count="<?php echo max(count($types), 1); ?>"
        >
            0
        </strong>

        <span>
            Categories
        </span>

    </div>


    <div class="stat">

        <strong data-count="24">
            0
        </strong>

        <span>
            Hours Support (24/7)
        </span>

    </div>


    <div class="stat">

        <strong
            data-count="100"
            data-suffix="%"
        >
            0
        </strong>

        <span>
            Verified Cars
        </span>

    </div>

</section>


<!-- ================= ABOUT ================= -->

<section
    class="about upgraded-about"
    id="about"
>

    <div class="section-heading">

        <p>
            WHY DRIVE-ME
        </p>

        <h2>
            Everything You Need for a Better Journey
        </h2>

        <span>
            Simple booking, transparent pricing and
            a vehicle for every kind of trip.
        </span>

    </div>


    <div class="features upgraded-features">

        <div class="feature reveal">

            <div class="feature-icon">
                🚗
            </div>

            <h3>
                Wide Range
            </h3>

            <p>
                Choose from different cars, types,
                fuel options and price ranges.
            </p>

        </div>


        <div class="feature reveal">

            <div class="feature-icon">
                💰
            </div>

            <h3>
                Clear Pricing
            </h3>

            <p>
                Daily rental pricing makes it easy
                to understand your total cost.
            </p>

        </div>


        <div class="feature reveal">

            <div class="feature-icon">
                ⚡
            </div>

            <h3>
                Easy Booking
            </h3>

            <p>
                Select a car, choose dates and
                confirm your booking quickly.
            </p>

        </div>


        <div class="feature reveal">

            <div class="feature-icon">
                🔒
            </div>

            <h3>
                Secure Login
            </h3>

            <p>
                User accounts and passwords are
                handled with secure PHP practices.
            </p>

        </div>

    </div>

</section>


<!-- ================= HOW IT WORKS ================= -->

<section class="how-it-works upgraded-how">

    <div class="section-heading">

        <p>
            HOW IT WORKS
        </p>

        <h2>
            Rent in 3 Simple Steps
        </h2>

    </div>


    <div class="steps">

        <div class="step reveal">

            <div class="step-number">
                01
            </div>

            <h3>
                Choose a Car
            </h3>

            <p>
                Browse available vehicles and
                open the car you like.
            </p>

        </div>


        <div class="step reveal">

            <div class="step-number">
                02
            </div>

            <h3>
                Select Dates
            </h3>

            <p>
                Choose your pickup and return
                dates for your journey.
            </p>

        </div>


        <div class="step reveal">

            <div class="step-number">
                03
            </div>

            <h3>
                Confirm Booking
            </h3>

            <p>
                Review the rental amount and
                submit your booking.
            </p>

        </div>

    </div>

</section>


<!-- ================= CTA ================= -->

<section class="cta upgraded-cta">

    <div>

        <span>
            READY WHEN YOU ARE
        </span>

        <h2>
            Your next journey starts here.
        </h2>

        <p>
            Find a car that fits your trip,
            your style and your budget.
        </p>

        <a
            href="cars.php"
            class="btn"
        >
            Explore Cars →
        </a>

    </div>

</section>


<!-- ================= FOOTER ================= -->

<footer id="contact">

    <div class="footer-content">

        <div>

            <h3>
                🚘 Drive-Me
            </h3>

            <p>
                Easy, fast and reliable car rental
                for your next journey.
            </p>

        </div>


        <div>

            <h4>
                Quick Links
            </h4>

            <a href="index.php">
                Home
            </a>

            <a href="cars.php">
                Cars
            </a>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

        </div>


        <div>

            <h4>
                Contact
            </h4>

            <p>
                📧 support-drive-me@gmail.com
            </p>

            <p>
                📞 +91 98765 43210
            </p>

        </div>

    </div>


    <div class="footer-bottom">

        <p>
            © 2026 Drive-Me. All Rights Reserved.
        </p>

    </div>

</footer>


<!-- ================= IMAGE FALLBACK ================= -->

<script>

function showCarPlaceholder(img) {

    img.onerror = null;

    img.style.display = "none";

    const parent = img.parentElement;

    const placeholder = document.createElement("div");

    placeholder.className = "car-image-placeholder";

    placeholder.innerHTML =
        '<span>🚘</span>' +
        '<small>Image unavailable</small>';

    parent.insertBefore(
        placeholder,
        img
    );
}

</script>


<!-- ================= HOME JAVASCRIPT ================= -->

<script src="js/home.js"></script>

</body>
</html>
