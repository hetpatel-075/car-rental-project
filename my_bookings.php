<?php

require_once __DIR__ . "/config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Car Image
|--------------------------------------------------------------------------
*/

function getCarImage($image)
{
    $image = trim((string) $image);

    if ($image === '') {
        return '';
    }

    /*
     * Cloudinary / External Image
     */
    if (
        strpos($image, 'https://') === 0 ||
        strpos($image, 'http://') === 0
    ) {
        return $image;
    }

    /*
     * Local Image
     */
    $image = ltrim($image, '/\\');

    /*
     * Already contains images/
     */
    if (strpos($image, 'images/') === 0) {
        return $image;
    }

    /*
     * Add images/
     */
    return 'images/' . $image;
}


/*
|--------------------------------------------------------------------------
| Get User Bookings
|--------------------------------------------------------------------------
*/

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

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Bookings - CarRental</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        body {
            background: #f4f6f9;
            color: #222;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #222;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            overflow-y: auto;
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
            background: #444;
        }


        .sidebar .active {
            background: #444;
        }


        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 240px;
            padding: 35px;
            width: calc(100% - 240px);
            max-width: 1700px;
        }


        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
        }


        .main-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }


        .main-header p {
            color: #666;
            font-size: 18px;
        }


        .browse-btn {
            background: #222;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 7px;
            white-space: nowrap;
        }


        .browse-btn:hover {
            background: #444;
        }


        /* =========================
           BOOKING CARD
        ========================= */

        .booking-card {
            width: 100%;
            background: white;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 25px;
            display: flex;
            gap: 30px;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.08);
        }


        /* =========================
           IMAGE
        ========================= */

        .image-box {
            width: 310px;
            min-width: 310px;
            height: 210px;
            border-radius: 12px;
            overflow: hidden;
            background: #eef1f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }


        .no-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
            font-size: 16px;
        }


        /* =========================
           DETAILS
        ========================= */

        .booking-details {
            flex: 1;
            min-width: 0;
        }


        .booking-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
        }


        .booking-title h2 {
            font-size: 27px;
            margin-bottom: 5px;
        }


        .booking-id {
            color: #777;
            font-size: 15px;
        }


        /* =========================
           INFORMATION GRID
        ========================= */

        .info-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, minmax(140px, 1fr));
            gap: 12px;
        }


        .info {
            background: #f7f8fa;
            padding: 13px;
            border-radius: 8px;
            min-width: 0;
        }


        .info strong {
            display: block;
            color: #777;
            font-size: 12px;
            margin-bottom: 6px;
        }


        .info span {
            display: block;
            font-size: 15px;
            color: #111;
            overflow-wrap: anywhere;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }


        .pending {
            background: #fff3cd;
            color: #856404;
        }


        .confirmed {
            background: #d4edda;
            color: #155724;
        }


        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }


        /* =========================
           AMOUNT
        ========================= */

        .amount-box {
            margin-top: 20px;
            font-size: 17px;
        }


        .amount {
            color: #111;
            font-size: 23px;
            font-weight: bold;
            margin-left: 5px;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {
            background: white;
            text-align: center;
            padding: 60px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }


        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }


        .empty h2 {
            margin-bottom: 10px;
        }


        .empty p {
            color: #666;
            margin-bottom: 20px;
        }


        /* =========================
           TABLET
        ========================= */

        @media (max-width: 1200px) {

            .booking-card {
                gap: 22px;
            }


            .image-box {
                width: 260px;
                min-width: 260px;
                height: 190px;
            }


            .info-grid {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
            }

        }


        /* =========================
           SMALL TABLET
        ========================= */

        @media (max-width: 900px) {

            .sidebar {
                width: 210px;
            }


            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 25px;
            }


            .booking-card {
                display: block;
            }


            .image-box {
                width: 100%;
                min-width: 0;
                height: 250px;
                margin-bottom: 22px;
            }


            .booking-title {
                align-items: flex-start;
            }

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 650px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding-bottom: 10px;
            }


            .main {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }


            .main-header {
                display: block;
            }


            .main-header h1 {
                font-size: 27px;
            }


            .main-header p {
                font-size: 16px;
            }


            .browse-btn {
                display: inline-block;
                margin-top: 15px;
            }


            .booking-card {
                padding: 15px;
            }


            .image-box {
                height: 210px;
            }


            .booking-title {
                display: block;
            }


            .status {
                margin-top: 12px;
            }


            .info-grid {
                grid-template-columns: 1fr;
            }


            .booking-title h2 {
                font-size: 23px;
            }


            .amount {
                font-size: 20px;
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


    <a href="client/dashboard.php">
        🏠 Dashboard
    </a>


    <a href="client/profile.php">
        👤 My Profile
    </a>


    <a href="cars.php">
        🚘 Browse Cars
    </a>


    <a
        href="my_bookings.php"
        class="active"
    >
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


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main">


    <!-- HEADER -->

    <div class="main-header">

        <div>

            <h1>
                📅 My Bookings
            </h1>

            <p>
                View and manage your car rental bookings.
            </p>

        </div>


        <a
            href="cars.php"
            class="browse-btn"
        >
            🚘 Browse Cars
        </a>

    </div>


    <?php if ($result && mysqli_num_rows($result) > 0) { ?>


        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>


            <?php

            /*
             * Get correct image URL
             */

            $imagePath = getCarImage(
                $booking['image'] ?? ''
            );

            ?>


            <!-- BOOKING CARD -->

            <div class="booking-card">


                <!-- IMAGE -->

                <div class="image-box">


                    <?php if ($imagePath !== '') { ?>


                        <img
                            src="<?php echo htmlspecialchars(
                                $imagePath,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            class="car-image"
                            alt="<?php echo htmlspecialchars(
                                $booking['car_name'] ?? 'Car',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >


                        <div
                            class="no-image"
                            style="display:none;"
                        >
                            🚗 Image not available
                        </div>


                    <?php } else { ?>


                        <div class="no-image">
                            🚗 Image not available
                        </div>


                    <?php } ?>


                </div>


                <!-- BOOKING DETAILS -->

                <div class="booking-details">


                    <!-- TITLE -->

                    <div class="booking-title">


                        <div>

                            <h2>

                                <?php

                                echo htmlspecialchars(
                                    $booking['car_name'] ?? 'Car',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </h2>


                            <span class="booking-id">

                                Booking ID:

                                #

                                <?php

                                echo (int) $booking['booking_id'];

                                ?>

                            </span>

                        </div>


                        <?php

                        $status = strtolower(
                            trim(
                                $booking['booking_status'] ?? 'Pending'
                            )
                        );

                        $status_class = '';

                        if ($status === 'pending') {

                            $status_class = 'pending';

                        } elseif ($status === 'confirmed') {

                            $status_class = 'confirmed';

                        } elseif ($status === 'cancelled') {

                            $status_class = 'cancelled';

                        }

                        ?>


                        <span
                            class="status <?php echo $status_class; ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $booking['booking_status'] ?? 'Pending',
                                ENT_QUOTES,
                                'UTF-8'
                            );

                            ?>

                        </span>


                    </div>


                    <!-- INFORMATION -->

                    <div class="info-grid">


                        <!-- BRAND -->

                        <div class="info">

                            <strong>
                                BRAND
                            </strong>

                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $booking['brand'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </span>

                        </div>


                        <!-- MODEL -->

                        <div class="info">

                            <strong>
                                MODEL
                            </strong>

                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $booking['model'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </span>

                        </div>


                        <!-- PICKUP DATE -->

                        <div class="info">

                            <strong>
                                PICKUP DATE
                            </strong>

                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $booking['pickup_date'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </span>

                        </div>


                        <!-- RETURN DATE -->

                        <div class="info">

                            <strong>
                                RETURN DATE
                            </strong>

                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $booking['return_date'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </span>

                        </div>


                        <!-- TOTAL DAYS -->

                        <div class="info">

                            <strong>
                                TOTAL DAYS
                            </strong>

                            <span>

                                <?php

                                echo (int) (
                                    $booking['total_days'] ?? 0
                                );

                                ?>

                                day(s)

                            </span>

                        </div>


                        <!-- BOOKING DATE -->

                        <div class="info">

                            <strong>
                                BOOKING DATE
                            </strong>

                            <span>

                                <?php

                                if (!empty($booking['created_at'])) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $booking['created_at']
                                        )
                                    );

                                } else {

                                    echo '-';

                                }

                                ?>

                            </span>

                        </div>


                    </div>


                    <!-- AMOUNT -->

                    <div class="amount-box">

                        Booking Amount:

                        <span class="amount">

                            ₹<?php

                            echo number_format(
                                (float) (
                                    $booking['total_amount'] ?? 0
                                ),
                                2
                            );

                            ?>

                        </span>

                    </div>


                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


        <!-- NO BOOKINGS -->

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


            <a
                href="cars.php"
                class="browse-btn"
            >
                Browse Available Cars
            </a>

        </div>


    <?php } ?>


</div>


</body>

</html>
