<?php
declare(strict_types=1);

function validate_entry_input(string $title, string $content): array {
    $errors = [];

    if (empty($title)) {
        $errors[] = "Title is required";
    }

    if (empty($content)) {
        $errors[] = "Content is required";
    }

    return $errors;
}