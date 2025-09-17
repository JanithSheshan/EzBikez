<?php
$current_page = $_SERVER['REQUEST_URI'];
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #003132;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/ezbikez">
            <img src="/ezbikez/includes/img/EzBikezlogo.png" alt="EzBikez Logo" height="40" class="me-2">
            <span class="fw-bold">Ez<span style="color: #4a8a26">Bikez</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == '/ezbikez/' || $current_page == '/ezbikez') ? 'active' : ''; ?>" href="/ezbikez">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($current_page, '/ezbikez/public/about.php') !== false) ? 'active' : ''; ?>" href="/ezbikez/public/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($current_page, '/ezbikez/public/availability.php') !== false) ? 'active' : ''; ?>" href="/ezbikez/public/availability.php">Rent a Bike</a>
                </li>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['is_admin'] == 0): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($current_page, '/ezbikez/public/my-bookings.php') !== false) ? 'active' : ''; ?>" href="/ezbikez/public/my-bookings.php">My Bookings</a>
                </li>
                <?php endif; ?>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($current_page, '/ezbikez/admin/') !== false) ? 'active' : ''; ?>" href="/ezbikez/admin/">Admin Panel</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (strpos($current_page, '/ezbikez/public/my-bookings.php') !== false) ? 'active' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i> <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/ezbikez/public/my-bookings.php">My Bookings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/ezbikez/public/logout.php">Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-success ms-lg-2 w-100 w-lg-auto <?php echo (strpos($current_page, '/ezbikez/public/login.php') !== false || strpos($current_page, '/ezbikez/public/register.php') !== false) ? 'active' : ''; ?>" 
                       href="/ezbikez/public/login.php" 
                       style="color: #fff; background-color: #4a8a26; border: none; border-radius: 50px;">
                        Login / Register
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>