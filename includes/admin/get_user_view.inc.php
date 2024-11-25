<?php
// File: includes/admin/get_user_view.inc.php

require_once "../../config/dbh.inc.php";
require_once "../security/admin_security.inc.php";

// Verify admin privileges
try {
    $pdo = require_admin_priv();
} catch (Exception $e) {
    http_response_code(403);
    die("Unauthorized access");
}

// Get user ID from query string
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id) {
    http_response_code(400);
    die("Invalid user ID");
}

try {
    // Get user details with stats
    $query = "SELECT 
                u.*,
                COUNT(DISTINCT j.entry_id) as total_entries,
                COUNT(DISTINCT c.collection_id) as total_collections,
                MAX(j.created_at) as last_entry_date
              FROM Users u
              LEFT JOIN JournalEntries j ON u.user_id = j.user_id
              LEFT JOIN Collections c ON u.user_id = c.user_id
              WHERE u.user_id = :user_id AND u.is_admin = 0
              GROUP BY u.user_id";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        die("User not found");
    }

    // Get recent entries
    $query = "SELECT entry_id, title, created_at
              FROM JournalEntries 
              WHERE user_id = :user_id
              ORDER BY created_at DESC
              LIMIT 5";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $recent_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate the HTML view
    ?>
    <div class="user-details">
        <div class="user-profile">
            <img src="<?php echo !empty($user['profile_image']) ? '../' . htmlspecialchars($user['profile_image']) : '../assets/bg.jpg'; ?>" 
                 alt="Profile" 
                 class="user-avatar">
            <div class="user-info">
                <h2><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h2>
                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="user-status <?php echo $user['last_login'] > (time() - 86400) ? 'active' : 'inactive'; ?>">
                    <?php echo $user['last_login'] > (time() - 86400) ? 'Currently Active' : 'Last active ' . date('M j, Y', strtotime($user['last_login'])); ?>
                </p>
            </div>
        </div>

        <div class="user-stats">
            <div class="stat-group">
                <div class="stat">
                    <span class="stat-label">Total Entries</span>
                    <span class="stat-value"><?php echo number_format($user['total_entries']); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Collections</span>
                    <span class="stat-value"><?php echo number_format($user['total_collections']); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Member Since</span>
                    <span class="stat-value"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($recent_entries)): ?>
        <div class="recent-activity">
            <h3>Recent Activity</h3>
            <ul class="activity-list">
                <?php foreach ($recent_entries as $entry): ?>
                    <li>
                        <span class="activity-title"><?php echo htmlspecialchars($entry['title']); ?></span>
                        <span class="activity-date"><?php echo date('M j, Y', strtotime($entry['created_at'])); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php

} catch (Exception $e) {
    http_response_code(500);
    die("Error fetching user details: " . $e->getMessage());
}