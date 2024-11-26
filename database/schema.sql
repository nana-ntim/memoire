-- Create database with proper character encoding
CREATE DATABASE IF NOT EXISTS memoire;
USE memoire;

-- Users table - Core user information (INITIALIZATION COMPLETE)
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    passwd VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_image VARCHAR(255) DEFAULT NULL
    INDEX idx_email (email)
);

-- Journal Entries Table (INITIALIZATION COMPLETE)
CREATE TABLE JournalEntries (
    entry_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255),
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at DESC)
);

-- Media Attachments Table (INITIALIZATION COMPLETE)
CREATE TABLE EntryMedia (
    media_id INT PRIMARY KEY AUTO_INCREMENT,
    entry_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    media_type VARCHAR(50) NOT NULL,  -- 'image', 'video', etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES JournalEntries(entry_id) ON DELETE CASCADE,
    INDEX idx_entry_media (entry_id)
);

-- Collections table (INITIALIZATION COMPLETE)
CREATE TABLE Collections (
    collection_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_collection_name_per_user (user_id, name),
    INDEX idx_user_collections (user_id)
);

-- Collection Entries junction table (INITIALIZATION COMPLETE)
CREATE TABLE CollectionEntries (
    collection_id INT NOT NULL,
    entry_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (collection_id, entry_id),
    FOREIGN KEY (collection_id) REFERENCES Collections(collection_id) ON DELETE CASCADE,
    FOREIGN KEY (entry_id) REFERENCES JournalEntries(entry_id) ON DELETE CASCADE
);

-- Reflections table
CREATE TABLE Reflections (
    reflection_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    question1 TEXT NOT NULL,
    question2 TEXT NOT NULL,
    question3 TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at DESC)
);

-- Add full-text search index to JournalEntries
ALTER TABLE JournalEntries 
ADD FULLTEXT INDEX ft_entry_content (title, content);