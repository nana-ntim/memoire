<?php
// Required includes and security checks
require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/journal/journal_model.inc.php";
require_once "../includes/collections/collections_model.inc.php";
force_login();

// Get collection ID from URL
$collection_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$collection_id) {
    header("Location: collections.php");
    die();
}

// Fetch collection data and verify ownership
$collection = get_collection_by_id($pdo, $collection_id, $_SESSION["user_id"]);
if (!$collection) {
    header("Location: collections.php");
    die();
}

// Get sort parameter
$sort = $_GET['sort'] ?? 'newest';
$sort_sql = $sort === 'oldest' ? 'ASC' : 'DESC';

// Fetch collection entries
$query = "SELECT e.*, m.file_path, m.media_type 
         FROM JournalEntries e 
         JOIN CollectionEntries ce ON e.entry_id = ce.entry_id 
         LEFT JOIN EntryMedia m ON e.entry_id = m.entry_id 
         WHERE ce.collection_id = :collection_id 
         ORDER BY e.created_at " . $sort_sql;
$stmt = $pdo->prepare($query);
$stmt->execute([":collection_id" => $collection_id]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get success/error messages
$successMessage = $_SESSION["collection_success"] ?? "";
$errorMessage = $_SESSION["collection_error"] ?? "";

// Clear messages
unset($_SESSION["collection_success"], $_SESSION["collection_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($collection['name']); ?> - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/collection.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <div class="alert-container" id="alertContainer"></div>
    
    <main class="collection-container">
        <!-- Collection Header -->
        <div class="collection-header">
            <div class="header-content">
                <a href="collections.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="header-info">
                    <div class="header-top">
                        <h1><?php echo htmlspecialchars($collection['name']); ?></h1>
                        <button class="edit-button" onclick="openEditModal()">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                    </div>
                    <div class="header-meta">
                        <span class="entry-count">
                            <?php echo count($entries); ?> 
                            <?php echo count($entries) === 1 ? 'entry' : 'entries'; ?>
                        </span>
                        <span class="separator">•</span>
                        <span class="created-date">
                            Created <?php echo date('F j, Y', strtotime($collection['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <div class="sort-control">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="changeSort(this.value)">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest first</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest first</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Collection Content -->
        <div class="collection-content">
            <?php if (empty($entries)): ?>
                <div class="empty-state">
                    <div class="empty-state-content">
                        <i class="fas fa-images"></i>
                        <h2>This collection is empty</h2>
                        <p>Add entries from your journal to see them here</p>
                        <a href="journal.php" class="browse-button">
                            <i class="fas fa-journal-whills"></i>
                            Browse Journal
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="entries-grid">
                    <?php foreach ($entries as $entry): ?>
                        <div class="entry-card" data-entry-id="<?php echo $entry['entry_id']; ?>">
                            <a href="entry.php?id=<?php echo $entry['entry_id']; ?>" class="entry-link">
                                <div class="entry-image" style="background-image: url('<?php echo htmlspecialchars($entry['file_path']); ?>')">
                                    <div class="entry-overlay">
                                        <h3 class="entry-title"><?php echo htmlspecialchars($entry['title']); ?></h3>
                                        <time datetime="<?php echo $entry['created_at']; ?>" class="entry-date">
                                            <?php echo date('M d, Y', strtotime($entry['created_at'])); ?>
                                        </time>
                                    </div>
                                </div>
                            </a>
                            <button class="remove-entry-btn" onclick="confirmRemoveEntry(<?php echo $entry['entry_id']; ?>, '<?php echo htmlspecialchars(addslashes($entry['title'])); ?>')">
                                <i class="fas fa-times"></i>
                                <span class="tooltip">Remove from collection</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Edit Collection Modal -->
        <div id="editCollectionModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Collection</h2>
                    <button class="close-btn" onclick="closeModal('editCollectionModal')">&times;</button>
                </div>
                <form id="editCollectionForm" action="../includes/collections/update_collection.inc.php" method="POST">
                    <input type="hidden" name="collection_id" value="<?php echo $collection_id; ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="collectionName">Collection Name</label>
                            <input type="text" 
                                   id="collectionName" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($collection['name']); ?>" 
                                   required 
                                   maxlength="255">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editCollectionModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Remove Entry Form (Hidden) -->
        <form id="removeEntryForm" action="../includes/collections/remove_from_collection.inc.php" method="POST" style="display: none;">
            <input type="hidden" name="collection_id" value="<?php echo $collection_id; ?>">
            <input type="hidden" name="entry_id" id="removeEntryId">
        </form>

        <script>
            // Handle automatic alert dismissal
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

                // Initialize masonry layout if entries exist
                if (document.querySelector('.entries-grid')) {
                    initializeMasonry();
                }
            });

            // Modal functionality
            function openModal(modalId) {
                document.getElementById(modalId).classList.add('show');
                document.body.style.overflow = 'hidden';
                
                // Focus input if present
                const input = document.getElementById(modalId).querySelector('input[type="text"]');
                if (input) {
                    setTimeout(() => input.focus(), 100);
                }
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.remove('show');
                document.body.style.overflow = 'auto';
                // Reset form if present
                const form = document.getElementById(modalId).querySelector('form');
                if (form) form.reset();
            }

            function openEditModal() {
                openModal('editCollectionModal');
            }

            // Sort functionality
            function changeSort(value) {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', value);
                window.location.href = url.toString();
            }

            // Remove entry functionality
            function confirmRemoveEntry(entryId, entryTitle) {
                if (confirm(`Remove "${entryTitle}" from this collection?`)) {
                    const form = document.getElementById('removeEntryForm');
                    document.getElementById('removeEntryId').value = entryId;
                    
                    // Submit form with fetch
                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Animate removal and update count
                            const entryCard = document.querySelector(`.entry-card[data-entry-id="${entryId}"]`);
                            entryCard.classList.add('removing');
                            setTimeout(() => {
                                entryCard.remove();
                                updateEntryCount(-1);
                                showAlert(data.message, 'success');
                                
                                // Check if grid is empty and show empty state if needed
                                const grid = document.querySelector('.entries-grid');
                                if (grid && !grid.children.length) {
                                    location.reload();
                                }
                            }, 300);
                        } else {
                            showAlert(data.error, 'error');
                        }
                    })
                    .catch(error => {
                        showAlert('An error occurred. Please try again.', 'error');
                    });
                }
            }

            // Update entry count in header
            function updateEntryCount(change) {
                const countElement = document.querySelector('.entry-count');
                const currentCount = parseInt(countElement.textContent);
                const newCount = currentCount + change;
                const text = `${newCount} ${newCount === 1 ? 'entry' : 'entries'}`;
                countElement.textContent = text;

                // Animate the count change
                countElement.classList.add('updating');
                setTimeout(() => countElement.classList.remove('updating'), 300);
            }

            // Handle collection edit form
            document.getElementById('editCollectionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update collection name in header
                        document.querySelector('.header-info h1').textContent = data.collection.name;
                        showAlert(data.message, 'success');
                        closeModal('editCollectionModal');
                    } else {
                        showAlert(data.error, 'error');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Save Changes';
                });
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

            // Masonry layout initialization
            function initializeMasonry() {
                const grid = document.querySelector('.entries-grid');
                if (!grid) return;

                // Show grid only after images are loaded
                const images = grid.querySelectorAll('.entry-image');
                let loadedImages = 0;

                function revealGrid() {
                    grid.classList.add('loaded');
                }

                images.forEach(image => {
                    const bg = image.style.backgroundImage.slice(4, -1).replace(/"/g, "");
                    const img = new Image();
                    img.onload = () => {
                        loadedImages++;
                        if (loadedImages === images.length) {
                            revealGrid();
                        }
                    };
                    img.src = bg;
                });
            }

            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    closeModal(event.target.id);
                }
            };
        </script>
    </main>
</body>
</html>