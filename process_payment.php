<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['quiz_id']) || !isset($input['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$user_id = $_SESSION['user_id'];
$category_id = $input['quiz_id'];
$amount = $input['amount'];

try {
    // Check if quiz category exists
    $stmt = $pdo->prepare("SELECT id, name, price FROM quiz_categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        echo json_encode(['success' => false, 'message' => 'Quiz category not found']);
        exit();
    }
    
    // Check if user already purchased this quiz
    $stmt = $pdo->prepare("
        SELECT id FROM user_purchases 
        WHERE user_id = ? AND category_id = ? AND status = 'completed'
    ");
    $stmt->execute([$user_id, $category_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You already own this quiz']);
        exit();
    }
    
    // Generate a mock payment ID
    $payment_id = 'pay_' . uniqid() . '_' . time();
    
    // In a real implementation, this would integrate with Razorpay
    // For demo purposes, we'll simulate a successful payment
    
    // Record the purchase
    $stmt = $pdo->prepare("
        INSERT INTO user_purchases (user_id, category_id, amount, payment_id, status) 
        VALUES (?, ?, ?, ?, 'completed')
    ");
    
    $stmt->execute([$user_id, $category_id, $amount, $payment_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment successful! You can now take the quiz.',
        'payment_id' => $payment_id,
        'quiz_name' => $category['name']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Payment processing failed'
    ]);
}
?> 