<?php
// Online hosting: set DB_HOST, DB_USER, DB_PASSWORD and DB_NAME as environment variables.
// Local XAMPP: the defaults below work with the usual root/no-password setup.
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'car_rental_db';
$port = getenv('DB_PORT') ?: 3306;

$conn = new mysqli($host, $username, $password, $database, (int)$port);

if ($conn->connect_error) {
    die('Database connection failed. Please check the database settings.');
}

$conn->set_charset('utf8mb4');
?>
