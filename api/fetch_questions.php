<?php
require_once dirname(__DIR__) . '/config/database.php';

// Function to fetch questions from The Trivia API
function fetchQuestionsFromAPI($category = null, $limit = 10) {
    $url = 'https://the-trivia-api.com/v2/questions';
    
    $params = [
        'limit' => $limit
    ];
    
    if ($category) {
        $params['categories'] = $category;
    }
    
    $url .= '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

// Function to map API categories to our database categories
function mapCategoryToDatabase($apiCategory) {
    $categoryMap = [
        'general_knowledge' => 1, // General Knowledge
        'history' => 2, // History
        'science' => 3, // Science
        'geography' => 4, // Geography
        'sports' => 5, // Sports
        'entertainment' => 6, // Entertainment
        'arts_and_literature' => 1, // Map to General Knowledge
        'film_and_tv' => 6, // Map to Entertainment
        'food_and_drink' => 1, // Map to General Knowledge
        'society_and_culture' => 1, // Map to General Knowledge
        'music' => 6, // Map to Entertainment
    ];
    
    return isset($categoryMap[$apiCategory]) ? $categoryMap[$apiCategory] : 1;
}

// Function to store questions in database
function storeQuestions($questions) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO quiz_questions (category_id, question_text, correct_answer, incorrect_answers, difficulty) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $inserted = 0;
    
    foreach ($questions as $question) {
        try {
            $categoryId = mapCategoryToDatabase($question['category']);
            $questionText = $question['question']['text'];
            $correctAnswer = $question['correctAnswer'];
            $incorrectAnswers = json_encode($question['incorrectAnswers']);
            $difficulty = $question['difficulty'];
            
            $stmt->execute([
                $categoryId,
                $questionText,
                $correctAnswer,
                $incorrectAnswers,
                $difficulty
            ]);
            
            $inserted++;
        } catch (Exception $e) {
            // Skip duplicate questions or other errors
            continue;
        }
    }
    
    return $inserted;
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? null;
    $limit = $_POST['limit'] ?? 10;
    
    $questions = fetchQuestionsFromAPI($category, $limit);
    
    if ($questions) {
        $inserted = storeQuestions($questions);
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully fetched and stored $inserted questions",
            'inserted' => $inserted
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch questions from API'
        ]);
    }
} else {
    // For testing purposes, fetch some questions
    $questions = fetchQuestionsFromAPI(null, 5);
    
    if ($questions) {
        $inserted = storeQuestions($questions);
        echo "Successfully stored $inserted questions in the database.";
    } else {
        echo "Failed to fetch questions from API.";
    }
}
?> 