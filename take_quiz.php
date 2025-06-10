<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    header('Location: dashboard.php');
    exit();
}

// Check if user has purchased this quiz
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT id FROM user_purchases 
    WHERE user_id = ? AND category_id = ? AND status = 'completed'
");
$stmt->execute([$user_id, $category_id]);

if (!$stmt->fetch()) {
    header('Location: dashboard.php');
    exit();
}

// Get category info
$stmt = $pdo->prepare("SELECT name FROM quiz_categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: dashboard.php');
    exit();
}

// Get questions for this category
$stmt = $pdo->prepare("
    SELECT id, question_text, correct_answer, incorrect_answers, difficulty 
    FROM quiz_questions 
    WHERE category_id = ? 
    ORDER BY RAND() 
    LIMIT 10
");
$stmt->execute([$category_id]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    // If no questions in database, fetch from API
    require_once 'api/fetch_questions.php';
    
    // Map category ID to API category
    $categoryMap = [
        1 => 'general_knowledge',
        2 => 'history',
        3 => 'science',
        4 => 'geography',
        5 => 'sports',
        6 => 'entertainment'
    ];
    
    $apiCategory = $categoryMap[$category_id] ?? 'general_knowledge';
    $apiQuestions = fetchQuestionsFromAPI($apiCategory, 10);
    
    if ($apiQuestions) {
        storeQuestions($apiQuestions);
        
        // Fetch questions again
        $stmt = $pdo->prepare("
            SELECT id, question_text, correct_answer, incorrect_answers, difficulty 
            FROM quiz_questions 
            WHERE category_id = ? 
            ORDER BY RAND() 
            LIMIT 10
        ");
        $stmt->execute([$category_id]);
        $questions = $stmt->fetchAll();
    }
}

// Format questions for JavaScript
$quizData = [];
foreach ($questions as $question) {
    $incorrectAnswers = json_decode($question['incorrect_answers'], true);
    $quizData[] = [
        'id' => $question['id'],
        'question' => ['text' => $question['question_text']],
        'correctAnswer' => $question['correct_answer'],
        'incorrectAnswers' => $incorrectAnswers,
        'difficulty' => $question['difficulty']
    ];
}

$page_title = $category['name'] . ' Quiz - QuizMaster';
include 'includes/header.php';
?>

<div class="container">
    <div class="quiz-container">
        <div class="quiz-header">
            <h2><?php echo htmlspecialchars($category['name']); ?> Quiz</h2>
            <div class="quiz-progress">
                <div class="quiz-progress-bar" id="progress-bar" style="width: 10%;"></div>
            </div>
            <p>Question <span id="current-question">1</span> of <span id="total-questions"><?php echo count($quizData); ?></span></p>
        </div>
        
        <div class="quiz-question">
            <h3 id="question-text">Loading question...</h3>
            <div class="quiz-options" id="options-container">
                <!-- Options will be loaded here -->
            </div>
        </div>
        
        <div class="quiz-navigation">
            <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Exit Quiz</button>
            <button class="btn" id="next-btn">Next Question</button>
        </div>
    </div>
</div>

<script>
// Pass quiz data to JavaScript
const quizData = <?php echo json_encode($quizData); ?>;
const quizCategoryId = <?php echo $category_id; ?>;

// Update question counter
function updateQuestionCounter() {
    const currentQuestionElement = document.getElementById('current-question');
    if (currentQuestionElement) {
        currentQuestionElement.textContent = currentQuestion + 1;
    }
}

// Override the loadQuestion function to update counter
const originalLoadQuestion = window.loadQuestion;
window.loadQuestion = function() {
    if (originalLoadQuestion) {
        originalLoadQuestion();
    }
    updateQuestionCounter();
};
</script>

<?php include 'includes/footer.php'; ?> 