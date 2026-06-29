-- Migración 004: Endurecer api_tokens
-- - Guardar el token hasheado (SHA-256, 64 hex) en lugar de texto plano
-- - Añadir expiración (expires_at)
-- Nota: los tokens previos (en texto plano) quedan invalidados; basta volver a iniciar sesión.

ALTER TABLE api_tokens
    CHANGE COLUMN IF EXISTS token token_hash VARCHAR(64) NOT NULL,
    ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL AFTER last_used_at;
