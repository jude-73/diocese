<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'diocesedb');

// Create connections
try {
    // MySQLi connection for legacy code
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("MySQLi Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // PDO connection for newer code
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Base URL - adjust this to match your installation
define('BASE_URL', 'http://localhost/ketakatsi-diocese');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>