<?php
require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/collections/collections_model.inc.php";
force_login();

// Get user's collections
$collections = get_user_collections($pdo, $_SESSION["user_id"]);

// Get success/error messages from session
$successMessage = $_SESSION["collection_success"] ?? "";
$errorMessage = $_SESSION["collection_error"] ?? "";

// Clear messages after retrieving
unset($_SESSION["collection_success"], $_SESSION["collection_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/collections.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <!-- Alert Messages -->
     <!-- Alert Container --> 
    <div class="alert-container" id="alertContainer"></div>
    <?php if ($successMessage): ?>
        <div class="alert alert-success" id="successAlert">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <main class="collections-container">
        <!-- Collections Header -->
        <div class="collections-header">
            <h1 class="collections-title">Your Collections</h1>
            <button class="create-collection-btn" onclick="openCreateModal()">
                <i class="fas fa-plus"></i>
                Create Collection
            </button>
        </div>

        <?php if (empty($collections)): ?>
            <!-- Empty State -->
            <div class="empty-collections">
                <i class="fas fa-layer-group"></i>
                <p>Create your first collection to organize your memories</p>
                <button class="create-collection-btn" onclick="openCreateModal()">
                    Create Collection
                </button>
            </div>
        <?php else: ?>
            <!-- Collections Grid -->
            <div class="collections-grid">
                <?php foreach ($collections as $collection): ?>
                    <?php
                    // Get preview entries for this collection
                    $preview_entries = get_collection_entries($pdo, $collection['collection_id'], $_SESSION["user_id"]);
                    $preview_entries = array_slice($preview_entries, 0, 4); // Get up to 4 entries for preview
                    ?>
                    <div class="collection-card">
                        <a href="collection.php?id=<?php echo $collection['collection_id']; ?>">
                            <div class="collection-preview">
                                <div class="preview-grid">
                                    <?php 
                                    // Display up to 4 preview images
                                    for ($i = 0; $i < 4; $i++): 
                                        $bg_image = isset($preview_entries[$i]) ? 
                                            "background-image: url('" . htmlspecialchars($preview_entries[$i]['file_path']) . "')" : 
                                            "background-color: #333";
                                    ?>
                                        <div class="preview-item" style="<?php echo $bg_image; ?>"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </a>
                        <div class="collection-info">
                            <h2 class="collection-name"><?php echo htmlspecialchars($collection['name']); ?></h2>
                            <div class="collection-meta">
                                <span><?php echo $collection['entry_count']; ?> entries</span>
                                <div class="collection-actions">
                                    <button class="action-btn" onclick="openEditModal(<?php 
                                        echo htmlspecialchars(json_encode([
                                            'id' => $collection['collection_id'],
                                            'name' => $collection['name']
                                        ])); 
                                    ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="confirmDelete(<?php 
                                        echo $collection['collection_id']; 
                                    ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Create Collection Modal -->
        <div id="createCollectionModal" class="collections-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Create Collection</h2>
                    <button class="close-modal" onclick="closeModal('createCollectionModal')">&times;</button>
                </div>
                <form id="createCollectionForm" action="../includes/collections/create_collection.inc.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="collectionName">Collection Name</label>
                            <input type="text" id="collectionName" name="name" required 
                                   placeholder="Enter collection name"
                                   maxlength="255">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal(this.closest('.collections-modal').id)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Collection Modal -->
        <div id="editCollectionModal" class="collections-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Collection</h2>
                    <button class="close-modal" onclick="closeModal('editCollectionModal')">&times;</button>
                </div>
                <form id="editCollectionForm" action="../includes/collections/update_collection.inc.php" method="POST">
                    <input type="hidden" id="editCollectionId" name="collection_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editCollectionName">Collection Name</label>
                            <input type="text" id="editCollectionName" name="name" required 
                                   placeholder="Enter collection name"
                                   maxlength="255">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal(this.closest('.collections-modal').id)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Hidden Delete Form -->
        <form id="deleteCollectionForm" action="../includes/collections/delete_collection.inc.php" method="POST" style="display: none;">
            <input type="hidden" id="deleteCollectionId" name="collection_id">
        </form>
    </main>

    <script>
        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 3000);
            });
        });

        // Modal functions
        function openCreateModal() {
            document.getElementById('createCollectionModal').classList.add('show');
            document.getElementById('collectionName').focus();
            document.body.style.overflow = 'hidden';
        }

        function openEditModal(collection) {
            document.getElementById('editCollectionId').value = collection.id;
            document.getElementById('editCollectionName').value = collection.name;
            document.getElementById('editCollectionModal').classList.add('show');
            document.getElementById('editCollectionName').focus();
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
            // Reset form
            document.getElementById(modalId).querySelector('form').reset();
        }

        // Delete confirmation
        function confirmDelete(collectionId) {
            if (confirm('Are you sure you want to delete this collection? The entries in this collection will not be deleted.')) {
                document.getElementById('deleteCollectionId').value = collectionId;
                document.getElementById('deleteCollectionForm').submit();
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('collections-modal')) {
                closeModal(event.target.id);
            }
        }

        // Handle form submissions with fetch
        document.getElementById('createCollectionForm').addEventListener('submit', handleFormSubmit);
        document.getElementById('editCollectionForm').addEventListener('submit', handleFormSubmit);

        function handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    showAlert(data.error, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'error');
            });
        }

        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                ${message}
            `;
            document.body.appendChild(alert);

            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 3000);
        }

        // Handle form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.classList.add('loading');
                submitButton.disabled = true;

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        showAlert(data.message, 'success');
                        closeModal(form.closest('.collections-modal').id);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showAlert(data.error, 'error');
                    }
                } catch (error) {
                    showAlert('An error occurred. Please try again.', 'error');
                } finally {
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                }
            });
        });

        // Alert handling
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                ${message}
            `;
            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Autofocus form inputs when modal opens
        document.querySelectorAll('.collections-modal').forEach(modal => {
            modal.addEventListener('show', () => {
                const input = modal.querySelector('input[type="text"]');
                if (input) setTimeout(() => input.focus(), 100);
            });
        });
    </script>
</body>
</html>