<?php
declare(strict_types=1);

function get_journal_entries(object $pdo, int $user_id) {
    try {
        $query = "SELECT e.*, m.file_path, m.media_type
                 FROM JournalEntries e 
                 LEFT JOIN EntryMedia m ON e.entry_id = m.entry_id 
                 WHERE e.user_id = :user_id 
                 ORDER BY e.created_at DESC";
                 
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching journal entries: " . $e->getMessage());
        return [];
    }
}

function get_entry_by_id(object $pdo, int $entry_id, int $user_id) {
    try {
        $query = "SELECT e.*, m.file_path, m.media_type
                 FROM JournalEntries e 
                 LEFT JOIN EntryMedia m ON e.entry_id = m.entry_id 
                 WHERE e.entry_id = :entry_id 
                 AND e.user_id = :user_id";
                 
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":entry_id" => $entry_id,
            ":user_id" => $user_id
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching entry: " . $e->getMessage());
        return false;
    }
}

function delete_journal_entry(object $pdo, int $entry_id, int $user_id) {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // First, get the file path
        $query = "SELECT m.file_path 
                 FROM EntryMedia m 
                 JOIN JournalEntries e ON m.entry_id = e.entry_id 
                 WHERE e.entry_id = :entry_id AND e.user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":entry_id" => $entry_id,
            ":user_id" => $user_id
        ]);
        $file_path = $stmt->fetchColumn();

        // Log the retrieved file path
        error_log("Retrieved file path for deletion: " . $file_path);

        // Delete the entry (cascade will handle EntryMedia)
        $query = "DELETE FROM JournalEntries 
                 WHERE entry_id = :entry_id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            ":entry_id" => $entry_id,
            ":user_id" => $user_id
        ]);

        if ($result && $file_path) {
            // Clean up the file path (remove leading '../' if present)
            $file_path = preg_replace('/^\.\.\//', '', $file_path);
            $full_file_path = __DIR__ . "/../../" . $file_path;
            
            error_log("Attempting to delete file: " . $full_file_path);
            
            if (file_exists($full_file_path)) {
                if (!unlink($full_file_path)) {
                    error_log("Failed to delete file: " . $full_file_path);
                } else {
                    error_log("Successfully deleted file: " . $full_file_path);
                }
            } else {
                error_log("File does not exist: " . $full_file_path);
            }
        }

        // Commit transaction
        $pdo->commit();
        return true;
        
    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting journal entry: " . $e->getMessage());
        return false;
    }
}

function update_journal_entry(object $pdo, int $entry_id, int $user_id, string $title, string $content, ?array $new_media = null) {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update the entry text content
        $query = "UPDATE JournalEntries 
                 SET title = :title, content = :content 
                 WHERE entry_id = :entry_id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":title" => $title,
            ":content" => $content,
            ":entry_id" => $entry_id,
            ":user_id" => $user_id
        ]);

        // Handle media update if new file is provided
        if ($new_media && $new_media['error'] === UPLOAD_ERR_OK) {
            // Get current media file path
            $query = "SELECT file_path FROM EntryMedia WHERE entry_id = :entry_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([":entry_id" => $entry_id]);
            $old_file_path = $stmt->fetchColumn();

            // Generate new filename
            $upload_dir = "../uploads/journal/";
            $file_extension = strtolower(pathinfo($new_media['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('journal_', true) . '.' . $file_extension;
            $new_file_path = $upload_dir . $new_filename;

            // Move new file
            if (move_uploaded_file($new_media['tmp_name'], $new_file_path)) {
                // Update database with new file path
                $query = "UPDATE EntryMedia 
                         SET file_path = :file_path, media_type = :media_type
                         WHERE entry_id = :entry_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ":file_path" => $new_file_path,
                    ":media_type" => $new_media['type'],
                    ":entry_id" => $entry_id
                ]);

                // Delete old file if it exists
                if ($old_file_path && file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
        }

        // Commit transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log("Error updating journal entry: " . $e->getMessage());
        return false;
    }
}

function get_more_entries(object $pdo, int $user_id, int $current_entry_id, int $limit = 3) {
    try {
        // Get random entries excluding the current one
        $query = "SELECT e.*, m.file_path 
                 FROM JournalEntries e 
                 LEFT JOIN EntryMedia m ON e.entry_id = m.entry_id 
                 WHERE e.user_id = :user_id 
                 AND e.entry_id != :current_entry_id 
                 ORDER BY RAND() 
                 LIMIT :limit_num";
                 
        $stmt = $pdo->prepare($query);
        
        // Bind parameters - note that LIMIT parameter must be bound with explicit type
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":current_entry_id", $current_entry_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit_num", $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching more entries: " . $e->getMessage());
        return [];
    }
}

function create_journal_entry(object $pdo, int $user_id, string $title, string $content): int {
    try {
        $query = "INSERT INTO JournalEntries (user_id, title, content) VALUES (:user_id, :title, :content)";
        $stmt = $pdo->prepare($query);
        
        if (!$stmt->execute([
            ":user_id" => $user_id,
            ":title" => $title,
            ":content" => $content
        ])) {
            throw new Exception("Failed to insert journal entry");
        }

        return (int)$pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating journal entry: " . $e->getMessage());
        throw $e;
    }
}

function create_media_entry(object $pdo, int $entry_id, string $file_path, string $media_type): bool {
    try {
        $query = "INSERT INTO EntryMedia (entry_id, file_path, media_type) VALUES (:entry_id, :file_path, :media_type)";
        $stmt = $pdo->prepare($query);
        
        return $stmt->execute([
            ":entry_id" => $entry_id,
            ":file_path" => $file_path,
            ":media_type" => $media_type
        ]);
    } catch (PDOException $e) {
        error_log("Error creating media entry: " . $e->getMessage());
        throw $e;
    }
}