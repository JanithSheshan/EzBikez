<?php
require_once '../includes/header.php';
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /ezbikez/public/login.php');
    exit();
}

// Check if booking ID is provided
if (!isset($_POST['booking_id'])) {
    header('Location: /ezbikez/public/my-bookings.php');
    exit();
}

$booking_id = $_POST['booking_id'];
$user_id = $_SESSION['user_id'];

// Verify booking belongs to user and can be cancelled
try {
    $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";
        header('Location: /ezbikez/public/my-bookings.php');
        exit();
    }
    
    // Only allow cancellation if booking is pending or approved
    if ($booking['status'] !== 'pending' && $booking['status'] !== 'approved') {
        $_SESSION['error'] = "This booking cannot be cancelled.";
        header('Location: /ezbikez/public/my-bookings.php');
        exit();
    }
    
    // Update booking status to cancelled
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    $_SESSION['success'] = "Booking cancelled successfully.";
    header('Location: /ezbikez/public/my-bookings.php');
    exit();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
    header('Location: /ezbikez/public/my-bookings.php');
    exit();
}
?>