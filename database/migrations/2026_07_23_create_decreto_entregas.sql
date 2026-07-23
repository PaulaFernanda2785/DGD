CREATE TABLE IF NOT EXISTS decreto_entregas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    desastre_id BIGINT UNSIGNED NOT NULL,
    tipo_ajuda_id BIGINT UNSIGNED NOT NULL,
    quantidade DECIMAL(12,2) NOT NULL,
    valor_total DECIMAL(14,2) NOT NULL,
    data_entrega DATE NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_decreto_entregas_desastre FOREIGN KEY (desastre_id) REFERENCES desastres(id) ON DELETE CASCADE,
    CONSTRAINT fk_decreto_entregas_tipo FOREIGN KEY (tipo_ajuda_id) REFERENCES tipos_ajuda(id) ON DELETE RESTRICT,
    INDEX idx_decreto_entregas_desastre (desastre_id),
    INDEX idx_decreto_entregas_tipo (tipo_ajuda_id),
    INDEX idx_decreto_entregas_data (data_entrega)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
