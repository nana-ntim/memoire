<?php
declare(strict_types=1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../pages/journal.php");
    die();
}

// Prevent any output before JSON response
ob_start();
header('Content-Type: application/json');

try {
    require_once "../../config/dbh.inc.php";
    require_once "../../config/config_session.inc.php";
    require_once "journal_model.inc.php";
    require_once "entry_helpers.php";

    // Check authentication
    if (!isset($_SESSION["user_id"])) {
        throw new Exception("User not authenticated");
    }

    // Validate required data
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    // Validate input
    $errors = validate_entry_input($title, $content);

    // Validate file upload
    if (!isset($_FILES["media"]) || $_FILES["media"]["error"] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please select an image for your journal entry";
    } elseif ($_FILES["media"]["error"] !== UPLOAD_ERR_OK) {
        $errors[] = match ($_FILES["media"]["error"]) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File size exceeds limit",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file",
            UPLOAD_ERR_EXTENSION => "File upload stopped by extension",
            default => "Unknown upload error"
        };
    } else {
        // Validate file type
        $allowed_types = ["image/jpeg", "image/png", "image/gif"];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES["media"]["tmp_name"]);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        }

        // Validate file size (5MB)
        if ($_FILES["media"]["size"] > 5 * 1024 * 1024) {
            $errors[] = "File size must be less than 5MB";
        }
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        die();
    }

    try {
        // Set up upload directory
        $upload_dir = "../../uploads/journal/";
        $db_path = "../uploads/journal/";
        
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
            chmod($upload_dir, 0777);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid('journal_', true) . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;
        $db_file_path = $db_path . $unique_filename;

        // Start transaction
        $pdo->beginTransaction();

        // Create journal entry
        $query = "INSERT INTO JournalEntries (user_id, title, content) VALUES (:user_id, :title, :content)";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            ":user_id" => $_SESSION["user_id"],
            ":title" => $title,
            ":content" => $content
        ]);

        if (!$result) {
            throw new Exception("Failed to create journal entry");
        }

        $entry_id = (int)$pdo->lastInsertId();

        // Move uploaded file
        if (!move_uploaded_file($_FILES["media"]["tmp_name"], $upload_path)) {
            throw new Exception("Failed to save uploaded file");
        }

        // Create media entry
        $media_query = "INSERT INTO EntryMedia (entry_id, file_path, media_type) 
                       VALUES (:entry_id, :file_path, :media_type)";
        $media_stmt = $pdo->prepare($media_query);
        $result = $media_stmt->execute([
            ":entry_id" => $entry_id,
            ":file_path" => $db_file_path,
            ":media_type" => $mime_type
        ]);

        if (!$result) {
            throw new Exception("Failed to create media entry");
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'redirect' => '../pages/journal.php',
            'message' => "Journal entry created successfully!"
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Clean up uploaded file if it exists
        if (isset($upload_path) && file_exists($upload_path)) {
            unlink($upload_path);
        }

        throw $e;
    }

} catch (Exception $e) {
    error_log("Error creating entry: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'errors' => [$e->getMessage()]
    ]);
}