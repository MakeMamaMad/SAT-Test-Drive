-- Import in MySQL using ISPmanager/phpMyAdmin. Do not expose this file publicly.
CREATE TABLE IF NOT EXISTS registrations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_token CHAR(64) NOT NULL UNIQUE,
 model ENUM('SAT-500','SAT-545') NOT NULL,
 full_name VARCHAR(180) NOT NULL,
 phone VARCHAR(40) NOT NULL,
 email VARCHAR(200) NOT NULL,
 status ENUM('waiting','invited','completed','cancelled') NOT NULL DEFAULT 'waiting',
 consent_at DATETIME NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX queue_order (model,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
