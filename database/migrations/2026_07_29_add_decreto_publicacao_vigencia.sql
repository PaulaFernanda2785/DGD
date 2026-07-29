-- Adiciona informações próprias de publicação e vigência do decreto.
-- Compatível com MySQL 8 e MariaDB usados no ambiente local e na Hostinger.

ALTER TABLE desastres
    ADD COLUMN data_publicacao_decreto DATE NULL AFTER data_decreto_municipal,
    ADD COLUMN dias_vigencia_decreto SMALLINT UNSIGNED NULL AFTER data_publicacao_decreto;
