-- Event Invite System - Schema Completo

CREATE DATABASE IF NOT EXISTS event_invite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_invite;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    location_address TEXT,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    image_path VARCHAR(255) NULL,
    custom_message TEXT,
    status ENUM('draft', 'published', 'cancelled') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_event_date (event_date)
);

CREATE TABLE guests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    unique_token CHAR(64) NOT NULL UNIQUE,
    qrcode_path VARCHAR(255) NULL,
    status ENUM('pending', 'confirmed', 'declined') DEFAULT 'pending',
    email_sent_at TIMESTAMP NULL,
    whatsapp_sent_at TIMESTAMP NULL,
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_email_event (event_id, email),
    INDEX idx_token (unique_token),
    INDEX idx_status (event_id, status)
);

CREATE TABLE invite_clicks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guest_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
    INDEX idx_guest_clicked (guest_id, clicked_at)
);

CREATE TABLE email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guest_id INT UNSIGNED NOT NULL,
    type ENUM('invite', 'reminder', 'confirmation') NOT NULL,
    status ENUM('sent', 'failed', 'opened') DEFAULT 'sent',
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE
);

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(50) NOT NULL DEFAULT 'default',
    payload JSON NOT NULL,
    attempts TINYINT UNSIGNED DEFAULT 0,
    reserved_at TIMESTAMP NULL,
    available_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_queue_available (queue, available_at),
    INDEX idx_reserved (reserved_at)
);

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payload JSON NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
