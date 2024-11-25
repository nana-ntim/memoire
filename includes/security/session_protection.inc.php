<?php
session_start();

function is_user_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

function is_admin_user($pdo, $user_id) {
    $query = "SELECT is_admin FROM Users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([":user_id" => $user_id]);
    return (bool)$stmt->fetchColumn();
}

function force_login() {
    if (!is_user_logged_in()) {
        header("Location: ../public/login.php");
        die();
    }
}

function prevent_login_access() {
    if (is_user_logged_in()) {
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