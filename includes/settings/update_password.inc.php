<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST["currentPassword"];
    $newPassword = $_POST["newPassword"];
    $confirmPassword = $_POST["confirmPassword"];

    try {
        require_once "../../config/dbh.inc.php";
        require_once "settings_model.inc.php";
        require_once "../../config/config_session.inc.php";

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION["settings_error"] = "All fields are required";
            header("Location: ../../pages/settings.php");
            die();
        }

        // Check if new passwords match
        if ($newPassword !== $confirmPassword) {
            $_SESSION["settings_error"] = "New passwords do not match";
            header("Location: ../../pages/settings.php");
            die();
        }

        // Verify password length
        if (strlen($newPassword) < 8) {
            $_SESSION["settings_error"] = "Password must be at least 8 characters long";
            header("Location: ../../pages/settings.php");
            die();
        }

        // Verify current password
        if (!verify_current_password($pdo, $_SESSION["user_id"], $currentPassword)) {
            $_SESSION["settings_error"] = "Current password is incorrect";
            header("Location: ../../pages/settings.php");
            die();
        }

        // Update password
        if (update_user_password($pdo, $_SESSION["user_id"], $newPassword)) {
            $_SESSION["settings_success"] = "Password updated successfully!";
        } else {
            $_SESSION["settings_error"] = "Failed to update password";
        }

        header("Location: ../../pages/settings.php");
        die();

    } catch (Exception $e) {
        $_SESSION["settings_error"] = "An error occurred. Please try again later.";
        error_log("Error updating password: " . $e->getMessage());
        header("Location: ../../pages/settings.php");
        die();
    }
} else {
    header("Location: ../../pages/settings.php");
    die();
}