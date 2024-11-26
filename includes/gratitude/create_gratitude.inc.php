<?php
// File: includes/gratitude/create_gratitude.inc.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once "../../config/dbh.inc.php";
        require_once "../../config/config_session.inc.php";
        require_once "gratitude_model.inc.php";

        // Basic security check - ensure user is logged in
        if (!isset($_SESSION["user_id"])) {
            header("Location: ../../public/login.php");
            die();
        }

        // Get and validate content
        $content = trim($_POST["content"] ?? "");
        
        if (empty($content)) {
            throw new Exception("Please enter your gratitude");
        }

        // Create gratitude entry
        if (create_gratitude_entry($pdo, $_SESSION["user_id"], $content)) {
            $_SESSION["entry_success"] = "Your gratitude has been saved!";
        } else {
            throw new Exception("Failed to save your gratitude");
        }

        header("Location: ../../pages/gratitude.php");
        die();

    } catch (Exception $e) {
        error_log("Error creating gratitude entry: " . $e->getMessage());
        $_SESSION["entry_error"] = $e->getMessage();
        header("Location: ../../pages/gratitude.php");
        die();
    }
} else {
    header("Location: ../../pages/gratitude.php");
    die();
}