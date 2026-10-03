<?php
require_once __DIR__ . "/session.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";


// -------------------------
// SEARCH / SORT
// -------------------------

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : "newest";


// -------------------------
// BUILD QUERY
// -------------------------

$sql = "SELECT user_id, name, email, phone, created_at
        FROM users
        WHERE 1=1";

$params = [];
$types = "";

if ($search != "") {

    $sql .= " AND (
                name LIKE ?
                OR email LIKE ?
                OR phone LIKE ?
              )";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}


// -------------------------
// SORTING
// -------------------------

if ($sort == "oldest") {

    $sql .= " ORDER BY created_at ASC";

} elseif ($sort == "name_az") {

    $sql .= " ORDER BY name ASC";

} elseif ($sort == "name_za") {

    $sql .= " ORDER BY name DESC";

} else {

    $sql .= " ORDER BY created_at DESC";
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
// TOTAL USERS
// -------------------------

$totalUsers = 0;

$countQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

if ($countQuery) {

    $countData = mysqli_fetch_assoc($countQuery);

    $totalUsers = $countData['total'];
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

    <title>Manage Users - CarRental Admin</title>


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


        /* STAT CARD */

        .stats {

            margin-bottom: 25px;
        }


        .stat-card {

            background: white;

            padding: 22px;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0,0,0,0.07);

            width: 250px;
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


        /* SEARCH BOX */

        .search-box {

            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow: 0 2px 10px rgba(0,0,0,0.07);

            margin-bottom: 25px;
        }


        .search-form {

            display: grid;

            grid-template-columns: 2fr 1fr auto auto;

            gap: 10px;
        }


        .search-form input,
        .search-form select {

            width: 100%;

            padding: 11px;

            border: 1px solid #d1d5db;

            border-radius: 7px;

            outline: none;
        }


        .search-form input:focus,
        .search-form select:focus {

            border-color: #2563eb;
        }


        .search-btn {

            background: #2563eb;

            color: white;

            border: none;

            padding: 11px 18px;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;
        }


        .search-btn:hover {

            background: #1d4ed8;
        }


        .reset-btn {

            background: #6b7280;

            color: white;

            padding: 11px 16px;

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

            min-width: 750px;
        }


        th,
        td {

            padding: 15px;

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


        .user-id {

            font-weight: bold;

            color: #2563eb;
        }


        .user-name {

            font-weight: bold;

            color: #111827;
        }


        .email {

            color: #374151;
        }


        .phone {

            color: #374151;
        }


        .date {

            color: #6b7280;
        }


        .user-icon {

            width: 40px;

            height: 40px;

            background: #e5e7eb;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;
        }


        .user-cell {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .empty {

            text-align: center;

            padding: 45px;

            color: #6b7280;
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


            .stat-card {

                width: 100%;
            }


            .search-form {

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


    <a href="cars.php">
        🚘 Manage Cars
    </a>


    <a href="add_car.php">
        ➕ Add Car
    </a>


    <a href="users.php" class="active">
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

            <h1>Manage Users</h1>

            <p class="subtitle">
                View and search registered customers
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


    <!-- STATISTICS -->

    <div class="stats">

        <div class="stat-card">

            <h3>Total Registered Users</h3>

            <p>
                <?php echo $totalUsers; ?>
            </p>

        </div>

    </div>


    <!-- SEARCH -->

    <div class="search-box">

        <form method="GET" class="search-form">


            <input
                type="text"
                name="search"
                placeholder="Search by name, email or phone..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <select name="sort">

                <option value="newest"
                    <?php
                    if ($sort == "newest") echo "selected";
                    ?>
                >
                    Newest First
                </option>


                <option value="oldest"
                    <?php
                    if ($sort == "oldest") echo "selected";
                    ?>
                >
                    Oldest First
                </option>


                <option value="name_az"
                    <?php
                    if ($sort == "name_az") echo "selected";
                    ?>
                >
                    Name A-Z
                </option>


                <option value="name_za"
                    <?php
                    if ($sort == "name_za") echo "selected";
                    ?>
                >
                    Name Z-A
                </option>

            </select>


            <button
                type="submit"
                class="search-btn"
            >
                🔍 Search
            </button>


            <a
                href="users.php"
                class="reset-btn"
            >
                Reset
            </a>

        </form>

    </div>


    <!-- USERS TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>User</th>

                    <th>Email</th>

                    <th>Phone</th>

                    <th>Registered On</th>

                </tr>

            </thead>


            <tbody>


            <?php if (mysqli_num_rows($result) > 0) { ?>


                <?php while ($user = mysqli_fetch_assoc($result)) { ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <span class="user-id">

                                #
                                <?php
                                echo $user['user_id'];
                                ?>

                            </span>

                        </td>


                        <!-- USER -->

                        <td>

                            <div class="user-cell">


                                <div class="user-icon">
                                    👤
                                </div>


                                <div>

                                    <div class="user-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $user['name']
                                        );

                                        ?>

                                    </div>

                                </div>


                            </div>

                        </td>


                        <!-- EMAIL -->

                        <td>

                            <span class="email">

                                <?php

                                echo htmlspecialchars(
                                    $user['email']
                                );

                                ?>

                            </span>

                        </td>


                        <!-- PHONE -->

                        <td>

                            <span class="phone">

                                <?php

                                echo !empty($user['phone'])
                                    ? htmlspecialchars($user['phone'])
                                    : "Not provided";

                                ?>

                            </span>

                        </td>


                        <!-- DATE -->

                        <td>

                            <span class="date">

                                <?php

                                echo date(
                                    "d M Y, h:i A",
                                    strtotime($user['created_at'])
                                );

                                ?>

                            </span>

                        </td>


                    </tr>


                <?php } ?>


            <?php } else { ?>


                <tr>

                    <td
                        colspan="5"
                        class="empty"
                    >

                        👥 No users found.

                        <br><br>

                        Try changing your search.

                    </td>

                </tr>


            <?php } ?>


            </tbody>

        </table>

    </div>


</div>


</body>

</html>