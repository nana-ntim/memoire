<?php
// File: pages/gratitude.php

require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/gratitude/gratitude_model.inc.php";
force_login();

// Get page number for pagination
$page = max(1, $_GET['page'] ?? 1);
$per_page = 5;

// Get gratitude entries
$gratitude_data = get_user_gratitude_entries($pdo, $_SESSION["user_id"], $page, $per_page);

// Get success/error messages
$successMessage = $_SESSION["entry_success"] ?? "";
$errorMessage = $_SESSION["entry_error"] ?? "";

// Clear messages
unset($_SESSION["entry_success"], $_SESSION["entry_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gratitude - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/gratitude.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <div class="alert-container" id="alertContainer">
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
    </div>

    <main class="gratitude-container">
        <!-- Gratitude Entry Form -->
        <div class="gratitude-card">
            <div class="gratitude-header">
                <h2>Daily Gratitude</h2>
                <p>Take a moment to appreciate life's blessings...</p>
            </div>

            <form action="../includes/gratitude/create_gratitude.inc.php" method="POST">
                <div class="question-container">
                    <div class="question">
                        <div class="question-text">What are you grateful for today?</div>
                        <textarea name="content" required></textarea>
                    </div>
                </div>

                <div class="button-container">
                    <button type="submit" class="submit-btn">Save Entry</button>
                </div>
            </form>
        </div>

        <!-- Previous Entries Section -->
        <section class="previous-entries">
            <h2>Previous Entries</h2>
            
            <?php if (empty($gratitude_data['entries'])): ?>
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <p>You haven't added any gratitude entries yet.</p>
                </div>
            <?php else: ?>
                <div class="entries-grid">
                    <?php foreach ($gratitude_data['entries'] as $entry): ?>
                        <article class="entry-card">
                            <div class="entry-header">
                                <time class="entry-date">
                                    <?php echo date('F j, Y', strtotime($entry['created_at'])); ?>
                                </time>
                                <div class="entry-actions">
                                    <button class="action-btn" onclick="openEditModal(<?php echo $entry['entry_id']; ?>, <?php echo htmlspecialchars(json_encode($entry['content'])); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmDelete(<?php echo $entry['entry_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="entry-content">
                                <?php echo nl2br(htmlspecialchars($entry['content'])); ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($gratitude_data['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <span class="page-info">
                            Page <?php echo $page; ?> of <?php echo $gratitude_data['total_pages']; ?>
                        </span>

                        <?php if ($page < $gratitude_data['total_pages']): ?>
                            <a href="?page=<?php echo ($page + 1); ?>" class="page-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="gratitude-card">
                <div class="gratitude-header">
                    <h2>Edit Gratitude</h2>
                    <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
                </div>

                <form action="../includes/gratitude/update_gratitude.inc.php" method="POST">
                    <input type="hidden" name="entry_id" id="editEntryId">
                    <div class="question-container">
                        <div class="question">
                            <div class="question-text">What are you grateful for?</div>
                            <textarea name="content" id="editContent" required></textarea>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                        <button type="submit" class="btn submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" action="../includes/gratitude/delete_gratitude.inc.php" method="POST" style="display: none;">
        <input type="hidden" name="entry_id" id="deleteEntryId">
    </form>

    <script>
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => alert.remove(), 300);
                }, 3000);
            });
        });

        // Modal functions
        function openEditModal(entryId, content) {
            document.getElementById('editEntryId').value = entryId;
            document.getElementById('editContent').value = content;
            document.getElementById('editModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
            const form = document.getElementById(modalId).querySelector('form');
            if (form) form.reset();
        }

        // Delete confirmation
        function confirmDelete(entryId) {
            if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
            document.getElementById('deleteEntryId').value = entryId;
            document.getElementById('deleteForm').submit();
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    }
    </script>
    <script src="../js/admin/navbar.js"></script>
</body>
</html>