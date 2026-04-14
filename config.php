<?php
// Database Configuration (MySQL)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'secureauth_db');

// CSV Data File Name
define('CSV_FILE', 'users.csv');

// Google reCAPTCHA Keys
define('RECAPTCHA_SITE_KEY', '6LfP7IMsAAAAAFrTBS1cGa0_3naP-_nSiZm2tv3n');
define('RECAPTCHA_SECRET_KEY', '6LfP7IMsAAAAAK-8ULkaE1x-eYYEOdAlqc3t20qs');

try {
    // MySQL Connection using PDO
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create Database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    // Create Table if not exists
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(30) NOT NULL,
        last_name VARCHAR(30) NOT NULL,
        address_line1 TEXT NOT NULL,
        address_line2 TEXT,
        mobile_no VARCHAR(20) NOT NULL,
        country_code VARCHAR(10) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($query);
} catch (PDOException $e) {
    // If MySQL fails, we can still log the error, but we'll try to rely on CSV if needed
    // For this project, we assume MySQL is required as per your request
    die("ERROR: Could not connect to MySQL. " . $e->getMessage());
}

// Initialize CSV file if it doesn't exist
if (!file_exists(CSV_FILE)) {
    $header = ["id", "email", "username", "password", "first_name", "last_name", "address_line1", "address_line2", "mobile_no", "country_code", "gender", "created_at"];
    $f = fopen(CSV_FILE, 'w');
    fputcsv($f, $header);
    fclose($f);
}

/**
 * Helper function to save to CSV
 */
function save_to_csv($data) {
    $f = fopen(CSV_FILE, 'a');
    fputcsv($f, $data);
    fclose($f);
}

/**
 * Helper function to prevent XSS by escaping HTML characters
 */
function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to find user in MySQL (Primary Source of Truth)
 */
function find_user_mysql($field, $value) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE $field = ?");
    $stmt->execute([$value]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
