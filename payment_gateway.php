<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/razorpay.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$category_id = $_GET['id'] ?? null;
$amount = $_GET['amount'] ?? null;

if (!$category_id || !$amount) {
    header('Location: dashboard.php');
    exit();
}

// Get category info
$stmt = $pdo->prepare("SELECT id, name, price FROM quiz_categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: dashboard.php');
    exit();
}

// Check if user already purchased this quiz
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT id FROM user_purchases 
    WHERE user_id = ? AND category_id = ? AND status = 'completed'
");
$stmt->execute([$user_id, $category_id]);

if ($stmt->fetch()) {
    header('Location: take_quiz.php?id=' . $category_id);
    exit();
}

$error = '';
$success = '';

// Get Razorpay credentials
$credentials = getRazorpayCredentials();
$modeInfo = getModeInfo();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method === 'razorpay') {
        // Simulate Razorpay payment processing
        $payment_id = generatePaymentId();
        
        try {
            // Record the purchase
            $stmt = $pdo->prepare("
                INSERT INTO user_purchases (user_id, category_id, amount, payment_id, status) 
                VALUES (?, ?, ?, ?, 'completed')
            ");
            
            $stmt->execute([$user_id, $category_id, $amount, $payment_id]);
            
            $success = 'Payment successful! Redirecting to quiz...';
            
            // Redirect after 2 seconds
            header("refresh:2;url=take_quiz.php?id=" . $category_id);
            
        } catch (Exception $e) {
            $error = 'Payment processing failed. Please try again.';
        }
    } else {
        $error = 'Please select a payment method.';
    }
}

$page_title = 'Payment - QuizMaster';
include 'includes/header.php';
?>

