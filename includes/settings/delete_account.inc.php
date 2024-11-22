<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Gets the data from the user accepting
    $confirmDelete = $_POST["confirmDelete"];

    try {

        // Required files
        require_once "../../config/dbh.inc.php";
        require_once "settings_model.inc.php";
        require_once "../../config/config_session.inc.php";

        // Verify confirmation text
        if ($confirmDelete !== "DELETE") {
            $_SESSION["settings_error"] = "Please type DELETE to confirm account deletion";
        }

        // Delete account and all associated data
        if (delete_user_account($pdo, $_SESSION["user_id"])) {
            // Clear session and redirect to signup page
            session_unset();
            session_destroy();
            
            // Start new session for message
            session_start();
            $_SESSION["signup_success"] = "Your account has been successfully deleted.";
            header("Location: ../../public/login.php");
        } else {
            $_SESSION["settings_error"] = "Failed to delete account. Please try again.";
            header("Location: ../../pages/settings.php");
        }
        die();

    } catch (Exception $e) {
        $_SESSION["settings_error"] = "An error occurred. Please try again later.";
        error_log("Error deleting account: " . $e->getMessage());
        header("Location: ../../pages/settings.php");
        die();
    }
} else {
    header("Location: ../../public/login.php");
    die();
}