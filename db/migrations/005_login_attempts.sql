-- Migración 005: Rate limiting de login (anti fuerza bruta)
-- Registra cada intento fallido por email + IP; el login bloquea tras N fallos en la ventana.

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_ip_time (email, ip, attempted_at)
);
