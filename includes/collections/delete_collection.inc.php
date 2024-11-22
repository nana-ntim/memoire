<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "../../config/config_session.inc.php";
        require_once "collections_model.inc.php";

        // Validate user authentication
        if (!isset($_SESSION["user_id"])) {
            throw new Exception("User not authenticated");
        }

        // Get and validate collection ID
        $collection_id = filter_input(INPUT_POST, 'collection_id', FILTER_VALIDATE_INT);
        
        if (!$collection_id) {
            throw new Exception("Invalid collection ID");
        }

        // Get collection info before deletion for success message
        $collection = get_collection_by_id($pdo, $collection_id, $_SESSION["user_id"]);
        
        if (!$collection) {
            throw new Exception("Collection not found");
        }

        // Delete the collection
        if (delete_collection($pdo, $collection_id, $_SESSION["user_id"])) {
            $_SESSION["collection_success"] = "Collection '" . htmlspecialchars($collection['name']) . "' deleted successfully";
        } else {
            throw new Exception("Failed to delete collection");
        }

    } catch (Exception $e) {
        $_SESSION["collection_error"] = $e->getMessage();
    }

    // Redirect back to collections page
    header("Location: ../../pages/collections.php");
    die();
} else {
    header("Location: ../../pages/collections.php");
    die();
}