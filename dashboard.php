<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once "config.php";

// Fetch user details
$user_data = find_user_mysql('id', $_SESSION["id"]);

if(!$user_data){
    echo "Error: User not found.";
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SecureAuthX</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-card {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            margin-top: 20px;
            border: 1px solid #eee;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #888;
            font-size: 13px;
            text-transform: uppercase;
        }
        .value {
            color: #333;
            font-weight: 500;
        }
        .btn-logout {
            background: #ff4d4d;
            margin-top: 30px;
        }
        .btn-logout:hover {
            background: #e60000;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo">SecureAuth<span>X</span></div>
        <nav class="nav-links">
            <a href="logout.php" class="btn-login-nav">Logout</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h2>User Dashboard</h2>
            <p class="subtitle">Welcome back, <b><?php echo xss_clean($_SESSION["username"]); ?></b>!</p>
            
            <div class="dashboard-card">
                <div class="info-item">
                    <span class="label">Full Name</span>
                    <span class="value"><?php echo xss_clean($user_data['first_name'] . ' ' . $user_data['last_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Email</span>
                    <span class="value"><?php echo xss_clean($user_data['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Mobile</span>
                    <span class="value"><?php echo xss_clean($user_data['mobile_no']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Gender</span>
                    <span class="value"><?php echo ucfirst(xss_clean($user_data['gender'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Address</span>
                    <span class="value"><?php echo xss_clean($user_data['address_line1'] . ($user_data['address_line2'] ? ', ' . $user_data['address_line2'] : '')); ?></span>
                </div>
            </div>

            <a href="logout.php" class="btn-register btn-logout" style="display: block; text-align: center; text-decoration: none;">Sign Out</a>
        </div>
    </main>
</body>
</html>
