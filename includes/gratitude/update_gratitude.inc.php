<?php
// File: includes/gratitude/update_gratitude.inc.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "../../config/config_session.inc.php";
        require_once "gratitude_model.inc.php";

        // Basic security check - ensure user is logged in
        if (!isset($_SESSION["user_id"])) {
            header("Location: ../../public/login.php");
            die();
        }

        // Get and validate input
        $entry_id = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);
        $content = trim($_POST["content"] ?? "");

        // Validate data
        if (!$entry_id || empty($content)) {
            throw new Exception("Please enter your gratitude");
        }

        // Verify ownership and update
        if (update_gratitude_entry($pdo, $entry_id, $_SESSION["user_id"], $content)) {
            $_SESSION["entry_success"] = "Entry updated successfully";
        } else {
            throw new Exception("Failed to update entry");
        }

        header("Location: ../../pages/gratitude.php");
        die();

    } catch (Exception $e) {
        error_log("Error updating gratitude entry: " . $e->getMessage());
        $_SESSION["entry_error"] = $e->getMessage();
        header("Location: ../../pages/gratitude.php");
        die();
    }
} else {
    header("Location: ../../pages/gratitude.php");
    die();
}