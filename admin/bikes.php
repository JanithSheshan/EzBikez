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

// Get all bikes
try {
    $stmt = $pdo->query("
        SELECT b.*, 
               (SELECT COUNT(*) FROM bike_items WHERE bike_id = b.id AND is_available = 1) as available_count,
               (SELECT COUNT(*) FROM bike_items WHERE bike_id = b.id) as total_count
        FROM bikes b 
        ORDER BY b.category, b.model
    ");
    $bikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving bikes: " . $e->getMessage();
}

// Handle bike deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_bike'])) {
    $bike_id = $_POST['bike_id'];
    
    try {
        // Check if bike has active bookings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE bike_id = ? 
            AND status IN ('pending', 'approved', 'confirmed')
        ");
        $stmt->execute([$bike_id]);
        $active_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($active_bookings > 0) {
            $error = "Cannot delete bike with active bookings.";
        } else {
            // Delete bike items first
            $stmt = $pdo->prepare("DELETE FROM bike_items WHERE bike_id = ?");
            $stmt->execute([$bike_id]);
            
            // Delete bike
            $stmt = $pdo->prepare("DELETE FROM bikes WHERE id = ?");
            $stmt->execute([$bike_id]);
            
            header('Location: /ezbikez/admin/bikes.php?success=1');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting bike: " . $e->getMessage();
    }
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
                        <a href="/ezbikez/admin/bikes.php" class="nav-link active">
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
                <h1 class="h2">Manage Bikes</h1>
                <a href="/ezbikez/admin/bike-add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Bike
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Bike deleted successfully.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['created'])): ?>
                <div class="alert alert-success">Bike created successfully.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">Bike updated successfully.</div>
            <?php endif; ?>
            
            <div class="row">
                <?php foreach ($bikes as $bike): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($bike['image'])): ?>
                                <img src="/ezbikez/public/uploads/bikes/<?php echo $bike['image']; ?>" class="card-img-top bike-card-img" alt="<?php echo $bike['model']; ?>">
                            <?php else: ?>
                                <img src="/ezbikez/public/assets/img/bike-placeholder.jpg" class="card-img-top bike-card-img" alt="Bike placeholder">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo $bike['model']; ?></h5>
                                    <span class="badge bg-primary">Category <?php echo $bike['category']; ?></span>
                                </div>
                                <p class="card-text"><?php echo $bike['description']; ?></p>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-motorcycle me-1"></i> 
                                        <?php echo $bike['available_count']; ?> available of <?php echo $bike['total_count']; ?> total
                                    </small>
                                </div>
                                <ul class="list-group list-group-flush mb-3">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Price per day:</span>
                                        <span class="fw-bold"><?php echo formatPrice($bike['price_per_day']); ?></span>
                                    </li>
                                </ul>
                                <div class="d-grid gap-2">
                                    <a href="/ezbikez/admin/bike-edit.php?id=<?php echo $bike['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this bike?');">
                                        <input type="hidden" name="bike_id" value="<?php echo $bike['id']; ?>">
                                        <button type="submit" name="delete_bike" class="btn btn-outline-danger w-100">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($bikes)): ?>
                <div class="alert alert-info">
                    No bikes found. <a href="/ezbikez/admin/bike-add.php">Add your first bike</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>