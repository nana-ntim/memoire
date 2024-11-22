<?php
// Required includes and security checks
require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/journal/journal_model.inc.php";
force_login();

// Get success/error messages from session
$successMessage = $_SESSION["entry_success"] ?? "";
$errorMessage = $_SESSION["entry_error"] ?? "";

// Clear messages after retrieving
unset($_SESSION["entry_success"], $_SESSION["entry_error"]);

// Fetch journal entries for the logged-in user
$entries = get_journal_entries($pdo, $_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/journal.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <div class="alert-container" id="alertContainer"></div>
    
    <main class="journal-container">
        <!-- Display success/error messages if they exist -->
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

        <!-- Display empty state or journal entries -->
        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <p>Tap the + Icon to</p>
                <p>add a journal entry</p>
            </div>
        <?php else: ?>
            <!-- Journal entries grid -->
            <div class="masonry-grid">
                <?php foreach ($entries as $entry): ?>
                    <div class="journal-entry-wrapper">
                        <a href="entry.php?id=<?php echo htmlspecialchars($entry['entry_id']); ?>" 
                           class="journal-entry">
                            <div class="entry-media" style="background-image: url('<?php echo htmlspecialchars($entry['file_path']); ?>');"></div>
                            <div class="entry-overlay">
                                <h2 class="entry-title"><?php echo htmlspecialchars($entry['title']); ?></h2>
                                <div class="entry-date">
                                    <?php echo date('M d, Y', strtotime($entry['created_at'])); ?>
                                </div>
                            </div>
                        </a>
                        <button class="add-to-collection-btn" onclick="openCollectionModal(<?php echo $entry['entry_id']; ?>)">
                            <i class="fas fa-layer-group"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Floating Action Button -->
        <button class="fab" onclick="openModal('newEntryModal')">
            <i class="fas fa-plus"></i>
        </button>
    </main>

    <!-- Collections Modal -->
    <div id="collectionsModal" class="collections-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add to Collection</h2>
                <button class="close-btn" onclick="closeCollectionModal()">&times;</button>
            </div>
            <div class="modal-body">
                <?php
                // Fetch user's collections
                require_once "../includes/collections/collections_model.inc.php";
                $collections = get_user_collections($pdo, $_SESSION["user_id"]);
                
                if (empty($collections)): ?>
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
                            <div class="collection-option">
                                <input type="radio" 
                                    id="collection_<?php echo $collection['collection_id']; ?>" 
                                    name="collection_id" 
                                    value="<?php echo $collection['collection_id']; ?>">
                                <label for="collection_<?php echo $collection['collection_id']; ?>">
                                    <?php echo htmlspecialchars($collection['name']); ?>
                                    <span class="entry-count"><?php echo $collection['entry_count']; ?> entries</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCollectionModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="addToCollection()">Add to Collection</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- New Entry Modal -->
    <div id="newEntryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Journal Entry</h2>
                <button class="close-btn" onclick="closeModal('newEntryModal')">&times;</button>
            </div>
            <form action="../includes/journal/create_entry.inc.php" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="journalForm">
                
                <!-- Error message container -->
                <div id="modalError" class="modal-error" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Please select an image for your journal entry</span>
                </div>

                <!-- Image upload section -->
                <div class="form-group media-upload">
                    <label for="media-input" class="upload-label">
                        <i class="fas fa-image"></i>
                        <span>Add Photo</span>
                    </label>
                    <input type="file" 
                           id="media-input" 
                           name="media" 
                           accept="image/*" 
                           class="hidden">
                    <div id="media-preview" class="media-preview"></div>
                </div>

                <div class="form-group">
                    <input type="text" 
                           name="title" 
                           placeholder="Entry Title" 
                           required>
                </div>

                <div class="form-group">
                    <textarea name="content" 
                              placeholder="What's on your mind?" 
                              required></textarea>
                </div>

                <button type="submit" class="submit-btn">Post</button>
            </form>
        </div>
    </div>

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

        async function handleSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const submitBtn = form.querySelector('.submit-btn');
            const modalError = document.getElementById('modalError');
            
            try {
                // Disable submit button and show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';

                const formData = new FormData(form);

                // Log form data for debugging
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                const response = await fetch('../includes/journal/create_entry.inc.php', {
                    method: 'POST',
                    body: formData
                });

                // First check if we got a response at all
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Get the response text
                const text = await response.text();
                console.log('Raw server response:', text);

                // Check if we got any content
                if (!text.trim()) {
                    throw new Error('Server returned empty response');
                }

                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse server response:', text);
                    throw new Error('Invalid JSON response from server');
                }

                if (data.success) {
                    showAlert(data.message || 'Entry created successfully', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '../pages/journal.php';
                    }, 1000);
                } else {
                    const errors = Array.isArray(data.errors) ? data.errors : ['An unknown error occurred'];
                    showErrors(errors);
                }

            } catch (error) {
                console.error('Submission error:', error);
                showErrors([
                    'An error occurred while submitting the form',
                    'Technical details: ' + error.message
                ]);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post';
            }
        }

        function showErrors(errors) {
            const modalError = document.getElementById('modalError');
            if (errors && errors.length > 0) {
                const errorHtml = `
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="error-messages">
                        ${errors.map(error => `<div class="error-message">${error}</div>`).join('')}
                    </div>
                `;
                modalError.innerHTML = errorHtml;
                modalError.style.display = 'flex';
                modalError.classList.add('shake');
                
                setTimeout(() => {
                    modalError.classList.remove('shake');
                }, 500);
            } else {
                modalError.style.display = 'none';
            }
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden';
            document.getElementById('modalError').style.display = 'none';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
            const form = document.getElementById(modalId).querySelector('form');
            if (form) form.reset();
            document.getElementById('media-preview').innerHTML = '';
            document.getElementById('modalError').style.display = 'none';
        }

        // Image preview functionality
        const mediaInput = document.getElementById('media-input');
        const mediaPreview = document.getElementById('media-preview');

        mediaInput.addEventListener('change', function() {
            mediaPreview.innerHTML = '';
            const file = this.files[0];
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showErrors(['Please select a valid image file (JPG, PNG, or GIF)']);
                    this.value = '';
                    return;
                }

                // Validate file size (5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    showErrors(['File size must be less than 5MB']);
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'preview-item';
                    preview.style.backgroundImage = `url(${e.target.result})`;
                    mediaPreview.appendChild(preview);
                    document.getElementById('modalError').style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

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
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 3000);
        }

        // Initialize form submission handler
        document.getElementById('journalForm').addEventListener('submit', handleSubmit);

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        };

        let currentEntryId = null;

        function openCollectionModal(entryId) {
            currentEntryId = entryId;
            document.getElementById('collectionsModal').classList.add('show');
            document.body.style.overflow = 'hidden';

            // Clear any previously selected radio buttons
            const radioButtons = document.querySelectorAll('input[name="collection_id"]');
            radioButtons.forEach(radio => radio.checked = false);
        }

        function closeCollectionModal() {
            document.getElementById('collectionsModal').classList.remove('show');
            document.body.style.overflow = 'auto';
            currentEntryId = null;
        }

        async function addToCollection() {
            const selectedCollection = document.querySelector('input[name="collection_id"]:checked');
            
            if (!selectedCollection) {
                showAlert('Please select a collection', 'error');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('entry_id', currentEntryId);
                formData.append('collection_id', selectedCollection.value);

                const response = await fetch('../includes/collections/add_to_collection.inc.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');
                    closeCollectionModal();
                } else {
                    showAlert(data.error, 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            }
        }

        // Close modal when clicking outside
        document.getElementById('collectionsModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeCollectionModal();
            }
        });
    </script>
</body>
</html>