<?php
// File: components/navbar.php

require_once "../includes/auth/auth_functions.inc.php";
require_once "../config/dbh.inc.php";

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Get user's data
$user = get_user_data($pdo, $_SESSION["user_id"]);
$is_admin = is_admin_user($pdo, $_SESSION["user_id"]);

// Get profile image path
$profile_image_path = !empty($user['profile_image']) ? 
    "../" . $user['profile_image'] : 
    "../assets/bg.jpg";
?>

<nav class="navbar">
    <div class="nav-container">
        <a href="../pages/journal.php" class="nav-logo">Memoire</a>
        
        <div class="nav-center">
            <a href="../pages/journal.php" class="nav-link <?php echo ($current_page === 'journal.php') ? 'active' : ''; ?>">Journal</a>
            <a href="../pages/collections.php" class="nav-link <?php echo ($current_page === 'collections.php') ? 'active' : ''; ?>">Collections</a>
            <a href="../pages/reflect.php" class="nav-link <?php echo ($current_page === 'reflect.php') ? 'active' : ''; ?>">Reflect</a>
            <a href="../pages/gratitude.php" class="nav-link <?php echo ($current_page === 'gratitude.php') ? 'active' : ''; ?>">Gratitude</a>
        </div>

        <div class="nav-profile">
            <img src="<?php echo htmlspecialchars($profile_image_path); ?>" 
                 alt="Profile" 
                 class="profile-img" 
                 id="profileButton"
                 onerror="this.src='../assets/bg.jpg'">
            
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-items">
                    <?php if ($is_admin): ?>
                        <a href="../admin/dashboard.php" class="dropdown-item">
                            <i class="fas fa-user-shield"></i>
                            Admin Portal
                        </a>
                        <div class="dropdown-divider"></div>
                    <?php endif; ?>
                    
                    <a href="../pages/settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../includes/auth/logout.inc.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <button class="mobile-toggle" id="mobileMenuButton">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Mobile menu -->
<div class="mobile-menu" id="mobileMenu">
    <a href="../pages/journal.php" class="mobile-link <?php echo ($current_page === 'journal.php') ? 'active' : ''; ?>">Journal</a>
    <a href="../pages/collections.php" class="mobile-link <?php echo ($current_page === 'collections.php') ? 'active' : ''; ?>">Collections</a>
    <a href="../pages/reflect.php" class="mobile-link <?php echo ($current_page === 'reflect.php') ? 'active' : ''; ?>">Reflect</a>
    <a href="../pages/gratitude.php" class="mobile-link <?php echo ($current_page === 'gratitude.php') ? 'active' : ''; ?>">Gratitude</a>
    
    <div class="mobile-profile">
        <?php if ($is_admin): ?>
            <a href="../admin/dashboard.php" class="mobile-profile-link">
                <i class="fas fa-user-shield"></i>
                Admin Portal
            </a>
        <?php endif; ?>
        
        <a href="../pages/settings.php" class="mobile-profile-link">
            <i class="fas fa-cog"></i>
            Settings
        </a>
        <a href="../includes/auth/logout.inc.php" class="mobile-profile-link">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</div>