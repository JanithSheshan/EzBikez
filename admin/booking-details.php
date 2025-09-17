<?php
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/db.php';
require_once '../includes/helpers.php';

// Check if user is admin
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    header('Location: /ezbikez/public/login.php');
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: /ezbikez/admin/bookings.php');
    exit();
}

$booking_id = (int)$_GET['id'];
$error = '';
$success = '';

// Get booking details
try {
    $stmt = $pdo->prepare("
        SELECT b.*, u.name as user_name, u.email, u.phone, u.address, bk.* 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN bikes bk ON b.bike_id = bk.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";
        header('Location: /ezbikez/admin/bookings.php');
        exit();
    }
    
    // Get available bike items for this category - FIXED QUERY
    $stmt = $pdo->prepare("
        SELECT bi.* 
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
    $stmt->execute([
        $booking['bike_id'], 
        $booking['start_date'], $booking['end_date'],
        $booking['start_date'], $booking['end_date'],
        $booking['start_date'], $booking['end_date']
    ]);
    $available_bike_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get assigned bike item if any
    $stmt = $pdo->prepare("
        SELECT bi.*, bki.bike_item_id
        FROM booking_items bki
        JOIN bike_items bi ON bki.bike_item_id = bi.id
        WHERE bki.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $assigned_bike = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error retrieving booking details. Please try again.";
}


// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);

        // If status is confirmed and a bike is assigned, mark it as unavailable
        if ($new_status === 'confirmed' && isset($_POST['bike_item_id']) && !empty($_POST['bike_item_id'])) {
            $bike_item_id = (int)$_POST['bike_item_id'];

            // Validate bike item exists and is available
            $stmt = $pdo->prepare("SELECT id FROM bike_items WHERE id = ? AND is_available = 1");
            $stmt->execute([$bike_item_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Selected bike is not available.");
            }

            // Check if already assigned to this booking
            $stmt = $pdo->prepare("SELECT * FROM booking_items WHERE booking_id = ?");
            $stmt->execute([$booking_id]);

            if ($stmt->rowCount() > 0) {
                // Update existing assignment
                $stmt = $pdo->prepare("UPDATE booking_items SET bike_item_id = ? WHERE booking_id = ?");
                $stmt->execute([$bike_item_id, $booking_id]);
            } else {
                // Create new assignment - FIXED: Use proper INSERT statement
                $stmt = $pdo->prepare("INSERT INTO booking_items (booking_id, bike_item_id, assigned_at) VALUES (?, ?, NOW())");
                $stmt->execute([$booking_id, $bike_item_id]);
            }

            // Mark bike as unavailable
            $stmt = $pdo->prepare("UPDATE bike_items SET is_available = 0 WHERE id = ?");
            $stmt->execute([$bike_item_id]);

            $success = "Bike #" . $bike_item_id . " assigned and status updated successfully.";
        }

        // If status changed from confirmed to something else, free up the bike
        if ($booking['status'] === 'confirmed' && $new_status !== 'confirmed' && $assigned_bike) {
            $stmt = $pdo->prepare("DELETE FROM booking_items WHERE booking_id = ?");
            $stmt->execute([$booking_id]);

            $stmt = $pdo->prepare("UPDATE bike_items SET is_available = 1 WHERE id = ?");
            $stmt->execute([$assigned_bike['bike_item_id']]);

            $success = "Status updated and Bike #" . $assigned_bike['bike_item_id'] . " released.";
        }

        // For other status changes
        if (empty($success)) {
            $success = "Status updated successfully.";
        }

        // --- EMAIL LOGIC FOR STATUS CHANGES ---
        // Send email after status update
        $to = $booking['email'];
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: EzBikez <no-reply@{$_SERVER['HTTP_HOST']}>\r\n";
        $user_name = htmlspecialchars($booking['user_name']);

        if ($new_status === 'approved') {
            $subject = "EzBikez Booking Approved - Pickup Details";
            $message = "
                <h2>Booking Approved!</h2>
                <p>Dear {$user_name},</p>
                <p>Your booking process is successfully completed. You can pick up your bike at the following location:</p>
                <p>
                    <strong>EzBikez Pickup Location:</strong><br>
                    123 Main Street, City Center<br>
                    Phone: 011-1234567
                </p>
                <p>Thank you for choosing EzBikez!</p>
            ";
            mail($to, $subject, $message, $headers);
        } elseif ($new_status === 'rejected') {
            $subject = "EzBikez Booking Update - Apology";
            $message = "
                <h2>Booking Rejected</h2>
                <p>Dear {$user_name},</p>
                <p>We apologize, but we are unable to process your booking at this time. We will return your payment as soon as possible.</p>
                <p>Thank you for your understanding.<br>EzBikez Team</p>
            ";
            mail($to, $subject, $message, $headers);
        } elseif ($new_status === 'completed') {
            $subject = "Thank You for Renting with EzBikez!";
            $message = "
                <h2>Thank You!</h2>
                <p>Dear {$user_name},</p>
                <p>Thank you for choosing EzBikez and renting a bike with us. We hope you had a great experience and look forward to seeing you again soon!</p>
                <p>Best regards,<br>EzBikez Team</p>
            ";
            mail($to, $subject, $message, $headers);
        }

        $pdo->commit();

        // Refresh the page to show updated data
        header('Location: /ezbikez/admin/booking-details.php?id=' . $booking_id . '&success=1');
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Database error: " . $e->getMessage());
        $error = "Error updating booking: " . $e->getMessage();
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}


// Handle bike return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_returned'])) {
    try {
        $pdo->beginTransaction();
        
        // Update booking status to completed
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        // Free up the bike if one was assigned
        if ($assigned_bike) {
            $stmt = $pdo->prepare("UPDATE bike_items SET is_available = 1 WHERE id = ?");
            $update_result = $stmt->execute([$assigned_bike['bike_item_id']]);
            
            if ($update_result && $stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("DELETE FROM booking_items WHERE booking_id = ?");
                $stmt->execute([$booking_id]);
                $success = "Bike #" . $assigned_bike['bike_item_id'] . " marked as returned and available for rental.";
            } else {
                throw new Exception("Failed to update bike availability.");
            }
        } else {
            $success = "Booking marked as completed (no bike was assigned).";
        }
        
        // Send email after marking as returned
        $to = $booking['email'];
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: EzBikez <no-reply@{$_SERVER['HTTP_HOST']}>\r\n";
        $user_name = htmlspecialchars($booking['user_name']);
        $subject = "Thank You for Renting with EzBikez!";
        $message = "
            <h2>Thank You!</h2>
            <p>Dear {$user_name},</p>
            <p>Thank you for choosing EzBikez and renting a bike with us. We hope you had a great experience and look forward to seeing you again soon!</p>
            <p>Best regards,<br>EzBikez Team</p>
        ";
        mail($to, $subject, $message, $headers);
        
        $pdo->commit();
        
        // Refresh the page to show updated data
        header('Location: /ezbikez/admin/booking-details.php?id=' . $booking_id . '&success=1');
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Database error: " . $e->getMessage());
        $error = "Error marking bike as returned: " . $e->getMessage();
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Handle advance payment email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $to = $booking['email'];
    $subject = "EzBikez Advance Payment Instructions for Booking #" . $booking_id;
    $payment_link = "https://test.janithaththanayaka.com/ezbikez/public/booking-details.php?id=" . $booking_id;
    $login_link = "https://test.janithaththanayaka.com/ezbikez/public/login.php";
    $message = '
        <h2>EzBikez Advance Payment Instructions</h2>
        <p>Dear ' . htmlspecialchars($booking['user_name']) . ',</p>
        <p>We are pleased to inform you that we can provide your selected bike for the requested dates. To confirm your booking, please complete the advance payment process as soon as possible.</p>
        <p>
            <strong>Advance Payment Amount:</strong> Rs.500.00<br><br>
            <strong>Bank Account Details:</strong><br>
            Account Name: EzBikez Pvt Ltd<br>
            Account Number: 1234567890<br>
            Bank: ABC Bank<br>
            Branch: Main City
        </p>
        <p>
            After making the payment, <br>
            1. login to your account using <br><a href="' . htmlspecialchars($login_link) . '">' . htmlspecialchars($login_link) . '</a><br>
            2. goto your bookings page -> select this booking -> view details and please upload the slip<br><br>
            OR<br><br>
            1. login to your account using <a href="' . htmlspecialchars($login_link) . '">' . htmlspecialchars($login_link) . '</a><br>
            2. Return to this email<br>
            3. please upload your payment slip using the following link:<br>
            <a href="' . htmlspecialchars($payment_link) . '">' . htmlspecialchars($payment_link) . '</a>
        </p>
        <p>
            <em>Please note: Bookings are confirmed on a first-come, first-served basis. In rare cases, if the bike becomes unavailable, the EzBikez team will refund your payment as soon as possible.</em>
        </p>
        <p>Thank you for choosing EzBikez!<br>
        Best regards,<br>
        EzBikez Team</p>
    ';

    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: EzBikez <no-reply@{$_SERVER['HTTP_HOST']}>" . "\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        $email_success = "Advance payment email sent to " . htmlspecialchars($booking['email']) . ".";
    } else {
        $email_error = "Failed to send email. Please try again.";
    }
}

// --- AUTO CANCEL IF SLIP NOT UPLOADED AND TODAY IS START DATE ---
if (
    empty($booking['slip']) &&
    date('Y-m-d') === $booking['start_date'] &&
    in_array($booking['status'], ['pending', 'approved'])
) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);
        $pdo->commit();

        // Optionally, send email to user about cancellation
        $to = $booking['email'];
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: EzBikez <no-reply@{$_SERVER['HTTP_HOST']}>\r\n";
        $user_name = htmlspecialchars($booking['user_name']);
        $subject = "EzBikez Booking Cancelled - Payment Slip Not Uploaded";
        $message = "
            <h2>Booking Cancelled</h2>
            <p>Dear {$user_name},</p>
            <p>Your booking was automatically cancelled because the payment slip was not uploaded before the rental start date.</p>
            <p>If you have any questions, please contact us.<br>EzBikez Team</p>
        ";
        mail($to, $subject, $message, $headers);

        // Refresh to show updated status
        header('Location: /ezbikez/admin/booking-details.php?id=' . $booking_id . '&success=1');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Auto-cancel error: " . $e->getMessage());
        // Optionally set $error here if you want to show a message
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
                <h1 class="h2">Booking Details #<?php echo $booking_id; ?></h1>
                <span><?php echo getBookingStatus($booking['status']); ?></span>
            </div>
            
            <!-- Notification Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo isset($success) ? $success : 'Operation completed successfully.'; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($email_success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $email_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($email_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $email_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Booking Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Booking Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                                    <p><strong>Booking Date:</strong> <?php echo date('F j, Y H:i', strtotime($booking['created_at'])); ?></p>
                                    <p><strong>Rental Period:</strong><br>
                                        <?php echo date('F j, Y', strtotime($booking['start_date'])); ?> to 
                                        <?php echo date('F j, Y', strtotime($booking['end_date'])); ?>
                                    </p>
                                    <p><strong>Duration:</strong> 
                                        <?php 
                                        $start = new DateTime($booking['start_date']);
                                        $end = new DateTime($booking['end_date']);
                                        $interval = $start->diff($end);
                                        echo $interval->days . ' days';
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Bike Model:</strong> <?php echo $booking['model']; ?></p>
                                    <p><strong>Category:</strong> <?php echo getCategoryName($booking['category']); ?></p>
                                    <p><strong>Price per day:</strong> <?php echo formatPrice($booking['price_per_day']); ?></p>
                                    <p><strong>Total Amount:</strong> <?php echo formatPrice($booking['total_price']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <?php echo $booking['user_name']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $booking['phone'] ? $booking['phone'] : 'Not provided'; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Address:</strong><br><?php echo $booking['address'] ? nl2br($booking['address']) : 'Not provided'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Status Update -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Update Status</h5>
                        </div>

                        <?php if (!empty($booking['slip'])): ?>
                            <div class="alert alert-success d-flex align-items-center mb-3">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                <span>
                                    <strong>Payment Slip:</strong>
                                    <a href="/ezbikez/uploads/slip/<?php echo htmlspecialchars($booking['slip']); ?>" class="btn btn-sm btn-outline-primary ms-2" target="_blank" download>
                                        <i class="fas fa-download me-1"></i> Download Slip
                                    </a>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-center mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span>Payment slip not uploaded yet.</span>
                            </div>
                            <!-- Hide status update form and button if slip not uploaded -->
                            <div class="alert alert-info d-flex align-items-center mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <span>
                                    <strong>Note:</strong> You cannot update the booking status until the customer uploads the payment slip.
                                    <?php if (!empty($available_bike_items)): ?>
                                        <br>
                                        <strong>Available Bikes:</strong>
                                        <?php foreach ($available_bike_items as $bike_item): ?>
                                            <span class="badge bg-secondary me-1">Bike #<?php echo $bike_item['id']; ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <br>
                                        <span class="text-danger">No bikes available in this category for the selected dates.</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <style>
                                #statusForm, #statusForm button[type="submit"] {
                                    display: none !important;
                                }
                            </style>
                        <?php endif; ?>

                        <div class="card-body">
                            <form method="POST" action="" id="statusForm">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $booking['status'] === 'approved' ? 'selected' : ''; ?>>Approved (Ready for Bike Assignment)</option>
                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed (Bike Assigned)</option>
                                        <option value="rejected" <?php echo $booking['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <option value="completed">Completed</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <?php if (($booking['status'] === 'pending' || $booking['status'] === 'approved') && !empty($available_bike_items)): ?>
                                    <div class="mb-3" id="bikeSelection">
                                        <label for="bike_item_id" class="form-label">Assign Specific Bike</label>
                                        <select class="form-select" id="bike_item_id" name="bike_item_id">
                                            <option value="">-- Select a specific bike --</option>
                                            <?php foreach ($available_bike_items as $bike_item): ?>
                                                <option value="<?php echo $bike_item['id']; ?>">Bike #<?php echo $bike_item['id']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Select a specific bike to assign to this booking.</div>
                                    </div>
                                <?php elseif ($assigned_bike): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Assigned Bike:</strong> Bike #<?php echo $assigned_bike['bike_item_id']; ?>
                                    </div>
                                <?php elseif (empty($available_bike_items) && ($booking['status'] === 'pending' || $booking['status'] === 'approved')): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        No bikes available in this category for the selected dates.
                                    </div>
                                <?php endif; ?>
                                
                                <button type="submit" name="update_status" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Update Status
                                </button>
                            </form>
                            
                            <?php if ($booking['status'] === 'confirmed' && $assigned_bike): ?>
                                <hr>
                                <form method="POST" action="" id="returnForm">
                                    <button type="submit" name="mark_returned" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to mark Bike #<?php echo $assigned_bike['bike_item_id']; ?> as returned?')">
                                        <i class="fas fa-check-circle me-1"></i> Mark Bike #<?php echo $assigned_bike['bike_item_id']; ?> as Returned
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="/ezbikez/admin/bookings.php" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="fas fa-arrow-left me-1"></i> Back to Bookings
                            </a>
                            <a href="/ezbikez/admin/booking-print.php?id=<?php echo $booking_id; ?>" target="_blank" class="btn btn-outline-primary w-100">
                                <i class="fas fa-print me-1"></i> Print Details
                            </a>
                            <!-- Send Email Button -->
                            <form method="post" action="" class="mt-2">
                                <input type="hidden" name="send_email" value="1">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="fas fa-envelope me-1"></i> Send Advance Payment Email
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Bike Status Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-bicycle me-2"></i>Bike Status</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($assigned_bike): ?>
                                <div class="alert alert-info">
                                    <p class="mb-1"><strong>Bike Assigned:</strong> Yes</p>
                                    <p class="mb-1"><strong>Bike ID:</strong> #<?php echo $assigned_bike['bike_item_id']; ?></p>
                                    <p class="mb-0"><strong>Assigned At:</strong> <?php echo date('M j, Y H:i', strtotime($assigned_bike['assigned_at'])); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <p class="mb-0"><strong>Bike Assigned:</strong> No</p>
                                    <?php if (!empty($available_bike_items)): ?>
                                        <p class="mb-0 mt-2"><strong>Available Bikes:</strong> <?php echo count($available_bike_items); ?> bike(s) available</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide bike selection based on status
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const bikeSelection = document.getElementById('bikeSelection');
    
    if (statusSelect && bikeSelection) {
        // Initial state
        bikeSelection.style.display = (statusSelect.value === 'confirmed') ? 'block' : 'none';
        
        // Change event
        statusSelect.addEventListener('change', function() {
            bikeSelection.style.display = (this.value === 'confirmed') ? 'block' : 'none';
            
            // Make bike selection required if status is confirmed
            const bikeItemSelect = document.getElementById('bike_item_id');
            if (bikeItemSelect) {
                bikeItemSelect.required = (this.value === 'confirmed');
            }
        });
    }
    
    // Form validation
    const statusForm = document.getElementById('statusForm');
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            const status = document.getElementById('status').value;
            const bikeItem = document.getElementById('bike_item_id');
            
            if (status === 'confirmed' && bikeItem && (!bikeItem.value || bikeItem.value === '')) {
                e.preventDefault();
                alert('Please select a specific bike to assign for confirmed status.');
                bikeItem.focus();
            }
        });
    }
});
</script>

<?php
require_once '../includes/footer.php';
?>