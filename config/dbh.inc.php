<?php

// Setup data for the database connection
// These can be changed with the right details
// for any database
// $host = 'localhost';
// $dbname = 'webtech_fall2024_asamoah_ntim';
// $dbusername = 'asamoah.ntim';
// $dbpassword = 'Frimpomaah123#';

$host = 'localhost';
$dbname = 'memoire';
$dbusername = 'root';
$dbpassword = '';

// Now we try to create a connection with 
// the database and get error messages if
// the connection was unsuccessful
try {

    // The actual database object
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname",
        $dbusername,
        $dbpassword
    );

    // This gives the style our errors 
    // should be output
    $pdo -> setAttribute(
        PDO::ATTR_ERRMODE, 
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    die("Connection failed: " . $e -> getMessage());
}