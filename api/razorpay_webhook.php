<?php
require_once dirname(__DIR__) . '/config/database.php';

// Razorpay Demo Configuration
$razorpay_key_secret = 'demo_secret_key_123456789'; // Demo secret

// Get the webhook payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// In a real implementation, you would verify the signature
// For demo purposes, we'll skip signature verification

$data = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit();
}

try {
    $event = $data['event'] ?? '';
    $payment_id = $data['payload']['payment']['entity']['id'] ?? '';
    $order_id = $data['payload']['payment']['entity']['order_id'] ?? '';
    $status = $data['payload']['payment']['entity']['status'] ?? '';
    
    if ($event === 'payment.captured' && $status === 'captured') {
        // Payment successful - update database
        $stmt = $pdo->prepare("
            UPDATE user_purchases 
            SET status = 'completed', payment_id = ? 
            WHERE payment_id = ? AND status = 'pending'
        ");
        
        $stmt->execute([$payment_id, $order_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Payment processed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Order not found or already processed']);
        }
    } else {
        // Payment failed or other event
        $stmt = $pdo->prepare("
            UPDATE user_purchases 
            SET status = 'failed' 
            WHERE payment_id = ? AND status = 'pending'
        ");
        
        $stmt->execute([$order_id]);
        
        echo json_encode(['success' => true, 'message' => 'Payment status updated']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Webhook processing failed']);
}
?> 