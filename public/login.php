<?php
require_once '../includes/header.php';
require_once '../includes/nav.php';
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];

                if ($user['is_admin']) {
                    header('Location: /ezbikez/admin/index.php');
                } else {
                    header('Location: /ezbikez/public/availability.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } catch(PDOException $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Login to Your Account</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                    <i id="togglePasswordIcon" class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const passwordInput = document.getElementById('password');
                            const toggleBtn = document.getElementById('togglePassword');
                            const icon = document.getElementById('togglePasswordIcon');
                            toggleBtn.addEventListener('click', function() {
                                if (passwordInput.type === 'password') {
                                    passwordInput.type = 'text';
                                    icon.classList.remove('bi-eye');
                                    icon.classList.add('bi-eye-slash');
                                } else {
                                    passwordInput.type = 'password';
                                    icon.classList.remove('bi-eye-slash');
                                    icon.classList.add('bi-eye');
                                }
                            });
                        });
                        </script>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Don't have an account? <a href="/ezbikez/public/register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- bootstrap icon -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
require_once '../includes/footer.php';
?>