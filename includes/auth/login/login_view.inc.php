<?php
declare(strict_types = 1);

function check_login_errors() {
    if(isset($_SESSION['errors_login'])) {
        $errors = $_SESSION['errors_login'];
        
        echo '<div class="error-container">';
        echo '<h3>Please fix the following errors:</h3>';
        echo '<ul class="error-list">';
        foreach ($errors as $error) {
            echo '<li class="error-item">' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
        echo '</div>';

        unset($_SESSION['errors_login']);
    }
}

function check_signup_success() {
    if(isset($_SESSION['signup_success'])) {
        echo '<div class="success-container">';
        echo '<p class="success-message">' . htmlspecialchars($_SESSION['signup_success']) . '</p>';
        echo '</div>';
        
        unset($_SESSION['signup_success']);
    }
}