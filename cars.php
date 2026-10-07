<?php

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/db.php";


/* =========================
   GET FILTER VALUES
========================= */

$search = trim($_GET['search'] ?? '');
$car_type = trim($_GET['car_type'] ?? '');
$fuel_type = trim($_GET['fuel_type'] ?? '');
$pickup_date = trim($_GET['pickup_date'] ?? '');
$return_date = trim($_GET['return_date'] ?? '');
$sort = trim($_GET['sort'] ?? '');

$date_search = false;
$date_error = '';


/* =========================
   DATE VALIDATION
========================= */

if ($pickup_date !== '' || $return_date !== '') {

    if ($pickup_date === '' || $return_date === '') {

        $date_error =
            "Please select both pickup and return dates.";

    } elseif ($return_date <= $pickup_date) {

        $date_error =
            "Return date must be after pickup date.";

    } else {

        $date_search = true;
    }
}


/* =========================
   BUILD QUERY
========================= */

$sql = "SELECT c.*
        FROM cars c
        WHERE 1=1";


/* =========================
   SEARCH
========================= */

if ($search !== '') {

    $safe =
        mysqli_real_escape_string(
            $conn,
            $search
        );

    $sql .= " AND (
        c.car_name LIKE '%$safe%'
        OR c.brand LIKE '%$safe%'
        OR c.model LIKE '%$safe%'
    )";
}


/* =========================
   CAR TYPE
========================= */

if ($car_type !== '') {

    $safe =
        mysqli_real_escape_string(
            $conn,
            $car_type
        );

    $sql .=
        " AND c.car_type = '$safe'";
}


/* =========================
   FUEL TYPE
========================= */

if ($fuel_type !== '') {

    $safe =
        mysqli_real_escape_string(
            $conn,
            $fuel_type
        );

    $sql .=
        " AND c.fuel_type = '$safe'";
}


/* =========================
   DATE AVAILABILITY
========================= */

if ($date_search) {

    $pickup_safe =
        mysqli_real_escape_string(
            $conn,
            $pickup_date
        );

    $return_safe =
        mysqli_real_escape_string(
            $conn,
            $return_date
        );

    $sql .= " AND LOWER(c.status) = 'available'

        AND NOT EXISTS (

            SELECT 1
            FROM bookings b

            WHERE b.car_id = c.car_id

            AND LOWER(
                COALESCE(
                    b.booking_status,
                    ''
                )
            ) <> 'cancelled'

            AND b.pickup_date < '$return_safe'

            AND b.return_date > '$pickup_safe'
        )";
}


/* =========================
   SORT
========================= */

if ($sort === 'price_low') {

    $sql .=
        " ORDER BY c.price_per_day ASC";

} elseif ($sort === 'price_high') {

    $sql .=
        " ORDER BY c.price_per_day DESC";

} elseif ($sort === 'name_az') {

    $sql .=
        " ORDER BY c.car_name ASC";

} elseif ($sort === 'name_za') {

    $sql .=
        " ORDER BY c.car_name DESC";

} else {

    $sql .=
        " ORDER BY c.car_id DESC";
}


/* =========================
   EXECUTE QUERY
========================= */

$result =
    mysqli_query(
        $conn,
        $sql
    );

if (!$result) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}


/* =========================
   CAR TYPES
========================= */

$type_result =
    mysqli_query(
        $conn,
        "SELECT DISTINCT car_type
         FROM cars
         WHERE car_type IS NOT NULL
         AND car_type != ''
         ORDER BY car_type"
    );


/* =========================
   FUEL TYPES
========================= */

$fuel_result =
    mysqli_query(
        $conn,
        "SELECT DISTINCT fuel_type
         FROM cars
         WHERE fuel_type IS NOT NULL
         AND fuel_type != ''
         ORDER BY fuel_type"
    );


$car_count =
    mysqli_num_rows($result);

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


/* =========================
   NAVBAR
========================= */

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


