-- Creates the database if it does not exist
CREATE DATABASE IF NOT EXISTS memoire_db;
USE memoire_db;

-- Users table
CREATE TABLE Users (
	user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Journal Entries table
CREATE TABLE JournalEntries (
	entry_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_private BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Moods table
CREATE TABLE Moods (
	mood_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mood_rating INT NOT NULL CHECK (mood_rating BETWEEN 1 AND 5),
    mood_description VARCHAR(100),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Gratitude Entries table
CREATE TABLE GratitudeEntries (
	gratitude_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Reflection Prompts table
CREATE TABLE ReflectionPrompts (
	prompt_id INT PRIMARY KEY AUTO_INCREMENT,
    prompt_text TEXT NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reflections table
CREATE TABLE Reflections (
	reflection_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    prompt_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (prompt_id) REFERENCES ReflectionPrompts(prompt_id)
);

-- Media Attachments table
CREATE TABLE MediaAttachments (
	media_id INT PRIMARY KEY AUTO_INCREMENT,
    entry_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES JournalEntries(entry_id) ON DELETE CASCADE
);

-- Tags table
CREATE TABLE TAGS (
	tag_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Entry Tags junction table
CREATE TABLE EntryTags (
	entry_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (entry_id, tag_id),
    FOREIGN KEY (entry_id) REFERENCES JournalEntries(entry_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES Tags(tag_id) ON DELETE CASCADE
);

-- User Preferences table
CREATE TABLE UserPreferences (
	user_id INT PRIMARY KEY,
    theme VARCHAR(20) DEFAULT 'light',
    email_notifications BOOLEAN DEFAULT TRUE,
    privacy_level VARCHAR(20) DEFAULT 'private',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Indexes for better query performance
CREATE INDEX idx_journal_user ON JournalEntries(user_id);
CREATE INDEX idx_moods_user ON Moods(user_id);
CREATE INDEX idx_gratitude_user ON GratitudeEntries(user_id);
CREATE INDEX idx_reflections_user ON Reflections(user_id);
CREATE INDEX idx_media_entry ON MediaAttachments(entry_id);
