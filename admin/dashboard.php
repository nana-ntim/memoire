<?php
// File: admin/dashboard.php

require_once "../includes/security/admin_security.inc.php";
require_once "../includes/admin/dashboard_data.inc.php";

// Check admin access and get PDO
$pdo = require_admin_priv();

// Get dashboard metrics
$metrics = get_dashboard_metrics($pdo);

// Get current admin data
$admin = get_admin_data($pdo, $_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Memoire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin/common.css">
    <link rel="stylesheet" href="../styles/admin/dashboard.css">
</head>
<body>
    <?php include_once '../components/admin/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-content">
                <h1>Welcome, <?php echo htmlspecialchars($admin['firstName']); ?>!</h1>
                <p class="header-subtitle">System overview and key metrics</p>
            </div>
        </div>

        <div class="metrics-grid">
            <!-- Users Metric -->
            <div class="metric-card">
                <div class="metric-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <h3>Total Users</h3>
                    <div class="metric-value">
                        <?php echo number_format($metrics['total_users']); ?>
                        <span class="metric-label">registered users</span>
                    </div>
                </div>
            </div>

            <!-- Collections Metric -->
            <div class="metric-card">
                <div class="metric-icon collections">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="metric-content">
                    <h3>Collections</h3>
                    <div class="metric-value">
                        <?php echo number_format($metrics['total_collections']); ?>
                        <span class="metric-label">total collections</span>
                    </div>
                </div>
            </div>

            <!-- Journal Entries Metric -->
            <div class="metric-card">
                <div class="metric-icon entries">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="metric-content">
                    <h3>Journal Entries</h3>
                    <div class="metric-value">
                        <?php echo number_format($metrics['total_entries']); ?>
                        <span class="metric-label">total entries</span>
                    </div>
                </div>
            </div>

            <!-- Storage Usage Metric -->
            <div class="metric-card">
                <div class="metric-icon storage">
                    <i class="fas fa-database"></i>
                </div>
                <div class="metric-content">
                    <h3>Storage Used</h3>
                    <div class="metric-value">
                        <?php echo $metrics['storage_used']; ?>
                        <span class="metric-label">total space</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin/navbar.js"></script>
</body>
</html>