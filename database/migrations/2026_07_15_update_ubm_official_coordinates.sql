-- DGD - Atualizacao das coordenadas oficiais das Unidades Bombeiro Militar.
-- Fonte: unidades_bombeiros_militares (1).sql, recebido em 15/07/2026.
-- Compatibilidade: MySQL 5.7+, MySQL 8+ e MariaDB 10.2+.

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ubms'
      AND COLUMN_NAME = 'latitude'
);
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE ubms ADD COLUMN latitude DECIMAL(11,8) NULL AFTER descricao',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ubms'
      AND COLUMN_NAME = 'longitude'
);
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE ubms ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP TEMPORARY TABLE IF EXISTS tmp_ubm_coordenadas_20260715;
CREATE TEMPORARY TABLE tmp_ubm_coordenadas_20260715 (
    numero TINYINT UNSIGNED NOT NULL,
    latitude DECIMAL(11,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    PRIMARY KEY (numero)
) ENGINE=InnoDB;

INSERT INTO tmp_ubm_coordenadas_20260715 (numero, latitude, longitude) VALUES
    (2,  -1.30294200, -47.92828910),
    (3,  -1.35003540, -48.40310380),
    (4,  -2.42788460, -54.70236550),
    (5,  -5.37016100, -49.13215560),
    (6,  -1.54114920, -48.70047040),
    (7,  -4.26670981, -55.99166312),
    (8,  -3.79133930, -49.67651800),
    (9,  -3.19974040, -52.20367680),
    (10, -8.04527624, -50.01376213),
    (11, -1.67301003, -50.47471048),
    (12, -1.28788240, -48.15153070),
    (13, -0.63827744, -47.33710522),
    (14, -2.91217480, -48.96233120),
    (15, -1.72043470, -48.88210590),
    (16, -6.54678020, -49.85617390),
    (17, -0.85546683, -48.14249217),
    (18, -0.75238250, -48.52259940),
    (19, -1.20771930, -47.17768720),
    (22, -2.24256920, -49.50900460),
    (23, -6.07553154, -49.88468447),
    (24, -1.01590940, -46.77748130),
    (25, -1.36514160, -48.33223340),
    (27, -3.01052990, -47.35744910),
    (28, -1.61085728, -47.47831649),
    (29, -1.88517150, -48.76902120),
    (30, -1.40693000, -48.46482000),
    (31, -6.64167768, -51.96022077),
    (32, -1.52425903, -52.57869956),
    (33, -7.03694966, -55.40621474),
    (34, -7.09780115, -49.93895426);

-- O numero inicial identifica a unidade mesmo quando ha variacao no simbolo ordinal
-- ou no complemento do nome. Todas as vinculacoes municipais da unidade recebem o
-- mesmo ponto geografico oficial do quartel.
UPDATE ubms u
INNER JOIN tmp_ubm_coordenadas_20260715 coordenada
    ON coordenada.numero = CAST(TRIM(u.nome) AS UNSIGNED)
SET u.latitude = coordenada.latitude,
    u.longitude = coordenada.longitude,
    u.atualizado_em = CURRENT_TIMESTAMP
WHERE u.nome LIKE '%GBM%'
  AND (
      u.latitude IS NULL
      OR u.longitude IS NULL
      OR u.latitude <> coordenada.latitude
      OR u.longitude <> coordenada.longitude
  );

DROP TEMPORARY TABLE IF EXISTS tmp_ubm_coordenadas_20260715;
