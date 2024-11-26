<?php
// File: components/admin/navbar.php

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Get user's profile image
$query = "SELECT profile_image FROM Users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(":user_id", $_SESSION["user_id"]);
$stmt->execute();
$profile_image = $stmt->fetch(PDO::FETCH_ASSOC)['profile_image'];

// Set default image if none exists
$profile_image_path = !empty($profile_image) ? "../" . $profile_image : "../assets/bg.jpg";
?>

<nav class="navbar">
    <div class="nav-container">
        <a href="../pages/journal.php" class="nav-logo">Memoire Admin</a>
        
        <div class="nav-center">
            <a href="dashboard.php" class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
            <a href="reports.php" class="nav-link <?php echo ($current_page === 'reports.php') ? 'active' : ''; ?>">
                <i class="fas fa-flag"></i>
                Reports
            </a>
        </div>

        <div class="nav-profile">
            <img src="<?php echo htmlspecialchars($profile_image_path); ?>" 
                 alt="Profile" 
                 class="profile-img" 
                 id="profileButton">
            
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-items">
                    <a href="../pages/journal.php" class="dropdown-item">
                        <i class="fas fa-arrow-left"></i>
                        Exit Admin
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
    <a href="dashboard.php" class="mobile-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i>
        Dashboard
    </a>
    <a href="reports.php" class="mobile-link <?php echo ($current_page === 'reports.php') ? 'active' : ''; ?>">
        <i class="fas fa-flag"></i>
        Reports
    </a>
    
    <div class="mobile-profile">
        <a href="../pages/journal.php" class="mobile-profile-link">
            <i class="fas fa-arrow-left"></i>
            Exit Admin
        </a>
        <a href="../includes/auth/logout.inc.php" class="mobile-profile-link">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</div>

<script>
    // Profile dropdown functionality
    const profileButton = document.getElementById('profileButton');
    const profileDropdown = document.getElementById('profileDropdown');
    
    profileButton.addEventListener('click', () => {
        profileDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!profileButton.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
        }
    });

    // Mobile menu functionality
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileMenuButton.addEventListener('click', () => {
        mobileMenuButton.classList.toggle('active');
        mobileMenu.classList.toggle('show');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
            mobileMenuButton.classList.remove('active');
            mobileMenu.classList.remove('show');
        }
    });

    // Close mobile menu when window is resized above mobile breakpoint
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            mobileMenuButton.classList.remove('active');
            mobileMenu.classList.remove('show');
        }
    });
</script>