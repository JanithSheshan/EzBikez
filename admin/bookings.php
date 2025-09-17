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

// Get all bookings
try {
    $stmt = $pdo->query("
        SELECT b.*, u.name as user_name, u.email, bk.model, bk.category 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN bikes bk ON b.bike_id = bk.id 
        ORDER BY b.created_at DESC
    ");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving bookings: " . $e->getMessage();
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
                        <a href="/ezbikez/admin/bookings.php" class="nav-link active">
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
            <h1 class="h2">Manage Bookings</h1>
            </div>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Booking updated successfully.</div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="mb-3">
            <input type="text" id="bookingSearch" class="form-control" placeholder="Search bookings...">
            </div>
            
            <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-bordered" id="bookingsTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Bike</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Slip</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td>
                            <?php echo $booking['user_name']; ?><br>
                            <small class="text-muted"><?php echo $booking['email']; ?></small>
                        </td>
                        <td>
                            <?php echo $booking['model']; ?><br>
                            <small class="text-muted"><?php echo getCategoryName($booking['category']); ?></small>
                        </td>
                        <td>
                            <?php echo date('M j', strtotime($booking['start_date'])); ?> - 
                            <?php echo date('M j', strtotime($booking['end_date'])); ?>
                        </td>
                        <td><?php echo formatPrice($booking['total_price']); ?></td>
                        <td><?php echo getBookingStatus($booking['status']); ?></td>
                        <td>
                            <a href="/ezbikez/admin/booking-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">View</a>
                        </td>
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
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('bookingSearch');
            const table = document.getElementById('bookingsTable');
            searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
            });
        });
        </script>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>