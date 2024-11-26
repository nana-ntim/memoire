<?php
// File: admin/reports.php

require_once "../includes/security/admin_security.inc.php";
require_once "../includes/admin/reports_data.inc.php";

// Check admin access and get PDO
$pdo = require_admin_priv();

// Get reports data
$stats = get_platform_stats($pdo);
$user_growth = get_user_growth_data($pdo);
$activity_metrics = get_activity_metrics($pdo);
$retention_data = get_retention_metrics($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Memoire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin/common.css">
    <link rel="stylesheet" href="../styles/admin/reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include_once '../components/admin/navbar.php'; ?>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-content">
                <h1>Platform Analytics</h1>
                <p class="header-subtitle">Comprehensive insights and metrics</p>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <h3>Total Users</h3>
                    <div class="metric-value">
                        <?php echo number_format($stats['total_users']); ?>
                        <span class="trend positive">
                            +<?php echo number_format($stats['new_this_month']); ?> this month
                        </span>
                    </div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon active">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="metric-content">
                    <h3>Active Today</h3>
                    <div class="metric-value">
                        <?php echo number_format($stats['active_today']); ?>
                        <span class="metric-label">active users</span>
                    </div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon entries">
                    <i class="fas fa-book"></i>
                </div>
                <div class="metric-content">
                    <h3>Total Entries</h3>
                    <div class="metric-value">
                        <?php echo number_format($stats['total_entries']); ?>
                        <span class="trend <?php echo $stats['entries_trend'] > 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($stats['entries_trend'] > 0 ? '+' : '') . number_format($stats['entries_trend'], 1); ?>% vs last month
                        </span>
                    </div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon collections">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="metric-content">
                    <h3>Collections</h3>
                    <div class="metric-value">
                        <?php echo number_format($stats['total_collections']); ?>
                        <span class="trend <?php echo $stats['collections_trend'] > 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($stats['collections_trend'] > 0 ? '+' : '') . number_format($stats['collections_trend'], 1); ?>% vs last month
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- User Growth Chart -->
            <div class="chart-card wide">
                <div class="chart-header">
                    <h3>User Growth</h3>
                    <div class="chart-actions">
                        <select id="growthPeriod" onchange="updateGrowthChart(this.value)">
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="180">Last 180 Days</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <!-- Activity Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Platform Activity</h3>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Retention Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>User Retention</h3>
                </div>
                <div class="chart-container">
                    <canvas id="retentionChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize chart data
        const userGrowthData = <?php echo json_encode($user_growth); ?>;
        const activityData = <?php echo json_encode($activity_metrics); ?>;
        const retentionData = <?php echo json_encode($retention_data); ?>;

        // Chart.js configuration
        Chart.defaults.color = '#9CA3AF';
        Chart.defaults.font.family = "'Inter', sans-serif";

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    </script>
    <script src="../js/admin/reports.js"></script>
    <script src="../js/admin/navbar.js"></script>
</body>
</html>