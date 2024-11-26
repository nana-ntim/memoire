<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "reflection_model.inc.php";
        require_once "../../config/config_session.inc.php";

        // Basic security check - ensure user is logged in
        if (!isset($_SESSION["user_id"])) {
            throw new Exception("User not authenticated");
        }

        // Get and validate reflection ID
        $reflection_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$reflection_id) {
            throw new Exception("Invalid reflection ID");
        }

        // Get reflection data
        $reflection = get_reflection_by_id($pdo, $reflection_id, $_SESSION["user_id"]);
        if (!$reflection) {
            throw new Exception("Reflection not found or access denied");
        }

        echo json_encode([
            'success' => true,
            'reflection' => $reflection
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    die();
} else {
    header("Location: ../../pages/reflect.php");
    die();
}