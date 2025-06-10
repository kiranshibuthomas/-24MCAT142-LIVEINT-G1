<?php
require_once 'config/database.php';
require_once 'api/fetch_questions.php';

echo "<h1>QuizMaster Setup</h1>";

try {
    // Check if database connection works
    echo "<p>✓ Database connection successful</p>";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'quiz_categories'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ Database tables exist</p>";
    } else {
        echo "<p>✗ Database tables not found. Please import the schema.sql file first.</p>";
        exit();
    }
    
    // Check if categories exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quiz_categories");
    $categoryCount = $stmt->fetch()['count'];
    
    if ($categoryCount > 0) {
        echo "<p>✓ Quiz categories found ($categoryCount categories)</p>";
    } else {
        echo "<p>✗ No quiz categories found. Please import the schema.sql file first.</p>";
        exit();
    }
    
    // Check if questions exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quiz_questions");
    $questionCount = $stmt->fetch()['count'];
    
    echo "<p>Current questions in database: $questionCount</p>";
    
    if ($questionCount < 50) {
        echo "<p>Fetching questions from The Trivia API...</p>";
        
        // Fetch questions for each category
        $categories = ['general_knowledge', 'history', 'science', 'geography', 'sports', 'entertainment'];
        
        foreach ($categories as $category) {
            echo "<p>Fetching questions for $category...</p>";
            $questions = fetchQuestionsFromAPI($category, 10);
            
            if ($questions) {
                $inserted = storeQuestions($questions);
                echo "<p>✓ Inserted $inserted questions for $category</p>";
            } else {
                echo "<p>✗ Failed to fetch questions for $category</p>";
            }
        }
        
        // Check final count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM quiz_questions");
        $finalCount = $stmt->fetch()['count'];
        echo "<p>✓ Total questions in database: $finalCount</p>";
    } else {
        echo "<p>✓ Sufficient questions already in database</p>";
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li><a href='register.php'>Register a new account</a></li>";
    echo "<li><a href='login.php'>Login to existing account</a></li>";
    echo "<li><a href='dashboard.php'>Go to dashboard</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}
?> 