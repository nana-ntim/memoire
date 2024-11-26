<?php
// File: includes/gratitude/delete_gratitude.inc.php

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

        // Get and validate entry ID
        $entry_id = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);
        if (!$entry_id) {
            throw new Exception("Invalid entry ID");
        }

        // Delete the entry
        if (delete_gratitude_entry($pdo, $entry_id, $_SESSION["user_id"])) {
            $_SESSION["entry_success"] = "Entry deleted successfully";
        } else {
            throw new Exception("Failed to delete entry");
        }

        header("Location: ../../pages/gratitude.php");
        die();

    } catch (Exception $e) {
        error_log("Error deleting gratitude entry: " . $e->getMessage());
        $_SESSION["entry_error"] = $e->getMessage();
        header("Location: ../../pages/gratitude.php");
        die();
    }
} else {
    header("Location: ../../pages/gratitude.php");
    die();
}