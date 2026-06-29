-- Migración 003: Limpiar hotfixes de runtime
-- Consolida los ALTER hechos por fix_db.php, force_alter.php, fix_users.php y User.php

-- require_password_change en users (antes lo añadía User::__construct() en cada request)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS require_password_change TINYINT(1) DEFAULT 0 AFTER password;

-- billing_day en subscriptions (next_billing_date fue reemplazado en runtime por billing_day)
ALTER TABLE subscriptions
    ADD COLUMN IF NOT EXISTS billing_day INT NOT NULL DEFAULT 1 AFTER billing_cycle;

-- Columnas de crédito en accounts (db_patch.sql tenía "AFTER balance" — incorrecto; es "AFTER initial_balance")
ALTER TABLE accounts
    ADD COLUMN IF NOT EXISTS type ENUM('debit','credit') DEFAULT 'debit' AFTER initial_balance,
    ADD COLUMN IF NOT EXISTS credit_limit DECIMAL(10,2) NULL AFTER type,
    ADD COLUMN IF NOT EXISTS cutoff_date INT NULL AFTER credit_limit,
    ADD COLUMN IF NOT EXISTS payment_date INT NULL AFTER cutoff_date;
