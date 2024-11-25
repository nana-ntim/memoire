<?php
// File: admin/settings.php

require_once "../includes/security/admin_security.inc.php";
require_once "../includes/admin/settings_data.inc.php";

// Check admin access and get PDO
$pdo = require_admin_priv();

try {
    // Get system stats
    $stats = get_system_stats($pdo);
    
    // Get PHP settings
    $php_settings = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit')
    ];

    // Get success/error messages
    $success = $_SESSION['admin_settings_success'] ?? '';
    $error = $_SESSION['admin_settings_error'] ?? '';
    
    // Clear messages
    unset($_SESSION['admin_settings_success'], $_SESSION['admin_settings_error']);

} catch (Exception $e) {
    error_log("Admin settings error: " . $e->getMessage());
    $error = "An error occurred while loading settings";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Memoire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin/common.css">
    <link rel="stylesheet" href="../styles/admin/settings.css">
</head>
<body>
    <?php include_once '../components/admin/navbar.php'; ?>

    <div class="settings-container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- System Overview -->
        <div class="settings-section">
            <h3 class="section-title">System Overview</h3>
            <div class="overview-grid">
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="overview-details">
                        <span class="overview-label">Total Users</span>
                        <span class="overview-value"><?php echo number_format($stats['total_users']); ?></span>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="overview-details">
                        <span class="overview-label">Total Entries</span>
                        <span class="overview-value"><?php echo number_format($stats['total_entries']); ?></span>
                    </div>
                </div>

                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="overview-details">
                        <span class="overview-label">Storage Used</span>
                        <span class="overview-value"><?php echo $stats['storage_used']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP Configuration -->
        <div class="settings-section">
            <h3 class="section-title">PHP Configuration</h3>
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="setting-header">
                        <span class="setting-name">Upload Max Filesize</span>
                        <span class="setting-value"><?php echo $php_settings['upload_max_filesize']; ?></span>
                    </div>
                    <p class="setting-description">Maximum allowed file size for uploads</p>
                </div>

                <div class="setting-card">
                    <div class="setting-header">
                        <span class="setting-name">Post Max Size</span>
                        <span class="setting-value"><?php echo $php_settings['post_max_size']; ?></span>
                    </div>
                    <p class="setting-description">Maximum size of POST data</p>
                </div>

                <div class="setting-card">
                    <div class="setting-header">
                        <span class="setting-name">Max Execution Time</span>
                        <span class="setting-value"><?php echo $php_settings['max_execution_time']; ?>s</span>
                    </div>
                    <p class="setting-description">Maximum execution time of scripts</p>
                </div>

                <div class="setting-card">
                    <div class="setting-header">
                        <span class="setting-name">Memory Limit</span>
                        <span class="setting-value"><?php echo $php_settings['memory_limit']; ?></span>
                    </div>
                    <p class="setting-description">Maximum amount of memory a script can consume</p>
                </div>
            </div>
        </div>

        <!-- Maintenance Tools -->
        <div class="settings-section">
            <h3 class="section-title">Maintenance Tools</h3>
            <div class="tools-grid">
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Clean Unused Files</h4>
                        <p>Remove orphaned files from storage</p>
                        <button class="btn btn-secondary" onclick="cleanUnusedFiles()">
                            Start Cleanup
                        </button>
                    </div>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Optimize Database</h4>
                        <p>Optimize database tables and indexes</p>
                        <button class="btn btn-secondary" onclick="optimizeDatabase()">
                            Start Optimization
                        </button>
                    </div>
                </div>

                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Backup System</h4>
                        <p>Create a complete system backup</p>
                        <button class="btn btn-secondary" onclick="createBackup()">
                            Create Backup
                        </button>
                    </div>
                </div>

                <div class="tool-card danger">
                    <div class="tool-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Reset System</h4>
                        <p>Reset the system to default settings</p>
                        <button class="btn btn-danger" onclick="confirmReset()">
                            Reset System
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin/settings.js"></script>
</body>
</html>