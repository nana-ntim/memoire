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

        // Get and validate form data
        $question1 = trim($_POST["question1"] ?? "");
        $question2 = trim($_POST["question2"] ?? "");
        $question3 = trim($_POST["question3"] ?? "");

        // Validate all fields are filled
        if (empty($question1) || empty($question2) || empty($question3)) {
            throw new Exception("All questions must be answered");
        }

        // Create reflection
        if (create_reflection($pdo, $_SESSION["user_id"], $question1, $question2, $question3)) {
            $_SESSION["reflection_success"] = "Reflection saved successfully!";
            header("Location: ../../pages/reflect.php");
            die();
        } else {
            throw new Exception("Failed to save reflection");
        }

    } catch (Exception $e) {
        error_log("Error creating reflection: " . $e->getMessage());
        $_SESSION["reflection_error"] = $e->getMessage();
        header("Location: ../../pages/reflect.php");
        die();
    }
} else {
    header("Location: ../../pages/reflect.php");
    die();
}