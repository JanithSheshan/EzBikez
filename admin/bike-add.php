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

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = trim($_POST['model']);
    $category = $_POST['category'];
    $price_per_day = trim($_POST['price_per_day']);
    $description = trim($_POST['description']);
    $quantity = intval($_POST['quantity']);
    
    // Validation
    if (empty($model) || empty($category) || empty($price_per_day) || $quantity < 1) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($category, ['A', 'B', 'C'])) {
        $error = 'Invalid category selected.';
    } elseif (!is_numeric($price_per_day) || $price_per_day <= 0) {
        $error = 'Price must be a positive number.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Handle image upload
            $image_name = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $image_name = uniqid() . '.' . $extension;
                $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/ezbikez/public/uploads/bikes/' . $image_name;
                
                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    throw new Exception('Failed to upload image.');
                }
            }
            
            // Insert bike
            $stmt = $pdo->prepare("
                INSERT INTO bikes (model, category, price_per_day, description, image) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$model, $category, $price_per_day, $description, $image_name]);
            $bike_id = $pdo->lastInsertId();
            
            // Add bike items - FIXED: Create individual bike items
            for ($i = 0; $i < $quantity; $i++) {
                $stmt = $pdo->prepare("INSERT INTO bike_items (bike_id, is_available) VALUES (?, 1)");
                $stmt->execute([$bike_id]);
            }
            
            $pdo->commit();
            
            header('Location: /ezbikez/admin/bikes.php?created=1');
            exit();
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = 'Error adding bike: ' . $e->getMessage();
        }
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
                <h1 class="h2">Add New Bike</h1>
                <a href="/ezbikez/admin/bikes.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Bikes
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="model" class="form-label">Model Name *</label>
                                <input type="text" class="form-control" id="model" name="model" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="A">Category A (Premium)</option>
                                    <option value="B">Category B (Standard)</option>
                                    <option value="C">Category C (Economy)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price_per_day" class="form-label">Price per Day (LKR) *</label>
                                <input type="number" class="form-control" id="price_per_day" name="price_per_day" step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required value="1">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image" class="form-label">Bike Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Add Bike</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>