<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = mysqli_connect("db", "root", "root", "asset_db");

if (! $conn) {
    die("DB Connection Failed: " . mysqli_connect_error());
}
?>
