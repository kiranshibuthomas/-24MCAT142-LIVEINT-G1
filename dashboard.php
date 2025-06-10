<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user stats
$user_id = $_SESSION['user_id'];

// Get total quizzes taken
$stmt = $pdo->prepare("SELECT COUNT(*) as total_attempts FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_attempts = $stmt->fetch()['total_attempts'];

// Get average score
$stmt = $pdo->prepare("SELECT AVG(score) as avg_score FROM quiz_attempts WHERE user_id = ?");
$stmt->execute([$user_id]);
$avg_score = round($stmt->fetch()['avg_score'], 1) ?: 0;

// Get total purchases
$stmt = $pdo->prepare("SELECT COUNT(*) as total_purchases FROM user_purchases WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$total_purchases = $stmt->fetch()['total_purchases'];

// Get available quiz categories
$stmt = $pdo->prepare("
    SELECT c.*, 
           CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END as is_purchased
    FROM quiz_categories c
    LEFT JOIN user_purchases p ON c.id = p.category_id AND p.user_id = ? AND p.status = 'completed'
    ORDER BY c.name
");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

$page_title = 'Dashboard - QuizMaster';
include 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Ready to test your knowledge? Choose a quiz category below to get started.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-trophy"></i>
            <h3><?php echo $total_attempts; ?></h3>
            <p>Quizzes Taken</p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-chart-line"></i>
            <h3><?php echo $avg_score; ?>%</h3>
            <p>Average Score</p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-shopping-cart"></i>
            <h3><?php echo $total_purchases; ?></h3>
            <p>Quizzes Purchased</p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-star"></i>
            <h3><?php echo count($categories); ?></h3>
            <p>Available Categories</p>
        </div>
    </div>

    <!-- Quiz Categories -->
    <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Available Quiz Categories</h2>
    
    <div class="quiz-grid">
        <?php foreach ($categories as $category): ?>
            <div class="quiz-card">
                <div class="quiz-card-header">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                </div>
                
                <div class="quiz-card-body">
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </div>
                
                <div class="quiz-card-footer">
                    <div class="quiz-price">$<?php echo number_format($category['price'], 2); ?></div>
                    
                    <?php if ($category['is_purchased']): ?>
                        <a href="take_quiz.php?id=<?php echo $category['id']; ?>" class="btn btn-success">
                            <i class="fas fa-play"></i> Take Quiz
                        </a>
                    <?php else: ?>
                        <a href="payment_gateway.php?id=<?php echo $category['id']; ?>&amount=<?php echo $category['price']; ?>" class="btn">
                            <i class="fas fa-shopping-cart"></i> Purchase
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 