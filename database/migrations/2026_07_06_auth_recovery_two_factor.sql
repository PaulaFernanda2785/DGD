ALTER TABLE usuarios
    ADD COLUMN two_factor_secret VARCHAR(128) NULL AFTER bloqueado_ate,
    ADD COLUMN two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER two_factor_secret,
    ADD COLUMN two_factor_confirmed_at DATETIME NULL AFTER two_factor_enabled,
    ADD COLUMN two_factor_last_verified_at DATETIME NULL AFTER two_factor_confirmed_at,
    ADD INDEX idx_usuarios_two_factor_enabled (two_factor_enabled);

CREATE TABLE IF NOT EXISTS recuperacoes_senha (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    email_solicitado VARCHAR(180) NOT NULL,
    ip_solicitacao VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    expira_em DATETIME NOT NULL,
    usado_em DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recuperacoes_senha_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_recuperacoes_senha_usuario (usuario_id),
    INDEX idx_recuperacoes_senha_token_hash (token_hash),
    INDEX idx_recuperacoes_senha_expira_em (expira_em),
    INDEX idx_recuperacoes_senha_usado_em (usado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
