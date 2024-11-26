<?php
declare(strict_types=1);

function create_gratitude_entry(object $pdo, int $user_id, string $content) {
    try {
        $query = "INSERT INTO GratitudeEntries (user_id, content) VALUES (:user_id, :content)";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ":user_id" => $user_id,
            ":content" => $content
        ]);
    } catch (PDOException $e) {
        error_log("Error creating gratitude entry: " . $e->getMessage());
        throw $e;
    }
}

function get_user_gratitude_entries(object $pdo, int $user_id, int $page = 1, int $per_page = 5) {
    try {
        $offset = ($page - 1) * $per_page;
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) FROM GratitudeEntries WHERE user_id = :user_id";
        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->execute([':user_id' => $user_id]);
        $total_items = $count_stmt->fetchColumn();
        
        // Get entries with pagination
        $query = "SELECT * FROM GratitudeEntries 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'entries' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total_pages' => ceil($total_items / $per_page),
            'current_page' => $page,
            'total_items' => $total_items,
            'per_page' => $per_page
        ];
    } catch (PDOException $e) {
        error_log("Error fetching gratitude entries: " . $e->getMessage());
        return [
            'entries' => [],
            'total_pages' => 0,
            'current_page' => 1,
            'total_items' => 0,
            'per_page' => $per_page
        ];
    }
}

function get_gratitude_by_id(object $pdo, int $entry_id, int $user_id) {
    try {
        $query = "SELECT * FROM GratitudeEntries 
                 WHERE entry_id = :entry_id 
                 AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':entry_id' => $entry_id,
            ':user_id' => $user_id
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching gratitude entry: " . $e->getMessage());
        return null;
    }
}

function update_gratitude_entry(object $pdo, int $entry_id, int $user_id, string $content) {
    try {
        $query = "UPDATE GratitudeEntries 
                 SET content = :content 
                 WHERE entry_id = :entry_id 
                 AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ':entry_id' => $entry_id,
            ':user_id' => $user_id,
            ':content' => $content
        ]);
    } catch (PDOException $e) {
        error_log("Error updating gratitude entry: " . $e->getMessage());
        return false;
    }
}

function delete_gratitude_entry(object $pdo, int $entry_id, int $user_id) {
    try {
        $query = "DELETE FROM GratitudeEntries 
                 WHERE entry_id = :entry_id 
                 AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ':entry_id' => $entry_id,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Error deleting gratitude entry: " . $e->getMessage());
        return false;
    }
}