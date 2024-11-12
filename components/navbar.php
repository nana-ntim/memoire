<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Get user's profile image
$query = "SELECT profile_image FROM Users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(":user_id", $_SESSION["user_id"]);
$stmt->execute();
$profile_image = $stmt->fetch(PDO::FETCH_ASSOC)['profile_image'];

// Set default image if none exists
$profile_image_path = !empty($profile_image) ? "../" . $profile_image : "../assets/default-avatar.jpg";
?>

<nav class="navbar">
    <div class="nav-container">
        <a href="../pages/journal.php" class="nav-logo">Memoire</a>
        
        <div class="nav-center">
            <a href="../pages/journal.php" class="nav-link <?php echo ($current_page === 'journal.php') ? 'active' : ''; ?>">Journal</a>
            <a href="../pages/reflect.php" class="nav-link <?php echo ($current_page === 'reflect.php') ? 'active' : ''; ?>">Reflect</a>
            <a href="../pages/gratitude.php" class="nav-link <?php echo ($current_page === 'gratitude.php') ? 'active' : ''; ?>">Gratitude</a>
        </div>

        <div class="nav-profile">
            <img src="<?php echo htmlspecialchars($profile_image_path); ?>" 
                 alt="Profile" 
                 class="profile-img" 
                 id="profileButton"
                 onerror="this.src='../assets/default-avatar.jpg'">
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-items">
                    <a href="../pages/settings.php" class="dropdown-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../includes/logout.inc.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <button class="mobile-toggle" aria-label="Menu" id="mobileMenuButton">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Mobile menu -->
<div class="mobile-menu" id="mobileMenu">
    <a href="../pages/journal.php" class="mobile-link <?php echo ($current_page === 'journal.php') ? 'active' : ''; ?>">Journal</a>
    <a href="../pages/reflect.php" class="mobile-link <?php echo ($current_page === 'reflect.php') ? 'active' : ''; ?>">Reflect</a>
    <a href="../pages/gratitude.php" class="mobile-link <?php echo ($current_page === 'gratitude.php') ? 'active' : ''; ?>">Gratitude</a>
    
    <div class="mobile-profile">
        <a href="../pages/settings.php" class="mobile-profile-link <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            Settings
        </a>
        <a href="../includes/logout.inc.php" class="mobile-profile-link">
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