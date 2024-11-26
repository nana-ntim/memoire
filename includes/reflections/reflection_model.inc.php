<?php
declare(strict_types=1);

function create_reflection(object $pdo, int $user_id, string $question1, string $question2, string $question3): bool {
    try {
        $query = "INSERT INTO Reflections (user_id, question1, question2, question3) 
                 VALUES (:user_id, :q1, :q2, :q3)";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':q1' => $question1,
            ':q2' => $question2,
            ':q3' => $question3
        ]);
    } catch (PDOException $e) {
        error_log("Error creating reflection: " . $e->getMessage());
        return false;
    }
}

function get_user_reflections(object $pdo, int $user_id, int $page = 1, int $per_page = 5): array {
    try {
        $offset = ($page - 1) * $per_page;
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) FROM Reflections WHERE user_id = :user_id";
        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->execute([':user_id' => $user_id]);
        $total_items = $count_stmt->fetchColumn();
        
        // Get reflections with pagination
        $query = "SELECT * FROM Reflections 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'reflections' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total_pages' => ceil($total_items / $per_page),
            'current_page' => $page,
            'total_items' => $total_items,
            'per_page' => $per_page
        ];
    } catch (PDOException $e) {
        error_log("Error fetching reflections: " . $e->getMessage());
        return [
            'reflections' => [],
            'total_pages' => 0,
            'current_page' => 1,
            'total_items' => 0,
            'per_page' => $per_page
        ];
    }
}

function get_reflection_by_id(object $pdo, int $reflection_id, int $user_id): ?array {
    try {
        $query = "SELECT * FROM Reflections 
                 WHERE reflection_id = :reflection_id 
                 AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':reflection_id' => $reflection_id,
            ':user_id' => $user_id
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching reflection: " . $e->getMessage());
        return null;
    }
}

function update_reflection(object $pdo, int $reflection_id, int $user_id, string $question1, string $question2, string $question3): bool {
    try {
        $query = "UPDATE Reflections 
                 SET question1 = :q1, question2 = :q2, question3 = :q3 
                 WHERE reflection_id = :reflection_id 
                 AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ':reflection_id' => $reflection_id,
            ':user_id' => $user_id,
            ':q1' => $question1,
            ':q2' => $question2,
            ':q3' => $question3
        ]);
    } catch (PDOException $e) {
        error_log("Error updating reflection: " . $e->getMessage());
        return false;
    }
}

function delete_reflection(object $pdo, int $reflection_id, int $user_id): bool {
    try {
        $query = "DELETE FROM Reflections 
                 WHERE reflection_id = :reflection_id 
                 AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ':reflection_id' => $reflection_id,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Error deleting reflection: " . $e->getMessage());
        return false;
    }
}