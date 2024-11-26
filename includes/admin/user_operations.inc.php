<?php
// File: includes/admin/user_operations.inc.php

header('Content-Type: application/json');

require_once "../../config/dbh.inc.php";
require_once "../security/admin_security.inc.php";

try {
    // Verify admin privileges
    $pdo = require_admin_priv();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['action']) || !isset($input['user_id'])) {
        throw new Exception('Missing required parameters');
    }

    // Validate user_id
    $user_id = filter_var($input['user_id'], FILTER_VALIDATE_INT);
    if (!$user_id) {
        throw new Exception('Invalid user ID');
    }

    // Handle user deletion
    if ($input['action'] === 'delete') {
        // Start transaction
        $pdo->beginTransaction();

        try {
            // First, verify this is not an admin account
            $query = "SELECT is_admin, profile_image FROM Users WHERE user_id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            if ($user['is_admin']) {
                throw new Exception('Cannot delete admin user');
            }

            // Get files to delete
            $query = "SELECT m.file_path
                     FROM EntryMedia m
                     JOIN JournalEntries e ON m.entry_id = e.entry_id
                     WHERE e.user_id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            $files_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete user (will cascade to entries and collections)
            $query = "DELETE FROM Users WHERE user_id = :user_id AND is_admin = 0";
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([':user_id' => $user_id]);

            if (!$result) {
                throw new Exception('Failed to delete user');
            }

            // Delete associated files
            foreach ($files_to_delete as $file_path) {
                if ($file_path) {
                    $full_path = __DIR__ . '/../../' . ltrim($file_path, '/');
                    if (file_exists($full_path)) {
                        unlink($full_path);
                    }
                }
            }

            // Delete profile image if exists
            if ($user['profile_image']) {
                $profile_path = __DIR__ . '/../../' . ltrim($user['profile_image'], '/');
                if (file_exists($profile_path)) {
                    unlink($profile_path);
                }
            }

            // Commit transaction
            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("Error in user_operations: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}