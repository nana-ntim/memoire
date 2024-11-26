<?php
// File: includes/admin/user_view_modal_content.php
?>
<div class="modal-header">
    <h2>User Details</h2>
    <button class="close-btn" onclick="closeModal('userViewModal')">&times;</button>
</div>
<div class="modal-body">
    <div class="user-details">
        <div class="user-profile">
            <img src="<?php echo !empty($user['profile_image']) ? '../' . htmlspecialchars($user['profile_image']) : '../assets/bg.jpg'; ?>" 
                 alt="Profile" 
                 class="user-avatar"
                 onerror="this.src='../assets/bg.jpg'">
            <div class="user-info">
                <h2><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h2>
                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="user-status <?php echo $user['last_active'] > (time() - 86400) ? 'active' : 'inactive'; ?>">
                    <?php echo $user['last_active'] > (time() - 86400) ? 'Currently Active' : 'Last active ' . date('M j, Y', strtotime($user['last_login'])); ?>
                </p>
            </div>
        </div>

        <div class="user-stats">
            <div class="stat-group">
                <div class="stat">
                    <span class="stat-label">Total Entries</span>
                    <span class="stat-value"><?php echo number_format($user['entry_count']); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Collections</span>
                    <span class="stat-value"><?php echo number_format($user['collection_count']); ?></span>
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
</div>
<div class="modal-actions">
    <button class="btn btn-secondary" onclick="closeModal('userViewModal')">Close</button>
    <button class="btn btn-danger" onclick="deleteUserConfirm(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($user['firstName'] . ' ' . $user['lastName'])); ?>')">Delete User</button>
</div>