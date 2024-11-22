<?php

session_start();

function is_user_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

function force_login() {
    if (!is_user_logged_in()) {
        header("Location: ../public/login.php");
        die();
    }
}

function prevent_login_access() {
    if (is_user_logged_in()) {
        header("Location: ../pages/journal.php");
        die();
    }
}