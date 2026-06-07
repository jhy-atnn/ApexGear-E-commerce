<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "db_apexgear";

require_once __DIR__ . '/db_bootstrap.php';
apexgear_prepare_database($host, $username, $password, $database);

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
