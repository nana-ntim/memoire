# Memoire - Digital Journal Application

## Overview
Memoire is a web-based journaling application that allows users to document their memories, reflections, and moments of gratitude. Built with PHP and MySQL, it provides a secure and intuitive platform for personal journaling and memory collection.

## Team Members
- Nana Ntim
- Sinam Ametewee
- PraiseGod Osiagor
- Naa Lamiokor Dove

## Features
- **Journal Entries**: Create and manage personal journal entries with images
- **Collections**: Organize entries into custom collections
- **Reflections**: Structured reflection prompts for deeper introspection
- **Gratitude Journal**: Daily gratitude practice with dedicated entries
- **User Authentication**: Secure login and registration system
- **Profile Management**: Customize user profiles and settings

## Technologies Used
- PHP 7.4+
- MySQL 5.7+
- HTML5/CSS3
- JavaScript
- Font Awesome Icons

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependencies)

### Setup Instructions

1. Clone the repository
```bash
git clone https://github.com/yourusername/memoire.git
cd memoire
```

2. Import the database schema
```bash
mysql -u your_username -p your_database_name < schema.sql
```

3. Configure database connection
- Rename `config/dbh.inc.example.php` to `config/dbh.inc.php`
- Update database credentials in `dbh.inc.php`:
```php
$host = 'localhost';
$dbname = 'your_database_name';
$dbusername = 'your_username';
$dbpassword = 'your_password';
```

4. Set up file permissions
```bash
chmod 755 -R uploads/
chmod 755 -R assets/
```

5. Configure web server
- Point document root to the project's public directory
- Ensure proper URL rewriting rules are in place

6. Start the application
- Access through your web browser: `http://localhost/memoire`
- Register a new account to begin using the application

## Directory Structure
```
memoire/
├── assets/          # Static assets (images, fonts)
├── config/          # Configuration files
├── includes/        # PHP includes and modules
├── public/          # Public accessible files
├── styles/          # CSS stylesheets
├── uploads/         # User uploaded content
└── components/      # Reusable UI components
```

## Features Explanation

### Journal
- Create entries with text and images
- View entries in a masonry grid layout
- Edit and delete entries
- Add entries to collections

### Collections
- Create custom collections to organize entries
- Add/remove entries from collections
- View collection details and contents
- Manage collection settings

### Reflections
- Structured reflection prompts
- Track personal growth and insights
- View past reflections chronologically

### Gratitude
- Daily gratitude entries
- Simple and focused interface
- View past gratitude entries

## Security Features
- Password hashing using bcrypt
- SQL injection prevention
- XSS protection
- CSRF protection
- Secure session handling
- Input validation and sanitization
