<?php
// File: includes/admin/settings_data.inc.php

declare(strict_types=1);

function get_system_stats($pdo): array {
    try {
        // Get total users (excluding admins)
        $query = "SELECT COUNT(*) FROM Users WHERE is_admin = FALSE";
        $total_users = $pdo->query($query)->fetchColumn();

        // Get total entries
        $query = "SELECT COUNT(*) FROM JournalEntries";
        $total_entries = $pdo->query($query)->fetchColumn();

        // Calculate storage usage
        $uploads_dir = __DIR__ . "/../../uploads";
        $total_space = get_directory_size($uploads_dir);

        return [
            'total_users' => $total_users,
            'total_entries' => $total_entries,
            'storage_used' => format_storage_size($total_space)
        ];
    } catch (PDOException $e) {
        error_log("Error fetching system stats: " . $e->getMessage());
        return [
            'total_users' => 0,
            'total_entries' => 0,
            'storage_used' => '0 B'
        ];
    }
}

function clean_unused_files($pdo): array {
    try {
        $cleaned = 0;
        $space_freed = 0;
        $errors = [];

        // Get list of all files in database
        $query = "SELECT file_path FROM EntryMedia 
                 UNION 
                 SELECT profile_image FROM Users 
                 WHERE profile_image IS NOT NULL";
        $db_files = $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);

        // Get all files in uploads directory
        $uploads_dir = __DIR__ . "/../../uploads";
        $filesystem_files = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploads_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $rel_path = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__ . "/../../")));
                $filesystem_files[] = $rel_path;
            }
        }

        // Find files that exist on filesystem but not in database
        $orphaned_files = array_diff($filesystem_files, $db_files);

        // Delete orphaned files
        foreach ($orphaned_files as $file) {
            $full_path = __DIR__ . "/../../" . $file;
            if (file_exists($full_path)) {
                $size = filesize($full_path);
                if (unlink($full_path)) {
                    $cleaned++;
                    $space_freed += $size;
                } else {
                    $errors[] = "Failed to delete: " . $file;
                }
            }
        }

        return [
            'success' => true,
            'cleaned' => $cleaned,
            'space_freed' => format_storage_size($space_freed),
            'errors' => $errors
        ];

    } catch (Exception $e) {
        error_log("Error cleaning unused files: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function optimize_database($pdo): array {
    try {
        $optimized = 0;
        $errors = [];

        // Get all tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            try {
                // Analyze table
                $pdo->query("ANALYZE TABLE `$table`");
                
                // Optimize table
                $pdo->query("OPTIMIZE TABLE `$table`");
                
                $optimized++;
            } catch (PDOException $e) {
                $errors[] = "Failed to optimize $table: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'optimized' => $optimized,
            'errors' => $errors
        ];

    } catch (PDOException $e) {
        error_log("Error optimizing database: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function create_system_backup($pdo): array {
    try {
        $backup_dir = __DIR__ . "/../../backups";
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backup_path = $backup_dir . "/backup_$timestamp";

        // 1. Backup database
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql = "";
        
        foreach ($tables as $table) {
            // Get create table statement
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $sql .= "\n\n" . $row['Create Table'] . ";\n\n";

            // Get table data
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $values = array_map([$pdo, 'quote'], $row);
                $sql .= "INSERT INTO `$table` VALUES (" . implode(",", $values) . ");\n";
            }
        }

        file_put_contents($backup_path . ".sql", $sql);

        // 2. Backup uploads directory
        $uploads_source = __DIR__ . "/../../uploads";
        $uploads_backup = $backup_path . "_uploads";
        
        if (is_dir($uploads_source)) {
            recurse_copy($uploads_source, $uploads_backup);
        }

        return [
            'success' => true,
            'backup_location' => $backup_path,
            'timestamp' => $timestamp
        ];

    } catch (Exception $e) {
        error_log("Error creating backup: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Helper functions
function get_directory_size($dir): int {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    return $size;
}

function format_storage_size($bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function reset_system($pdo): array {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Create backup before reset
        $backup_result = create_system_backup($pdo);
        if (!$backup_result['success']) {
            throw new Exception("Failed to create backup before reset");
        }

        // Truncate non-essential tables
        $tables = ['JournalEntries', 'EntryMedia', 'Collections', 'CollectionEntries'];
        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE `$table`");
        }

        // Delete non-admin users
        $pdo->exec("DELETE FROM Users WHERE is_admin = FALSE");

        // Clean uploads directory
        $uploads_dir = __DIR__ . "/../../uploads";
        if (is_dir($uploads_dir)) {
            deleteDirectory($uploads_dir);
            mkdir($uploads_dir);
            mkdir($uploads_dir . "/journal");
            mkdir($uploads_dir . "/profiles");
        }

        // Reset auto-increment values
        foreach ($tables as $table) {
            $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
        }

        $pdo->commit();
        return [
            'success' => true,
            'message' => "System reset completed successfully",
            'backup_location' => $backup_result['backup_location']
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    
    return rmdir($dir);
}