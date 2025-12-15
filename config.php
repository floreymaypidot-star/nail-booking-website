<?php
/**
 * Database Configuration
 * 
 * This file contains all database configuration settings
 * for the Nail Booking Website application.
 * 
 * Created: 2025-12-15 11:20:05 UTC
 */

// Database Connection Parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nail_booking_db');
define('DB_PORT', 3306);

// Connection Options
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Database Connection String
$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;
$db_port = DB_PORT;

// Create MySQLi Connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset(DB_CHARSET);
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Application Settings
define('APP_NAME', 'Nail Booking Website');
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost/nail-booking-website');

// Session Configuration
session_start();
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

?>
