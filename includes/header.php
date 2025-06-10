<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Quiz App'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="dashboard.php">
                        <i class="fas fa-brain"></i>
                        <span>QuizMaster</span>
                    </a>
                </div>
                
                <div class="nav-menu" id="nav-menu">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                        <a href="quizzes.php" class="nav-link">Quizzes</a>
                        <a href="profile.php" class="nav-link">Profile</a>
                        <a href="logout.php" class="nav-link">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">Login</a>
                        <a href="register.php" class="nav-link">Register</a>
                    <?php endif; ?>
                </div>
                
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="main-content"> 