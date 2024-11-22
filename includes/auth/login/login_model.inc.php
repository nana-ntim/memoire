<?php
declare(strict_types = 1);

function get_user(object $pdo, string $email) {
    $query = "SELECT * FROM Users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function update_login_timestamp(object $pdo, int $userId) {
    $query = "UPDATE Users SET last_login = CURRENT_TIMESTAMP WHERE user_id = :userId;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":userId", $userId);
    $stmt->execute();
}