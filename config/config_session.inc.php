<?php

ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 1800,
    'domain' => 'localhost',
    'path' => '/',
    'secure' => true,
    'httponly' => true
]);

// This starts a session
session_start();

// Checks for the last time a session was
// regenerated for security
if (!isset($_SESSION['last_regeneration'])) {

    // This if statement checks if we have ever had
    // a session before. If this is our first time
    // creating it, it will generate an ID here.
    session_regenerate_id(true);

    // This creates a session variable that saves the
    // last time we generated a session ID
    $_SESSION['last_regeneration'] = time();

} else {

    // This sets the interval as 30 minutes (60 seconds * 30)
    $interval = 60 * 30;

    // Here, we check if the time for our session currently
    // is greater than the time for our interval
    if (time() - $_SESSION['last_regeneration'] >= $interval) {

        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}