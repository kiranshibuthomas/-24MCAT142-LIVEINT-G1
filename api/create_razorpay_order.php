<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/razorpay.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

if (!$input || !isset($input['category_id']) || !isset($input['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$user_id = $_SESSION['user_id'];
$category_id = $input['category_id'];
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
    
    // Convert amount to paise (Razorpay expects amount in paise)
    $amount_paise = $amount * USD_TO_INR_RATE * 100; // Convert USD to INR and then to paise
    
    // Get Razorpay credentials
    $credentials = getRazorpayCredentials();
    
    // Create order data for Razorpay API
    $orderData = [
        'amount' => $amount_paise,
        'currency' => 'INR',
        'receipt' => 'receipt_' . uniqid(),
        'notes' => [
            'category_id' => $category_id,
            'user_id' => $user_id,
            'business_profile' => 'https://razorpay.me/@jamesvarghese'
        ]
    ];
    
    // Call Razorpay API to create order
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($credentials['key_id'] . ':' . $credentials['key_secret'])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Razorpay API error: HTTP $httpCode, Response: $response");
        throw new Exception('Failed to create Razorpay order. Please try again.');
    }
    
    $razorpayOrder = json_decode($response, true);
    
    if (!$razorpayOrder || !isset($razorpayOrder['id'])) {
        throw new Exception('Invalid response from Razorpay');
    }
    
    // Store order in our database
    $stmt = $pdo->prepare("
        INSERT INTO user_purchases (user_id, category_id, amount, payment_id, status) 
        VALUES (?, ?, ?, ?, 'pending')
    ");
    
    $stmt->execute([$user_id, $category_id, $amount, $razorpayOrder['id']]);
    
    // Log the order creation for debugging
    error_log("Razorpay order created: Order ID: {$razorpayOrder['id']}, Amount: $amount_paise paise, Category: {$category['name']}");
    
    // Return order details for Razorpay
    echo json_encode([
        'success' => true,
        'order_id' => $razorpayOrder['id'],
        'amount' => $amount_paise,
        'currency' => 'INR',
        'description' => $category['name'] . ' Quiz Purchase'
    ]);
    
} catch (Exception $e) {
    error_log("Razorpay order creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order: ' . $e->getMessage()
    ]);
}
?> 