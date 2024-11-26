<?php
// File: includes/auth/login.inc.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $pwd = $_POST["password"];

    try {
        require_once "../../../config/dbh.inc.php";
        require_once "../../../config/config_session.inc.php";
        require_once "login_model.inc.php";
        require_once "login_contr.inc.php";

        // ERROR HANDLERS
        $errors = [];

        if (is_input_empty($email, $pwd)) {
            $errors["empty_input"] = "Fill in all fields!";
        }

        $result = get_user($pdo, $email);
        if (is_email_wrong($result)) {
            $errors["login_incorrect"] = "Incorrect login info!";
        }

        if (!is_email_wrong($result) && is_password_wrong($pwd, $result["passwd"])) {
            $errors["login_incorrect"] = "Incorrect login info!";
        }

        if($errors) {
            $_SESSION["errors_login"] = $errors;
            header("Location: ../../../public/login.php");
            die();
        }

        // If we get here, login was successful
        $newSessionId = session_create_id();
        $sessionId = $newSessionId . "_" . $result["user_id"];
        session_id($sessionId);

        $_SESSION["user_id"] = $result["user_id"];
        $_SESSION["user_email"] = $result["email"];
        $_SESSION["last_regeneration"] = time();
        $_SESSION["is_admin"] = $result["is_admin"] ?? false;

        // Update the last_login timestamp
        $query = "UPDATE Users SET last_login = NOW() WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":user_id", $result["user_id"]);
        $stmt->execute();

        // Redirect based on user type - always to journal first
        header("Location: ../../../pages/journal.php");
        
        $pdo = null;
        $stmt = null;
        die();

    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
} else {
    header("Location: ../../../public/login.php");
    die();
}