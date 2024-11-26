<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "../../config/config_session.inc.php";

        // Define upload directory
        $upload_dir = __DIR__ . "/../../uploads/profiles/";

        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validate the uploaded file
        if (!isset($_FILES["profile_image"]) || $_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
            throw new Exception(getUploadErrorMessage($_FILES["profile_image"]["error"] ?? UPLOAD_ERR_NO_FILE));
        }

        $file = $_FILES["profile_image"];
        
        // Security checks
        // 1. File size limit (2MB)
        $maxFileSize = 2 * 1024 * 1024;
        if ($file["size"] > $maxFileSize) {
            throw new Exception("File is too large. Maximum size is 2MB.");
        }

        // 2. Validate file type using both MIME and extension
        $allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "webp"];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);
        
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        if (!in_array($mime_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.");
        }

        // 3. Generate safe filename
        $unique_filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;
        $relative_path = "uploads/profiles/" . $unique_filename;

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Get old profile image
            $stmt = $pdo->prepare("SELECT profile_image FROM Users WHERE user_id = ?");
            $stmt->execute([$_SESSION["user_id"]]);
            $old_image = $stmt->fetch(PDO::FETCH_COLUMN);

            // Move uploaded file
            if (!move_uploaded_file($file["tmp_name"], $upload_path)) {
                throw new Exception("Failed to save image. Please try again.");
            }

            // Update database with relative path
            $stmt = $pdo->prepare("UPDATE Users SET profile_image = ? WHERE user_id = ?");
            if (!$stmt->execute([$relative_path, $_SESSION["user_id"]])) {
                throw new Exception("Failed to update profile image reference.");
            }

            // Delete old profile image if it exists and is not the default image
            if ($old_image && $old_image !== 'assets/default-avatar.jpg') {
                $old_file_path = __DIR__ . "/../../" . $old_image;
                if (file_exists($old_file_path) && is_file($old_file_path)) {
                    if (!unlink($old_file_path)) {
                        error_log("Failed to delete old profile image: " . $old_file_path);
                    }
                }
            }

            // Commit transaction
            $pdo->commit();
            
            $_SESSION["settings_success"] = "Profile picture updated successfully!";

        } catch (Exception $e) {
            // If anything fails, rollback and clean up
            $pdo->rollBack();
            if (file_exists($upload_path)) {
                unlink($upload_path);
            }
            throw $e;
        }

        header("Location: ../../pages/settings.php");
        die();

    } catch (Exception $e) {
        error_log("Profile image update error: " . $e->getMessage());
        $_SESSION["settings_error"] = "Failed to update profile picture: " . $e->getMessage();
        header("Location: ../../pages/settings.php");
        die();
    }
} else {
    header("Location: ../../pages/settings.php");
    die();
}

function getUploadErrorMessage($code) {
    return match ($code) {
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the server's maximum file size limit.",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file is too large.",
        UPLOAD_ERR_PARTIAL => "The file was only partially uploaded. Please try again.",
        UPLOAD_ERR_NO_FILE => "No file was selected. Please choose an image.",
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => "Server configuration error. Please contact support.",
        default => "An unknown error occurred. Please try again."
    };
}