<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "journal_model.inc.php";
        require_once "../../config/config_session.inc.php";
        
        // Basic security check - ensure user is logged in
        if (!isset($_SESSION["user_id"])) {
            header("Location: ../../public/login.php");
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

        // Initialize variables for media handling
        $new_media = null;
        $old_media_path = null;

        // Get the current entry's media path before update
        $query = "SELECT m.file_path FROM EntryMedia m 
                 JOIN JournalEntries e ON m.entry_id = e.entry_id 
                 WHERE e.entry_id = :entry_id AND e.user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':entry_id' => $entry_id,
            ':user_id' => $_SESSION["user_id"]
        ]);
        $old_media_path = $stmt->fetchColumn();

        // Handle file upload if present
        if (isset($_FILES["media"]) && $_FILES["media"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $new_media = $_FILES["media"];
            
            // Validate file type
            $allowed_types = ["image/jpeg", "image/png", "image/gif"];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $new_media["tmp_name"]);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception("Only JPG, PNG, and GIF images are allowed");
            }

            // Validate file size (5MB limit)
            if ($new_media["size"] > 5 * 1024 * 1024) {
                throw new Exception("File size must be less than 5MB");
            }

            // Generate new filename and path
            $file_extension = strtolower(pathinfo($new_media["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid('journal_', true) . '.' . $file_extension;
            $upload_dir = "../../uploads/journal/";
            $db_path = "../uploads/journal/";
            $upload_path = $upload_dir . $new_filename;
            $db_file_path = $db_path . $new_filename;

            // Create upload directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Update the entry text content
                $query = "UPDATE JournalEntries 
                         SET title = :title, content = :content 
                         WHERE entry_id = :entry_id AND user_id = :user_id";
                $stmt = $pdo->prepare($query);
                if (!$stmt->execute([
                    ":title" => $title,
                    ":content" => $content,
                    ":entry_id" => $entry_id,
                    ":user_id" => $_SESSION["user_id"]
                ])) {
                    throw new Exception("Failed to update entry content");
                }

                // Move new file
                if (!move_uploaded_file($new_media["tmp_name"], $upload_path)) {
                    throw new Exception("Failed to save uploaded file");
                }

                // Update media entry
                $query = "UPDATE EntryMedia 
                         SET file_path = :file_path, media_type = :media_type 
                         WHERE entry_id = :entry_id";
                $stmt = $pdo->prepare($query);
                if (!$stmt->execute([
                    ":file_path" => $db_file_path,
                    ":media_type" => $mime_type,
                    ":entry_id" => $entry_id
                ])) {
                    throw new Exception("Failed to update media entry");
                }

                // Delete old file if it exists
                if ($old_media_path) {
                    $old_media_path = preg_replace('/^\.\.\//', '', $old_media_path);
                    $full_old_path = __DIR__ . "/../../" . $old_media_path;
                    if (file_exists($full_old_path)) {
                        if (!unlink($full_old_path)) {
                            error_log("Failed to delete old file: " . $full_old_path);
                        }
                    }
                }

                // Commit transaction
                $pdo->commit();
                $_SESSION["entry_success"] = "Entry updated successfully";

            } catch (Exception $e) {
                // Rollback and cleanup on error
                $pdo->rollBack();
                if (file_exists($upload_path)) {
                    unlink($upload_path);
                }
                throw $e;
            }
        } else {
            // Only update text content if no new media
            $query = "UPDATE JournalEntries 
                     SET title = :title, content = :content 
                     WHERE entry_id = :entry_id AND user_id = :user_id";
            $stmt = $pdo->prepare($query);
            if (!$stmt->execute([
                ":title" => $title,
                ":content" => $content,
                ":entry_id" => $entry_id,
                ":user_id" => $_SESSION["user_id"]
            ])) {
                throw new Exception("Failed to update entry content");
            }
            $_SESSION["entry_success"] = "Entry updated successfully";
        }

        // Redirect back to the entry page
        header("Location: ../../pages/entry.php?id=" . $entry_id);
        die();

    } catch (Exception $e) {
        error_log("Error updating entry: " . $e->getMessage());
        $_SESSION["entry_error"] = $e->getMessage();
        header("Location: ../../pages/entry.php?id=" . ($entry_id ?? ''));
        die();
    }
} else {
    header("Location: ../../pages/journal.php");
    die();
}