# Memoire: Your Digital Safe Space 📔

Welcome to Memoire – where your thoughts find their home. This isn't just another journaling app; it's your digital sanctuary for capturing life's moments, tracking your emotional journey, and reflecting on your personal growth.

## 🌟 Why Memoire?

Ever wished you had a place where you could:
- Pour out your thoughts without judgment
- Track how your moods change over time
- Look back on your growth journey
- Keep your memories safe and organized

That's exactly why we built Memoire. We believe everyone deserves a private space to document their journey, track their growth, and reflect on their experiences.

## ✨ Features

- **Secure Journaling**: Your thoughts, safely encrypted
- **Mood Tracking**: Understand your emotional patterns
- **Media Support**: Attach photos, sketches, or voice notes
- **Reflection Prompts**: Get inspired when words don't flow easily
- **Rich Text Editor**: Format your entries beautifully
- **Privacy First**: Your data belongs to you, always

## 🛠 Tech Stack

### Core
- **Frontend**: HTML5, SCSS, JavaScript
- **Backend**: PHP 8.1+
- **Database**: MySQL 8.0+
- **Server**: Apache 2.4+

### Development Tools
- **CSS Preprocessor**: SASS
- **Version Control**: Git
- **Dependency Management**: Composer
- **Build Tools**: Node.js & npm

## 🚀 Getting Started

### Prerequisites
1. PHP 8.1 or higher
2. MySQL 8.0 or higher
3. Apache web server
4. Node.js and npm
5. Composer

### Quick Start
```bash
# Clone the repository
git clone https://github.com/your-username/memoire.git

# Navigate to project directory
cd memoire

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install

# Create your environment file
cp .env.example .env

# Generate application key
php scripts/generate-key.php

# Set up your database
mysql -u root -p
source database/schema.sql

# Build frontend assets
npm run build

# Start development server
php -S localhost:8000 -t public
```

Visit `http://localhost:8000` and you're ready to start journaling! 🎉

## 📁 Project Structure

Our project structure is designed to be intuitive and scalable. Here's why everything is where it is:

### At The Root
```
memoire/
```
This is your project's home. Everything starts here.

### Public Files
```
📁 public/
```
The only folder accessible through the web. It's like the receptionist of our app – it handles all incoming requests and directs them appropriately.

### Source Files
```
📁 src/
```
Where all our frontend magic happens. It's organized to separate concerns:
- `js/` - JavaScript modules and features
- `scss/` - Styled components and layouts

### Application Core
```
📁 app/
```
The brain of our operation. Contains all PHP logic neatly organized into:
- `core/` - Essential functions and classes
- `models/` - Data structures
- `controllers/` - Request handlers
- `services/` - Business logic

### Views
```
📁 views/
```
The face of our application. Templates are organized by:
- `layouts/` - Base page structures
- `components/` - Reusable parts
- `pages/` - Individual page templates

### Everything Else
- `assets/` - Original images and fonts
- `storage/` - User uploads and generated files
- `tests/` - Because we care about quality
- `docs/` - Project documentation
- `scripts/` - Helpful development tools
- `README.md/` - Detailed account of the project
- `FILE_STRUCTURE.md` - Full structure of the project

## 🤝 Contributing

We love contributions! If you'd like to help improve Memoire:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Development Guidelines

### Code Style
- Use meaningful variable names
- Follow PSR-12 for PHP code
- Use BEM methodology for CSS
- Document complex functions
- Write tests for new features

### Git Commit Messages
- Use clear, descriptive messages
- Start with a verb (Add, Update, Fix, etc.)
- Reference issue numbers when applicable

## 🔐 Security

Security is our top priority. We:
- Encrypt all journal entries
- Use secure password hashing
- Implement CSRF protection
- Regular security updates
- Maintain secure session handling

Found a security issue? Please email ntim.nana@icloud.com.

## 🎯 Roadmap

Here's what's coming next:
- [ ] Heavy PHP functions for encryption
- [ ] Connecting everything to our database
- [ ] Feature building
- [ ] Creating timeless screen flows

## 💡 Need Help?

- 🐛 Report issues on [GitHub](https://github.com/nana-ntim/memoire/issues)
- 📧 Email us at ntim.nana@icloud.com

## ✨ Special Thanks

A huge thank you to everyone on the team making this possible.
