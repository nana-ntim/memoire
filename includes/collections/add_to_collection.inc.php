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

        // Attempt to add entry to collection
        if (add_entry_to_collection($pdo, $collection_id, $entry_id, $_SESSION["user_id"])) {
            // Get collection name for success message
            $collection = get_collection_by_id($pdo, $collection_id, $_SESSION["user_id"]);
            $collection_name = $collection ? $collection['name'] : 'collection';

            echo json_encode([
                'success' => true,
                'message' => "Entry added to '$collection_name' successfully!"
            ]);
        } else {
            throw new Exception("Failed to add entry to collection");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    header("Location: ../../pages/collections.php");
    die();
}