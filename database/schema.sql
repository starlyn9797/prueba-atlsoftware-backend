CREATE DATABASE IF NOT EXISTS contacts_api
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE contacts_api;

CREATE TABLE IF NOT EXISTS contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS phones (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    contact_id   INT         NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    label        VARCHAR(50) DEFAULT 'mobile',
    CONSTRAINT fk_phones_contact
        FOREIGN KEY (contact_id)
        REFERENCES contacts(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
