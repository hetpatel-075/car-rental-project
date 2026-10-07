<?php
require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$sql = "
    SELECT
        b.booking_id,
        b.user_id,
        b.car_id,
        b.pickup_date,
        b.return_date,
        b.total_days,
        b.total_amount,
        b.booking_status,
        c.car_name,
        c.brand,
        c.model,
        c.image
    FROM bookings b
    LEFT JOIN cars c
        ON b.car_id = c.car_id
    WHERE b.user_id = ?
    ORDER BY b.booking_id DESC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

function carImage($image)
{
    $image = trim((string)$image);

    if ($image == "") {
        return "";
    }

    if (
        strpos($image, "http://") === 0 ||
        strpos($image, "https://") === 0
    ) {
        return $image;
    }

    $image = str_replace("\\", "/", $image);
    $image = ltrim($image, "/");

    if (strpos($image, "images/") === 0) {
        return "../" . $image;
    }

    if (strpos($image, "vehicles/") === 0) {
        return "../images/" . $image;
    }

    return "../images/" . $image;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking History</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        h1 {
            margin-bottom: 25px;
            color: #222;
        }

        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .car-image {
            width: 220px;
            height: 145px;
            object-fit: cover;
            border-radius: 10px;
            background: #eee;
        }

        .no-image {
            width: 220px;
            height: 145px;
            border-radius: 10px;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
        }

        .booking-info {
            flex: 1;
        }

        .booking-info h2 {
            margin-bottom: 12px;
            color: #222;
        }

        .booking-info p {
            margin: 7px 0;
            color: #444;
        }

        .status {
            font-weight: bold;
        }

        .book-again {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 18px;
            background: #222;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .book-again:hover {
            background: #444;
        }

        .empty {
            background: white;
            padding: 50px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .empty h2 {
            margin-bottom: 10px;
        }

        .empty p {
            color: #777;
            margin-bottom: 20px;
        }

        @media(max-width: 800px) {

            .main {
                margin-left: 0;
                padding: 20px;
            }

            .booking-card {
                flex-direction: column;
            }

            .car-image,
            .no-image {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<?php include __DIR__ . "/includes/sidebar.php"; ?>

<div class="main">

    <h1>🕘 Booking History</h1>

    <?php if ($result && mysqli_num_rows($result) > 0) { ?>

        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>

            <div class="booking-card">

                <?php
                $image = carImage($booking['image'] ?? '');
                ?>

                <?php if ($image != "") { ?>

                    <img
                        src="<?php echo htmlspecialchars($image); ?>"
                        class="car-image"
                        alt="Car"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                    >

                    <div
                        class="no-image"
                        style="display:none;"
                    >
                        No Image
                    </div>

                <?php } else { ?>

                    <div class="no-image">
                        No Image
                    </div>

                <?php } ?>


                <div class="booking-info">

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $booking['car_name'] ?? 'Car'
                        );
                        ?>
                    </h2>

                    <p>
                        <strong>Brand:</strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['brand'] ?? '-'
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Model:</strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['model'] ?? '-'
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Pickup Date:</strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['pickup_date']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Return Date:</strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['return_date']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Total Days:</strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['total_days']
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Booking Amount:</strong>
                        ₹<?php
                        echo number_format(
                            (float)$booking['total_amount'],
                            2
                        );
                        ?>
                    </p>

                    <p class="status">

                        <strong>Status:</strong>

                        <?php
                        echo htmlspecialchars(
                            $booking['booking_status']
                        );
                        ?>

                    </p>

                    <a
                        href="../booking.php?car_id=<?php echo (int)$booking['car_id']; ?>"
                        class="book-again"
                    >
                        🔁 Book Again
                    </a>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty">

            <h2>No Booking History</h2>

            <p>
                No booking found for this account.
            </p>

            <a href="../cars.php" class="book-again">
                🚘 Browse Cars
            </a>

        </div>

    <?php } ?>

</div>

</body>

</html>

<?php
mysqli_stmt_close($stmt);
?>
