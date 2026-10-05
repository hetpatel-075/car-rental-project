<?php
require_once __DIR__ . "/session.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";


// -------------------------
// SEARCH / FILTER VALUES
// -------------------------

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$fuel = isset($_GET['fuel']) ? trim($_GET['fuel']) : "";
$type = isset($_GET['type']) ? trim($_GET['type']) : "";
$status = isset($_GET['status']) ? trim($_GET['status']) : "";
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : "newest";


// -------------------------
// BUILD QUERY
// -------------------------

$sql = "SELECT * FROM cars WHERE 1=1";

$params = [];
$types = "";

if ($search != "") {
    $sql .= " AND (car_name LIKE ? OR brand LIKE ? OR model LIKE ?)";
    
    $searchValue = "%" . $search . "%";
    
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    
    $types .= "sss";
}

if ($fuel != "") {
    $sql .= " AND fuel_type = ?";
    $params[] = $fuel;
    $types .= "s";
}

if ($type != "") {
    $sql .= " AND car_type = ?";
    $params[] = $type;
    $types .= "s";
}

if ($status != "") {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}


// -------------------------
// SORTING
// -------------------------

if ($sort == "price_low") {
    $sql .= " ORDER BY price_per_day ASC";
}
elseif ($sort == "price_high") {
    $sql .= " ORDER BY price_per_day DESC";
}
elseif ($sort == "name_az") {
    $sql .= " ORDER BY car_name ASC";
}
elseif ($sort == "name_za") {
    $sql .= " ORDER BY car_name DESC";
}
else {
    $sql .= " ORDER BY car_id DESC";
}


// -------------------------
// PREPARED STATEMENT
// -------------------------

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// -------------------------
// STATISTICS
// -------------------------

$totalCars = 0;
$availableCars = 0;
$unavailableCars = 0;

