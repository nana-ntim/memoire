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

        // Get and validate collection name
        $name = trim($_POST["name"] ?? "");
        
        if (empty($name)) {
            throw new Exception("Collection name is required");
        }

        if (strlen($name) > 255) {
            throw new Exception("Collection name is too long");
        }

        // Create the collection
        $collection_id = create_collection($pdo, $_SESSION["user_id"], $name);
        
        if ($collection_id) {
            echo json_encode([
                'success' => true,
                'message' => 'Collection created successfully!',
                'collection_id' => $collection_id
            ]);
        } else {
            throw new Exception("Failed to create collection");
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