<?php
require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/reflections/reflection_model.inc.php";
force_login();

// Get query parameters
$page = max(1, $_GET['page'] ?? 1);
$per_page = 5;

// Get reflections data
$reflections_data = get_user_reflections($pdo, $_SESSION["user_id"], $page, $per_page);

// Get success/error messages from session
$successMessage = $_SESSION["reflection_success"] ?? "";
$errorMessage = $_SESSION["reflection_error"] ?? "";

// Clear messages after retrieving
unset($_SESSION["reflection_success"], $_SESSION["reflection_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reflect - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/reflect.css">
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

    <main class="reflect-container">
        <!-- New Reflection Card -->
        <div class="reflection-card">
            <div class="reflection-header">
                <h2>New Reflection</h2>
                <p>Take a moment to reflect on your experience...</p>
            </div>

            <form action="../includes/reflections/create_reflection.inc.php" method="POST">
                <div class="question-container">
                    <div class="question">
                        <div class="number-circle">1</div>
                        <div class="question-content">
                            <div class="question-text">What happened? Describe your experience...</div>
                            <textarea name="question1" required></textarea>
                        </div>
                    </div>

                    <div class="question">
                        <div class="number-circle">2</div>
                        <div class="question-content">
                            <div class="question-text">How did this make you feel and why?</div>
                            <textarea name="question2" required></textarea>
                        </div>
                    </div>

                    <div class="question">
                        <div class="number-circle">3</div>
                        <div class="question-content">
                            <div class="question-text">What did you learn from this experience?</div>
                            <textarea name="question3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="submit" class="submit-btn">Save Reflection</button>
                </div>
            </form>
        </div>

        <!-- Previous Reflections Section -->
        <section class="previous-reflections">
            <h2>Previous Reflections</h2>
            
            <?php if (empty($reflections_data['reflections'])): ?>
                <!-- Empty state section -->
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>You haven't added any reflections yet.</p>
                </div>
            <?php else: ?>
                <div class="reflection-grid">
                    <?php foreach ($reflections_data['reflections'] as $reflection): ?>
                        <article class="reflection-entry">
                            <div class="entry-header">
                                <time class="entry-date">
                                    <?php echo date('F j, Y', strtotime($reflection['created_at'])); ?>
                                </time>
                                <div class="entry-actions">
                                    <button class="action-btn" onclick="openEditModal(<?php echo $reflection['reflection_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmDelete(<?php echo $reflection['reflection_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="entry-content">
                                <div class="reflection-qa">
                                    <div class="reflection-question">What happened?</div>
                                    <div class="reflection-answer"><?php echo nl2br(htmlspecialchars($reflection['question1'])); ?></div>
                                </div>
                                <div class="reflection-qa">
                                    <div class="reflection-question">How did this make you feel?</div>
                                    <div class="reflection-answer"><?php echo nl2br(htmlspecialchars($reflection['question2'])); ?></div>
                                </div>
                                <div class="reflection-qa">
                                    <div class="reflection-question">What did you learn?</div>
                                    <div class="reflection-answer"><?php echo nl2br(htmlspecialchars($reflection['question3'])); ?></div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($reflections_data['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <span class="page-info">
                            Page <?php echo $page; ?> of <?php echo $reflections_data['total_pages']; ?>
                        </span>

                        <?php if ($page < $reflections_data['total_pages']): ?>
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
            <div class="reflection-card">
                <div class="reflection-header">
                    <h2>Edit Reflection</h2>
                    <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
                </div>

                <form id="editForm" action="../includes/reflections/update_reflection.inc.php" method="POST">
                    <input type="hidden" name="reflection_id" id="editReflectionId">
                    <div class="question-container">
                        <div class="question">
                            <div class="number-circle">1</div>
                            <div class="question-content">
                                <div class="question-text">What happened? Describe your experience...</div>
                                <textarea name="question1" id="editQuestion1" required></textarea>
                            </div>
                        </div>

                        <div class="question">
                            <div class="number-circle">2</div>
                            <div class="question-content">
                                <div class="question-text">How did this make you feel and why?</div>
                                <textarea name="question2" id="editQuestion2" required></textarea>
                            </div>
                        </div>

                        <div class="question">
                            <div class="number-circle">3</div>
                            <div class="question-content">
                                <div class="question-text">What did you learn from this experience?</div>
                                <textarea name="question3" id="editQuestion3" required></textarea>
                            </div>
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
    <form id="deleteForm" action="../includes/reflections/delete_reflection.inc.php" method="POST" style="display: none;">
        <input type="hidden" name="reflection_id" id="deleteReflectionId">
    </form>

    <script>
        // Auto-hide alerts after 3 seconds
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
        function openEditModal(reflectionId) {
            document.getElementById('editReflectionId').value = reflectionId;
            
            // Fetch reflection data
            fetch(`../includes/reflections/get_reflection.inc.php?id=${reflectionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editQuestion1').value = data.reflection.question1;
                        document.getElementById('editQuestion2').value = data.reflection.question2;
                        document.getElementById('editQuestion3').value = data.reflection.question3;
                        document.getElementById('editModal').classList.add('show');
                        document.body.style.overflow = 'hidden';
                    } else {
                        showAlert(data.error, 'error');
                    }
                })
                .catch(error => {
                    showAlert('Failed to load reflection', 'error');
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
            // Reset form if exists
            const form = document.getElementById(modalId).querySelector('form');
            if (form) form.reset();
        }

        // Delete confirmation
        function confirmDelete(reflectionId) {
            if (confirm('Are you sure you want to delete this reflection? This action cannot be undone.')) {
                document.getElementById('deleteReflectionId').value = reflectionId;
                document.getElementById('deleteForm').submit();
            }
        }

        // Alert function
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                ${message}
            `;
            document.getElementById('alertContainer').appendChild(alert);

            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 300);
            }, 3000);
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