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
        
        // Delete user's journal entries (cascade will handle EntryMedia)
        $query = "DELETE FROM JournalEntries WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        // Delete the user
        $query = "DELETE FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log("Error deleting user account: " . $e->getMessage());
        return false;
    }
}