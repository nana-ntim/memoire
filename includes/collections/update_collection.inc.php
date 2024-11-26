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
        $collection_id = filter_input(INPUT_POST, 'collection_id', FILTER_VALIDATE_INT);
        $new_name = trim($_POST["name"] ?? "");

        // Validate collection ID
        if (!$collection_id) {
            throw new Exception("Invalid collection ID");
        }

        // Validate new name
        if (empty($new_name)) {
            throw new Exception("Collection name is required");
        }

        if (strlen($new_name) > 255) {
            throw new Exception("Collection name is too long");
        }

        // Verify collection exists and belongs to user
        $collection = get_collection_by_id($pdo, $collection_id, $_SESSION["user_id"]);
        if (!$collection) {
            throw new Exception("Collection not found");
        }

        // Update collection name
        if (update_collection_name($pdo, $collection_id, $_SESSION["user_id"], $new_name)) {
            echo json_encode([
                'success' => true,
                'message' => 'Collection updated successfully!',
                'collection' => [
                    'id' => $collection_id,
                    'name' => $new_name
                ]
            ]);
        } else {
            throw new Exception("Failed to update collection");
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