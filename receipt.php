<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die("Invalid booking ID.");
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        b.booking_id,
        b.pickup_date,
        b.return_date,
        b.total_days,
        b.total_amount,
        b.created_at,
        c.car_name,
        c.brand,
        c.model,
        u.name,
        u.email,
        u.phone
    FROM bookings b
    JOIN cars c ON b.car_id = c.car_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.booking_id = ?
    AND b.user_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die("Booking not found.");
}

$car_name = trim(
    ($booking['brand'] ?? '') . ' ' .
    ($booking['car_name'] ?? '')
);

if ($car_name === '') {
    $car_name = $booking['model'] ?? 'Car';
}

$pickup_date = !empty($booking['pickup_date'])
    ? date("d M Y", strtotime($booking['pickup_date']))
    : "-";

$return_date = !empty($booking['return_date'])
    ? date("d M Y", strtotime($booking['return_date']))
    : "-";

$booking_date = !empty($booking['created_at'])
    ? date("d M Y", strtotime($booking['created_at']))
    : date("d M Y");

$total_days = max(1, (int)$booking['total_days']);
$total_amount = (float)$booking['total_amount'];

$price_per_day = $total_amount / $total_days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Receipt | DriveNow</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 40px 20px;
    font-family: Arial, sans-serif;
    background: #f3f4f6;
    color: #222;
}

.receipt {
    width: 100%;
    max-width: 800px;
    margin: auto;
    background: #fff;
    padding: 40px;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0,0,0,.10);
}

.header {
    text-align: center;
    border-bottom: 2px solid #222;
    padding-bottom: 22px;
    margin-bottom: 25px;
}

.logo {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: 2px;
}

.subtitle {
    margin-top: 6px;
    color: #777;
    font-size: 14px;
}

.booking-info {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 30px;
}

.booking-info div {
    font-size: 14px;
    line-height: 1.8;
}

.section {
    margin-top: 25px;
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    border-bottom: 1px solid #ddd;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 30px;
}

.detail {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.label {
    color: #777;
}

.value {
    font-weight: 600;
    text-align: right;
}

.payment {
    margin-top: 25px;
    border-top: 2px solid #222;
    padding-top: 18px;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.payment-row.total {
    font-size: 20px;
    font-weight: 800;
    border-top: 1px solid #ddd;
    margin-top: 10px;
    padding-top: 15px;
}

.cash {
    margin-top: 18px;
    padding: 14px;
    text-align: center;
    background: #f5f5f5;
    border-radius: 8px;
    font-weight: 700;
}

.footer {
    text-align: center;
    margin-top: 35px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    color: #777;
    font-size: 13px;
}

.actions {
    max-width: 800px;
    margin: 20px auto 0;
    display: flex;
    justify-content: center;
    gap: 12px;
}

.btn {
    border: none;
    padding: 12px 22px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.print-btn {
    background: #111;
    color: white;
}

.back-btn {
    background: #ddd;
    color: #222;
}

@media (max-width: 600px) {
    body {
        padding: 15px;
    }

    .receipt {
        padding: 22px;
    }

    .booking-info {
        flex-direction: column;
    }

    .details {
        grid-template-columns: 1fr;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}

@media print {
    body {
        background: white;
        padding: 0;
    }

    .receipt {
        max-width: 100%;
        box-shadow: none;
        border-radius: 0;
    }

    .actions {
        display: none;
    }
}
</style>
</head>

<body>

<div class="receipt">

    <div class="header">
        <div class="logo">DRIVENOW</div>
        <div class="subtitle">CAR RENTAL RECEIPT</div>
    </div>

    <div class="booking-info">
        <div>
            <strong>Booking ID:</strong>
            #<?= htmlspecialchars($booking['booking_id']) ?><br>

            <strong>Booking Date:</strong>
            <?= htmlspecialchars($booking_date) ?>
        </div>
    </div>

    <div class="section">

        <div class="section-title">
            Customer Details
        </div>

        <div class="details">

            <div class="detail">
                <span class="label">Name</span>
                <span class="value">
                    <?= htmlspecialchars($booking['name']) ?>
                </span>
            </div>

            <div class="detail">
                <span class="label">Email</span>
                <span class="value">
                    <?= htmlspecialchars($booking['email']) ?>
                </span>
            </div>

            <div class="detail">
                <span class="label">Phone</span>
                <span class="value">
                    <?= htmlspecialchars($booking['phone']) ?>
                </span>
            </div>

        </div>

    </div>

    <div class="section">

        <div class="section-title">
            Car Details
        </div>

        <div class="details">

            <div class="detail">
                <span class="label">Car</span>
                <span class="value">
                    <?= htmlspecialchars($car_name) ?>
                </span>
            </div>

            <?php if (!empty($booking['model'])): ?>
            <div class="detail">
                <span class="label">Model</span>
                <span class="value">
                    <?= htmlspecialchars($booking['model']) ?>
                </span>
            </div>
            <?php endif; ?>

        </div>

    </div>

    <div class="section">

        <div class="section-title">
            Rental Details
        </div>

        <div class="details">

            <div class="detail">
                <span class="label">Pickup Date</span>
                <span class="value">
                    <?= htmlspecialchars($pickup_date) ?>
                </span>
            </div>

            <div class="detail">
                <span class="label">Return Date</span>
                <span class="value">
                    <?= htmlspecialchars($return_date) ?>
                </span>
            </div>

            <div class="detail">
                <span class="label">Total Days</span>
                <span class="value">
                    <?= $total_days ?>
                </span>
            </div>

        </div>

    </div>

    <div class="payment">

        <div class="payment-row">
            <span>Price Per Day</span>
            <span>₹<?= number_format($price_per_day, 2) ?></span>
        </div>

        <div class="payment-row">
            <span>Rental Days</span>
            <span><?= $total_days ?></span>
        </div>

        <div class="payment-row total">
            <span>Total Amount</span>
            <span>₹<?= number_format($total_amount, 2) ?></span>
        </div>

        <div class="cash">
            Payment Method: Cash on Counter
        </div>

    </div>

    <div class="footer">
        Thank you for choosing DriveNow Car Rental.<br>
        We hope you have a safe and pleasant journey.
    </div>

</div>

<div class="actions">
    <button class="btn print-btn" onclick="window.print()">
        🖨️ Print Receipt
    </button>

    <button class="btn back-btn" onclick="history.back()">
        ← Back
    </button>
</div>

</body>
</html>
