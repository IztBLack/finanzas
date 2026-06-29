-- Actualización para Cuentas de Crédito
ALTER TABLE accounts 
    ADD COLUMN type ENUM('debit', 'credit') DEFAULT 'debit' AFTER balance,
    ADD COLUMN credit_limit DECIMAL(10,2) NULL AFTER type,
    ADD COLUMN cutoff_date INT NULL AFTER credit_limit,
    ADD COLUMN payment_date INT NULL AFTER cutoff_date;

-- Crear tabla de Suscripciones
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    account_id INT NOT NULL,
    billing_cycle ENUM('monthly', 'yearly') DEFAULT 'monthly',
    next_billing_date DATE NOT NULL,
    status ENUM('active', 'paused') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);
