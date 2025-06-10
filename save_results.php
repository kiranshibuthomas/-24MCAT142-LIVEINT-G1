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

if (!$input || !isset($input['category_id']) || !isset($input['score']) || !isset($input['total_questions'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$user_id = $_SESSION['user_id'];
$category_id = $input['category_id'];
$score = $input['score'];
$total_questions = $input['total_questions'];

try {
    // Save quiz attempt
    $stmt = $pdo->prepare("
        INSERT INTO quiz_attempts (user_id, category_id, score, total_questions) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$user_id, $category_id, $score, $total_questions]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Results saved successfully',
        'attempt_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save results'
    ]);
}
?> 