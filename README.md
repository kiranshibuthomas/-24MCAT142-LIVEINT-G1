# QuizMaster - Interactive Quiz Application

A responsive web application for taking quizzes with user authentication, payment integration, and real-time quiz functionality.

## Features

- **User Authentication**: Secure login and registration system
- **Session Management**: Proper session handling and security
- **Quiz Categories**: Multiple quiz categories with different pricing
- **Payment Integration**: Simulated Razorpay payment gateway
- **Real-time Quiz Interface**: Interactive quiz taking experience
- **Progress Tracking**: Visual progress indicators and scoring
- **Results Management**: Detailed quiz results and history
- **Responsive Design**: Mobile-friendly interface
- **API Integration**: Fetches questions from The Trivia API

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Payment**: Razorpay (simulated)
- **API**: The Trivia API (https://the-trivia-api.com)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome

## File Structure

```
quiz/
├── index.php                 # Main entry point
├── login.php                 # User login page
├── register.php              # User registration page
├── logout.php                # Logout functionality
├── dashboard.php             # Main dashboard
├── take_quiz.php             # Quiz taking interface
├── profile.php               # User profile page
├── save_results.php          # Save quiz results
├── process_payment.php       # Payment processing
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   └── js/
│       └── main.js           # Main JavaScript
├── api/
│   └── fetch_questions.php   # API integration
├── database/
│   └── schema.sql            # Database schema
└── README.md                 # This file
```

## Installation

### Prerequisites

- XAMPP, WAMP, or similar local server environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled

### Setup Instructions

1. **Clone/Download the Project**
   ```bash
   # Place the project in your web server directory
   # For XAMPP: C:\xampp\htdocs\quiz
   # For WAMP: C:\wamp\www\quiz
   ```

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `quiz_app`
   - Import the database schema from `database/schema.sql`

3. **Configuration**
   - Open `config/database.php`
   - Update database credentials if needed:
     ```php
     $host = 'localhost';
     $dbname = 'quiz_app';
     $username = 'root';
     $password = '';
     ```

4. **Start the Application**
   - Start your web server (Apache)
   - Start MySQL service
   - Navigate to `http://localhost/quiz`

5. **Initial Setup**
   - Register a new account
   - The system will automatically fetch questions from The Trivia API
   - Purchase quizzes to start taking them

## Usage

### For Users

1. **Registration/Login**
   - Create a new account or login with existing credentials
   - All passwords are securely hashed

2. **Dashboard**
   - View your quiz statistics
   - Browse available quiz categories
   - See purchased vs unpurchased quizzes

3. **Purchasing Quizzes**
   - Click "Purchase" on any quiz category
   - Complete the payment process (simulated)
   - Access the quiz immediately after purchase

4. **Taking Quizzes**
   - Select a purchased quiz
   - Answer 10 multiple-choice questions
   - View real-time feedback and progress
   - See final results and percentage

5. **Profile Management**
   - View quiz history and performance
   - Check purchase history
   - Monitor progress over time

### For Developers

1. **Adding New Quiz Categories**
   - Add entries to the `quiz_categories` table
   - Update the category mapping in `api/fetch_questions.php`

2. **Customizing Payment Integration**
   - Replace the simulated payment in `process_payment.php`
   - Integrate with actual Razorpay API
   - Update payment verification logic

3. **Styling Customization**
   - Modify `assets/css/style.css` for visual changes
   - Update color schemes and layouts as needed

4. **API Integration**
   - The system automatically fetches questions from The Trivia API
   - Questions are cached in the local database
   - Customize question fetching logic in `api/fetch_questions.php`

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Session Security**: Proper session management and validation
- **Input Validation**: Server-side validation for all user inputs
- **XSS Prevention**: HTML escaping for all output

## API Integration

The application integrates with [The Trivia API](http://the-trivia-api.com) to fetch quiz questions:

- **Endpoint**: `https://the-trivia-api.com/v2/questions`
- **Categories**: General Knowledge, History, Science, Geography, Sports, Entertainment
- **Question Types**: Multiple choice with 4 options
- **Difficulty Levels**: Easy, Medium, Hard

## Payment Integration

The application includes a simulated Razorpay payment system:

- **Payment Flow**: Purchase → Payment Modal → Processing → Access
- **Real Integration**: Replace simulation with actual Razorpay SDK
- **Security**: Payment verification and transaction logging

## Responsive Design

The application is fully responsive and works on:

- **Desktop**: Full-featured interface
- **Tablet**: Optimized layout
- **Mobile**: Touch-friendly design with mobile navigation

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database `quiz_app` exists

2. **Questions Not Loading**
   - Check internet connection for API access
   - Verify The Trivia API is accessible
   - Check PHP cURL extension is enabled

3. **Payment Not Working**
   - This is a simulation - replace with real Razorpay integration
   - Check browser console for JavaScript errors

4. **Styling Issues**
   - Clear browser cache
   - Check CSS file path in `includes/header.php`
   - Verify Font Awesome CDN is accessible

### Performance Optimization

- Enable PHP OPcache for better performance
- Use MySQL query caching
- Optimize images and assets
- Consider CDN for static assets

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Check the troubleshooting section
- Review the code comments
- Test with different browsers and devices

## Future Enhancements

- Real Razorpay integration
- User leaderboards
- Quiz sharing functionality
- Advanced analytics
- Multi-language support
- Quiz creation tools
- Social media integration 