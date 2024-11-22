<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "../../config/config_session.inc.php";
        require_once "collections_model.inc.php";

        // Validate user authentication
        if (!isset($_SESSION["user_id"])) {
            throw new Exception("User not authenticated");
        }

        // Get and validate input
        $entry_id = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);
        $collection_id = filter_input(INPUT_POST, 'collection_id', FILTER_VALIDATE_INT);

        if (!$entry_id || !$collection_id) {
            throw new Exception("Invalid entry or collection ID");
        }

        // Verify collection belongs to user
        $collection = get_collection_by_id($pdo, $collection_id, $_SESSION["user_id"]);
        if (!$collection) {
            throw new Exception("Collection not found or access denied");
        }

        // Remove entry from collection
        $query = "DELETE FROM CollectionEntries 
                 WHERE collection_id = :collection_id 
                 AND entry_id = :entry_id";
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([
            ":collection_id" => $collection_id,
            ":entry_id" => $entry_id
        ]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Entry removed from collection successfully'
            ]);
        } else {
            throw new Exception("Failed to remove entry from collection");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    die();
} else {
    header("Location: ../../pages/collections.php");
    die();
}