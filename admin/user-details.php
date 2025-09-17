<?php
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/db.php';
require_once '../includes/helpers.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: /ezbikez/public/login.php');
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    header('Location: /ezbikez/admin/users.php');
    exit();
}

$user_id = $_GET['id'];

// Get user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_admin = 0");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: /ezbikez/admin/users.php');
        exit();
    }
    
    // Get user's bookings
    $stmt = $pdo->prepare("
        SELECT b.*, bk.model, bk.category 
        FROM bookings b 
        JOIN bikes bk ON b.bike_id = bk.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving user details: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 admin-sidebar p-0">
            <div class="d-flex flex-column p-3">
                <h5 class="text-center mb-4">Admin Panel</h5>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="/ezbikez/admin/" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/ezbikez/admin/bookings.php" class="nav-link">
                            <i class="fas fa-calendar-check me-2"></i> Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/ezbikez/admin/bikes.php" class="nav-link">
                            <i class="fas fa-motorcycle me-2"></i> Bikes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/ezbikez/admin/users.php" class="nav-link active">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a href="/ezbikez" class="nav-link">
                            <i class="fas fa-home me-2"></i> Back to Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/ezbikez/public/logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">User Details</h1>
                <a href="/ezbikez/admin/users.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Users
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <!-- User Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>ID:</strong> <?php echo $user['id']; ?></p>
                            <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $user['phone'] ? $user['phone'] : 'Not provided'; ?></p>
                            <p><strong>Address:</strong><br><?php echo $user['address'] ? nl2br($user['address']) : 'Not provided'; ?></p>
                            <p><strong>Registered:</strong> <?php echo date('F j, Y H:i', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <!-- User's Bookings -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Booking History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($bookings)): ?>
                                <p class="text-muted">This user has no bookings yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Bike</th>
                                                <th>Dates</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($bookings as $booking): ?>
                                                <tr>
                                                    <td>#<?php echo $booking['id']; ?></td>
                                                    <td><?php echo $booking['model']; ?></td>
                                                    <td>
                                                        <?php echo date('M j', strtotime($booking['start_date'])); ?> - 
                                                        <?php echo date('M j', strtotime($booking['end_date'])); ?>
                                                    </td>
                                                    <td><?php echo getBookingStatus($booking['status']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>