/* =========================
   PAGE HEADER
========================= */

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


/* =========================
   FILTER BOX
========================= */

.filter-box {
    width: 90%;
    max-width: 1400px;
    margin: 0 auto 35px;
    background: white;
    padding: 22px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}


/* =========================
   SEARCH HELP TEXT
========================= */

.search-help {
    margin-bottom: 16px;
    padding: 13px 16px;
    background: #eff6ff;
    border-radius: 9px;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.search-help strong {
    font-size: 15px;
}

.search-help span {
    color: #64748b;
    font-size: 14px;
}


/* =========================
   FILTER FORM
========================= */

.filter-form {
    display: grid;
    grid-template-columns:
        2fr
        1fr
        1fr
        1.1fr
        1.1fr
        1fr
        auto;

    gap: 12px;
    align-items: center;
}

.filter-form input,
.filter-form select {
    width: 100%;
    height: 54px;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    background: white;
}

.filter-form input:focus,
.filter-form select:focus {
    outline: none;
    border-color: #2563eb;
}


/* =========================
   BUTTONS
========================= */

.btn {
    border: none;
    padding: 12px 18px;
    min-height: 54px;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    text-align: center;
    font-size: 14px;
    white-space: nowrap;
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


/* =========================
   DATE ERROR
========================= */

.date-error {
    margin-top: 15px;
    padding: 12px 15px;
    background: #fee2e2;
    color: #b91c1c;
    border-radius: 8px;
    font-size: 14px;
}

.date-info {
    margin-top: 15px;
    padding: 12px 15px;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 8px;
    font-size: 14px;
}


/* =========================
   CARS CONTAINER
========================= */

.cars-container {
    width: 70%;
    max-width: 1600px;
    margin: 0 auto;
    padding-bottom: 60px;
}

.results-text {
    margin-bottom: 20px;
    color: #555;
}

.results-text strong {
    color: #111827;
}


/* =========================
   CAR GRID
========================= */

.cars-grid {
    display: grid;
    grid-template-columns:
        repeat(3, 1fr);

    gap: 28px;
}

.car-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.08);

    transition: 0.3s;
}

.car-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 8px 22px rgba(0,0,0,0.12);
}


/* =========================
   CAR IMAGE
========================= */

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


/* =========================
   CAR CONTENT
========================= */

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


/* =========================
   STATUS
========================= */

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


/* =========================
   CARD BUTTONS
========================= */

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


/* =========================
   NO CARS
========================= */

.no-cars {
    background: white;
    padding: 50px;
    text-align: center;
    border-radius: 12px;
    color: #666;
}

.no-cars h2 {
    color: #333;
    margin-bottom: 10px;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 1200px) {

    .filter-form {
        grid-template-columns:
            repeat(3, 1fr);
    }

}


