<?php
require_once __DIR__ . "/session.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";


// -------------------------
// CHECK CAR ID
// -------------------------

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$car_id = (int) $_GET['id'];


// -------------------------
// GET CAR DETAILS
// -------------------------

$stmt = mysqli_prepare(
    $conn,
    "SELECT car_id, car_name, image
     FROM cars
     WHERE car_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $car_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$car = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// Car does not exist
if (!$car) {
    header("Location: cars.php");
    exit();
}


// -------------------------
// CHECK BOOKINGS
// -------------------------

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE car_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $car_id);
mysqli_stmt_execute($stmt);

$bookingResult = mysqli_stmt_get_result($stmt);
$bookingData = mysqli_fetch_assoc($bookingResult);

mysqli_stmt_close($stmt);

$totalBookings = (int) $bookingData['total'];


// -------------------------
// DELETE CAR
// -------------------------

if ($totalBookings > 0) {

    /*
     * Do not delete a car that has booking records.
     * This keeps booking history safe.
     */

    header("Location: cars.php?delete_error=bookings");
    exit();
}


// -------------------------
// DELETE FROM DATABASE
// -------------------------

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM cars
     WHERE car_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $car_id);

$deleted = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


// -------------------------
// DELETE IMAGE
// -------------------------

if ($deleted) {

    if (
        !empty($car['image']) &&
        file_exists("../images/" . $car['image'])
    ) {

        unlink("../images/" . $car['image']);
    }

    header("Location: cars.php?deleted=1");
    exit();
}


// -------------------------
// DELETE FAILED
// -------------------------

header("Location: cars.php?delete_error=failed");
exit();

?>