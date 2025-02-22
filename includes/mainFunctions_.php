<?php
//session_start(); // Start the session

// Check if database name is set in session
if (!isset($_SESSION['dbname'])) {
    die("Database name is not set in the session.");
}

// Enable error display
ini_set("display_errors", 1);

// Variables for connection
$host = 'localhost';
$port = 5432;
$database = $_SESSION['dbname'];
$username = getenv('DB_USERNAME') ?: 'postgres'; // Use environment variable or fallback
$password = getenv('DB_PASSWORD') ?: 'Orellana'; // Use environment variable or fallback

// Initialize error flag
$errorDbConexion = false;

try {
    // Construct DSN and create PDO object with exception handling enabled
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    $dblink = new PDO($dsn, $username, $password);
    $dblink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit(); // Stop execution if connection fails (optional)
}