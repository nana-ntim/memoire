<?php
// File: admin/users.php

require_once "../includes/security/admin_security.inc.php";
require_once "../includes/admin/users_data.inc.php";

// Check admin access and get PDO
$pdo = require_admin_priv();

// Get query parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$filter = $_GET['filter'] ?? 'all';
$page = max(1, $_GET['page'] ?? 1);

// Get users data
$users_data = get_users_data($pdo, [
    'search' => $search,
    'sort' => $sort,
    'filter' => $filter,
    'page' => $page
]);

// Get user statistics
$stats = get_user_statistics($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Memoire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/admin/common.css">
    <link rel="stylesheet" href="../styles/admin/users.css">
</head>
<body>
    <?php include_once '../components/admin/navbar.php'; ?>

    <div class="alert-container" id="alertContainer"></div>

    <div class="users-container">
        <!-- Header Section -->
        <div class="users-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>Users</h1>
                    <p class="header-subtitle">Manage and monitor user accounts</p>
                </div>
                
                <!-- Quick Stats -->
                <div class="quick-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Users</span>
                        <span class="stat-value"><?php echo number_format($stats['total_users']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Active Today</span>
                        <span class="stat-value"><?php echo number_format($stats['active_today']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">New This Month</span>
                        <span class="stat-value"><?php echo number_format($stats['new_this_month']); ?></span>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" 
                               placeholder="Search users..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               onchange="updateSearch(this.value)">
                    </div>
                    
                    <div class="filters">
                        <select onchange="updateFilter(this.value)">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active Users</option>
                            <option value="inactive" <?php echo $filter === 'inactive' ? 'selected' : ''; ?>>Inactive Users</option>
                        </select>
                        
                        <select onchange="updateSort(this.value)">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="most_active" <?php echo $sort === 'most_active' ? 'selected' : ''; ?>>Most Active</option>
                            <option value="least_active" <?php echo $sort === 'least_active' ? 'selected' : ''; ?>>Least Active</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="users-grid">
            <?php if (empty($users_data['users'])): ?>
                <div class="no-users">
                    <i class="fas fa-users"></i>
                    <h3>No Users Found</h3>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($users_data['users'] as $user): ?>
                    <div class="user-card" data-user-id="<?php echo $user['user_id']; ?>">
                        <div class="user-header">
                            <img src="<?php echo !empty($user['profile_image']) ? '../' . htmlspecialchars($user['profile_image']) : '../assets/bg.jpg'; ?>" 
                                 alt="Profile" 
                                 class="user-avatar"
                                 onerror="this.src='../assets/bg.jpg'">
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h3>
                                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="user-status <?php echo $user['last_active'] > (time() - 86400) ? 'active' : 'inactive'; ?>">
                                <?php echo $user['last_active'] > (time() - 86400) ? 'Active' : 'Inactive'; ?>
                            </div>
                        </div>

                        <div class="user-stats">
                            <div class="stat">
                                <span class="stat-value"><?php echo number_format($user['entry_count']); ?></span>
                                <span class="stat-label">Entries</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value"><?php echo number_format($user['collection_count']); ?></span>
                                <span class="stat-label">Collections</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                <span class="stat-label">Joined</span>
                            </div>
                        </div>

                        <div class="user-actions">
                            <button onclick="viewUser(<?php echo $user['user_id']; ?>)" class="btn-view">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                            <button onclick="deleteUser(<?php echo $user['user_id']; ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($users_data['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&filter=<?php echo urlencode($filter); ?>" class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <span class="page-info">
                    Page <?php echo $page; ?> of <?php echo $users_data['total_pages']; ?>
                </span>

                <?php if ($page < $users_data['total_pages']): ?>
                    <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&filter=<?php echo urlencode($filter); ?>" class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- User View Modal -->
    <div id="userViewModal" class="modal">
        <div class="modal-content">
            <!-- Content loaded dynamically -->
        </div>
    </div>

    <script src="../js/admin/users.js"></script>
    <script src="../js/admin/navbar.js"></script>
</body>
</html>