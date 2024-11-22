<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Include required files and start session
        require_once "../../config/dbh.inc.php";
        require_once "journal_model.inc.php";
        require_once "../../config/config_session.inc.php";

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

        // Start transaction
        $pdo->beginTransaction();

        try {
            // First, get the entry details and verify ownership
            $query = "SELECT e.*, m.file_path 
                     FROM JournalEntries e 
                     LEFT JOIN EntryMedia m ON e.entry_id = m.entry_id 
                     WHERE e.entry_id = :entry_id AND e.user_id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':entry_id' => $entry_id,
                ':user_id' => $_SESSION["user_id"]
            ]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$entry) {
                throw new Exception("Entry not found or access denied");
            }

            // Store the file path for deletion
            $file_path = $entry['file_path'];

            // Log the file path for debugging
            error_log("File path from database: " . $file_path);

            // Construct the full file path
            // Remove the leading '../' from the file path as we're already using a relative path
            $file_path = preg_replace('/^\.\.\//', '', $file_path);
            $full_file_path = __DIR__ . "/../../" . $file_path;

            // Log the constructed full path
            error_log("Constructed full file path: " . $full_file_path);

            // Delete the entry (cascade will handle EntryMedia)
            $delete_query = "DELETE FROM JournalEntries WHERE entry_id = :entry_id AND user_id = :user_id";
            $delete_stmt = $pdo->prepare($delete_query);
            if (!$delete_stmt->execute([
                ':entry_id' => $entry_id,
                ':user_id' => $_SESSION["user_id"]
            ])) {
                throw new Exception("Failed to delete entry from database");
            }

            // Check if file exists before attempting deletion
            if ($file_path && file_exists($full_file_path)) {
                error_log("File exists, attempting deletion: " . $full_file_path);
                if (!unlink($full_file_path)) {
                    error_log("Failed to delete file: " . $full_file_path);
                    // Continue execution as database deletion was successful
                } else {
                    error_log("Successfully deleted file: " . $full_file_path);
                }
            } else {
                error_log("File does not exist: " . $full_file_path);
            }

            // Commit transaction
            $pdo->commit();

            $_SESSION["entry_success"] = "Entry deleted successfully";
            header("Location: ../../pages/journal.php");

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

    } catch (Exception $e) {
        // Log error and set user-friendly message
        error_log("Error deleting entry: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        $_SESSION["entry_error"] = "An error occurred while deleting the entry: " . $e->getMessage();
        header("Location: ../../pages/journal.php");
    }
    die();
} else {
    // If not POST request, redirect to journal page
    header("Location: ../../pages/journal.php");
    die();
}