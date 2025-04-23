# CalambaGO! Backend

## Overview
CalambaGO! is a backend application designed to support the CalambaGO! frontend. It provides RESTful API endpoints for managing places, users, and feedback submissions. The application is built using PHP and follows a structured MVC architecture.

## Project Structure
```
calambago-backend
├── public
│   ├── index.php          # Entry point for the application
│   ├── api
│   │   ├── places.php     # API for managing places
│   │   ├── users.php      # API for user management
│   │   └── feedback.php    # API for feedback submissions
├── src
│   ├── config
│   │   └── database.php   # Database connection configuration
│   ├── controllers
│   │   ├── PlacesController.php  # Controller for places
│   │   ├── UsersController.php   # Controller for users
│   │   └── FeedbackController.php # Controller for feedback
│   ├── models
│   │   ├── Place.php      # Model for places
│   │   ├── User.php       # Model for users
│   │   └── Feedback.php    # Model for feedback
│   └── helpers
│       └── response.php    # Helper functions for JSON responses
├── vendor                  # Composer dependencies
├── .env                    # Environment variables
├── composer.json           # Composer configuration
├── composer.lock           # Locked versions of dependencies
└── README.md               # Project documentation
```

## Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   cd calambago-backend
   ```

2. Install dependencies using Composer:
   ```
   composer install
   ```

3. Set up your environment variables in the `.env` file. You can copy the `.env.example` file if it exists.

4. Configure your database connection in `src/config/database.php`.

## Usage
- The application can be accessed via the `public/index.php` file.
- API endpoints are available at:
  - `/api/places` for managing places
  - `/api/users` for user-related actions
  - `/api/feedback` for submitting and retrieving feedback

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.