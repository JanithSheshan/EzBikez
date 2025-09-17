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

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header('Location: /ezbikez/public/my-bookings.php');
    exit();
}

$booking_id = $_GET['id'];

// Get booking details
try {
    $stmt = $pdo->prepare("
        SELECT b.*, bk.model, bk.category, bk.price_per_day, bk.description, u.name as user_name, u.email, u.phone, u.address
        FROM bookings b 
        JOIN bikes bk ON b.bike_id = bk.id 
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        header('Location: /ezbikez/public/my-bookings.php');
        exit();
    }
    
    // Get assigned bike if any
    $stmt = $pdo->prepare("
        SELECT bi.* 
        FROM booking_items bi 
        WHERE bi.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $assigned_bike = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error retrieving booking details: " . $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Booking Details #<?php echo $booking['id']; ?></h1>
                <span><?php echo getBookingStatus($booking['status']); ?></span>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Rental Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Rental Period</h5>
                            <p>
                                <strong>From:</strong> <?php echo date('F j, Y', strtotime($booking['start_date'])); ?><br>
                                <strong>To:</strong> <?php echo date('F j, Y', strtotime($booking['end_date'])); ?><br>
                                <strong>Duration:</strong> 
                                <?php 
                                $start = new DateTime($booking['start_date']);
                                $end = new DateTime($booking['end_date']);
                                $interval = $start->diff($end);
                                echo $interval->days . ' days';
                                ?>
                            </p>

                            <?php
                            // Check if slip is already uploaded
                            if (isset($booking['slip']) && !empty($booking['slip'])): ?>
                                <div class="alert alert-success">
                                    You have successfully uploaded your payment slip.
                                </div>
                            <?php else: ?>
                                <?php
                                // Handle slip upload
                                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['slip'])) {
                                    $upload_dir = __DIR__ . '/../uploads/slip/';
                                    if (!is_dir($upload_dir)) {
                                        mkdir($upload_dir, 0777, true);
                                    }
                                    $file = $_FILES['slip'];
                                    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                                    if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed_types)) {
                                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                                        $filename = 'slip_' . $booking_id . '_' . time() . '.' . $ext;
                                        $target = $upload_dir . $filename;
                                        if (move_uploaded_file($file['tmp_name'], $target)) {
                                            // Update bookings table
                                            $stmt = $pdo->prepare("UPDATE bookings SET slip = ? WHERE id = ?");
                                            $stmt->execute([$filename, $booking_id]);

                                            // Send email to admin
                                            $admin_email = 'janithaththanayaka06@gmail.com'; // Change to your admin email
                                            $subject = "Payment Slip Uploaded for Booking #{$booking_id}";
                                            $message = "<html><body>";
                                            $message .= "<h2>Payment Slip Uploaded for Booking #{$booking_id}</h2>";
                                            $message .= "<p>A payment slip has been uploaded for Booking #<strong>{$booking_id}</strong> by user <strong>{$booking['user_name']}</strong> ({$booking['email']}).</p><br>";
                                            $message .= "<p>You can review the slip in the admin panel.</p>";
                                            $message .= "</body></html>";
                                            $headers = "MIME-Version: 1.0\r\n";
                                            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                                            $headers .= "From: EzBikez <no-reply@{$_SERVER['HTTP_HOST']}>" . "\r\n";
                                            @mail($admin_email, $subject, $message, $headers);

                                            // Refresh to show success message
                                            header("Location: booking-details.php?id=" . urlencode($booking_id));
                                            exit();
                                        } else {
                                            echo '<div class="alert alert-danger">Failed to upload slip. Please try again.</div>';
                                        }
                                    } else {
                                        echo '<div class="alert alert-danger">Invalid file type. Only JPG, PNG, and PDF are allowed.</div>';
                                    }
                                }
                                ?>
                                <div class="alert alert-warning">
                                    <strong>Note:</strong> If you haven't received an email from Ezbike regarding the advanced payment, please do not upload anything here.
                                </div>
                                <form method="post" enctype="multipart/form-data" class="mb-3">
                                    <label for="slip" class="form-label">Upload Payment Slip (JPG, PNG, PDF):</label>
                                    <input type="file" name="slip" id="slip" class="form-control mb-2" required>
                                    <button type="submit" class="btn btn-primary">Upload Slip</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5>Bike Information</h5>
                            <p>
                                <strong>Model:</strong> <?php echo $booking['model']; ?><br>
                                <strong>Category:</strong> <?php echo getCategoryName($booking['category']); ?><br>
                                <strong>Description:</strong> <?php echo $booking['description']; ?><br>
                                <strong>Price per day:</strong> <?php echo formatPrice($booking['price_per_day']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Pricing Details</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <td>Daily Rate (<?php echo $booking['price_per_day']; ?>/day) × 
                                <?php 
                                $start = new DateTime($booking['start_date']);
                                $end = new DateTime($booking['end_date']);
                                $interval = $start->diff($end);
                                echo $interval->days ;
                                ?> days
                            </td>
                            <td class="text-end"><?php echo formatPrice($booking['total_price']); ?></td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <th class="text-end"><?php echo formatPrice($booking['total_price']); ?></th>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php if ($assigned_bike): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Assigned Bike</h4>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Bike ID:</strong> <?php echo $assigned_bike['bike_item_id']; ?><br>
                            <strong>Assigned at:</strong> <?php echo date('F j, Y H:i', strtotime($assigned_bike['assigned_at'])); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Your Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Name:</strong> <?php echo $booking['user_name']; ?><br>
                                <strong>Email:</strong> <?php echo $booking['email']; ?><br>
                                <strong>Phone:</strong> <?php echo $booking['phone'] ? $booking['phone'] : 'Not provided'; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Address:</strong> <?php echo $booking['address'] ? nl2br($booking['address']) : 'Not provided'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="/ezbikez/public/my-bookings.php" class="btn btn-outline-primary">Back to My Bookings</a>
                
                <?php if ($booking['status'] === 'pending'): ?>
                    <button type="button" class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        Cancel Booking
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking?</p>
                <p class="text-danger">Cancellation fees may apply depending on how close your booking is to the start date.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form method="POST" action="/ezbikez/public/cancel-booking.php">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>