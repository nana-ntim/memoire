<?php
declare(strict_types=1);

function get_user_data(object $pdo, int $user_id) {
    try {
        $query = "SELECT user_id, firstName, lastName, email, profile_image FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no profile image is set, use default
        if (!$result['profile_image']) {
            $result['profile_image'] = '../assets/default-avatar.jpg';
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return false;
    }
}

function update_user_profile(object $pdo, int $user_id, string $firstName, string $lastName, string $email) {
    try {
        $query = "UPDATE Users 
                 SET firstName = :firstName, 
                     lastName = :lastName, 
                     email = :email 
                 WHERE user_id = :user_id";
                 
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":firstName", $firstName);
        $stmt->bindParam(":lastName", $lastName);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        return false;
    }
}

function update_user_password(object $pdo, int $user_id, string $newPassword) {
    try {
        $hashedPwd = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $query = "UPDATE Users 
                 SET passwd = :password 
                 WHERE user_id = :user_id";
                 
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":password", $hashedPwd);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating password: " . $e->getMessage());
        return false;
    }
}

function verify_current_password(object $pdo, int $user_id, string $currentPassword) {
    try {
        $query = "SELECT passwd FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && password_verify($currentPassword, $result['passwd'])) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error verifying password: " . $e->getMessage());
        return false;
    }
}

function delete_user_account(object $pdo, int $user_id) {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // First, get all image paths that need to be deleted
        // Get profile image
        $query = "SELECT profile_image FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        $profile_image = $stmt->fetchColumn();

        // Get all journal entry images
        $query = "SELECT m.file_path 
                 FROM EntryMedia m 
                 JOIN JournalEntries e ON m.entry_id = e.entry_id 
                 WHERE e.user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        $journal_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Delete user's journal entries (cascade will handle EntryMedia and CollectionEntries)
        $query = "DELETE FROM JournalEntries WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);

        // Delete user's collections (cascade will handle CollectionEntries)
        $query = "DELETE FROM Collections WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        
        // Delete the user
        $query = "DELETE FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        
        // After successful database deletion, delete all associated files
        $deleted_files = [];
        $failed_files = [];

        // Delete profile image if it exists and is not the default
        if ($profile_image && $profile_image !== 'assets/default-avatar.jpg') {
            $profile_image = preg_replace('/^\.\.\//', '', $profile_image);
            $full_path = __DIR__ . "/../../" . $profile_image;
            
            if (file_exists($full_path)) {
                if (unlink($full_path)) {
                    $deleted_files[] = $full_path;
                } else {
                    $failed_files[] = $full_path;
                }
            }
        }

        // Delete all journal entry images
        foreach ($journal_images as $image_path) {
            if ($image_path) {
                $image_path = preg_replace('/^\.\.\//', '', $image_path);
                $full_path = __DIR__ . "/../../" . $image_path;
                
                if (file_exists($full_path)) {
                    if (unlink($full_path)) {
                        $deleted_files[] = $full_path;
                    } else {
                        $failed_files[] = $full_path;
                    }
                }
            }
        }

        // Log the results
        if (!empty($deleted_files)) {
            error_log("Successfully deleted files for user $user_id: " . implode(", ", $deleted_files));
        }
        if (!empty($failed_files)) {
            error_log("Failed to delete files for user $user_id: " . implode(", ", $failed_files));
        }

        // Commit transaction
        $pdo->commit();
        return true;

    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting user account: " . $e->getMessage());
        return false;
    }
}