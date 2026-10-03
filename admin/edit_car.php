<?php
require_once __DIR__ . "/session.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

$message = "";
$messageType = "";


// -------------------------
// GET CAR ID
// -------------------------

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$car_id = (int) $_GET['id'];


// -------------------------
// GET EXISTING CAR
// -------------------------

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM cars WHERE car_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $car_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$car = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$car) {
    header("Location: cars.php");
    exit();
}


// -------------------------
// FORM SUBMISSION
// -------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $car_name = trim($_POST['car_name']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $car_type = trim($_POST['car_type']);
    $price_per_day = trim($_POST['price_per_day']);
    $fuel_type = trim($_POST['fuel_type']);
    $seats = trim($_POST['seats']);
    $status = trim($_POST['status']);

    // Keep old image by default
    $imageName = $car['image'];

    // -------------------------
    // VALIDATION
    // -------------------------

    if (
        $car_name == "" ||
        $brand == "" ||
        $model == "" ||
        $car_type == "" ||
        $price_per_day == "" ||
        $fuel_type == "" ||
        $seats == "" ||
        $status == ""
    ) {

        $message = "Please fill all required fields.";
        $messageType = "error";

    } elseif (!is_numeric($price_per_day) || $price_per_day <= 0) {

        $message = "Please enter a valid price.";
        $messageType = "error";

    } elseif (!is_numeric($seats) || $seats < 1 || $seats > 20) {

        $message = "Seats must be between 1 and 20.";
        $messageType = "error";

    } else {

        // -------------------------
        // NEW IMAGE UPLOAD
        // -------------------------

        $newImageUploaded = false;

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {

                $originalName = $_FILES['image']['name'];
                $tmpName = $_FILES['image']['tmp_name'];
                $fileSize = $_FILES['image']['size'];

                $extension = strtolower(
                    pathinfo($originalName, PATHINFO_EXTENSION)
                );

                $allowedExtensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];

                if (!in_array($extension, $allowedExtensions)) {

                    $message =
                        "Only JPG, JPEG, PNG and WEBP images are allowed.";

                    $messageType = "error";

                } elseif ($fileSize > 5 * 1024 * 1024) {

                    $message =
                        "Image size must be less than 5 MB.";

                    $messageType = "error";

                } else {

                    $newImageName =
                        time() . "_" .
                        uniqid() . "." .
                        $extension;

                    $uploadPath =
                        "../images/" . $newImageName;

                    if (move_uploaded_file($tmpName, $uploadPath)) {

                        $imageName = $newImageName;
                        $newImageUploaded = true;

                    } else {

                        $message = "Failed to upload new image.";
                        $messageType = "error";
                    }
                }

            } else {

                $message =
                    "There was an error uploading the image.";

                $messageType = "error";
            }
        }


        // -------------------------
        // UPDATE DATABASE
        // -------------------------

        if ($message == "") {

            $sql = "UPDATE cars SET
                        car_name = ?,
                        brand = ?,
                        model = ?,
                        car_type = ?,
                        price_per_day = ?,
                        fuel_type = ?,
                        seats = ?,
                        image = ?,
                        status = ?
                    WHERE car_id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssssdsissi",
                $car_name,
                $brand,
                $model,
                $car_type,
                $price_per_day,
                $fuel_type,
                $seats,
                $imageName,
                $status,
                $car_id
            );

            if (mysqli_stmt_execute($stmt)) {

                // Delete old image only after successful DB update
                if (
                    $newImageUploaded &&
                    !empty($car['image']) &&
                    file_exists("../images/" . $car['image'])
                ) {

                    unlink("../images/" . $car['image']);
                }

                header("Location: cars.php?updated=1");
                exit();

            } else {

                // If DB update fails, remove newly uploaded image
                if (
                    $newImageUploaded &&
                    $imageName != "" &&
                    file_exists("../images/" . $imageName)
                ) {

                    unlink("../images/" . $imageName);
                }

                $message =
                    "Failed to update car. Please try again.";

                $messageType = "error";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Car - CarRental Admin</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #222;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #111827;
            color: white;
            padding: 25px 15px;
        }

        .logo {
            font-size: 23px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 6px;
            border-radius: 8px;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }

        /* MAIN */

        .main {
            margin-left: 240px;
            padding: 30px;
            max-width: 1250px;
        }

        /* TOP BAR */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }

        .topbar h1 {
            font-size: 28px;
        }

        .subtitle {
            color: #6b7280;
            margin-top: 5px;
        }

        .admin-name {
            background: white;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* MESSAGE */

        .message {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* FORM */

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            max-width: 900px;
        }

        .form-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-weight: bold;
            margin-bottom: 7px;
            color: #374151;
        }

        .required {
            color: #dc2626;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            outline: none;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.1);
        }

        .help-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        /* CURRENT IMAGE */

        .current-image-box {
            margin-bottom: 10px;
        }

        .current-image {
            width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .no-image {
            width: 180px;
            height: 120px;
            background: #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .image-input {
            padding: 10px;
            background: #f9fafb;
        }

        /* BUTTONS */

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            border: none;
            padding: 12px 20px;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .save-btn {
            background: #2563eb;
            color: white;
        }

        .save-btn:hover {
            background: #1d4ed8;
        }

        .cancel-btn {
            background: #6b7280;
            color: white;
        }

        .cancel-btn:hover {
            background: #4b5563;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .form-card {
                padding: 20px;
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

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="cars.php" class="active">
        🚘 Manage Cars
    </a>

    <a href="add_car.php">
        ➕ Add Car
    </a>

    <a href="users.php">
        👥 Users
    </a>

    <a href="bookings.php">
        📅 Bookings
    </a>

    <a href="../logout.php">
        🚪 Logout
    </a>

</div>


<!-- MAIN -->

<div class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <div>

            <h1>Edit Car</h1>

            <p class="subtitle">
                Update the selected vehicle information
            </p>

        </div>

        <div class="admin-name">

            👤

            <?php

            echo isset($_SESSION['admin_username'])
                ? htmlspecialchars($_SESSION['admin_username'])
                : "Administrator";

            ?>

        </div>

    </div>


    <!-- ERROR MESSAGE -->

    <?php if ($message != "") { ?>

        <div class="message <?php echo $messageType; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php } ?>


    <!-- FORM -->

    <div class="form-card">

        <div class="form-title">
            ✏️ Edit Car Information
        </div>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">


                <!-- CAR NAME -->

                <div class="form-group">

                    <label>
                        Car Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="car_name"
                        value="<?php
                        echo htmlspecialchars($car['car_name']);
                        ?>"
                        required
                    >

                </div>


                <!-- BRAND -->

                <div class="form-group">

                    <label>
                        Brand <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="brand"
                        value="<?php
                        echo htmlspecialchars($car['brand']);
                        ?>"
                        required
                    >

                </div>


                <!-- MODEL -->

                <div class="form-group">

                    <label>
                        Model <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="model"
                        value="<?php
                        echo htmlspecialchars($car['model']);
                        ?>"
                        required
                    >

                </div>


                <!-- CAR TYPE -->

                <div class="form-group">

                    <label>
                        Car Type <span class="required">*</span>
                    </label>

                    <select
                        name="car_type"
                        required
                    >

                        <option value="">
                            Select Car Type
                        </option>

                        <option value="Hatchback"
                            <?php
                            if ($car['car_type'] == "Hatchback")
                                echo "selected";
                            ?>
                        >
                            Hatchback
                        </option>

                        <option value="Sedan"
                            <?php
                            if ($car['car_type'] == "Sedan")
                                echo "selected";
                            ?>
                        >
                            Sedan
                        </option>

                        <option value="SUV"
                            <?php
                            if ($car['car_type'] == "SUV")
                                echo "selected";
                            ?>
                        >
                            SUV
                        </option>

                        <option value="MUV"
                            <?php
                            if ($car['car_type'] == "MUV")
                                echo "selected";
                            ?>
                        >
                            MUV
                        </option>

                    </select>

                </div>


                <!-- PRICE -->

                <div class="form-group">

                    <label>
                        Price Per Day (₹)
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        name="price_per_day"
                        min="1"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars($car['price_per_day']);
                        ?>"
                        required
                    >

                </div>


                <!-- FUEL -->

                <div class="form-group">

                    <label>
                        Fuel Type <span class="required">*</span>
                    </label>

                    <select
                        name="fuel_type"
                        required
                    >

                        <option value="Petrol"
                            <?php
                            if ($car['fuel_type'] == "Petrol")
                                echo "selected";
                            ?>
                        >
                            Petrol
                        </option>

                        <option value="Diesel"
                            <?php
                            if ($car['fuel_type'] == "Diesel")
                                echo "selected";
                            ?>
                        >
                            Diesel
                        </option>

                        <option value="CNG"
                            <?php
                            if ($car['fuel_type'] == "CNG")
                                echo "selected";
                            ?>
                        >
                            CNG
                        </option>

                        <option value="Electric"
                            <?php
                            if ($car['fuel_type'] == "Electric")
                                echo "selected";
                            ?>
                        >
                            Electric
                        </option>

                    </select>

                </div>


                <!-- SEATS -->

                <div class="form-group">

                    <label>
                        Number of Seats
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        name="seats"
                        min="1"
                        max="20"
                        value="<?php
                        echo htmlspecialchars($car['seats']);
                        ?>"
                        required
                    >

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label>
                        Status <span class="required">*</span>
                    </label>

                    <select
                        name="status"
                        required
                    >

                        <option value="Available"
                            <?php
                            if ($car['status'] == "Available")
                                echo "selected";
                            ?>
                        >
                            Available
                        </option>

                        <option value="Unavailable"
                            <?php
                            if ($car['status'] == "Unavailable")
                                echo "selected";
                            ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>


                <!-- IMAGE -->

                <div class="form-group full">

                    <label>
                        Current Car Image
                    </label>

                    <div class="current-image-box">

                        <?php if (!empty($car['image'])) { ?>

                            <img
                                src="../images/<?php
                                echo htmlspecialchars($car['image']);
                                ?>"
                                class="current-image"
                                alt="Current Car Image"
                            >

                        <?php } else { ?>

                            <div class="no-image">
                                🚗
                            </div>

                        <?php } ?>

                    </div>

                </div>


                <!-- NEW IMAGE -->

                <div class="form-group full">

                    <label>
                        Replace Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        class="image-input"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="help-text">
                        Leave empty to keep the current image.
                        Allowed: JPG, JPEG, PNG, WEBP | Maximum: 5 MB
                    </div>

                </div>


            </div>


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn save-btn"
                >
                    💾 Save Changes
                </button>

                <a
                    href="cars.php"
                    class="btn cancel-btn"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>