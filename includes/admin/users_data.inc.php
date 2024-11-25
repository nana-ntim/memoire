<?php
// File: includes/admin/users_data.inc.php

declare(strict_types=1);

function get_users_data($pdo, array $params): array {
    try {
        $per_page = 12;
        $page = $params['page'] ?? 1;
        $offset = ($page - 1) * $per_page;

        // Base query without LIMIT
        $base_query = "SELECT 
                        u.*,
                        COUNT(DISTINCT j.entry_id) as entry_count,
                        COUNT(DISTINCT c.collection_id) as collection_count,
                        UNIX_TIMESTAMP(u.last_login) as last_active
                      FROM Users u
                      LEFT JOIN JournalEntries j ON u.user_id = j.user_id
                      LEFT JOIN Collections c ON u.user_id = c.user_id";

        $where_conditions = ['u.is_admin = 0']; // Exclude admin users
        $params_array = [];

        // Add search condition if search term exists
        if (!empty($params['search'])) {
            $where_conditions[] = "(u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search)";
            $params_array[':search'] = "%" . $params['search'] . "%";
        }

        // Add filter conditions
        if (!empty($params['filter'])) {
            switch($params['filter']) {
                case 'active':
                    $where_conditions[] = "u.last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                    break;
                case 'inactive':
                    $where_conditions[] = "u.last_login < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                    break;
            }
        }

        // Complete the base query
        $base_query .= " WHERE " . implode(' AND ', $where_conditions);
        $base_query .= " GROUP BY u.user_id";

        // Add sorting
        $base_query .= match($params['sort'] ?? 'newest') {
            'oldest' => " ORDER BY u.created_at ASC",
            'most_active' => " ORDER BY entry_count DESC",
            'least_active' => " ORDER BY entry_count ASC",
            default => " ORDER BY u.created_at DESC"
        };

        // Get total count for pagination
        $count_query = "SELECT COUNT(*) FROM Users u WHERE " . implode(' AND ', $where_conditions);
        $stmt = $pdo->prepare($count_query);
        foreach ($params_array as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total_users = $stmt->fetchColumn();
        $total_pages = ceil($total_users / $per_page);

        // Add pagination to the query
        $query = $base_query . " LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
        
        // Bind all parameters
        foreach ($params_array as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'users' => $users,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'total_users' => $total_users,
            'per_page' => $per_page
        ];

    } catch (PDOException $e) {
        error_log("Error in get_users_data: " . $e->getMessage());
        throw new Exception("Failed to fetch users data: " . $e->getMessage());
    }
}

function get_user_statistics($pdo): array {
    try {
        // Total users (excluding admins)
        $query = "SELECT COUNT(*) FROM Users WHERE is_admin = 0";
        $total_users = $pdo->query($query)->fetchColumn();

        // Active users in last 24 hours
        $query = "SELECT COUNT(*) FROM Users 
                 WHERE is_admin = 0 
                 AND last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $active_today = $pdo->query($query)->fetchColumn();

        // New users this month
        $query = "SELECT COUNT(*) FROM Users 
                 WHERE is_admin = 0 
                 AND created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
        $new_this_month = $pdo->query($query)->fetchColumn();

        return [
            'total_users' => $total_users,
            'active_today' => $active_today,
            'new_this_month' => $new_this_month
        ];
    } catch (PDOException $e) {
        error_log("Error in get_user_statistics: " . $e->getMessage());
        return [
            'total_users' => 0,
            'active_today' => 0,
            'new_this_month' => 0
        ];
    }
}