<div class="container">
    <div class="payment-container">
        <div class="payment-header">
            <h2>
                <i class="fas fa-credit-card"></i> Razorpay Payment Gateway
            </h2>
            <p>Complete your purchase to access the quiz</p>
        </div>

        <!-- Mode Indicator -->
        <div style="background: <?php echo $modeInfo['color']; ?>20; border: 1px solid <?php echo $modeInfo['color']; ?>; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; text-align: center;">
            <p style="margin: 0; color: <?php echo $modeInfo['color']; ?>; font-size: 0.9rem;">
                <i class="<?php echo $modeInfo['icon']; ?>"></i> 
                <strong><?php echo $modeInfo['mode']; ?> Mode:</strong> <?php echo $modeInfo['description']; ?>
            </p>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h4>Order Summary</h4>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Quiz:</span>
                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Amount:</span>
                <strong style="color: #28a745; font-size: 1.2rem;">₹<?php echo number_format($amount * USD_TO_INR_RATE, 2); ?></strong>
            </div>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #dee2e6;">
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem;">
                <span>Total:</span>
                <span style="color: #667eea;">₹<?php echo number_format($amount * USD_TO_INR_RATE, 2); ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <!-- Razorpay Live Payment Form -->
            <form method="POST" action="" class="payment-form" id="razorpay-form">
                <input type="hidden" name="payment_method" value="razorpay">
                
                <div style="margin-bottom: 2rem;">
                    <h4 style="color: #333; margin-bottom: 1rem;">Payment Method</h4>
                    <div style="display: flex; align-items: center; padding: 1rem; border: 2px solid #667eea; border-radius: 8px; background: rgba(102, 126, 234, 0.05);">
                        <img src="https://razorpay.com/favicon.png" alt="Razorpay" style="width: 30px; height: 30px; margin-right: 1rem;">
                        <div>
                            <strong>Razorpay</strong>
                            <p style="margin: 0; color: #666; font-size: 0.9rem;">Secure payment gateway</p>
                        </div>
                    </div>
                </div>

                <?php if (RAZORPAY_MODE === 'demo'): ?>
                <!-- Demo Card Details -->
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border: 1px solid #e9ecef;">
                    <h5 style="color: #333; margin-bottom: 1rem;">
                        <i class="fas fa-info-circle"></i> Demo Card Details
                    </h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                        <div>
                            <strong>Card Number:</strong><br>
                            <code><?php echo DEMO_CARD_NUMBER; ?></code>
                        </div>
                        <div>
                            <strong>CVV:</strong><br>
                            <code><?php echo DEMO_CARD_CVV; ?></code>
                        </div>
                        <div>
                            <strong>Expiry:</strong><br>
                            <code><?php echo DEMO_CARD_EXPIRY; ?></code>
                        </div>
                        <div>
                            <strong>Name:</strong><br>
                            <code><?php echo DEMO_CARD_NAME; ?></code>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-full" style="font-size: 1.1rem; padding: 1rem;" id="pay-button">
                        <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($amount * USD_TO_INR_RATE, 2); ?> with Razorpay
                    </button>
                </div>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="dashboard.php" class="btn btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>

            <?php if (RAZORPAY_MODE === 'demo'): ?>
            <!-- Demo Notice -->
            <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 1rem; margin-top: 2rem; text-align: center;">
                <p style="margin: 0; color: #1976d2; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Demo Mode:</strong> This is a simulated Razorpay payment gateway. Use the demo card details above for testing.
                </p>
            </div>
            <?php else: ?>
            <!-- Live Payment Notice -->
            <div style="background: #d4edda; border: 1px solid #28a745; border-radius: 8px; padding: 1rem; margin-top: 2rem; text-align: center;">
                <p style="margin: 0; color: #155724; font-size: 0.9rem;">
                    <i class="fas fa-shield-alt"></i> 
                    <strong>Secure Payment:</strong> Your payment will be processed securely through Razorpay. All card details are encrypted.
                </p>
            </div>
            <?php endif; ?>

            <!-- Payment Processing Script -->
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
            document.getElementById('razorpay-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const payButton = document.getElementById('pay-button');
                payButton.disabled = true;
                payButton.innerHTML = '<span class="loading"></span> Creating Order...';
                
                <?php if (RAZORPAY_MODE === 'live'): ?>
                // Live payment flow
                fetch('api/create_razorpay_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        category_id: <?php echo $category_id; ?>,
                        amount: <?php echo $amount; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Initialize Razorpay checkout
                        const options = {
                            key: '<?php echo $credentials['key_id']; ?>',
                            amount: data.amount,
                            currency: data.currency,
                            name: '<?php echo BUSINESS_NAME; ?>',
                            description: data.description,
                            image: 'https://razorpay.com/favicon.png',
                            order_id: data.order_id,
                            handler: function(response) {
                                // Payment successful
                                console.log('Payment successful:', response);
                                
                                // Show success message
                                const successDiv = document.createElement('div');
                                successDiv.className = 'alert alert-success';
                                successDiv.innerHTML = '<i class="fas fa-check-circle"></i> Payment successful! Redirecting to quiz...';
                                
                                const form = document.getElementById('razorpay-form');
                                form.parentNode.insertBefore(successDiv, form);
                                
                                // Submit the form after a short delay
                                setTimeout(() => {
                                    document.getElementById('razorpay-form').submit();
                                }, 2000);
                            },
                            prefill: {
                                name: 'Demo User',
                                email: 'demo@example.com',
                                contact: '9999999999'
                            },
                            notes: {
                                address: '<?php echo BUSINESS_ADDRESS; ?>',
                                category_id: <?php echo $category_id; ?>
                            },
                            theme: {
                                color: '#667eea'
                            },
                            modal: {
                                ondismiss: function() {
                                    payButton.disabled = false;
                                    payButton.innerHTML = '<i class="fas fa-lock"></i> Pay ₹<?php echo number_format($amount * USD_TO_INR_RATE, 2); ?> with Razorpay';
                                }
                            }
                        };

                        const rzp = new Razorpay(options);
                        rzp.open();
                    } else {
                        alert('Failed to create order: ' + data.message);
                        payButton.disabled = false;
                        payButton.innerHTML = '<i class="fas fa-lock"></i> Pay ₹<?php echo number_format($amount * USD_TO_INR_RATE, 2); ?> with Razorpay';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Payment initialization failed. Please try again.');
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fas fa-lock"></i> Pay ₹<?php echo number_format($amount * USD_TO_INR_RATE, 2); ?> with Razorpay';
                });
                <?php else: ?>
                // Demo payment flow
                payButton.innerHTML = '<span class="loading"></span> Processing Payment...';
                
                setTimeout(() => {
                    const successDiv = document.createElement('div');
                    successDiv.className = 'alert alert-success';
                    successDiv.innerHTML = '<i class="fas fa-check-circle"></i> Payment successful! Redirecting to quiz...';
                    
                    const form = document.getElementById('razorpay-form');
                    form.parentNode.insertBefore(successDiv, form);
                    
                    setTimeout(() => {
                        document.getElementById('razorpay-form').submit();
                    }, 2000);
                    
                }, 3000);
                <?php endif; ?>
            });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 