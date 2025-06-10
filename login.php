<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login - QuizMaster';
include 'includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-full">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 1rem;">
            <p>Don't have an account? <a href="register.php" style="color: #667eea;">Register here</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 