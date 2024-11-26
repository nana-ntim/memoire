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

        // Get and validate input
        $reflection_id = filter_input(INPUT_POST, 'reflection_id', FILTER_VALIDATE_INT);
        $question1 = trim($_POST["question1"] ?? "");
        $question2 = trim($_POST["question2"] ?? "");
        $question3 = trim($_POST["question3"] ?? "");

        // Validate reflection ID
        if (!$reflection_id) {
            throw new Exception("Invalid reflection ID");
        }

        // Validate questions
        if (empty($question1) || empty($question2) || empty($question3)) {
            throw new Exception("All questions must be answered");
        }

        // Verify ownership and update
        if (update_reflection($pdo, $reflection_id, $_SESSION["user_id"], $question1, $question2, $question3)) {
            $_SESSION["reflection_success"] = "Reflection updated successfully!";
        } else {
            throw new Exception("Failed to update reflection");
        }

        header("Location: ../../pages/reflect.php");
        die();

    } catch (Exception $e) {
        error_log("Error updating reflection: " . $e->getMessage());
        $_SESSION["reflection_error"] = $e->getMessage();
        header("Location: ../../pages/reflect.php");
        die();
    }
} else {
    header("Location: ../../pages/reflect.php");
    die();
}