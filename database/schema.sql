-- DGD - Sistema de Gerenciamento de Desastres
-- Schema principal para MySQL/MariaDB
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS vw_painel_resumo;
DROP VIEW IF EXISTS vw_decretos_listagem;

DROP TABLE IF EXISTS auditoria_logs;
DROP TABLE IF EXISTS desastre_historico_status;
DROP TABLE IF EXISTS desastre_anexos;
DROP TABLE IF EXISTS desastres;
DROP TABLE IF EXISTS sequencias_protocolos;
DROP TABLE IF EXISTS configuracoes_sistema;
DROP TABLE IF EXISTS tipos_anexo;
DROP TABLE IF EXISTS status_envio_pge;
DROP TABLE IF EXISTS status_recurso;
DROP TABLE IF EXISTS status_reconhecimento;
DROP TABLE IF EXISTS status_homologacao;
DROP TABLE IF EXISTS tipos_decreto;
DROP TABLE IF EXISTS cobrade_subtipos;
DROP TABLE IF EXISTS cobrade_tipos;
DROP TABLE IF EXISTS cobrade_subgrupos;
DROP TABLE IF EXISTS cobrade_grupos;
DROP TABLE IF EXISTS ubms;
DROP TABLE IF EXISTS compdecs;
DROP TABLE IF EXISTS municipios;
DROP TABLE IF EXISTS login_logs;
DROP TABLE IF EXISTS usuarios_sessoes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS perfil_permissoes;
DROP TABLE IF EXISTS permissoes;
DROP TABLE IF EXISTS perfis;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE perfis (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(60) NOT NULL,
    descricao TEXT NULL,
    nivel_acesso TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissoes (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    modulo VARCHAR(60) NOT NULL,
    acao VARCHAR(60) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_permissoes_modulo (modulo),
    INDEX idx_permissoes_acao (acao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE perfil_permissoes (
    perfil_id TINYINT UNSIGNED NOT NULL,
    permissao_id SMALLINT UNSIGNED NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (perfil_id, permissao_id),
    CONSTRAINT fk_perfil_permissoes_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_perfil_permissoes_permissao FOREIGN KEY (permissao_id) REFERENCES permissoes(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perfil_id TINYINT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    cpf VARCHAR(14) NULL UNIQUE,
    telefone VARCHAR(30) NULL,
    cargo VARCHAR(120) NULL,
    instituicao VARCHAR(150) NOT NULL DEFAULT 'CEDEC-PA',
    senha_hash VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    trocar_senha_proximo_acesso TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_acesso_em DATETIME NULL,
    tentativas_login_falhas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_ate DATETIME NULL,
    two_factor_secret VARCHAR(128) NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_confirmed_at DATETIME NULL,
    two_factor_last_verified_at DATETIME NULL,
    criado_por BIGINT UNSIGNED NULL,
    atualizado_por BIGINT UNSIGNED NULL,
    excluido_por BIGINT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    excluido_em DATETIME NULL,
    CONSTRAINT fk_usuarios_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_usuarios_atualizado_por FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_usuarios_excluido_por FOREIGN KEY (excluido_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_usuarios_perfil (perfil_id),
    INDEX idx_usuarios_ativo (ativo),
    INDEX idx_usuarios_nome (nome),
    INDEX idx_usuarios_email (email),
    INDEX idx_usuarios_two_factor_enabled (two_factor_enabled),
    INDEX idx_usuarios_excluido_em (excluido_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recuperacoes_senha (
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

CREATE TABLE usuarios_sessoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    session_id_hash CHAR(64) NOT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    iniciou_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expira_em DATETIME NULL,
    encerrada_em DATETIME NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_usuarios_sessoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_usuarios_sessoes_hash (session_id_hash),
    INDEX idx_usuarios_sessoes_usuario (usuario_id),
    INDEX idx_usuarios_sessoes_ativa (ativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    email_informado VARCHAR(180) NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    motivo_falha VARCHAR(120) NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_login_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_login_logs_usuario (usuario_id),
    INDEX idx_login_logs_email (email_informado),
    INDEX idx_login_logs_sucesso (sucesso),
    INDEX idx_login_logs_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE municipios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_ibge INT UNSIGNED NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    uf CHAR(2) NOT NULL DEFAULT 'PA',
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_municipios_nome_uf (nome, uf),
    INDEX idx_municipios_nome (nome),
    INDEX idx_municipios_uf (uf),
    INDEX idx_municipios_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE compdecs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    municipio_codigo VARCHAR(7) NULL,
    municipio VARCHAR(150) NOT NULL,
    regiao_integracao VARCHAR(100) NULL,
    tem_compdec TINYINT(1) NOT NULL DEFAULT 0,
    prefeito VARCHAR(180) NULL,
    ubm_nome VARCHAR(180) NULL,
    coordenador VARCHAR(180) NULL,
    foto_coordenador VARCHAR(255) NULL,
    telefone VARCHAR(80) NULL,
    email VARCHAR(180) NULL,
    endereco VARCHAR(255) NULL,
    data_atualizacao VARCHAR(40) NULL,
    latitude DECIMAL(11,8) NULL,
    longitude DECIMAL(11,8) NULL,
    fonte_hash CHAR(64) NULL,
    sincronizado_em DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_compdecs_municipio (municipio),
    UNIQUE KEY uq_compdecs_municipio_codigo (municipio_codigo),
    INDEX idx_compdecs_regiao_integracao (regiao_integracao),
    INDEX idx_compdecs_tem_compdec (tem_compdec),
    INDEX idx_compdecs_ubm_nome (ubm_nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ubms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    municipio_id INT UNSIGNED NULL,
    codigo VARCHAR(50) NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    latitude DECIMAL(11,8) NULL,
    longitude DECIMAL(11,8) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ubms_municipio FOREIGN KEY (municipio_id) REFERENCES municipios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_ubms_municipio (municipio_id),
    INDEX idx_ubms_nome (nome),
    INDEX idx_ubms_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cobrade_grupos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cobrade_subgrupos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(30) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cobrade_subgrupos_grupo FOREIGN KEY (grupo_id) REFERENCES cobrade_grupos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_cobrade_subgrupos_codigo_grupo (grupo_id, codigo),
    INDEX idx_cobrade_subgrupos_grupo (grupo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cobrade_tipos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subgrupo_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(40) NOT NULL,
    nome VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cobrade_tipos_subgrupo FOREIGN KEY (subgrupo_id) REFERENCES cobrade_subgrupos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_cobrade_tipos_codigo_subgrupo (subgrupo_id, codigo),
    INDEX idx_cobrade_tipos_subgrupo (subgrupo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cobrade_subtipos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    simbologia VARCHAR(255) NULL,
    origem VARCHAR(120) NULL,
    versao VARCHAR(30) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cobrade_subtipos_tipo FOREIGN KEY (tipo_id) REFERENCES cobrade_tipos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_cobrade_subtipos_tipo (tipo_id),
    INDEX idx_cobrade_subtipos_codigo (codigo),
    INDEX idx_cobrade_subtipos_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipos_decreto (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(120) NOT NULL,
    duracao_padrao_dias SMALLINT UNSIGNED NULL,
    ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_homologacao (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(120) NOT NULL,
    classe_css VARCHAR(60) NULL,
    ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_reconhecimento (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    classe_css VARCHAR(60) NULL,
    ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_recurso (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    classe_css VARCHAR(60) NULL,
    ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_envio_pge (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    classe_css VARCHAR(60) NULL,
    ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipos_anexo (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    obrigatorio TINYINT(1) NOT NULL DEFAULT 0,
    ordem TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE configuracoes_sistema (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    tipo_dado VARCHAR(30) NOT NULL DEFAULT 'string',
    descricao TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sequencias_protocolos (
    ano SMALLINT UNSIGNED PRIMARY KEY,
    ultimo_sequencial INT UNSIGNED NOT NULL DEFAULT 0,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE desastres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protocolo_dgd VARCHAR(120) NOT NULL UNIQUE,
    protocolo_ano SMALLINT UNSIGNED NOT NULL,
    protocolo_sequencial INT UNSIGNED NOT NULL,
    municipio_id INT UNSIGNED NOT NULL,
    ubm_id INT UNSIGNED NULL,
    compdec_id INT UNSIGNED NULL,
    compdec_regiao_integracao VARCHAR(100) NULL,
    compdec_prefeito VARCHAR(180) NULL,
    compdec_coordenador VARCHAR(180) NULL,
    compdec_telefone VARCHAR(80) NULL,
    compdec_email VARCHAR(180) NULL,
    tipo_decreto_id TINYINT UNSIGNED NOT NULL,
    cobrade_subtipo_id INT UNSIGNED NOT NULL,
    data_desastre DATE NOT NULL,
    protocolo_s2id VARCHAR(80) NULL,
    numero_decreto_municipal VARCHAR(80) NULL,
    data_decreto_municipal DATE NULL,
    numero_decreto_homologacao_estadual VARCHAR(80) NULL,
    data_decreto_homologacao DATE NULL,
    homologacao_status_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    reconhecimento_status_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    protocolo_pae_pge VARCHAR(100) NULL,
    data_envio_pge DATE NULL,
    status_envio_pge_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    data_conclusao_pge DATE NULL,
    status_envio_pge_antes_homologacao_id TINYINT UNSIGNED NULL,
    data_conclusao_pge_antes_homologacao DATE NULL,
    analista_id BIGINT UNSIGNED NULL,
    recurso_resposta_status_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    recurso_reconstrucao_status_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    numero_obitos INT UNSIGNED NOT NULL DEFAULT 0,
    numero_feridos INT UNSIGNED NOT NULL DEFAULT 0,
    numero_enfermos INT UNSIGNED NOT NULL DEFAULT 0,
    numero_desabrigados INT UNSIGNED NOT NULL DEFAULT 0,
    numero_desalojados INT UNSIGNED NOT NULL DEFAULT 0,
    numero_outros_afetados INT UNSIGNED NOT NULL DEFAULT 0,
    total_afetados INT UNSIGNED GENERATED ALWAYS AS (
        numero_obitos + numero_feridos + numero_enfermos + numero_desabrigados + numero_desalojados + numero_outros_afetados
    ) STORED,
    observacoes TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_por BIGINT UNSIGNED NULL,
    atualizado_por BIGINT UNSIGNED NULL,
    excluido_por BIGINT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    excluido_em DATETIME NULL,
    CONSTRAINT fk_desastres_municipio FOREIGN KEY (municipio_id) REFERENCES municipios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_ubm FOREIGN KEY (ubm_id) REFERENCES ubms(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastres_compdec FOREIGN KEY (compdec_id) REFERENCES compdecs(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastres_tipo_decreto FOREIGN KEY (tipo_decreto_id) REFERENCES tipos_decreto(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_cobrade_subtipo FOREIGN KEY (cobrade_subtipo_id) REFERENCES cobrade_subtipos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_homologacao FOREIGN KEY (homologacao_status_id) REFERENCES status_homologacao(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_reconhecimento FOREIGN KEY (reconhecimento_status_id) REFERENCES status_reconhecimento(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_status_envio_pge FOREIGN KEY (status_envio_pge_id) REFERENCES status_envio_pge(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_status_pge_backup_homologacao FOREIGN KEY (status_envio_pge_antes_homologacao_id) REFERENCES status_envio_pge(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastres_analista FOREIGN KEY (analista_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastres_recurso_resposta FOREIGN KEY (recurso_resposta_status_id) REFERENCES status_recurso(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_recurso_reconstrucao FOREIGN KEY (recurso_reconstrucao_status_id) REFERENCES status_recurso(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastres_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastres_atualizado_por FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastres_excluido_por FOREIGN KEY (excluido_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    UNIQUE KEY uq_desastres_ano_sequencial (protocolo_ano, protocolo_sequencial),
    INDEX idx_desastres_municipio (municipio_id),
    INDEX idx_desastres_ubm (ubm_id),
    INDEX idx_desastres_compdec (compdec_id),
    INDEX idx_desastres_cobrade_subtipo (cobrade_subtipo_id),
    INDEX idx_desastres_data_desastre (data_desastre),
    INDEX idx_desastres_data_decreto_municipal (data_decreto_municipal),
    INDEX idx_desastres_data_envio_pge (data_envio_pge),
    INDEX idx_desastres_data_conclusao_pge (data_conclusao_pge),
    INDEX idx_desastres_status_pge_backup_homologacao (status_envio_pge_antes_homologacao_id),
    INDEX idx_desastres_homologacao (homologacao_status_id),
    INDEX idx_desastres_reconhecimento (reconhecimento_status_id),
    INDEX idx_desastres_status_envio_pge (status_envio_pge_id),
    INDEX idx_desastres_analista (analista_id),
    INDEX idx_desastres_ativo_excluido (ativo, excluido_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE desastre_anexos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    desastre_id BIGINT UNSIGNED NOT NULL,
    tipo_anexo_id TINYINT UNSIGNED NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_armazenado VARCHAR(500) NOT NULL,
    extensao VARCHAR(10) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL,
    hash_sha256 CHAR(64) NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    enviado_por BIGINT UNSIGNED NULL,
    excluido_por BIGINT UNSIGNED NULL,
    enviado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    excluido_em DATETIME NULL,
    CONSTRAINT fk_desastre_anexos_desastre FOREIGN KEY (desastre_id) REFERENCES desastres(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastre_anexos_tipo FOREIGN KEY (tipo_anexo_id) REFERENCES tipos_anexo(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastre_anexos_enviado_por FOREIGN KEY (enviado_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desastre_anexos_excluido_por FOREIGN KEY (excluido_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_desastre_anexos_desastre (desastre_id),
    INDEX idx_desastre_anexos_tipo (tipo_anexo_id),
    INDEX idx_desastre_anexos_ativo (ativo),
    INDEX idx_desastre_anexos_excluido_em (excluido_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE desastre_historico_status (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    desastre_id BIGINT UNSIGNED NOT NULL,
    campo VARCHAR(80) NOT NULL,
    valor_anterior VARCHAR(255) NULL,
    valor_novo VARCHAR(255) NULL,
    usuario_id BIGINT UNSIGNED NULL,
    justificativa TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_desastre_historico_desastre FOREIGN KEY (desastre_id) REFERENCES desastres(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desastre_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_desastre_historico_desastre (desastre_id),
    INDEX idx_desastre_historico_campo (campo),
    INDEX idx_desastre_historico_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auditoria_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    perfil_codigo VARCHAR(30) NULL,
    modulo VARCHAR(80) NOT NULL,
    acao VARCHAR(120) NOT NULL,
    entidade VARCHAR(120) NULL,
    entidade_id BIGINT UNSIGNED NULL,
    valor_anterior JSON NULL,
    valor_novo JSON NULL,
    justificativa TEXT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_auditoria_usuario (usuario_id),
    INDEX idx_auditoria_modulo (modulo),
    INDEX idx_auditoria_acao (acao),
    INDEX idx_auditoria_entidade (entidade, entidade_id),
    INDEX idx_auditoria_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fallback documentado:
-- Se o MariaDB/MySQL da hospedagem nao aceitar coluna gerada em desastres.total_afetados,
-- substitua a coluna gerada por INT UNSIGNED NOT NULL DEFAULT 0 e preserve o calculo no backend.
