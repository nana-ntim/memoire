<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $pwd = $_POST["password"];
    $confirm_pwd = $_POST["confirmPassword"];

    try {
        require_once "../../../config/dbh.inc.php";
        require_once "../../../config/config_session.inc.php";
        require_once "./signup_model.inc.php";
        require_once "./signup_contr.inc.php";

        // ERROR HANDLERS
        $errors = [];

        if (is_input_empty($firstName, $lastName, $pwd, $email)) {
            $errors["empty_input"] = "All fields are required";
        }

        if (is_email_invalid($email)) {
            $errors["invalid_email"] = "Please enter a valid email address";
        }

        if (is_email_registered($pdo, $email)) {
            $errors["email_used"] = "This email is already registered";
        }

        if (!is_password_correct($pwd, $confirm_pwd)) {
            $errors["not_matching_password"] = "Passwords do not match";
        }

        if (strlen($pwd) < 8) {
            $errors["password_length"] = "Password must be at least 8 characters long";
        }

        if($errors) {
            $_SESSION["errors_signup"] = $errors;

            $signupData = [
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $email
            ];

            $_SESSION["signup_data"] = $signupData;
            header("Location: ../../../public/signup.php");
            die();
        }

        create_user($pdo, $firstName, $lastName, $email, $pwd);

        // Set success message and redirect to login page
        $_SESSION["signup_success"] = "Account created successfully! Please log in.";
        header("Location: ../../../public/login.php");

        $pdo = null;
        $stmt = null;
        die();

    } catch (PDOException $e) {
        // Log the error (in a production environment)
        error_log("Database error: " . $e->getMessage());
        
        // Show a user-friendly error message
        $_SESSION["errors_signup"] = ["database_error" => "An error occurred. Please try again later."];
        header("Location: ../../../public/signup.php");
        die();
    }
} else {
    header("Location: ../../../public/signup.php");
    die();
}