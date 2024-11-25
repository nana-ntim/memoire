<?php
// File: includes/auth/auth_functions.inc.php

// First, check if any of our functions are already defined
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }
}

if (!function_exists('force_login')) {
    function force_login() {
        if (!is_logged_in()) {
            header("Location: ../public/login.php");
            die();
        }
    }
}

if (!function_exists('is_admin_user')) {
    function is_admin_user($pdo, $user_id) {
        $query = "SELECT is_admin FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        return (bool)$stmt->fetchColumn();
    }
}

if (!function_exists('prevent_login_access')) {
    function prevent_login_access() {
        if (is_logged_in()) {
            require_once __DIR__ . "/../../config/dbh.inc.php";
            
            // Check if user is admin
            if (is_admin_user($pdo, $_SESSION['user_id'])) {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../pages/journal.php");
            }
            die();
        }
    }
}

if (!function_exists('get_user_data')) {
    function get_user_data($pdo, $user_id) {
        $query = "SELECT * FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('require_admin')) {
    function require_admin($pdo) {
        force_login();
        
        if (!is_admin_user($pdo, $_SESSION['user_id'])) {
            header("Location: ../pages/journal.php");
            die();
        }
    }
}