<?php

require_once __DIR__ . "/config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Find Car Image
|--------------------------------------------------------------------------
*/

function getCarImage($image)
{
    $image = trim((string) $image);

    if ($image === '') {
        return '';
    }

    $imageDir = __DIR__ . '/images/';

    /*
     * Remove starting slash
     */
    $clean = ltrim($image, '/\\');

    /*
     * Remove duplicate images/ if database already contains it
     */
    if (stripos($clean, 'images/') === 0) {
        $clean = substr($clean, 7);
    }

    $clean = str_replace('\\', '/', $clean);

    /*
     * Possible locations
     */
    $possibleFiles = [
        $imageDir . $clean,
        $imageDir . 'vehicles/' . basename($clean),
        $imageDir . basename($clean)
    ];

    foreach ($possibleFiles as $file) {

        if (is_file($file)) {

            $relative = str_replace(
                __DIR__ . '/',
                '',
                $file
            );

            return str_replace('\\', '/', $relative);
        }
    }


    /*
     * Case-insensitive search inside images folder
     */
    if (is_dir($imageDir)) {

        try {

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $imageDir,
                    FilesystemIterator::SKIP_DOTS
                )
            );

            $wanted = strtolower(basename($clean));

            foreach ($iterator as $file) {

                if (!$file->isFile()) {
                    continue;
                }

                if (
                    strtolower($file->getFilename()) === $wanted
                ) {

                    $relative = str_replace(
                        __DIR__ . '/',
                        '',
                        $file->getPathname()
                    );

                    return str_replace(
                        '\\',
                        '/',
                        $relative
                    );
                }
            }

        } catch (Exception $e) {
            return '';
        }
    }

    return '';
}


/*
|--------------------------------------------------------------------------
| Get Bookings
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
            max-width: 1600px;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .main-header h1 {
            font-size: 30px;
        }

        .main-header p {
            color: #666;
            margin-top: 8px;
        }

        .browse-btn {
            background-color: #222;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 6px;
        }

        .browse-btn:hover {
            background-color: #444;
        }


        /* BOOKING CARD */

        .booking-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            gap: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }


        /* IMAGE */

        .image-box {
            width: 275px;
            min-width: 275px;
            height: 185px;
            border-radius: 10px;
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
            color: #888;
            font-size: 15px;
            text-align: center;
        }


        /* BOOKING DETAILS */

        .booking-details {
            flex: 1;
            min-width: 0;
        }

        .booking-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 18px;
        }

        .booking-title h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .booking-id {
            color: #777;
            font-size: 14px;
        }


        /* INFORMATION */

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(150px, 1fr));
            gap: 12px;
        }

        .info {
            padding: 12px;
            background-color: #f7f8fa;
            border-radius: 7px;
            min-width: 0;
        }

        .info strong {
            display: block;
            font-size: 12px;
            color: #777;
            margin-bottom: 6px;
        }

        .info span {
            font-size: 15px;
            word-break: break-word;
        }


        /* STATUS */

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
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


        /* AMOUNT */

        .amount-box {
            margin-top: 18px;
            font-size: 16px;
        }

        .amount {
            font-size: 21px;
            font-weight: bold;
            color: #111;
        }


        /* EMPTY */

        .empty {
            background-color: white;
            text-align: center;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin-bottom: 10px;
        }

        .empty p {
            color: #666;
            margin-bottom: 20px;
        }


        /* RESPONSIVE */

        @media (max-width: 1100px) {

            .booking-card {
                gap: 20px;
            }

            .image-box {
                width: 230px;
                min-width: 230px;
            }

            .info-grid {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
            }
        }


        @media (max-width: 800px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                padding: 25px;
            }

            .booking-card {
                display: block;
            }

            .image-box {
                width: 100%;
                min-width: 0;
                height: 230px;
                margin-bottom: 20px;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }


        @media (max-width: 600px) {

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding-bottom: 10px;
            }

            .sidebar a {
                padding: 10px 20px;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }

            .main-header {
                display: block;
            }

            .browse-btn {
                display: inline-block;
                margin-top: 15px;
            }

            .booking-card {
                padding: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .booking-title {
                display: block;
            }

            .status {
                margin-top: 10px;
            }
        }

    </style>

</head>

<body>


<!-- SIDEBAR -->

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

    <a href="my_bookings.php" class="active">
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


<!-- MAIN -->

<div class="main">

    <div class="main-header">

        <div>

            <h1>
                📅 My Bookings
            </h1>

            <p>
                View and manage your car rental bookings.
            </p>

        </div>

        <a href="cars.php" class="browse-btn">
            🚘 Browse Cars
        </a>

    </div>


    <?php if ($result && mysqli_num_rows($result) > 0) { ?>


        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>

            <?php

            $imagePath = getCarImage(
                $booking['image'] ?? ''
            );

            ?>


            <div class="booking-card">


                <!-- CAR IMAGE -->

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
                                $booking['car_name'],
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


                    <div class="booking-title">

                        <div>

                            <h2>
                                <?php
                                echo htmlspecialchars(
                                    $booking['car_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </h2>

                            <span class="booking-id">
                                Booking ID:
                                #<?php
                                echo (int) $booking['booking_id'];
                                ?>
                            </span>

                        </div>


                        <?php

                        $status = strtolower(
                            $booking['booking_status']
                        );

                        ?>

                        <span class="status <?php echo htmlspecialchars(
                            $status,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>">

                            <?php
                            echo htmlspecialchars(
                                $booking['booking_status'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </span>

                    </div>


                    <!-- INFORMATION -->

                    <div class="info-grid">


                        <div class="info">

                            <strong>
                                BRAND
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['brand'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                MODEL
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['model'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                PICKUP DATE
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['pickup_date'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                RETURN DATE
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $booking['return_date'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                TOTAL DAYS
                            </strong>

                            <span>
                                <?php
                                echo (int) $booking['total_days'];
                                ?>
                            </span>

                        </div>


                        <div class="info">

                            <strong>
                                BOOKING DATE
                            </strong>

                            <span>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $booking['created_at']
                                    )
                                );
                                ?>
                            </span>

                        </div>


                    </div>


                    <div class="amount-box">

                        Booking Amount:

                        <span class="amount">

                            ₹<?php
                            echo number_format(
                                (float) $booking['total_amount'],
                                2
                            );
                            ?>

                        </span>

                    </div>


                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


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

            <a href="cars.php" class="browse-btn">
                Browse Available Cars
            </a>

        </div>


    <?php } ?>


</div>

</body>

</html>
