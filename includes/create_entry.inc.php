<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "dbh.inc.php";
    require_once "config_session.inc.php";
    require_once "journal_model.inc.php";

    $response = [
        'success' => false,
        'errors' => []
    ];

    // Validate user authentication
    if (!isset($_SESSION["user_id"])) {
        $response['errors'][] = "User not authenticated";
        echo json_encode($response);
        die();
    }

    // Get and validate form data
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    // Validate required fields
    if (empty($title)) {
        $response['errors'][] = "Title is required";
    }
    if (empty($content)) {
        $response['errors'][] = "Content is required";
    }

    // Validate file upload
    if (!isset($_FILES["media"]) || $_FILES["media"]["error"] === UPLOAD_ERR_NO_FILE) {
        $response['errors'][] = "Please select an image for your journal entry";
    } elseif ($_FILES["media"]["error"] !== UPLOAD_ERR_OK) {
        $response['errors'][] = "Error uploading file: " . getUploadErrorMessage($_FILES["media"]["error"]);
    } else {
        $file = $_FILES["media"];
        
        // Validate file type and size
        $allowed_types = ["image/jpeg", "image/png", "image/gif"];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file["type"], $allowed_types)) {
            $response['errors'][] = "Only JPG, PNG, and GIF images are allowed";
        }

        if ($file["size"] > $max_size) {
            $response['errors'][] = "Image size must be less than 5MB";
        }
    }

    // If there are any errors, return them
    if (!empty($response['errors'])) {
        echo json_encode($response);
        die();
    }

    // Proceed with entry creation if no errors
    try {
        // Create upload directory if it doesn't exist
        $upload_dir = "../uploads/journal/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid('journal_', true) . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Start transaction
        $pdo->beginTransaction();

        // Create journal entry
        $query = "INSERT INTO JournalEntries (user_id, title, content) VALUES (:user_id, :title, :content)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ":user_id" => $_SESSION["user_id"],
            ":title" => $title,
            ":content" => $content
        ]);
        
        $entry_id = $pdo->lastInsertId();

        // Move uploaded file
        if (!move_uploaded_file($file["tmp_name"], $upload_path)) {
            throw new Exception("Failed to upload image");
        }

        // Create media entry
        $media_query = "INSERT INTO EntryMedia (entry_id, file_path, media_type) VALUES (:entry_id, :file_path, :media_type)";
        $media_stmt = $pdo->prepare($media_query);
        $media_stmt->execute([
            ":entry_id" => $entry_id,
            ":file_path" => $upload_path,
            ":media_type" => $file["type"]
        ]);

        // Commit transaction
        $pdo->commit();
        
        $response['success'] = true;
        $response['redirect'] = 'journal.php';
        $response['message'] = "Journal entry created successfully!";
        echo json_encode($response);
        die();

    } catch (Exception $e) {
        // Rollback and clean up
        $pdo->rollBack();
        if (isset($upload_path) && file_exists($upload_path)) {
            unlink($upload_path);
        }
        
        $response['errors'][] = "An error occurred while creating the entry. Please try again.";
        echo json_encode($response);
        die();
    }
}

function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the server's maximum file size limit.";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file is too large.";
        case UPLOAD_ERR_PARTIAL:
            return "The file was only partially uploaded. Please try again.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was selected.";
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
            return "Server configuration error. Please contact support.";
        default:
            return "An unknown error occurred. Please try again.";
    }
}