<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

function getCategoryName($category) {
    switch($category) {
        case 'A': return 'Premium Bikes';
        case 'B': return 'Standard Bikes';
        case 'C': return 'Economy Bikes';
        default: return 'Unknown Category';
    }
}

function getBookingStatus($status) {
    switch($status) {
        case 'pending': return '<span class="badge bg-warning">Pending</span>';
        case 'approved': return '<span class="badge bg-info">Approved (No Bike)</span>';
        case 'confirmed': return '<span class="badge bg-success">Confirmed</span>';
        case 'completed': return '<span class="badge bg-secondary">Completed</span>';
        case 'rejected': return '<span class="badge bg-danger">Rejected</span>';
        case 'cancelled': return '<span class="badge bg-dark">Cancelled</span>';
        default: return '<span class="badge bg-light text-dark">Unknown</span>';
    }
}

function calculateTotal($price_per_day, $start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days; // Include both start and end dates
    
    return $price_per_day * $days;
}
?>