<?php

require_once __DIR__ . "/config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| IMAGE FUNCTION
|--------------------------------------------------------------------------
*/

function getCarImage($image)
{
    $image = trim((string)$image);

    if ($image === '') {
        return '';
    }

    // External image / Cloudinary
    if (
        strpos($image, 'https://') === 0 ||
        strpos($image, 'http://') === 0
    ) {
        return $image;
    }

    // Remove starting slash
    $image = ltrim($image, '/\\');

    // Already images/
    if (strpos($image, 'images/') === 0) {
        return $image;
    }

    // Already images\ path
    if (strpos($image, 'images\\') === 0) {
        return str_replace('\\', '/', $image);
    }

    // Upload path
    if (strpos($image, 'uploads/') === 0) {
        return $image;
    }

    // Vehicles path
    if (strpos($image, 'vehicles/') === 0) {
        return 'images/' . $image;
    }

    // Normal image filename
    return 'images/' . $image;
}


/*
|--------------------------------------------------------------------------
| GET USER BOOKINGS
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
        WHERE bookings.user_id = ?
        ORDER BY bookings.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error.");
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

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
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
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
            background: #222;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 6px;
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
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }


        /* =========================
           IMAGE
        ========================= */

        .image-box {
            width: 260px;
            min-width: 260px;
            height: 180px;
            background: #eef1f5;
            border-radius: 10px;
            overflow: hidden;
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
            text-align: center;
        }


        /* =========================
           BOOKING DETAILS
        ========================= */

        .booking-details {
            flex: 1;
            min-width: 0;
        }

        .booking-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .booking-title h2 {
            font-size: 25px;
            margin-bottom: 5px;
        }

        .booking-id {
            color: #777;
            font-size: 14px;
        }


        /* =========================
           INFORMATION
        ========================= */

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(130px, 1fr));
            gap: 12px;
        }

        .info {
            padding: 11px;
            background: #f7f8fa;
            border-radius: 7px;
            min-width: 0;
        }

        .info strong {
            display: block;
            font-size: 12px;
            color: #777;
            margin-bottom: 5px;
        }

        .info span {
            display: block;
            font-size: 14px;
            color: #222;
            overflow-wrap: anywhere;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
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

        .completed {
            background: #dbeafe;
            color: #1e40af;
        }


        /* =========================
           AMOUNT
        ========================= */

        .amount-box {
            margin-top: 18px;
            font-size: 17px;
        }

        .amount {
            font-size: 21px;
            font-weight: bold;
            color: #111;
            margin-left: 5px;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {
            background: white;
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


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

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


        @media (max-width: 650px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                width: calc(100% - 200px);
                padding: 20px;
            }

            .main-header {
                display: block;
            }

            .main-header h1 {
                font-size: 27px;
            }

            .browse-btn {
                display: inline-block;
                margin-top: 15px;
            }

            .booking-card {
                padding: 15px;
            }

            .image-box {
                height: 200px;
            }

            .booking-title {
                display: block;
            }

            .status {
                margin-top: 10px;
            }

            .info-grid {
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
     MAIN
========================= -->

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

            $imagePath = getCarImage(
                $booking['image'] ?? ''
            );

            $status = strtolower(
                trim(
                    $booking['booking_status'] ?? 'Pending'
                )
            );

            ?>


            <!-- =========================
                 BOOKING CARD
            ========================= -->

            <div class="booking-card">


                <!-- IMAGE -->

                <div class="image-box">

                    <?php if ($imagePath !== '') { ?>

                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    $imagePath,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
                            class="car-image"
                            alt="<?php
                                echo htmlspecialchars(
                                    $booking['car_name'] ?? 'Car',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
                            onerror="imageError(this)"
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


                <!-- DETAILS -->

                <div class="booking-details">


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
                                #<?php
                                echo (int)$booking['booking_id'];
                                ?>

                            </span>

                        </div>


                        <span
                            class="status <?php
                                echo htmlspecialchars(
                                    $status,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
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


                    <!-- INFO -->

                    <div class="info-grid">


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


                        <div class="info">

                            <strong>
                                TOTAL DAYS
                            </strong>

                            <span>

                                <?php

                                echo (int)(
                                    $booking['total_days'] ?? 0
                                );

                                ?>

                                day(s)

                            </span>

                        </div>


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

                                    echo "-";

                                }

                                ?>

                            </span>

                        </div>


                    </div>


                    <!-- AMOUNT -->

                    <div class="amount-box">

                        <span>
                            Booking Amount:
                        </span>

                        <span class="amount">

                            ₹<?php

                            echo number_format(
                                (float)(
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


<script>

function imageError(img)
{
    img.style.display = "none";

    var fallback = img.nextElementSibling;

    if (fallback) {
        fallback.style.display = "flex";
    }
}

</script>


</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>
