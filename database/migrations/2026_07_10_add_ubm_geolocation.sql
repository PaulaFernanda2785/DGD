-- DGD - Geolocalizacao da UBM para a edicao avancada da COMPDEC.
-- Compatibilidade: MySQL/MariaDB.

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ubms'
      AND COLUMN_NAME = 'latitude'
);
SET @sql = IF(@column_exists = 0, 'ALTER TABLE ubms ADD COLUMN latitude DECIMAL(11,8) NULL AFTER descricao', 'SELECT 1');
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
SET @sql = IF(@column_exists = 0, 'ALTER TABLE ubms ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE ubms u
INNER JOIN municipios m ON m.id = u.municipio_id
INNER JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
SET u.latitude = COALESCE(u.latitude, c.latitude),
    u.longitude = COALESCE(u.longitude, c.longitude)
WHERE u.latitude IS NULL
  AND u.longitude IS NULL
  AND c.latitude IS NOT NULL
  AND c.longitude IS NOT NULL;