$statsQuery = mysqli_query($conn, "SELECT
    COUNT(*) AS total,
    SUM(status = 'Available') AS available,
    SUM(status != 'Available') AS unavailable
    FROM cars");

if ($statsQuery) {
    $stats = mysqli_fetch_assoc($statsQuery);

    $totalCars = $stats['total'];
    $availableCars = $stats['available'] ?? 0;
    $unavailableCars = $stats['unavailable'] ?? 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Cars - CarRental Admin</title>

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
        }

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

        .admin-name {
            background: white;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .add-btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 11px 18px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .add-btn:hover {
            background: #1d4ed8;
        }

        /* STAT CARDS */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .stat-card p {
            font-size: 28px;
            font-weight: bold;
        }

        /* FILTER */

        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            margin-bottom: 25px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
            gap: 10px;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            outline: none;
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: #2563eb;
        }

        .filter-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 11px 18px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        .reset-btn {
            background: #6b7280;
            color: white;
            padding: 11px 15px;
            border-radius: 7px;
            text-decoration: none;
            display: inline-block;
        }

        /* TABLE */

        .table-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th,
        td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            color: #374151;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        .car-image {
            width: 80px;
            height: 55px;
            object-fit: cover;
            border-radius: 7px;
        }

        .no-image {
            width: 80px;
            height: 55px;
            background: #e5e7eb;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .car-name {
            font-weight: bold;
            color: #111827;
        }

        .small-text {
            color: #6b7280;
            margin-top: 4px;
        }

        /* STATUS */

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .available {
            background: #dcfce7;
            color: #166534;
        }

        .unavailable {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ACTION BUTTONS */

        .action-btn {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            margin-right: 5px;
        }

        .edit-btn {
            background: #f59e0b;
            color: white;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.85;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        /* RESPONSIVE */

        @media (max-width: 1100px) {

            .filter-form {
                grid-template-columns: 1fr 1fr 1fr;
            }

        }

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

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-form {
                grid-template-columns: 1fr;
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

    <a href="logout.php">
        🚪 Logout
    </a>

</div>


<!-- MAIN -->

<div class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <div>
            <h1>Manage Cars</h1>
            <p style="color:#6b7280; margin-top:5px;">
                Add, edit and manage your rental cars
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


    <!-- ADD CAR -->

    <a href="add_car.php" class="add-btn">
        + Add New Car
    </a>


    <!-- STATISTICS -->

    <div class="stats">

        <div class="stat-card">

            <h3>Total Cars</h3>

            <p>
                <?php echo $totalCars; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>Available Cars</h3>

            <p>
                <?php echo $availableCars; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>Unavailable Cars</h3>

            <p>
                <?php echo $unavailableCars; ?>
            </p>

        </div>

    </div>


    <!-- FILTER BOX -->

    <div class="filter-box">

        <form method="GET" action="cars.php" class="filter-form">

            <input
                type="text"
                name="search"
                placeholder="Search car, brand or model..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <select name="fuel">

                <option value="">All Fuel Types</option>

                <option value="Petrol"
                    <?php if ($fuel == "Petrol") echo "selected"; ?>>
                    Petrol
                </option>

                <option value="Diesel"
                    <?php if ($fuel == "Diesel") echo "selected"; ?>>
                    Diesel
                </option>

                <option value="CNG"
                    <?php if ($fuel == "CNG") echo "selected"; ?>>
                    CNG
                </option>

                <option value="Electric"
                    <?php if ($fuel == "Electric") echo "selected"; ?>>
                    Electric
                </option>

            </select>


            <select name="type">

                <option value="">All Car Types</option>

                <option value="Hatchback"
                    <?php if ($type == "Hatchback") echo "selected"; ?>>
                    Hatchback
                </option>

                <option value="Sedan"
                    <?php if ($type == "Sedan") echo "selected"; ?>>
                    Sedan
                </option>

                <option value="SUV"
                    <?php if ($type == "SUV") echo "selected"; ?>>
                    SUV
                </option>

                <option value="MUV"
                    <?php if ($type == "MUV") echo "selected"; ?>>
                    MUV
                </option>

            </select>


            <select name="status">

                <option value="">All Status</option>

                <option value="Available"
                    <?php if ($status == "Available") echo "selected"; ?>>
                    Available
                </option>

                <option value="Unavailable"
                    <?php if ($status == "Unavailable") echo "selected"; ?>>
                    Unavailable
                </option>

            </select>


            <select name="sort" onchange="this.form.submit()">

                <option value="newest"
                    <?php if ($sort == "newest") echo "selected"; ?>>
                    Newest
                </option>

                <option value="price_low"
                    <?php if ($sort == "price_low") echo "selected"; ?>>
                    Price: Low to High
                </option>

                <option value="price_high"
                    <?php if ($sort == "price_high") echo "selected"; ?>>
                    Price: High to Low
                </option>

                <option value="name_az"
                    <?php if ($sort == "name_az") echo "selected"; ?>>
                    Name: A-Z
                </option>

                <option value="name_za"
                    <?php if ($sort == "name_za") echo "selected"; ?>>
                    Name: Z-A
                </option>

            </select>


            <button type="submit" class="filter-btn">
                Search
            </button>

            <a href="cars.php" class="reset-btn">
                Reset
            </a>

        </form>

    </div>


    <!-- CAR TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>Image</th>
                    <th>Car</th>
                    <th>Type</th>
                    <th>Fuel</th>
                    <th>Seats</th>
                    <th>Price / Day</th>
                    <th>Status</th>
                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>

            <?php if (mysqli_num_rows($result) > 0) { ?>

                <?php while ($car = mysqli_fetch_assoc($result)) { ?>

                    <tr>

                        <!-- IMAGE -->

                        <td>

                            <?php
                            $carImage = trim($car['image'] ?? '');
                            $carImageUrl = '';

                            if ($carImage !== '') {
                                if (
                                    strpos($carImage, 'http://') === 0 ||
                                    strpos($carImage, 'https://') === 0
                                ) {
                                    $carImageUrl = $carImage;
                                } else {
                                    $carImageUrl = '../images/' . ltrim($carImage, '/');
                                }
                            }
                            ?>

                            <?php if (!empty($carImageUrl)) { ?>

                                <img
                                    src="<?php echo htmlspecialchars($carImageUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="car-image"
                                    alt="<?php echo htmlspecialchars($car['car_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                >

                            <?php } else { ?>

                                <div class="no-image">
                                    🚗
                                </div>

                            <?php } ?>

                        </td>


                        <!-- CAR NAME -->

                        <td>

                            <div class="car-name">
                                <?php echo htmlspecialchars($car['car_name']); ?>
                            </div>

                            <div class="small-text">
                                <?php echo htmlspecialchars($car['brand']); ?>
                                -
                                <?php echo htmlspecialchars($car['model']); ?>
                            </div>

                        </td>


                        <!-- TYPE -->

                        <td>
                            <?php echo htmlspecialchars($car['car_type']); ?>
                        </td>


                        <!-- FUEL -->

                        <td>
                            <?php echo htmlspecialchars($car['fuel_type']); ?>
                        </td>


                        <!-- SEATS -->

                        <td>
                            <?php echo htmlspecialchars($car['seats']); ?>
                        </td>


                        <!-- PRICE -->

                        <td>
                            ₹<?php echo number_format($car['price_per_day'], 2); ?>
                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php if ($car['status'] == "Available") { ?>

                                <span class="status available">
                                    Available
                                </span>

                            <?php } else { ?>

                                <span class="status unavailable">
                                    <?php echo htmlspecialchars($car['status']); ?>
                                </span>

                            <?php } ?>

                        </td>


                        <!-- ACTIONS -->

                        <td>

                            <a
                                href="edit_car.php?id=<?php echo $car['car_id']; ?>"
                                class="action-btn edit-btn"
                            >
                                ✏️ Edit
                            </a>


                            <a
                                href="delete_car.php?id=<?php echo $car['car_id']; ?>"
                                class="action-btn delete-btn"
                                onclick="return confirm('Are you sure you want to delete this car?');"
                            >
                                🗑️ Delete
                            </a>

                        </td>

                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td colspan="8" class="empty">

                        🚗 No cars found.

                        <br><br>

                        Try changing your search or filters.

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>
