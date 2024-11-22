<?php
declare(strict_types = 1);

function is_input_empty(string $email, string $pwd) {
    return empty($email) || empty($pwd);
}

function is_email_wrong(bool|array $result) {
    return !$result;
}

function is_password_wrong(string $pwd, string $hashedPwd) {
    return !password_verify($pwd, $hashedPwd);
}