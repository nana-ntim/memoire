<?php
require_once "../includes/security/session_protection.inc.php";
require_once "../config/config_session.inc.php";
require_once "../includes/auth/login/login_view.inc.php";
prevent_login_access();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dark Theme</title>
    <link rel="stylesheet" href="../styles/access.css">
</head>
<body>
    <main class="signup-container">
        <section class="form-section">
            <div class="logo">
                Memoire
            </div>

            <div class="form-content">
                <h1>Welcome back!</h1>
                <p class="subtitle">Log in to your account to continue</p>

                <?php 
                    check_signup_success();
                    check_login_errors();
                 ?>

                <form class="signup-form" method="POST" action="../includes/auth/login/login.inc.php">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password">
                    </div>

                    <button type="submit" class="submit-btn">Log In</button>
                </form>

                <div class="login-link">
                    <p>Don't have an account? <a href="../public/signup.php">Sign up</a></p>
                </div>
            </div>
        </section>
        
        <section class="image-section">
            <div class="overlay"></div>
            <img src="../assets/bg.jpg" alt="Decorative image">
        </section>
    </main>
</body>
</html>