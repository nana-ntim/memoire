<?php
// File: includes/admin/user_operations.inc.php

header('Content-Type: application/json');

require_once "../../config/dbh.inc.php";
require_once "../security/admin_security.inc.php";

// Ensure request is made via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    die();
}

// Verify admin privileges
try {
    $pdo = require_admin_priv();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    die();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    die();
}

// Handle user deletion
if ($input['action'] === 'delete') {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get user data for cleanup
        $query = "SELECT profile_image FROM Users WHERE user_id = :user_id AND is_admin = 0";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':user_id' => $input['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception('User not found or cannot delete admin user');
        }

        // Delete user's journal entries (will cascade to EntryMedia and CollectionEntries)
        $query = "DELETE FROM JournalEntries WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':user_id' => $input['user_id']]);

        // Delete user's collections (will cascade to CollectionEntries)
        $query = "DELETE FROM Collections WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':user_id' => $input['user_id']]);

        // Delete the user
        $query = "DELETE FROM Users WHERE user_id = :user_id AND is_admin = 0";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':user_id' => $input['user_id']]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to delete user');
        }

        // Delete profile image if exists
        if ($user['profile_image']) {
            $profile_image_path = __DIR__ . "/../../" . $user['profile_image'];
            if (file_exists($profile_image_path)) {
                unlink($profile_image_path);
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
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to delete user: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action'
    ]);
}