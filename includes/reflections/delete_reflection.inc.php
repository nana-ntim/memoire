<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "reflection_model.inc.php";
        require_once "../../config/config_session.inc.php";

        // Basic security check - ensure user is logged in
        if (!isset($_SESSION["user_id"])) {
            header("Location: ../../public/login.php");
            die();
        }

        // Get and validate reflection ID
        $reflection_id = filter_input(INPUT_POST, 'reflection_id', FILTER_VALIDATE_INT);
        if (!$reflection_id) {
            throw new Exception("Invalid reflection ID");
        }

        // Delete reflection
        if (delete_reflection($pdo, $reflection_id, $_SESSION["user_id"])) {
            $_SESSION["reflection_success"] = "Reflection deleted successfully";
        } else {
            throw new Exception("Failed to delete reflection");
        }

        header("Location: ../../pages/reflect.php");
        die();

    } catch (Exception $e) {
        error_log("Error deleting reflection: " . $e->getMessage());
        $_SESSION["reflection_error"] = $e->getMessage();
        header("Location: ../../pages/reflect.php");
        die();
    }
} else {
    header("Location: ../../pages/reflect.php");
    die();
}