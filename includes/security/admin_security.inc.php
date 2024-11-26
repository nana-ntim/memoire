<?php
// File: includes/security/admin_security.inc.php

function require_admin_priv() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../public/login.php");
        die();
    }

    try {
        // Get database connection
        require_once __DIR__ . "/../../config/dbh.inc.php";
        
        // Verify database connection
        if (!isset($pdo)) {
            throw new Exception("Database connection failed");
        }
        
        // Verify admin status
        $query = "SELECT is_admin FROM Users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $_SESSION["user_id"]]);
        $is_admin = $stmt->fetchColumn();
        
        if (!$is_admin) {
            header("Location: ../pages/journal.php");
            die();
        }

        return $pdo;

    } catch (Exception $e) {
        error_log("Admin security check error: " . $e->getMessage());
        header("Location: ../pages/journal.php");
        die();
    }
}

function get_admin_data($pdo, $user_id) {
    try {
        $query = "SELECT * FROM Users WHERE user_id = :user_id AND is_admin = TRUE";
        $stmt = $pdo->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting admin data: " . $e->getMessage());
        return false;
    }
}