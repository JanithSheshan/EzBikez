<?php
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/db.php';
require_once '../includes/helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: /ezbikez/public/login.php');
    exit();
}

// Get user's bookings
try {
    $stmt = $pdo->prepare("
        SELECT b.*, bk.model, bk.category, bk.price_per_day 
        FROM bookings b 
        JOIN bikes bk ON b.bike_id = bk.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving bookings: " . $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="mb-4">My Bookings</h1>
            
            <?php if (isset($_GET['booking_success'])): ?>
                <div class="alert alert-success">
                    Your booking has been submitted successfully! We will review it and confirm shortly.
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info">
                    You don't have any bookings yet. <a href="/ezbikez/public/availability.php">Rent a bike now</a>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Bike Model</th>
                                <th>Category</th>
                                <th>Rental Period</th>
                                <th>Duration</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['model']; ?></td>
                                    <td><?php echo getCategoryName($booking['category']); ?></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> - 
                                        <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $start = new DateTime($booking['start_date']);
                                        $end = new DateTime($booking['end_date']);
                                        $interval = $start->diff($end);
                                        echo $interval->days . ' days';
                                        ?>
                                    </td>
                                    <td><?php echo formatPrice($booking['total_price']); ?></td>
                                    <td><?php echo getBookingStatus($booking['status']); ?></td>
                                    <td>
                                        <a href="/ezbikez/public/booking-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>