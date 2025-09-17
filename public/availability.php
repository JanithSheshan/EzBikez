<?php
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/db.php';
require_once '../includes/helpers.php';

// Handle search form submission
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$available_bikes = [];
$category_counts = ['A' => 0, 'B' => 0, 'C' => 0];

if (!empty($start_date) && !empty($end_date)) {
    // Validate dates
    if ($start_date > $end_date) {
        $error = "End date must be after start date.";
    } else {
        // Check available bikes for the selected dates - FIXED QUERY
        try {
            $stmt = $pdo->prepare("
                SELECT b.*, COUNT(bi.id) as available_count
                FROM bikes b 
                LEFT JOIN bike_items bi ON b.id = bi.bike_id AND bi.is_available = 1
                WHERE bi.id NOT IN (
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
                OR bi.id IS NULL
                GROUP BY b.id
            ");
            $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
            $available_bikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Count available bikes by category
            foreach ($available_bikes as $bike) {
                if (isset($category_counts[$bike['category']])) {
                    $category_counts[$bike['category']] = $bike['available_count'];
                }
            }
        } catch(PDOException $e) {
            $error = "Error checking availability: " . $e->getMessage();
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="text-center mb-4">Check Bike Availability</h1>
            
            <!-- Search Form -->
            <div class="availability-form mb-5">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Check Availability</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Wrapper -->
            <div class="results-container">
                <div class="results-section">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($start_date) && !empty($end_date) && empty($error)): ?>
                        <h2 class="mb-4">Available Bikes (<?php echo date('M j', strtotime($start_date)); ?> - <?php echo date('M j', strtotime($end_date)); ?>)</h2>
                        
                        <?php if (empty($available_bikes)): ?>
                            <div class="alert alert-info">
                                No bikes available for the selected dates. Please try different dates.
                            </div>
                        <?php else: ?>
                            <!-- Category Tabs -->
                            <ul class="nav nav-tabs mb-4 flex-nowrap overflow-hidden" id="categoryTabs" role="tablist" style="white-space:nowrap;">
                                <li class="nav-item" role="presentation" style="min-width:120px;">
                                    <button class="nav-link active w-100" id="cat-a-tab" data-bs-toggle="tab" data-bs-target="#cat-a" type="button" role="tab">
                                        Premium Bikes
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation" style="min-width:120px;">
                                    <button class="nav-link w-100" id="cat-b-tab" data-bs-toggle="tab" data-bs-target="#cat-b" type="button" role="tab">
                                        Standard Bikes
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation" style="min-width:120px;">
                                    <button class="nav-link w-100" id="cat-c-tab" data-bs-toggle="tab" data-bs-target="#cat-c" type="button" role="tab">
                                        Economy Bikes 
                                    </button>
                                </li>
                            </ul>
                            <style>
                            @media (max-width: 576px) {
                                #categoryTabs .nav-item {
                                    min-width: 100px;
                                    flex: 0 0 auto;
                                }
                                #categoryTabs {
                                    font-size: 0.95rem;
                                }
                            }
                            </style>
                            
                            <!-- Category Contents -->
                            <div class="tab-content" id="categoryTabContent">
                                <?php 
                                $categories = ['A', 'B', 'C'];
                                foreach ($categories as $index => $category): 
                                    $category_bikes = array_filter($available_bikes, fn($bike) => $bike['category'] === $category);
                                ?>
                                <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="cat-<?php echo strtolower($category); ?>" role="tabpanel">
                                    <?php if (empty($category_bikes)): ?>
                                        <div class="alert alert-warning">
                                            No bikes available in this category for the selected dates.
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($category_bikes as $bike): ?>
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="card h-100">
                                                        <span class="category-badge badge bg-primary">Category <?php echo $bike['category']; ?></span>
                                                        <?php if (!empty($bike['image'])): ?>
                                                            <img src="/ezbikez/public/uploads/bikes/<?php echo $bike['image']; ?>" class="card-img-top bike-card-img" alt="<?php echo $bike['model']; ?>">
                                                        <?php else: ?>
                                                            <img src="/ezbikez/public/assets/img/bike-placeholder.jpg" class="card-img-top bike-card-img" alt="Bike placeholder">
                                                        <?php endif; ?>
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?php echo $bike['model']; ?></h5>
                                                            <p class="card-text"><?php echo $bike['description']; ?></p>
                                                            <ul class="list-group list-group-flush mb-3">
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span>Price per day:</span>
                                                                    <span class="fw-bold"><?php echo formatPrice($bike['price_per_day']); ?></span>
                                                                </li>
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span>Available:</span>
                                                                    <span class="fw-bold"><?php echo $bike['available_count']; ?> bikes</span>
                                                                </li>
                                                            </ul>
                                                            <?php if ($bike['available_count'] < 3): ?>
                                                                <div class="alert alert-warning small">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i> 
                                                                    Limited availability. You may need to accept an alternative bike if this model is not available at pickup.
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (isLoggedIn()): ?>
                                                                <form method="POST" action="booking.php">
                                                                    <input type="hidden" name="bike_id" value="<?php echo $bike['id']; ?>">
                                                                    <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                                                    <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                                                    <button type="submit" class="btn btn-primary w-100">Book Now</button>
                                                                </form>
                                                            <?php else: ?>
                                                                <a href="/ezbikez/public/login.php?redirect=<?php echo urlencode("availability.php?start_date={$start_date}&end_date={$end_date}"); ?>" class="btn btn-primary w-100">Login to Book</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const resultsContainer = document.querySelector('.results-container');
    const resultsSection = document.querySelector('.results-section');

    // Safely get login state from PHP
    const isUserLoggedIn = <?php echo function_exists('isLoggedIn') ? json_encode(isLoggedIn()) : 'false'; ?>;

    // Restore dates from sessionStorage (always OK)
    if (sessionStorage.getItem('ezbikez_start_date') && startDateInput) {
        startDateInput.value = sessionStorage.getItem('ezbikez_start_date');
    }
    if (sessionStorage.getItem('ezbikez_end_date') && endDateInput) {
        endDateInput.value = sessionStorage.getItem('ezbikez_end_date');
    }

    // Restore cached HTML ONLY if user is NOT logged in.
    // If user IS logged in, remove stale cached HTML so server-rendered "Book Now" is shown.
    if (!isUserLoggedIn) {
        if (sessionStorage.getItem('ezbikez_last_html') && resultsContainer) {
            resultsContainer.innerHTML = sessionStorage.getItem('ezbikez_last_html');
        }
    } else {
        sessionStorage.removeItem('ezbikez_last_html');
    }

    // Save on form submit (dates only)
    const form = document.querySelector('.availability-form form');
    if (form) {
        form.addEventListener('submit', function() {
            if (startDateInput && endDateInput) {
                sessionStorage.setItem('ezbikez_start_date', startDateInput.value);
                sessionStorage.setItem('ezbikez_end_date', endDateInput.value);
            }
        });
    }

    // Save rendered results only for non-logged-in users (avoids caching book buttons)
    if (!isUserLoggedIn && resultsSection && resultsSection.innerHTML.trim() !== '') {
        sessionStorage.setItem('ezbikez_last_html', resultsSection.innerHTML);
    }
});
</script>


<?php
require_once '../includes/footer.php';
?>
