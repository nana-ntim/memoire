<?php
declare(strict_types = 1);

function get_email(object $pdo, string $email) {
    $query = "SELECT email FROM Users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function set_user(
    object $pdo, 
    string $firstName,
    string $lastName,
    string $email,
    string $pwd
) {
    $query = "INSERT INTO Users (firstName, lastName, email, passwd) VALUES (:firstName, :lastName, :email, :pwd);";
    $stmt = $pdo->prepare($query);

    // Password hashing configuration
    $options = [
        'memory_cost' => 1 << 14,
        'time_cost'   => 4,
        'threads'     => 2
    ];

    // Hash the password
    $hashedPwd = password_hash($pwd, PASSWORD_BCRYPT, $options);
    
    // Bind parameters
    $stmt->bindParam(":firstName", $firstName);
    $stmt->bindParam(":lastName", $lastName);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":pwd", $hashedPwd);  // Use the hashed password
    
    $stmt->execute();
}