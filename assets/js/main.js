// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#e1e5e9';
                }
            });

            if (!isValid) {
                e.preventDefault();
                showAlert('Please fill in all required fields.', 'error');
            }
        });
    });
});

// Show alert function
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => {
            alertDiv.remove();
        }, 300);
    }, 5000);
}

// Quiz functionality
if (typeof quizData !== 'undefined') {
    let currentQuestion = 0;
    let score = 0;
    let selectedAnswer = null;

    function loadQuestion() {
        const question = quizData[currentQuestion];
        const questionElement = document.getElementById('question-text');
        const optionsContainer = document.getElementById('options-container');
        const progressBar = document.getElementById('progress-bar');

        if (questionElement && optionsContainer) {
            questionElement.textContent = question.question.text;
            
            // Update progress
            const progress = ((currentQuestion + 1) / quizData.length) * 100;
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }

            // Clear previous options
            optionsContainer.innerHTML = '';

            // Create options array with correct and incorrect answers
            const options = [question.correctAnswer, ...question.incorrectAnswers];
            
            // Shuffle options
            for (let i = options.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [options[i], options[j]] = [options[j], options[i]];
            }

            // Create option elements
            options.forEach((option, index) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'quiz-option';
                optionDiv.textContent = option;
                optionDiv.dataset.answer = option;
                
                optionDiv.addEventListener('click', function() {
                    // Remove previous selection
                    document.querySelectorAll('.quiz-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    // Select current option
                    this.classList.add('selected');
                    selectedAnswer = option;
                });

                optionsContainer.appendChild(optionDiv);
            });
        }
    }

    function nextQuestion() {
        if (selectedAnswer) {
            const question = quizData[currentQuestion];
            
            // Check if answer is correct
            if (selectedAnswer === question.correctAnswer) {
                score++;
            }

            // Show correct/incorrect feedback
            const options = document.querySelectorAll('.quiz-option');
            options.forEach(option => {
                if (option.dataset.answer === question.correctAnswer) {
                    option.classList.add('correct');
                } else if (option.dataset.answer === selectedAnswer && selectedAnswer !== question.correctAnswer) {
                    option.classList.add('incorrect');
                }
            });

            // Disable option clicks
            options.forEach(option => {
                option.style.pointerEvents = 'none';
            });

            // Wait 2 seconds then proceed
            setTimeout(() => {
                currentQuestion++;
                selectedAnswer = null;

                if (currentQuestion < quizData.length) {
                    loadQuestion();
                } else {
                    showResults();
                }
            }, 2000);
        } else {
            showAlert('Please select an answer before proceeding.', 'error');
        }
    }

    function showResults() {
        const quizContainer = document.querySelector('.quiz-container');
        if (quizContainer) {
            const percentage = Math.round((score / quizData.length) * 100);
            
            let message = '';
            if (percentage >= 80) {
                message = 'Excellent! You\'re a quiz master!';
            } else if (percentage >= 60) {
                message = 'Good job! You have solid knowledge!';
            } else if (percentage >= 40) {
                message = 'Not bad! Keep learning and improving!';
            } else {
                message = 'Keep practicing! You\'ll get better!';
            }

            quizContainer.innerHTML = `
                <div class="results-container">
                    <div class="results-score">${score}/${quizData.length}</div>
                    <div class="results-message">${message}</div>
                    <div class="results-percentage">${percentage}%</div>
                    <a href="dashboard.php" class="btn">Back to Dashboard</a>
                    <a href="dashboard.php" class="btn btn-secondary">Take Another Quiz</a>
                </div>
            `;

            // Send results to server
            fetch('save_results.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    category_id: quizCategoryId,
                    score: score,
                    total_questions: quizData.length
                })
            });
        }
    }

    // Initialize quiz
    if (document.getElementById('question-text')) {
        loadQuestion();
    }

    // Event listeners for quiz navigation
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) {
        nextBtn.addEventListener('click', nextQuestion);
    }
} 