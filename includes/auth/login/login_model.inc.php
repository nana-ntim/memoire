<?php
// File: includes/auth/login/login_model.inc.php

declare(strict_types = 1);

function get_user(object $pdo, string $email) {
    $query = "SELECT * FROM Users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function update_login_timestamp(object $pdo, int $userId) {
    $query = "UPDATE Users SET last_login = CURRENT_TIMESTAMP WHERE user_id = :userId;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":userId", $userId);
    $stmt->execute();
}

function is_user_admin(object $pdo, int $userId) {
    $query = "SELECT is_admin FROM Users WHERE user_id = :userId;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":userId", $userId);
    $stmt->execute();
    
    return (bool)$stmt->fetchColumn();
}