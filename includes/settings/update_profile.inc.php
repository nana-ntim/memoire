<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];

    try {
        require_once "../../config/dbh.inc.php";
        require_once "settings_model.inc.php";
        require_once "../../config/config_session.inc.php";

        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $_SESSION["settings_error"] = "All fields are required";
            header("Location: ../../pages/settings.php");
            die();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["settings_error"] = "Invalid email format";
            header("Location: ../../pages/settings.php");
            die();
        }

        // Update profile
        if (update_user_profile($pdo, $_SESSION["user_id"], $firstName, $lastName, $email)) {
            $_SESSION["settings_success"] = "Profile updated successfully!";
            // Update session email if it was changed
            $_SESSION["user_email"] = $email;
        } else {
            $_SESSION["settings_error"] = "Failed to update profile";
        }

        header("Location: ../../pages/settings.php");
        die();

    } catch (Exception $e) {
        $_SESSION["settings_error"] = "An error occurred. Please try again later.";
        error_log("Error updating profile: " . $e->getMessage());
        header("Location: ../../pages/settings.php");
        die();
    }
} else {
    header("Location: ../../pages/settings.php");
    die();
}