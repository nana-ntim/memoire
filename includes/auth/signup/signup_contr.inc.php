<?php
declare(strict_types = 1);

function is_input_empty(string $firstName, string $lastName, string $pwd, string $email) {
    return empty($firstName) || empty($lastName) || empty($pwd) || empty($email);
}

function is_email_invalid(string $email) {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_email_registered(object $pdo, string $email) {
    return get_email($pdo, $email) !== false;
}

// Fixed the password comparison logic
function is_password_correct(string $pwd, string $confirmPwd) {
    return $pwd === $confirmPwd;
}

function create_user(object $pdo, string $firstName, string $lastName, string $email, string $pwd) {
    set_user($pdo, $firstName, $lastName, $email, $pwd);
}