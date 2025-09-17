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

// Get stats for dashboard
try {
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $total_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
    $pending_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total bikes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bikes");
    $total_bikes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Recent bookings
    $stmt = $pdo->query("
        SELECT b.*, u.name as user_name, bk.model 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN bikes bk ON b.bike_id = bk.id 
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving dashboard data: " . $e->getMessage();
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
                        <a href="/ezbikez/admin/" class="nav-link active">
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
                        <a href="/ezbikez/admin/users.php" class="nav-link">
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
                <h1 class="h2">Dashboard</h1>
                <span class="text-muted"><?php echo date('F j, Y'); ?></span>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_bookings; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_bookings; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Bikes</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_bikes; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-motorcycle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Bike</th>
                                    <th>Dates</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Slip</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td><?php echo $booking['user_name']; ?></td>
                                        <td><?php echo $booking['model']; ?></td>
                                        <td>
                                            <?php echo date('M j', strtotime($booking['start_date'])); ?> - 
                                            <?php echo date('M j', strtotime($booking['end_date'])); ?>
                                        </td>
                                        <td><?php echo formatPrice($booking['total_price']); ?></td>
                                        <td><?php echo getBookingStatus($booking['status']); ?></td>
                                        <td>
                                            <?php if ($booking['slip']): ?>
                                                Uploaded
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>