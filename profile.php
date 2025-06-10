<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get quiz history
$stmt = $pdo->prepare("
    SELECT 
        qa.id,
        qa.score,
        qa.total_questions,
        qa.completed_at,
        qc.name as category_name,
        ROUND((qa.score / qa.total_questions) * 100, 1) as percentage
    FROM quiz_attempts qa
    JOIN quiz_categories qc ON qa.category_id = qc.id
    WHERE qa.user_id = ?
    ORDER BY qa.completed_at DESC
    LIMIT 20
");
$stmt->execute([$user_id]);
$quiz_history = $stmt->fetchAll();

// Get purchase history
$stmt = $pdo->prepare("
    SELECT 
        up.id,
        up.amount,
        up.payment_id,
        up.created_at,
        qc.name as category_name
    FROM user_purchases up
    JOIN quiz_categories qc ON up.category_id = qc.id
    WHERE up.user_id = ? AND up.status = 'completed'
    ORDER BY up.created_at DESC
");
$stmt->execute([$user_id]);
$purchase_history = $stmt->fetchAll();

$page_title = 'Profile - QuizMaster';
include 'includes/header.php';
?>

<div class="container">
    <div class="profile-header" style="text-align: center; margin-bottom: 3rem;">
        <h1 style="color: white; margin-bottom: 1rem;">Profile</h1>
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h2 style="color: #667eea; margin-bottom: 1rem;"><?php echo htmlspecialchars($user['username']); ?></h2>
            <p style="color: #666; margin-bottom: 0.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p style="color: #666; margin-bottom: 0.5rem;"><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <!-- Quiz History -->
    <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="color: #667eea; margin-bottom: 1.5rem; text-align: center;">Quiz History</h3>
        
        <?php if (empty($quiz_history)): ?>
            <p style="text-align: center; color: #666;">No quiz attempts yet. <a href="dashboard.php" style="color: #667eea;">Take your first quiz!</a></p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Quiz</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6;">Score</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6;">Percentage</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quiz_history as $attempt): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($attempt['category_name']); ?></td>
                                <td style="padding: 1rem; text-align: center;"><?php echo $attempt['score']; ?>/<?php echo $attempt['total_questions']; ?></td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="
                                        padding: 0.25rem 0.5rem; 
                                        border-radius: 4px; 
                                        background: <?php echo $attempt['percentage'] >= 80 ? '#d4edda' : ($attempt['percentage'] >= 60 ? '#fff3cd' : '#f8d7da'); ?>;
                                        color: <?php echo $attempt['percentage'] >= 80 ? '#155724' : ($attempt['percentage'] >= 60 ? '#856404' : '#721c24'); ?>;
                                    ">
                                        <?php echo $attempt['percentage']; ?>%
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;"><?php echo date('M j, Y', strtotime($attempt['completed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Purchase History -->
    <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
        <h3 style="color: #667eea; margin-bottom: 1.5rem; text-align: center;">Purchase History</h3>
        
        <?php if (empty($purchase_history)): ?>
            <p style="text-align: center; color: #666;">No purchases yet. <a href="dashboard.php" style="color: #667eea;">Browse available quizzes!</a></p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Quiz</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6;">Amount</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6;">Payment ID</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchase_history as $purchase): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($purchase['category_name']); ?></td>
                                <td style="padding: 1rem; text-align: center; font-weight: bold; color: #28a745;">$<?php echo number_format($purchase['amount'], 2); ?></td>
                                <td style="padding: 1rem; text-align: center; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($purchase['payment_id']); ?></td>
                                <td style="padding: 1rem; text-align: center;"><?php echo date('M j, Y', strtotime($purchase['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 