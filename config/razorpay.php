<?php
// Razorpay Configuration
// Set this to 'demo' for testing or 'live' for production
define('RAZORPAY_MODE', 'demo'); // Changed to demo for testing with test credentials

// Demo Configuration (for testing)
define('RAZORPAY_DEMO_KEY_ID', 'rzp_test_XPnjwwTZ0jMczs');
define('RAZORPAY_DEMO_KEY_SECRET', 'YFp49LOHblATcmSBxVUg6Vyi');

// Live Configuration (replace with your actual credentials)
define('RAZORPAY_LIVE_KEY_ID', 'rzp_test_XPnjwwTZ0jMczs'); // Replace with your actual test key
define('RAZORPAY_LIVE_KEY_SECRET', 'YFp49LOHblATcmSBxVUg6Vyi'); // Replace with your actual secret

// Get the appropriate credentials based on mode
function getRazorpayCredentials() {
    if (RAZORPAY_MODE === 'live') {
        return [
            'key_id' => RAZORPAY_LIVE_KEY_ID,
            'key_secret' => RAZORPAY_LIVE_KEY_SECRET
        ];
    } else {
        return [
            'key_id' => RAZORPAY_DEMO_KEY_ID,
            'key_secret' => RAZORPAY_DEMO_KEY_SECRET
        ];
    }
}

// Currency conversion rate (USD to INR)
define('USD_TO_INR_RATE', 75);

// Webhook URL (update with your domain for production)
define('RAZORPAY_WEBHOOK_URL', 'https://yourdomain.com/quiz/api/razorpay_webhook.php');

// Demo card details for testing
define('DEMO_CARD_NUMBER', '4111 1111 1111 1111');
define('DEMO_CARD_CVV', '123');
define('DEMO_CARD_EXPIRY', '12/25');
define('DEMO_CARD_NAME', 'Any Name');

// Business profile information
define('BUSINESS_NAME', 'QuizMaster');
define('BUSINESS_EMAIL', 'support@quizmaster.com');
define('BUSINESS_CONTACT', '+91 9999999999');
define('BUSINESS_ADDRESS', 'Demo Address, Demo City, Demo State');

// Razorpay checkout options
function getRazorpayOptions($orderId, $amount, $description, $categoryId) {
    $credentials = getRazorpayCredentials();
    
    return [
        'key' => $credentials['key_id'],
        'amount' => $amount * USD_TO_INR_RATE * 100, // Convert to paise
        'currency' => 'INR',
        'name' => BUSINESS_NAME,
        'description' => $description,
        'image' => 'https://razorpay.com/favicon.png',
        'order_id' => $orderId,
        'prefill' => [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'contact' => '9999999999'
        ],
        'notes' => [
            'address' => BUSINESS_ADDRESS,
            'category_id' => $categoryId,
            'business_profile' => 'https://razorpay.me/@jamesvarghese'
        ],
        'theme' => [
            'color' => '#667eea'
        ]
    ];
}

// Verify payment signature (for production)
function verifyPaymentSignature($payload, $signature) {
    if (RAZORPAY_MODE === 'live') {
        // In production, verify the signature using the secret key
        $credentials = getRazorpayCredentials();
        $expectedSignature = hash_hmac('sha256', $payload, $credentials['key_secret']);
        return hash_equals($expectedSignature, $signature);
    } else {
        // For demo mode, skip verification
        return true;
    }
}

// Generate order ID
function generateOrderId() {
    return 'order_' . uniqid() . '_' . time();
}

// Generate payment ID
function generatePaymentId() {
    return 'pay_' . uniqid() . '_' . time();
}

// Get mode-specific information
function getModeInfo() {
    if (RAZORPAY_MODE === 'live') {
        return [
            'mode' => 'Live',
            'description' => 'Real payments will be processed',
            'color' => '#28a745',
            'icon' => 'fas fa-check-circle'
        ];
    } else {
        return [
            'mode' => 'Demo',
            'description' => 'Test mode - no real payments',
            'color' => '#ffc107',
            'icon' => 'fas fa-info-circle'
        ];
    }
}
?> 