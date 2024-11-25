<?php
declare(strict_types=1);

function get_platform_stats($pdo): array {
    try {
        // Get total users (excluding admins)
        $query = "SELECT COUNT(*) FROM Users WHERE is_admin = FALSE";
        $total_users = $pdo->query($query)->fetchColumn();

        // Get active users today
        $query = "SELECT COUNT(DISTINCT user_id) FROM JournalEntries 
                 WHERE DATE(created_at) = CURRENT_DATE";
        $active_today = $pdo->query($query)->fetchColumn();

        // Get new users this month
        $query = "SELECT COUNT(*) FROM Users 
                 WHERE is_admin = FALSE 
                 AND MONTH(created_at) = MONTH(CURRENT_DATE)
                 AND YEAR(created_at) = YEAR(CURRENT_DATE)";
        $new_this_month = $pdo->query($query)->fetchColumn();

        // Get total entries and calculate monthly trend
        $current_month_entries = "SELECT COUNT(*) FROM JournalEntries 
                                WHERE MONTH(created_at) = MONTH(CURRENT_DATE)
                                AND YEAR(created_at) = YEAR(CURRENT_DATE)";
        $last_month_entries = "SELECT COUNT(*) FROM JournalEntries 
                             WHERE MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
                             AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))";
        
        $current_entries = $pdo->query($current_month_entries)->fetchColumn();
        $last_entries = $pdo->query($last_month_entries)->fetchColumn();
        
        $entries_trend = $last_entries > 0 ? 
            (($current_entries - $last_entries) / $last_entries) * 100 : 0;

        // Get total collections and calculate monthly trend
        $current_month_collections = "SELECT COUNT(*) FROM Collections 
                                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE)
                                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
        $last_month_collections = "SELECT COUNT(*) FROM Collections 
                                 WHERE MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
                                 AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))";
        
        $current_collections = $pdo->query($current_month_collections)->fetchColumn();
        $last_collections = $pdo->query($last_month_collections)->fetchColumn();
        
        $collections_trend = $last_collections > 0 ? 
            (($current_collections - $last_collections) / $last_collections) * 100 : 0;

        // Calculate average entries per user
        $query = "SELECT COALESCE(AVG(entry_count), 0) as avg_entries
                 FROM (
                     SELECT user_id, COUNT(*) as entry_count
                     FROM JournalEntries
                     GROUP BY user_id
                 ) as user_entries";
        $avg_entries = $pdo->query($query)->fetchColumn();

        $total_entries = $pdo->query("SELECT COUNT(*) FROM JournalEntries")->fetchColumn();
        $total_collections = $pdo->query("SELECT COUNT(*) FROM Collections")->fetchColumn();

        return [
            'total_users' => $total_users,
            'active_today' => $active_today,
            'new_this_month' => $new_this_month,
            'total_entries' => $total_entries,
            'total_collections' => $total_collections,
            'avg_entries_per_user' => $avg_entries,
            'entries_trend' => $entries_trend,
            'collections_trend' => $collections_trend
        ];

    } catch (PDOException $e) {
        error_log("Error fetching platform stats: " . $e->getMessage());
        return [
            'total_users' => 0,
            'active_today' => 0,
            'new_this_month' => 0,
            'total_entries' => 0,
            'total_collections' => 0,
            'avg_entries_per_user' => 0,
            'entries_trend' => 0,
            'collections_trend' => 0
        ];
    }
}

function get_user_growth_data($pdo): array {
    try {
        // Get daily user counts for the last 30 days
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_users,
                    (
                        SELECT COUNT(*) 
                        FROM Users 
                        WHERE created_at <= outer_users.created_at
                        AND is_admin = FALSE
                    ) as total_users,
                    (
                        SELECT COUNT(DISTINCT user_id) 
                        FROM JournalEntries 
                        WHERE DATE(created_at) = DATE(outer_users.created_at)
                    ) as active_users
                 FROM Users outer_users
                 WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                 AND is_admin = FALSE
                 GROUP BY DATE(created_at)
                 ORDER BY date";
                 
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching user growth data: " . $e->getMessage());
        return [];
    }
}

function get_activity_metrics($pdo): array {
    try {
        // Get daily activity counts for the last 30 days
        $query = "SELECT 
                    DATE(e.created_at) as date,
                    COUNT(DISTINCT e.entry_id) as entries,
                    COUNT(DISTINCT c.collection_id) as collections
                 FROM JournalEntries e
                 LEFT JOIN Collections c ON DATE(e.created_at) = DATE(c.created_at)
                 WHERE e.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                 GROUP BY DATE(e.created_at)
                 ORDER BY date";
                 
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching activity metrics: " . $e->getMessage());
        return [];
    }
}

function get_retention_metrics($pdo): array {
    try {
        // Calculate retention rates
        // Daily retention: users who created entries yesterday and today
        $daily_query = "SELECT 
            (COUNT(DISTINCT CASE WHEN DATE(created_at) = CURRENT_DATE THEN user_id END) * 100.0 / 
             NULLIF(COUNT(DISTINCT CASE WHEN DATE(created_at) = DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY) THEN user_id END), 0)) 
            as daily_retention
            FROM JournalEntries";
        
        // Weekly retention: users who created entries this week vs last week
        $weekly_query = "SELECT 
            (COUNT(DISTINCT CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURRENT_DATE) THEN user_id END) * 100.0 / 
             NULLIF(COUNT(DISTINCT CASE WHEN YEARWEEK(created_at) = YEARWEEK(DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK)) THEN user_id END), 0)) 
            as weekly_retention
            FROM JournalEntries";
        
        // Monthly retention: users who created entries this month vs last month
        $monthly_query = "SELECT 
            (COUNT(DISTINCT CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m') THEN user_id END) * 100.0 / 
             NULLIF(COUNT(DISTINCT CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m') THEN user_id END), 0)) 
            as monthly_retention
            FROM JournalEntries";

        return [
            'daily_retention' => $pdo->query($daily_query)->fetchColumn() ?? 0,
            'weekly_retention' => $pdo->query($weekly_query)->fetchColumn() ?? 0,
            'monthly_retention' => $pdo->query($monthly_query)->fetchColumn() ?? 0
        ];

    } catch (PDOException $e) {
        error_log("Error fetching retention metrics: " . $e->getMessage());
        return [
            'daily_retention' => 0,
            'weekly_retention' => 0,
            'monthly_retention' => 0
        ];
    }
}