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
        // CLOUDINARY IMAGE UPLOAD
        // -------------------------

        $imageName = "";

        if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {

            if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {

                $originalName = $_FILES['image']['name'];
                $tmpName = $_FILES['image']['tmp_name'];
                $fileSize = $_FILES['image']['size'];

                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $allowedExtensions = ["jpg", "jpeg", "png", "webp"];

                if (!in_array($extension, $allowedExtensions)) {
                    $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";
                    $messageType = "error";

                } elseif ($fileSize > 5 * 1024 * 1024) {
                    $message = "Image size must be less than 5 MB.";
                    $messageType = "error";

                } else {

                    $cloudName = "hct6y970";
                    $uploadPreset = "car_rental_images";
                    $uploadUrl = "https://api.cloudinary.com/v1_1/" . $cloudName . "/image/upload";

                    $curl = curl_init();

                    $postData = [
                        "file" => curl_file_create($tmpName, mime_content_type($tmpName), $originalName),
                        "upload_preset" => $uploadPreset,
                        "folder" => "car_rental"
                    ];

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $uploadUrl,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $postData,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 60
                    ]);

                    $response = curl_exec($curl);
                    $curlError = curl_error($curl);
                    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);

                    if ($response === false || $curlError || $httpCode < 200 || $httpCode >= 300) {
                        $message = "Image upload failed. Please try again.";
                        $messageType = "error";
                    } else {
                        $cloudinaryData = json_decode($response, true);

                        if (!empty($cloudinaryData['secure_url'])) {
                            $imageName = $cloudinaryData['secure_url'];
                        } else {
                            $message = "Cloudinary did not return an image URL.";
                            $messageType = "error";
                        }
                    }
                }

            } else {
                $message = "There was an error uploading the image.";
                $messageType = "error";
            }
        }



        // -------------------------
        // INSERT CAR
        // -------------------------

        if ($message == "") {

            $sql = "INSERT INTO cars
                    (
                        car_name,
                        brand,
                        model,
                        car_type,
                        price_per_day,
                        fuel_type,
                        seats,
                        image,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssssdsiss",
                $car_name,
                $brand,
                $model,
                $car_type,
                $price_per_day,
                $fuel_type,
                $seats,
                $imageName,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: cars.php?added=1");
                exit();

            } else {


                $message = "Failed to add car. Please try again.";
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

    <title>Add Car - CarRental Admin</title>


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


        /* FORM CARD */

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


        /* FORM GRID */

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


        /* IMAGE */

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

                gap: 15px;
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


    <a href="cars.php">
        🚘 Manage Cars
    </a>


    <a href="add_car.php" class="active">
        ➕ Add Car
    </a>


    <a href="users.php">
        👥 Users
    </a>


    <a href="bookings.php">
        📅 Bookings
    </a>


    <a href="logout.php">
        🚪 Logout
    </a>

</div>


<!-- MAIN -->

<div class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <div>

            <h1>Add New Car</h1>

            <p class="subtitle">
                Add a new vehicle to your rental fleet
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
            🚗 Car Information
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
                        placeholder="Example: Swift Dzire"
                        value="<?php
                        echo isset($_POST['car_name'])
                            ? htmlspecialchars($_POST['car_name'])
                            : '';
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
                        placeholder="Example: Maruti Suzuki"
                        value="<?php
                        echo isset($_POST['brand'])
                            ? htmlspecialchars($_POST['brand'])
                            : '';
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
                        placeholder="Example: 2025"
                        value="<?php
                        echo isset($_POST['model'])
                            ? htmlspecialchars($_POST['model'])
                            : '';
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
                            if (
                                isset($_POST['car_type']) &&
                                $_POST['car_type'] == "Hatchback"
                            ) echo "selected";
                            ?>
                        >
                            Hatchback
                        </option>

                        <option value="Sedan"
                            <?php
                            if (
                                isset($_POST['car_type']) &&
                                $_POST['car_type'] == "Sedan"
                            ) echo "selected";
                            ?>
                        >
                            Sedan
                        </option>

                        <option value="SUV"
                            <?php
                            if (
                                isset($_POST['car_type']) &&
                                $_POST['car_type'] == "SUV"
                            ) echo "selected";
                            ?>
                        >
                            SUV
                        </option>

                        <option value="MUV"
                            <?php
                            if (
                                isset($_POST['car_type']) &&
                                $_POST['car_type'] == "MUV"
                            ) echo "selected";
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
                        placeholder="Example: 1500"
                        min="1"
                        step="0.01"
                        value="<?php
                        echo isset($_POST['price_per_day'])
                            ? htmlspecialchars($_POST['price_per_day'])
                            : '';
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

                        <option value="">
                            Select Fuel Type
                        </option>

                        <option value="Petrol"
                            <?php
                            if (
                                isset($_POST['fuel_type']) &&
                                $_POST['fuel_type'] == "Petrol"
                            ) echo "selected";
                            ?>
                        >
                            Petrol
                        </option>

                        <option value="Diesel"
                            <?php
                            if (
                                isset($_POST['fuel_type']) &&
                                $_POST['fuel_type'] == "Diesel"
                            ) echo "selected";
                            ?>
                        >
                            Diesel
                        </option>

                        <option value="CNG"
                            <?php
                            if (
                                isset($_POST['fuel_type']) &&
                                $_POST['fuel_type'] == "CNG"
                            ) echo "selected";
                            ?>
                        >
                            CNG
                        </option>

                        <option value="Electric"
                            <?php
                            if (
                                isset($_POST['fuel_type']) &&
                                $_POST['fuel_type'] == "Electric"
                            ) echo "selected";
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
                        placeholder="Example: 5"
                        min="1"
                        max="20"
                        value="<?php
                        echo isset($_POST['seats'])
                            ? htmlspecialchars($_POST['seats'])
                            : '';
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
                            if (
                                !isset($_POST['status']) ||
                                $_POST['status'] == "Available"
                            ) echo "selected";
                            ?>
                        >
                            Available
                        </option>

                        <option value="Unavailable"
                            <?php
                            if (
                                isset($_POST['status']) &&
                                $_POST['status'] == "Unavailable"
                            ) echo "selected";
                            ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>


                <!-- IMAGE -->

                <div class="form-group full">

                    <label>
                        Car Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        class="image-input"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="help-text">
                        Allowed: JPG, JPEG, PNG, WEBP | Maximum size: 5 MB
                    </div>

                </div>


            </div>


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn save-btn"
                >
                    💾 Add Car
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
