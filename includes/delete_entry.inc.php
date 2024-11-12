<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Include required files and start session
        require_once "dbh.inc.php";
        require_once "journal_model.inc.php";
        require_once "config_session.inc.php";

        // Basic security check - ensure user is logged in
        if (!isset($_SESSION["user_id"])) {
            header("Location: ../public/login.php");
            die();
        }

        // Get and validate entry ID
        $entry_id = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);
        if (!$entry_id) {
            throw new Exception("Invalid entry ID");
        }

        // Attempt to delete the entry and its associated media
        if (delete_journal_entry($pdo, $entry_id, $_SESSION["user_id"])) {
            $_SESSION["entry_success"] = "Entry deleted successfully";
            // Redirect to journal page after successful deletion
            header("Location: ../pages/journal.php");
        } else {
            throw new Exception("Failed to delete entry");
        }

    } catch (Exception $e) {
        // Log error and set user-friendly message
        error_log("Error deleting entry: " . $e->getMessage());
        $_SESSION["entry_error"] = "An error occurred while deleting the entry";
        
        // Redirect back to journal page
        header("Location: ../pages/journal.php");
    }
    die();
} else {
    // If not POST request, redirect to journal page
    header("Location: ../pages/journal.php");
    die();
}