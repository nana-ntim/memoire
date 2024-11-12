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

        // Get form data with proper validation
        $entry_id = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);
        $title = trim($_POST["title"] ?? "");
        $content = trim($_POST["content"] ?? "");

        // Validate essential data
        if (!$entry_id || empty($title) || empty($content)) {
            throw new Exception("Required fields are missing");
        }

        // Initialize media variable
        $new_media = null;

        // Handle file upload if present
        if (isset($_FILES["media"]) && $_FILES["media"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $new_media = $_FILES["media"];
            
            // Validate file type
            $allowed_types = ["image/jpeg", "image/png", "image/gif"];
            if (!in_array($new_media["type"], $allowed_types)) {
                throw new Exception("Only JPG, PNG, and GIF images are allowed");
            }

            // Validate file size (5MB limit)
            if ($new_media["size"] > 5 * 1024 * 1024) {
                throw new Exception("File size must be less than 5MB");
            }
        }

        // Update the entry using the model function
        if (update_journal_entry($pdo, $entry_id, $_SESSION["user_id"], $title, $content, $new_media)) {
            $_SESSION["entry_success"] = "Entry updated successfully";
        } else {
            throw new Exception("Failed to update entry");
        }

        // Redirect back to the entry page
        header("Location: ../pages/entry.php?id=" . $entry_id);
        die();

    } catch (Exception $e) {
        // Log error and set user-friendly message
        error_log("Error updating entry: " . $e->getMessage());
        $_SESSION["entry_error"] = $e->getMessage();
        
        // Redirect back to entry page
        header("Location: ../pages/entry.php?id=" . ($entry_id ?? ''));
        die();
    }
} else {
    // If not POST request, redirect to journal page
    header("Location: ../pages/journal.php");
    die();
}