@media (max-width: 1000px) {

    .filter-form {
        grid-template-columns:
            1fr 1fr;
    }

    .cars-container {
        width: 90%;
    }

    .cars-grid {
        grid-template-columns:
            repeat(2, 1fr);
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

    .cars-container {
        width: 92%;
    }

    .cars-grid {
        grid-template-columns: 1fr;
    }

    .page-header h1 {
        font-size: 28px;
    }

    .search-help {
        display: block;
        line-height: 1.6;
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
     FILTER BOX
========================= -->

<div class="filter-box">


    <!-- SEARCH HELP -->

    <div class="search-help">

        <strong>
            🔍 Check car availability
        </strong>

        <span>
            Select pickup and return dates to check available cars,
            or search by car, brand or model.
        </span>

    </div>


    <form
        method="GET"
        action="cars.php"
        class="filter-form"
    >


        <!-- SEARCH -->

        <input
            type="text"
            name="search"
            placeholder="Search car, brand or model..."
            value="<?php
            echo htmlspecialchars(
                $search,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>"
        >


        <!-- CAR TYPE -->

        <select name="car_type">

            <option value="">
                All Car Types
            </option>


            <?php while (
                $type =
                mysqli_fetch_assoc($type_result)
            ) { ?>


                <option
                    value="<?php
                    echo htmlspecialchars(
                        $type['car_type'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>"
                    <?php
                    echo (
                        $car_type ==
                        $type['car_type']
                    )
                        ? 'selected'
                        : '';
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


        <!-- FUEL TYPE -->

        <select name="fuel_type">

            <option value="">
                All Fuel Types
            </option>


            <?php while (
                $fuel =
                mysqli_fetch_assoc($fuel_result)
            ) { ?>


                <option
                    value="<?php
                    echo htmlspecialchars(
                        $fuel['fuel_type'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>"
                    <?php
                    echo (
                        $fuel_type ==
                        $fuel['fuel_type']
                    )
                        ? 'selected'
                        : '';
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


        <!-- PICKUP DATE -->

        <input
            type="date"
            name="pickup_date"
            id="pickup_date"
            value="<?php
            echo htmlspecialchars(
                $pickup_date,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>"
            title="Pickup Date"
        >


        <!-- RETURN DATE -->

        <input
            type="date"
            name="return_date"
            id="return_date"
            value="<?php
            echo htmlspecialchars(
                $return_date,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>"
            title="Return Date"
        >


        <!-- SORT -->

        <select name="sort">

            <option value="">
                Sort By
            </option>


            <option
                value="price_low"
                <?php
                echo (
                    $sort === 'price_low'
                )
                    ? 'selected'
                    : '';
                ?>
            >
                Price: Low to High
            </option>


            <option
                value="price_high"
                <?php
                echo (
                    $sort === 'price_high'
                )
                    ? 'selected'
                    : '';
                ?>
            >
                Price: High to Low
            </option>


            <option
                value="name_az"
                <?php
                echo (
                    $sort === 'name_az'
                )
                    ? 'selected'
                    : '';
                ?>
            >
                Name: A to Z
            </option>


            <option
                value="name_za"
                <?php
                echo (
                    $sort === 'name_za'
                )
                    ? 'selected'
                    : '';
                ?>
            >
                Name: Z to A
            </option>

        </select>


        <!-- SEARCH BUTTON -->

        <button
            type="submit"
            class="btn search-btn"
        >
            🔍 Search
        </button>


    </form>


    <!-- DATE ERROR -->

    <?php if ($date_error !== '') { ?>

        <div class="date-error">

            ⚠️

            <?php
            echo htmlspecialchars(
                $date_error,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>

        </div>

    <?php } ?>


    <!-- DATE INFO -->

    <?php if ($date_search) { ?>

        <div class="date-info">

            📅 Showing cars available from

            <strong>

                <?php
                echo date(
                    'd M Y',
                    strtotime($pickup_date)
                );
                ?>

            </strong>

            to

            <strong>

                <?php
                echo date(
                    'd M Y',
                    strtotime($return_date)
                );
                ?>

            </strong>

        </div>

    <?php } ?>


</div>


<!-- =========================
     CARS
========================= -->

<div class="cars-container">


    <div class="results-text">

        <?php if ($date_search) { ?>

            Available cars:

        <?php } else { ?>

            Showing

        <?php } ?>


        <strong>
            <?php echo $car_count; ?>
        </strong>

        car(s)

    </div>


    <?php if ($car_count > 0) { ?>


        <div class="cars-grid">


            <?php while (
                $car =
                mysqli_fetch_assoc($result)
            ) { ?>


                <div class="car-card">


                    <!-- =========================
                         CAR IMAGE
                    ========================= -->

                    <?php

                    $image =
                        trim(
                            $car['image'] ?? ''
                        );

                    if ($image !== '') {


                        if (
                            strpos(
                                $image,
                                'http://'
                            ) === 0 ||

                            strpos(
                                $image,
                                'https://'
                            ) === 0
                        ) {

                            $image_url =
                                $image;

                        } else {

                            $image_url =
                                'images/' .
                                ltrim(
                                    $image,
                                    '/'
                                );

                        }

                    ?>


                        <img
                            src="<?php
                            echo htmlspecialchars(
                                $image_url,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>"
                            class="car-image"
                            alt="<?php
                            echo htmlspecialchars(
                                $car['car_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>"
                        >


                    <?php } else { ?>


                        <div class="no-image">
                            🚗
                        </div>


                    <?php } ?>


                    <!-- =========================
                         CAR CONTENT
                    ========================= -->

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


                        <!-- PRICE -->

                        <div class="price">

                            ₹<?php
                            echo number_format(
                                (float)
                                $car['price_per_day'],
                                2
                            );
                            ?>

                            <span>
                                / day
                            </span>

                        </div>


                        <!-- STATUS -->

                        <?php if ($date_search) { ?>


                            <div class="status available">

                                ● Available for selected dates

                            </div>


                        <?php } else { ?>


                            <?php

                            if (
                                strtolower(
                                    trim(
                                        $car['status']
                                    )
                                ) === 'available'
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


                        <?php } ?>


                        <!-- CARD BUTTONS -->

                        <div class="card-buttons">


                            <a
                                href="car_details.php?car_id=<?php echo (int)$car['car_id']; ?>"
                                class="btn details-btn"
                            >
                                👁 View Details
                            </a>


                            <?php

                            $booking_url =
                                "booking.php?car_id=" .
                                (int)
                                $car['car_id'];


                            if ($date_search) {

                                $booking_url .=
                                    "&pickup_date=" .
                                    urlencode(
                                        $pickup_date
                                    ) .

                                    "&return_date=" .
                                    urlencode(
                                        $return_date
                                    );

                            }

                            ?>


                            <?php

                            if (
                                strtolower(
                                    trim(
                                        $car['status']
                                    )
                                ) === 'available'
                            ) {

                            ?>


                                <a
                                    href="<?php
                                    echo htmlspecialchars(
                                        $booking_url,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>"
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


        <!-- =========================
             NO CARS
        ========================= -->

        <div class="no-cars">


            <?php if ($date_search) { ?>


                <h2>
                    😔 No Cars Available
                </h2>

                <p>
                    No car is available for the selected dates.
                    Please try different dates.
                </p>


            <?php } else { ?>


                <h2>
                    😔 No Cars Found
                </h2>

                <p>
                    Try changing your search or filter options.
                </p>


            <?php } ?>


            <a
                href="cars.php"
                class="btn search-btn"
            >
                View All Cars
            </a>


        </div>


    <?php } ?>


</div>


<script>

/* =========================
   DATE INPUTS
========================= */

const pickupInput =
    document.getElementById(
        "pickup_date"
    );

const returnInput =
    document.getElementById(
        "return_date"
    );


/* =========================
   TODAY
========================= */

const today =
    new Date();

const year =
    today.getFullYear();

const month =
    String(
        today.getMonth() + 1
    ).padStart(
        2,
        "0"
    );

const day =
    String(
        today.getDate()
    ).padStart(
        2,
        "0"
    );

const todayString =
    year +
    "-" +
    month +
    "-" +
    day;


/* =========================
   MIN DATE
========================= */

pickupInput.min =
    todayString;

returnInput.min =
    todayString;


/* =========================
   PICKUP DATE CHANGE
========================= */

pickupInput.addEventListener(
    "change",
    function () {

        returnInput.min =
            this.value;


        if (
            returnInput.value !== "" &&
            returnInput.value <=
            this.value
        ) {

            returnInput.value =
                "";

        }

    }
);


/* =========================
   CLEAR FILTERS ON REFRESH
========================= */

const navigation =
    performance.getEntriesByType(
        "navigation"
    )[0];


if (
    navigation &&
    navigation.type === "reload"
) {

    window.location.replace(
        "cars.php"
    );

}

</script>


</body>

</html>
