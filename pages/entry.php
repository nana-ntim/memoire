<?php
// Required includes and security checks
require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/journal/journal_model.inc.php";
require_once "../includes/collections/collections_model.inc.php";
force_login();

// Get entry ID from URL
$entry_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$entry_id) {
    header("Location: journal.php");
    die();
}

// Fetch entry data
$entry = get_entry_by_id($pdo, $entry_id, $_SESSION["user_id"]);
if (!$entry) {
    header("Location: journal.php");
    die();
}

// Fetch collections and entry's current collections
$collections = get_user_collections($pdo, $_SESSION["user_id"]);
$current_collections = [];
$query = "SELECT ce.collection_id 
          FROM CollectionEntries ce 
          JOIN Collections c ON ce.collection_id = c.collection_id 
          WHERE ce.entry_id = ? AND c.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$entry_id, $_SESSION["user_id"]]);
$current_collections = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch more entries
$more_entries = get_more_entries($pdo, $_SESSION["user_id"], $entry_id, 3);

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
    <title><?php echo htmlspecialchars($entry['title']); ?> - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/entry.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <div class="alert-container" id="alertContainer"></div>
    
    <main class="entry-container">
        <!-- Success/Error Messages -->
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

        <!-- Main Entry Content -->
        <article class="entry-content">
            <div class="entry-header">
                <div class="entry-meta">
                    <time datetime="<?php echo $entry['created_at']; ?>">
                        <?php echo date('F j, Y', strtotime($entry['created_at'])); ?>
                    </time>
                </div>
                <div class="entry-actions">
                    <button onclick="openModal('manageCollectionsModal')" class="action-btn">
                        <i class="fas fa-layer-group"></i>
                        Collections
                    </button>
                    <button onclick="openModal('editEntryModal')" class="action-btn">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button onclick="confirmDelete()" class="action-btn delete">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>

            <div class="entry-media">
                <img src="<?php echo htmlspecialchars($entry['file_path']); ?>" 
                     alt="<?php echo htmlspecialchars($entry['title']); ?>"
                     class="entry-image">
            </div>

            <div class="entry-body">
                <h1 class="entry-title">
                    <?php echo htmlspecialchars($entry['title']); ?>
                </h1>
                <div class="entry-text">
                    <?php
                    $content = htmlspecialchars($entry['content']);
                    $content = preg_replace('/\n\n+/', '</p><p>', $content);
                    echo '<p>' . $content . '</p>';
                    ?>
                </div>
            </div>
        </article>

        <!-- More Memories Section -->
        <?php if (!empty($more_entries)): ?>
        <section class="more-memories">
            <h2>More Memories</h2>
            <div class="memories-grid">
                <?php foreach ($more_entries as $memory): ?>
                    <a href="entry.php?id=<?php echo $memory['entry_id']; ?>" class="memory-card">
                        <div class="memory-image" style="background-image: url('<?php echo htmlspecialchars($memory['file_path']); ?>')"></div>
                        <div class="memory-info">
                            <h3><?php echo htmlspecialchars($memory['title']); ?></h3>
                            <time datetime="<?php echo $memory['created_at']; ?>">
                                <?php echo date('M j, Y', strtotime($memory['created_at'])); ?>
                            </time>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Manage Collections Modal -->
        <div id="manageCollectionsModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Manage Collections</h2>
                    <button class="close-btn" onclick="closeModal('manageCollectionsModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <?php if (empty($collections)): ?>
                        <div class="no-collections">
                            <p>You haven't created any collections yet.</p>
                            <a href="../pages/collections.php" class="create-collection-link">
                                <i class="fas fa-plus"></i>
                                Create your first collection
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="collections-list">
                            <?php foreach ($collections as $collection): ?>
                                <div class="collection-item">
                                    <div class="collection-checkbox">
                                        <input type="checkbox" 
                                               id="collection_<?php echo $collection['collection_id']; ?>"
                                               value="<?php echo $collection['collection_id']; ?>"
                                               <?php echo in_array($collection['collection_id'], $current_collections) ? 'checked' : ''; ?>
                                               onchange="handleCollectionChange(this)"
                                               data-collection-name="<?php echo htmlspecialchars($collection['name']); ?>">
                                        <label for="collection_<?php echo $collection['collection_id']; ?>">
                                            <?php echo htmlspecialchars($collection['name']); ?>
                                            <span class="entry-count"><?php echo $collection['entry_count']; ?> entries</span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Edit Entry Modal -->
        <div id="editEntryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Entry</h2>
                    <button class="close-btn" onclick="closeModal('editEntryModal')">&times;</button>
                </div>
                <form action="../includes/journal/update_entry.inc.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="entry_id" value="<?php echo $entry_id; ?>">
                    
                    <div class="form-group media-upload">
                        <label for="media-input" class="upload-label">
                            <i class="fas fa-image"></i>
                            <span>Change Photo</span>
                        </label>
                        <input type="file" id="media-input" name="media" accept="image/*" class="hidden">
                        <div id="media-preview" class="media-preview">
                            <div class="preview-item" style="background-image: url('<?php echo htmlspecialchars($entry['file_path']); ?>')"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" name="title" value="<?php echo htmlspecialchars($entry['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <textarea name="content" required><?php echo htmlspecialchars($entry['content']); ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Delete Form (Hidden) -->
        <form id="deleteForm" action="../includes/journal/delete_entry.inc.php" method="POST" style="display: none;">
            <input type="hidden" name="entry_id" value="<?php echo $entry_id; ?>">
        </form>
    </main>

    <script>
        // Auto-hide alerts
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

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Collection management
        function handleCollectionChange(checkbox) {
            const formData = new FormData();
            formData.append('entry_id', <?php echo $entry_id; ?>);
            formData.append('collection_id', checkbox.value);
            
            const collectionItem = checkbox.closest('.collection-item');
            collectionItem.classList.add('loading');
            checkbox.disabled = true;

            fetch(checkbox.checked ? '../includes/collections/add_to_collection.inc.php' : '../includes/collections/remove_from_collection.inc.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.error, 'error');
                    checkbox.checked = !checkbox.checked; // Revert checkbox state
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'error');
                checkbox.checked = !checkbox.checked; // Revert checkbox state
            })
            .finally(() => {
                collectionItem.classList.remove('loading');
                checkbox.disabled = false;
            });
        }

        // Delete confirmation
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        // Image preview functionality
        const mediaInput = document.getElementById('media-input');
        const mediaPreview = document.getElementById('media-preview');

        mediaInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    mediaPreview.innerHTML = `
                        <div class="preview-item" style="background-image: url('${e.target.result}')"></div>
                    `;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Alert functionality
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