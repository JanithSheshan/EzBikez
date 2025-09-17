<?php
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/db.php';
require_once '../includes/helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: /ezbikez/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if bike_id and dates are provided
if (!isset($_POST['bike_id']) || !isset($_POST['start_date']) || !isset($_POST['end_date'])) {
    header('Location: /ezbikez/public/availability.php');
    exit();
}

$bike_id = $_POST['bike_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$error = '';

// Validate dates
if ($start_date > $end_date) {
    $error = "Invalid date range.";
}

// Get bike details
try {
    $stmt = $pdo->prepare("SELECT * FROM bikes WHERE id = ?");
    $stmt->execute([$bike_id]);
    $bike = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bike) {
        $error = "Bike not found.";
    }
} catch(PDOException $e) {
    $error = "Error retrieving bike details: " . $e->getMessage();
}

// Check availability again
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(bi.id) as available_count
        FROM bike_items bi
        WHERE bi.bike_id = ? 
        AND bi.is_available = 1
        AND bi.id NOT IN (
            SELECT bike_item_id 
            FROM booking_items 
            WHERE booking_id IN (
                SELECT id 
                FROM bookings 
                WHERE (
                    (start_date BETWEEN ? AND ?) 
                    OR (end_date BETWEEN ? AND ?) 
                    OR (? BETWEEN start_date AND end_date) 
                    OR (? BETWEEN start_date AND end_date)
                ) 
                AND status IN ('approved', 'confirmed')
            )
        )
    ");
    $stmt->execute([$bike_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
    $availability = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($availability['available_count'] < 1) {
        $error = "Sorry, this bike is no longer available for the selected dates.";
    }
} catch(PDOException $e) {
    $error = "Error checking availability: " . $e->getMessage();
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking']) && empty($error)) {
    try {
        $pdo->beginTransaction();
        
        // Create booking
        $total_price = calculateTotal($bike['price_per_day'], $start_date, $end_date);
        
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, bike_id, start_date, end_date, total_price, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");

        $user_id = $_SESSION['user_id'];

        // Get user email and name
        $userStmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['email'])) {
            $to = $user['email'];
            $name = $user['name'];
            $subject = "EzBikez Booking Received";
            $message = "
            <html>
            <head>
            <title>EzBikez Booking Received</title>
            </head>
            <body>
            <h2>EzBikez Booking Received!</h2>
            <p>Dear " . htmlspecialchars($name) . ",</p>
            <p>We have successfully received your booking. One of our team members will contact you ASAP to confirm the details.</p>
            <p>Thank you for choosing <strong>EzBikez</strong>!</p>
            <br>
            <p>Best regards,<br>EzBikez Team</p>
            </body>
            </html>
            ";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: EzBikez <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
            mail($to, $subject, $message, $headers);

            // Send email to admin
            $admin_email = "janithaththanayaka06@gmail.com"; // Change to your admin email
            $admin_subject = "New Booking Received";
            $admin_message = "
            <html>
            <head>
            <title>New Booking Received</title>
            </head>
            <body>
            <h2>New Booking Received</h2>
            <p>A new booking has been made on EzBikez:</p>
            <ul>
                <li><strong>User:</strong> " . htmlspecialchars($name) . " (" . htmlspecialchars($user['email']) . ")</li>
                <li><strong>Bike:</strong> " . htmlspecialchars($bike['model']) . " (ID: " . $bike_id . ")</li>
                <li><strong>Rental Period:</strong> " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date) . "</li>
                <li><strong>Total Price:</strong> " . formatPrice($total_price) . "</li>
            </ul>
            <p>Check the admin panel for more details.</p>
            <p>If you need to manage this booking, please <a href='https://test.janithaththanayaka.com/ezbikez/public/login.php'>login</a> to the admin panel.</p>
            </body>
            </html>
            ";
            $admin_headers = "MIME-Version: 1.0\r\n";
            $admin_headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $admin_headers .= "From: EzBikez <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
            mail($admin_email, $admin_subject, $admin_message, $admin_headers);
        }

        $stmt->execute([$_SESSION['user_id'], $bike_id, $start_date, $end_date, $total_price]);
        $booking_id = $pdo->lastInsertId();
        
        $pdo->commit();
        
        header('Location: /ezbikez/public/my-bookings.php?booking_success=1');
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Booking error: " . $e->getMessage();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="text-center mb-4">Confirm Your Booking</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <div class="text-center mt-4">
                    <a href="/ezbikez/public/availability.php" class="btn btn-primary">Back to Availability</a>
                </div>
            <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Booking Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Rental Period</h5>
                                <p>
                                    <strong>From:</strong> <?php echo date('F j, Y', strtotime($start_date)); ?><br>
                                    <strong>To:</strong> <?php echo date('F j, Y', strtotime($end_date)); ?><br>
                                    <strong>Duration:</strong> <span id="total_days">
                                        <?php 
                                        $start = new DateTime($start_date);
                                        $end = new DateTime($end_date);
                                        $interval = $start->diff($end);
                                        echo $interval->days. ' days';
                                        ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>Bike Information</h5>
                                <p>
                                    <strong>Model:</strong> <?php echo $bike['model']; ?><br>
                                    <strong>Category:</strong> <?php echo getCategoryName($bike['category']); ?><br>
                                    <strong>Price per day:</strong> <?php echo formatPrice($bike['price_per_day']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Pricing Summary</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td>Daily Rate × <span id="days_count">
                                    <?php 
                                    $start = new DateTime($start_date);
                                    $end = new DateTime($end_date);
                                    $interval = $start->diff($end);
                                    echo $interval->days ;
                                    ?>
                                </span> days</td>
                                <td class="text-end"><?php echo formatPrice($bike['price_per_day']); ?></td>
                            </tr>
                            <tr>
                                <th>Total Amount</th>
                                <th class="text-end" id="total_price">
                                    <?php 
                                    $total = calculateTotal($bike['price_per_day'], $start_date, $end_date);
                                    echo formatPrice($total);
                                    ?>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if ($availability['available_count'] < 3): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i> Limited Availability Notice</h5>
                        <p class="mb-0">
                            There are only <?php echo $availability['available_count']; ?> bikes available in this category for your selected dates. 
                            By confirming this booking, you agree that we may provide an alternative bike of similar quality if your preferred model is not available at pickup.
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Confirm Booking</h4>
                    </div>
                    <div class="card-body">
                        <p>By confirming this booking, you agree to our terms and conditions.</p>
                        <form method="POST" action="">
                            <input type="hidden" name="bike_id" value="<?php echo $bike_id; ?>">
                            <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                            <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                            <input type="hidden" name="confirm_booking" value="1">
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>