<?php
// File: includes/admin/dashboard_data.inc.php

function get_dashboard_metrics($pdo) {
    try {
        // Get total users (excluding admins)
        $query = "SELECT COUNT(*) FROM Users WHERE is_admin = FALSE";
        $total_users = $pdo->query($query)->fetchColumn();

        // Get total collections
        $query = "SELECT COUNT(*) FROM Collections";
        $total_collections = $pdo->query($query)->fetchColumn();

        // Get total entries
        $query = "SELECT COUNT(*) FROM JournalEntries";
        $total_entries = $pdo->query($query)->fetchColumn();

        // Calculate storage usage
        $uploads_dir = __DIR__ . "/../../uploads";
        $storage_used = format_storage_size(get_directory_size($uploads_dir));

        return [
            'total_users' => $total_users,
            'total_collections' => $total_collections,
            'total_entries' => $total_entries,
            'storage_used' => $storage_used
        ];
    } catch (PDOException $e) {
        error_log("Error fetching dashboard metrics: " . $e->getMessage());
        return [
            'total_users' => 0,
            'total_collections' => 0,
            'total_entries' => 0,
            'storage_used' => '0 B'
        ];
    }
}

function get_directory_size($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    return $size;
}

function format_storage_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function get_recent_activity($pdo, $limit = 10) {
    try {
        $query = "SELECT 
                    'entry' as type,
                    u.firstName,
                    u.lastName,
                    e.title as description,
                    e.created_at
                 FROM JournalEntries e
                 JOIN Users u ON e.user_id = u.user_id
                 UNION ALL
                 SELECT 
                    'collection' as type,
                    u.firstName,
                    u.lastName,
                    c.name as description,
                    c.created_at
                 FROM Collections c
                 JOIN Users u ON c.user_id = u.user_id
                 ORDER BY created_at DESC
                 LIMIT :limit";
                 
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching recent activity: " . $e->getMessage());
        return [];
    }
}