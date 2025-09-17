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

// Check if bike ID is provided
if (!isset($_GET['id'])) {
    header('Location: /ezbikez/admin/bikes.php');
    exit();
}

$bike_id = $_GET['id'];
$error = '';
$success = '';

// Get bike details
try {
    $stmt = $pdo->prepare("SELECT * FROM bikes WHERE id = ?");
    $stmt->execute([$bike_id]);
    $bike = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bike) {
        header('Location: /ezbikez/admin/bikes.php');
        exit();
    }
} catch(PDOException $e) {
    $error = "Error retrieving bike details: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = trim($_POST['model']);
    $category = $_POST['category'];
    $price_per_day = trim($_POST['price_per_day']);
    $description = trim($_POST['description']);
    
    // Validation
    if (empty($model) || empty($category) || empty($price_per_day)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($category, ['A', 'B', 'C'])) {
        $error = 'Invalid category selected.';
    } elseif (!is_numeric($price_per_day) || $price_per_day <= 0) {
        $error = 'Price must be a positive number.';
    } else {
        try {
            // Handle image upload
            $image_name = $bike['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if exists
                if (!empty($bike['image'])) {
                    $old_image_path = $_SERVER['DOCUMENT_ROOT'] . '/ezbikez/public/uploads/bikes/' . $bike['image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                $file = $_FILES['image'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $image_name = uniqid() . '.' . $extension;
                $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/ezbikez/public/uploads/bikes/' . $image_name;
                
                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    throw new Exception('Failed to upload image.');
                }
            }
            
            // Update bike
            $stmt = $pdo->prepare("
                UPDATE bikes 
                SET model = ?, category = ?, price_per_day = ?, description = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$model, $category, $price_per_day, $description, $image_name, $bike_id]);
            
            header('Location: /ezbikez/admin/bikes.php?updated=1');
            exit();
        } catch(Exception $e) {
            $error = 'Error updating bike: ' . $e->getMessage();
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
                <h1 class="h2">Edit Bike</h1>
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
                                <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($bike['model']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="A" <?php echo $bike['category'] === 'A' ? 'selected' : ''; ?>>Category A (Premium)</option>
                                    <option value="B" <?php echo $bike['category'] === 'B' ? 'selected' : ''; ?>>Category B (Standard)</option>
                                    <option value="C" <?php echo $bike['category'] === 'C' ? 'selected' : ''; ?>>Category C (Economy)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price_per_day" class="form-label">Price per Day (LKR) *</label>
                            <input type="number" class="form-control" id="price_per_day" name="price_per_day" step="0.01" min="0" value="<?php echo $bike['price_per_day']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($bike['description']); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image" class="form-label">Bike Image</label>
                            <?php if (!empty($bike['image'])): ?>
                                <div class="mb-2">
                                    <img src="/ezbikez/public/uploads/bikes/<?php echo $bike['image']; ?>" alt="Current image" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Update Bike</button>
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