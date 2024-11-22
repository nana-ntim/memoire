<?php
declare(strict_types=1);

function create_collection(object $pdo, int $user_id, string $name, ?string $description = null) {
    try {
        $query = "INSERT INTO Collections (user_id, name, description) VALUES (:user_id, :name, :description)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":user_id" => $user_id,
            ":name" => $name,
            ":description" => $description
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry error
            throw new Exception("A collection with this name already exists");
        }
        error_log("Error creating collection: " . $e->getMessage());
        throw new Exception("Failed to create collection");
    }
}

function get_user_collections(object $pdo, int $user_id) {
    try {
        $query = "SELECT c.*, COUNT(ce.entry_id) as entry_count 
                 FROM Collections c 
                 LEFT JOIN CollectionEntries ce ON c.collection_id = ce.collection_id 
                 WHERE c.user_id = :user_id 
                 GROUP BY c.collection_id 
                 ORDER BY c.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching collections: " . $e->getMessage());
        return [];
    }
}

function get_collection_entries(object $pdo, int $collection_id, int $user_id) {
    try {
        $query = "SELECT e.*, m.file_path, m.media_type 
                 FROM JournalEntries e 
                 JOIN CollectionEntries ce ON e.entry_id = ce.entry_id 
                 LEFT JOIN EntryMedia m ON e.entry_id = m.entry_id 
                 JOIN Collections c ON ce.collection_id = c.collection_id 
                 WHERE c.collection_id = :collection_id 
                 AND c.user_id = :user_id 
                 ORDER BY ce.added_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":collection_id" => $collection_id,
            ":user_id" => $user_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching collection entries: " . $e->getMessage());
        return [];
    }
}

function add_entry_to_collection(object $pdo, int $collection_id, int $entry_id, int $user_id) {
    try {
        // First verify the collection belongs to the user
        $query = "SELECT user_id FROM Collections WHERE collection_id = :collection_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":collection_id" => $collection_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || $result['user_id'] !== $user_id) {
            throw new Exception("Collection not found or access denied");
        }

        // Then add the entry
        $query = "INSERT INTO CollectionEntries (collection_id, entry_id) VALUES (:collection_id, :entry_id)";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ":collection_id" => $collection_id,
            ":entry_id" => $entry_id
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry error
            throw new Exception("Entry is already in this collection");
        }
        error_log("Error adding entry to collection: " . $e->getMessage());
        throw new Exception("Failed to add entry to collection");
    }
}

function remove_entry_from_collection(object $pdo, int $collection_id, int $entry_id, int $user_id) {
    try {
        // First verify the collection belongs to the user
        $query = "SELECT user_id FROM Collections WHERE collection_id = :collection_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":collection_id" => $collection_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || $result['user_id'] !== $user_id) {
            throw new Exception("Collection not found or access denied");
        }

        $query = "DELETE FROM CollectionEntries 
                 WHERE collection_id = :collection_id AND entry_id = :entry_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ":collection_id" => $collection_id,
            ":entry_id" => $entry_id
        ]);
    } catch (PDOException $e) {
        error_log("Error removing entry from collection: " . $e->getMessage());
        return false;
    }
}

function delete_collection(object $pdo, int $collection_id, int $user_id) {
    try {
        $query = "DELETE FROM Collections 
                 WHERE collection_id = :collection_id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ":collection_id" => $collection_id,
            ":user_id" => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Error deleting collection: " . $e->getMessage());
        return false;
    }
}

function update_collection_name(object $pdo, int $collection_id, int $user_id, string $new_name) {
    try {
        $query = "UPDATE Collections 
                 SET name = :name 
                 WHERE collection_id = :collection_id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ":name" => $new_name,
            ":collection_id" => $collection_id,
            ":user_id" => $user_id
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry error
            throw new Exception("A collection with this name already exists");
        }
        error_log("Error updating collection name: " . $e->getMessage());
        throw new Exception("Failed to update collection name");
    }
}

function get_collection_by_id(object $pdo, int $collection_id, int $user_id) {
    try {
        $query = "SELECT * FROM Collections 
                 WHERE collection_id = :collection_id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":collection_id" => $collection_id,
            ":user_id" => $user_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching collection: " . $e->getMessage());
        return false;
    }
}