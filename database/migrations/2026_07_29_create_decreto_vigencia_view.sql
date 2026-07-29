-- Corrige instalações existentes e cria a fonte dinâmica do status de vigência.
-- A migração é idempotente e pode ser executada mesmo quando as colunas já existem.

SET @database_name = DATABASE();

SET @add_publicacao_sql = IF(
    (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'desastres'
          AND COLUMN_NAME = 'data_publicacao_decreto'
    ) = 0,
    'ALTER TABLE desastres ADD COLUMN data_publicacao_decreto DATE NULL AFTER data_decreto_municipal',
    'SET @data_publicacao_decreto_exists = 1'
);
PREPARE add_publicacao_statement FROM @add_publicacao_sql;
EXECUTE add_publicacao_statement;
DEALLOCATE PREPARE add_publicacao_statement;

SET @add_vigencia_sql = IF(
    (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'desastres'
          AND COLUMN_NAME = 'dias_vigencia_decreto'
    ) = 0,
    'ALTER TABLE desastres ADD COLUMN dias_vigencia_decreto SMALLINT UNSIGNED NULL AFTER data_publicacao_decreto',
    'SET @dias_vigencia_decreto_exists = 1'
);
PREPARE add_vigencia_statement FROM @add_vigencia_sql;
EXECUTE add_vigencia_statement;
DEALLOCATE PREPARE add_vigencia_statement;

CREATE OR REPLACE VIEW vw_decretos_vigencia AS
SELECT
    d.id,
    d.data_publicacao_decreto,
    d.dias_vigencia_decreto,
    CASE
        WHEN d.data_publicacao_decreto IS NULL OR d.dias_vigencia_decreto IS NULL THEN 'NAO_INFORMADO'
        WHEN CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto) > 1 THEN 'VIGENTE'
        WHEN CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto) = 1 THEN 'VENCE_HOJE'
        ELSE 'VENCIDO'
    END AS vigencia_status_codigo,
    CASE
        WHEN d.data_publicacao_decreto IS NULL OR d.dias_vigencia_decreto IS NULL THEN 'Aguardando dados'
        WHEN CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto) > 1 THEN 'Decreto vigente'
        WHEN CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto) = 1 THEN 'Vence hoje'
        ELSE 'Decreto vencido'
    END AS vigencia_status,
    CASE
        WHEN d.data_publicacao_decreto IS NULL OR d.dias_vigencia_decreto IS NULL THEN NULL
        WHEN CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto) >= 1
            THEN CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto)
        ELSE CAST(d.dias_vigencia_decreto AS SIGNED) - DATEDIFF(CURRENT_DATE, d.data_publicacao_decreto) - 1
    END AS vigencia_dias_restantes,
    CASE
        WHEN d.data_publicacao_decreto IS NULL OR d.dias_vigencia_decreto IS NULL THEN NULL
        ELSE ADDDATE(d.data_publicacao_decreto, CAST(d.dias_vigencia_decreto AS SIGNED) - 1)
    END AS data_fim_vigencia
FROM desastres d
WHERE d.excluido_em IS NULL;
