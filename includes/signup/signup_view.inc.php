<?php

// Our controller file takes care of any processing
// of information

declare(strict_types = 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function check_signup_errors() {
    if(isset($_SESSION['errors_signup'])) {
        $errors = $_SESSION['errors_signup'];
        
        echo '<h3>Please fix the following errors:</h3>';
        echo '<div class="error-container">';
        echo '<ul class="error-list">';
        foreach ($errors as $error) {
            echo '<li class="error-item">' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
        echo '</div>';

        unset($_SESSION['errors_signup']);
    }
}