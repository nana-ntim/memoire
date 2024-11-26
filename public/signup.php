<?php
require_once "../includes/security/session_protection.inc.php";
require_once "../config/config_session.inc.php";
require_once "../includes/auth/signup/signup_view.inc.php";
prevent_login_access();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Dark Theme</title>
    <link rel="stylesheet" href="../styles/access.css">
</head>
<body>
    <main class="signup-container">
        <section class="form-section">
            <div class="logo">
                Memoire
            </div>

            <div class="form-content">
                <h1>Welcome!</h1>
                <p class="subtitle">Create your account to get started</p>

                <?php
                // Display any errors
                check_signup_errors();
                
                // Get any saved form data
                $firstName = isset($_SESSION["signup_data"]["firstName"]) ? $_SESSION["signup_data"]["firstName"] : "";
                $lastName = isset($_SESSION["signup_data"]["lastName"]) ? $_SESSION["signup_data"]["lastName"] : "";
                $email = isset($_SESSION["signup_data"]["email"]) ? $_SESSION["signup_data"]["email"] : "";
                ?>

                <form class="signup-form" method="POST" action="../includes/auth/signup/signup.inc.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" 
                                   placeholder="Enter your first name" 
                                   value="<?php echo htmlspecialchars($firstName); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" 
                                   placeholder="Enter your last name"
                                   value="<?php echo htmlspecialchars($lastName); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" 
                               placeholder="Create a password">
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" 
                               placeholder="Confirm your password">
                    </div>

                    <button type="submit" class="submit-btn">Sign Up</button>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="../public/login.php">Log in</a></p>
                </div>
            </div>
        </section>
        
        <section class="image-section">
            <div class="overlay"></div>
            <img src="../assets/bg.jpg" alt="Decorative image">
        </section>
    </main>

    <?php
    // Clear the session data after displaying
    unset($_SESSION["signup_data"]);
    ?>
</body>
</html>