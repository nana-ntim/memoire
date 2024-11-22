<?php
require_once "../includes/security/session_protection.inc.php";
require_once "../config/dbh.inc.php";
require_once "../includes/settings/settings_model.inc.php";
force_login();

// Get user data
$user_data = get_user_data($pdo, $_SESSION["user_id"]);

// Get success/error messages from session if they exist
$successMessage = isset($_SESSION["settings_success"]) ? $_SESSION["settings_success"] : "";
$errorMessage = isset($_SESSION["settings_error"]) ? $_SESSION["settings_error"] : "";

// Clear the messages from session after retrieving them
unset($_SESSION["settings_success"]);
unset($_SESSION["settings_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Memoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/settings.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <main class="settings-container">
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="settings-content">
            <section class="account-settings">
                <h1>Account Settings</h1>
                
                <div class="profile-info">
                    <div class="profile-image-container">
                        <img src="<?php 
                            if (!empty($user_data['profile_image'])) {
                                echo htmlspecialchars('../' . $user_data['profile_image']);
                            } else {
                                echo '../assets/default-avatar.jpg';
                            }
                        ?>" 
                            alt="Profile" 
                            class="profile-avatar"
                            id="profileAvatar">
                        <button type="button" class="change-photo-btn" onclick="document.getElementById('profileImageInput').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                        <form id="profileImageForm" action="../includes/settings/update_profile_image.inc.php" method="POST" enctype="multipart/form-data">
                            <input type="file" 
                                id="profileImageInput" 
                                name="profile_image" 
                                accept="image/*" 
                                class="hidden"
                                onchange="handleImageUpload(this)">
                        </form>
                    </div>
                    <div class="profile-details">
                        <h2><?php echo htmlspecialchars($user_data['firstName'] . ' ' . $user_data['lastName']); ?></h2>
                        <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                </div>

                <div class="actions-group">
                    <button class="btn btn-primary" onclick="openModal('editProfileModal')">Edit Profile</button>
                    <button class="btn btn-secondary" onclick="openModal('changePasswordModal')">Change Password</button>
                </div>
            </section>

            <section class="danger-zone">
                <h2>Delete Account</h2>
                <p>Once you delete your account, there is no going back. Please be certain.</p>
                <button class="btn btn-danger" onclick="openModal('deleteAccountModal')">Delete Account</button>
            </section>
        </div>

        <!-- Edit Profile Modal -->
        <div id="editProfileModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Profile</h2>
                    <button class="close-btn" onclick="closeModal('editProfileModal')">&times;</button>
                </div>
                <form action="../includes/settings/update_profile.inc.php" method="POST">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" 
                               id="firstName" 
                               name="firstName" 
                               value="<?php echo htmlspecialchars($user_data['firstName']); ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" 
                               id="lastName" 
                               name="lastName" 
                               value="<?php echo htmlspecialchars($user_data['lastName']); ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                               required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div id="changePasswordModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Change Password</h2>
                    <button class="close-btn" onclick="closeModal('changePasswordModal')">&times;</button>
                </div>
                <form action="../includes/settings/update_password.inc.php" method="POST">
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('changePasswordModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div id="deleteAccountModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Delete Account</h2>
                    <button class="close-btn" onclick="closeModal('deleteAccountModal')">&times;</button>
                </div>
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>This action cannot be undone. This will permanently delete your account and remove all of your data.</p>
                </div>
                <form action="../includes/settings/delete_account.inc.php" method="POST" onsubmit="return validateDeleteForm()">
                    <div class="form-group">
                        <label for="confirmDelete">Type "DELETE" to confirm</label>
                        <input type="text" id="confirmDelete" name="confirmDelete" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('deleteAccountModal')">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
            // Reset forms when closing modals
            const form = document.getElementById(modalId).querySelector('form');
            if (form) form.reset();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        }

        // Delete account validation
        function validateDeleteForm() {
            const confirmInput = document.getElementById('confirmDelete');
            if (confirmInput.value !== 'DELETE') {
                alert('Please type DELETE to confirm account deletion');
                return false;
            }
            return confirm('Are you sure you want to delete your account? This action cannot be undone.');
        }

        // Show selected image preview
        document.getElementById('profileImageInput').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileAvatar').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Password validation
        document.querySelector('#changePasswordModal form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
        });
    </script>

    <script>
        function handleImageUpload(input) {
            if (input.files && input.files[0]) {
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileAvatar').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);

                // Submit form
                document.getElementById('profileImageForm').submit();
            }
        }
    </script>
</body>
</html>