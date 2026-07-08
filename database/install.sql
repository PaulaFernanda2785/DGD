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

﻿-- DGD - Seeds iniciais
-- Nao contem senha real. Gere o hash do Admin inicial com password_hash() antes da implantacao.

SET NAMES utf8mb4;

INSERT INTO perfis (id, codigo, nome, descricao, nivel_acesso) VALUES
(1, 'ADMIN', 'Admin', 'Administracao geral do sistema.', 3),
(2, 'GESTOR', 'Gestor', 'Gestao operacional dos desastres, decretos e status criticos.', 2),
(3, 'OPERADOR', 'Operador', 'Cadastro inicial e consulta operacional controlada.', 1);

INSERT INTO permissoes (id, codigo, modulo, acao, descricao) VALUES
(1, 'painel.visualizar', 'painel', 'visualizar', 'Visualizar painel.'),
(2, 'decretos.visualizar', 'decretos', 'visualizar', 'Visualizar listagem de decretos/desastres.'),
(3, 'decretos.detalhe', 'decretos', 'detalhe', 'Visualizar detalhe do desastre.'),
(4, 'decretos.criar', 'decretos', 'criar', 'Cadastrar novo desastre.'),
(5, 'decretos.editar', 'decretos', 'editar', 'Editar desastre.'),
(6, 'decretos.excluir', 'decretos', 'excluir', 'Excluir logicamente desastre.'),
(7, 'decretos.editar_status_listagem', 'decretos', 'editar_status_listagem', 'Editar status diretamente na listagem.'),
(8, 'anexos.upload', 'anexos', 'upload', 'Enviar anexos.'),
(9, 'anexos.excluir', 'anexos', 'excluir', 'Excluir logicamente anexos.'),
(10, 'usuarios.visualizar', 'usuarios', 'visualizar', 'Listar usuarios.'),
(11, 'usuarios.criar', 'usuarios', 'criar', 'Criar usuarios.'),
(12, 'usuarios.editar', 'usuarios', 'editar', 'Editar usuarios.'),
(13, 'usuarios.excluir', 'usuarios', 'excluir', 'Excluir logicamente usuarios.'),
(14, 'senha.alterar_propria', 'senha', 'alterar_propria', 'Alterar a propria senha.'),
(15, 'auditoria.visualizar', 'auditoria', 'visualizar', 'Visualizar auditoria.'),
(16, 'dominios.administrar', 'dominios', 'administrar', 'Administrar tabelas de dominio.'),
(17, 'compdecs.visualizar', 'compdecs', 'visualizar', 'Visualizar COMPDECs.'),
(18, 'compdecs.editar', 'compdecs', 'editar', 'Editar COMPDECs.');

INSERT INTO perfil_permissoes (perfil_id, permissao_id)
SELECT 1, id FROM permissoes;

INSERT INTO perfil_permissoes (perfil_id, permissao_id) VALUES
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 14), (2, 15), (2, 17), (2, 18);

INSERT INTO perfil_permissoes (perfil_id, permissao_id) VALUES
(3, 1), (3, 2), (3, 3), (3, 4), (3, 8), (3, 14);

INSERT INTO tipos_decreto (id, codigo, nome, duracao_padrao_dias, ordem) VALUES
(1, 'SITUACAO_EMERGENCIA', 'Situação de Emergência', 180, 1),
(2, 'ESTADO_CALAMIDADE_PUBLICA', 'Estado de Calamidade Pública', 180, 2);

INSERT INTO status_homologacao (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Não registrado', 'status-neutro', 1),
(2, 'NAO_SOLICITADO', 'Não solicitado', 'status-neutro', 2),
(3, 'SOLICITADO', 'Solicitado', 'status-info', 3),
(4, 'PENDENTE_DESPACHO', 'Pendente - despacho', 'status-alerta', 4),
(5, 'PENDENTE_PARECER', 'Pendente - parecer', 'status-alerta', 5),
(6, 'EM_ANALISE_DGD', 'Em análise DGD', 'status-info', 6),
(7, 'ENVIADO_PGE', 'Enviado à PGE', 'status-info', 7),
(8, 'HOMOLOGADO', 'Homologado', 'status-sucesso', 8),
(9, 'NAO_HOMOLOGADO', 'Não homologado', 'status-erro', 9);

INSERT INTO status_reconhecimento (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Não registrado', 'status-neutro', 1),
(2, 'SOLICITADO', 'Solicitado', 'status-info', 2),
(3, 'AGUARDANDO_ANALISE', 'Aguardando análise', 'status-alerta', 3),
(4, 'EM_ANALISE_SEDEC', 'Em análise SEDEC', 'status-info', 4),
(5, 'ENVIADO_RECONHECIMENTO', 'Enviado para reconhecimento', 'status-info', 5),
(6, 'AGUARDANDO_AJUSTE_MUNICIPIO', 'Aguardando ajuste município', 'status-alerta', 6),
(7, 'REGISTRADO', 'Registrado', 'status-info', 7),
(8, 'RECONHECIDO', 'Reconhecido', 'status-sucesso', 8),
(9, 'NAO_RECONHECIDO', 'Não reconhecido', 'status-erro', 9);

INSERT INTO status_recurso (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Não registrado', 'status-neutro', 1),
(2, 'NAO_SOLICITADO', 'Não solicitado', 'status-neutro', 2),
(3, 'SOLICITADO', 'Solicitado', 'status-info', 3),
(4, 'AGUARDANDO_AJUSTES', 'Aguardando ajustes', 'status-alerta', 4),
(5, 'EM_ANALISE_SEDEC', 'Em análise SEDEC', 'status-info', 5),
(6, 'PLANO_APROVADO', 'Plano aprovado', 'status-sucesso', 6),
(7, 'RECURSO_DEFERIDO', 'Recurso deferido', 'status-sucesso', 7),
(8, 'RECURSO_INDEFERIDO', 'Recurso indeferido', 'status-erro', 8),
(9, 'REGISTRO_REVISAO', 'Registro de revisão', 'status-alerta', 9),
(10, 'EMPENHO', 'Empenho', 'status-info', 10);

INSERT INTO status_envio_pge (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Não registrado', 'status-neutro', 1),
(2, 'NAO_ENVIADO', 'Não enviado', 'status-neutro', 2),
(3, 'EM_PREPARACAO', 'Em preparação', 'status-alerta', 3),
(4, 'ENVIADO_PGE', 'Enviado à PGE', 'status-info', 4),
(5, 'RETORNADO_AJUSTE', 'Retornado para ajuste', 'status-alerta', 5),
(6, 'CONCLUIDO', 'Concluído', 'status-sucesso', 6);

INSERT INTO tipos_anexo (id, codigo, nome, obrigatorio, ordem) VALUES
(1, 'DECRETO_MUNICIPAL', 'Decreto municipal', 1, 1),
(2, 'OFICIO_HOMOLOGACAO', 'Ofício de homologação', 0, 2),
(3, 'PARECER_ESTADUAL', 'Parecer estadual', 0, 3),
(4, 'PARECER_MUNICIPAL', 'Parecer municipal', 0, 4),
(5, 'OUTROS_DOCUMENTOS', 'Outros documentos', 0, 5);

INSERT INTO configuracoes_sistema (chave, valor, tipo_dado, descricao) VALUES
('prazo_pge_dias', '7', 'integer', 'Prazo operacional em dias para cálculo do status de prazo PGE.'),
('paginacao_padrao', '20', 'integer', 'Quantidade padrão e máxima de registros por página na listagem.'),
('upload_tamanho_maximo_mb', '20', 'integer', 'Tamanho máximo permitido por arquivo anexado.'),
('sistema_nome', 'DGD', 'string', 'Nome curto do sistema.'),
('sistema_orgao', 'CEDEC-PA', 'string', 'Órgão gestor do sistema.'),
('timezone', 'America/Belem', 'string', 'Fuso horário oficial da aplicação.');

-- Seed COBRADE completo com quatro niveis e simbologia oficial.
INSERT INTO cobrade_grupos (id, codigo, nome, descricao) VALUES
(1, '1.1', 'Geológico', 'Grupo COBRADE 1.1 - Geológico'),
(2, '1.2', 'Hidrológico', 'Grupo COBRADE 1.2 - Hidrológico'),
(3, '1.3', 'Meteorológico', 'Grupo COBRADE 1.3 - Meteorológico'),
(4, '1.4', 'Climatológico', 'Grupo COBRADE 1.4 - Climatológico'),
(5, '1.5', 'Biológico', 'Grupo COBRADE 1.5 - Biológico'),
(6, '2.1', 'Desastres relacionados a substâncias radioativas', 'Grupo COBRADE 2.1 - Desastres relacionados a substâncias radioativas'),
(7, '2.2', 'Desastres relacionados a produtos perigosos', 'Grupo COBRADE 2.2 - Desastres relacionados a produtos perigosos'),
(8, '2.3', 'Desastres relacionados a incêndios urbanos', 'Grupo COBRADE 2.3 - Desastres relacionados a incêndios urbanos'),
(9, '2.4', 'Desastres relacionados a obras civis', 'Grupo COBRADE 2.4 - Desastres relacionados a obras civis'),
(10, '2.5', 'Desastres relacionados a transporte de passageiros e cargas não perigosas', 'Grupo COBRADE 2.5 - Desastres relacionados a transporte de passageiros e cargas não perigosas');

INSERT INTO cobrade_subgrupos (id, grupo_id, codigo, nome, descricao) VALUES
(1, 1, '1.1.1', 'Terremoto', 'Subgrupo COBRADE 1.1.1 - Terremoto'),
(2, 1, '1.1.2', 'Emanação vulcânica', 'Subgrupo COBRADE 1.1.2 - Emanação vulcânica'),
(3, 1, '1.1.3', 'Movimento de massa', 'Subgrupo COBRADE 1.1.3 - Movimento de massa'),
(4, 1, '1.1.4', 'Erosão', 'Subgrupo COBRADE 1.1.4 - Erosão'),
(5, 2, '1.2.1', 'Inundações', 'Subgrupo COBRADE 1.2.1 - Inundações'),
(6, 2, '1.2.2', 'Enxurradas', 'Subgrupo COBRADE 1.2.2 - Enxurradas'),
(7, 2, '1.2.3', 'Alagamentos', 'Subgrupo COBRADE 1.2.3 - Alagamentos'),
(8, 3, '1.3.1', 'Sistemas de grande escala/Escala regional', 'Subgrupo COBRADE 1.3.1 - Sistemas de grande escala/Escala regional'),
(9, 3, '1.3.2', 'Tempestades', 'Subgrupo COBRADE 1.3.2 - Tempestades'),
(10, 3, '1.3.3', 'Temperaturas extremas', 'Subgrupo COBRADE 1.3.3 - Temperaturas extremas'),
(11, 4, '1.4.1', 'Seca', 'Subgrupo COBRADE 1.4.1 - Seca'),
(12, 5, '1.5.1', 'Epidemias', 'Subgrupo COBRADE 1.5.1 - Epidemias'),
(13, 5, '1.5.2', 'Infestações/Pragas', 'Subgrupo COBRADE 1.5.2 - Infestações/Pragas'),
(14, 6, '2.1.1', 'Desastres siderais com riscos radioativos', 'Subgrupo COBRADE 2.1.1 - Desastres siderais com riscos radioativos'),
(15, 6, '2.1.2', 'Desastres com substâncias e equipamentos radioativos de uso em pesquisas, indústrias e usinas nucleares', 'Subgrupo COBRADE 2.1.2 - Desastres com substâncias e equipamentos radioativos de uso em pesquisas, indústrias e usinas nucleares'),
(16, 6, '2.1.3', 'Desastres relacionados com riscos de intensa poluição ambiental provocada por resíduos radioativos', 'Subgrupo COBRADE 2.1.3 - Desastres relacionados com riscos de intensa poluição ambiental provocada por resíduos radioativos'),
(17, 7, '2.2.1', 'Desastres em plantas e distritos industriais, parques e armazenamentos com extravasamento de produtos perigosos', 'Subgrupo COBRADE 2.2.1 - Desastres em plantas e distritos industriais, parques e armazenamentos com extravasamento de produtos perigosos'),
(18, 7, '2.2.2', 'Desastres relacionados à contaminação da água', 'Subgrupo COBRADE 2.2.2 - Desastres relacionados à contaminação da água'),
(19, 7, '2.2.3', 'Desastres relacionados a conflitos bélicos', 'Subgrupo COBRADE 2.2.3 - Desastres relacionados a conflitos bélicos'),
(20, 7, '2.2.4', 'Desastres relacionados a transporte de produtos perigosos', 'Subgrupo COBRADE 2.2.4 - Desastres relacionados a transporte de produtos perigosos'),
(21, 8, '2.3.1', 'Incêndios urbanos', 'Subgrupo COBRADE 2.3.1 - Incêndios urbanos'),
(22, 9, '2.4.1', 'Colapso de edificações', 'Subgrupo COBRADE 2.4.1 - Colapso de edificações'),
(23, 9, '2.4.2', 'Rompimento/colapso de barragens', 'Subgrupo COBRADE 2.4.2 - Rompimento/colapso de barragens'),
(24, 10, '2.5.1', 'Transporte rodoviário', 'Subgrupo COBRADE 2.5.1 - Transporte rodoviário'),
(25, 10, '2.5.2', 'Transporte ferroviário', 'Subgrupo COBRADE 2.5.2 - Transporte ferroviário'),
(26, 10, '2.5.3', 'Transporte aéreo', 'Subgrupo COBRADE 2.5.3 - Transporte aéreo'),
(27, 10, '2.5.4', 'Transporte marítimo', 'Subgrupo COBRADE 2.5.4 - Transporte marítimo'),
(28, 10, '2.5.5', 'Transporte aquaviário', 'Subgrupo COBRADE 2.5.5 - Transporte aquaviário');

INSERT INTO cobrade_tipos (id, subgrupo_id, codigo, nome, descricao) VALUES
(1, 1, '1.1.1.1', 'Tremor de terra', 'Tipo COBRADE 1.1.1.1 - Tremor de terra'),
(2, 1, '1.1.1.2', 'Tsunami', 'Tipo COBRADE 1.1.1.2 - Tsunami'),
(3, 2, '1.1.2.0', 'Emanação vulcânica', 'Tipo COBRADE 1.1.2.0 - Emanação vulcânica'),
(4, 3, '1.1.3.1', 'Quedas, tombamentos e rolamentos', 'Tipo COBRADE 1.1.3.1 - Quedas, tombamentos e rolamentos'),
(5, 3, '1.1.3.2', 'Deslizamentos', 'Tipo COBRADE 1.1.3.2 - Deslizamentos'),
(6, 3, '1.1.3.3', 'Corridas de massa', 'Tipo COBRADE 1.1.3.3 - Corridas de massa'),
(7, 3, '1.1.3.4', 'Subsidências e colapsos', 'Tipo COBRADE 1.1.3.4 - Subsidências e colapsos'),
(8, 4, '1.1.4.1', 'Erosão costeira/Marinha', 'Tipo COBRADE 1.1.4.1 - Erosão costeira/Marinha'),
(9, 4, '1.1.4.2', 'Erosão de margem fluvial', 'Tipo COBRADE 1.1.4.2 - Erosão de margem fluvial'),
(10, 4, '1.1.4.3', 'Erosão continental', 'Tipo COBRADE 1.1.4.3 - Erosão continental'),
(11, 5, '1.2.1.0', 'Inundações', 'Tipo COBRADE 1.2.1.0 - Inundações'),
(12, 6, '1.2.2.0', 'Enxurradas', 'Tipo COBRADE 1.2.2.0 - Enxurradas'),
(13, 7, '1.2.3.0', 'Alagamentos', 'Tipo COBRADE 1.2.3.0 - Alagamentos'),
(14, 8, '1.3.1.1', 'Ciclones', 'Tipo COBRADE 1.3.1.1 - Ciclones'),
(15, 8, '1.3.1.2', 'Frentes frias/Zonas de convergência', 'Tipo COBRADE 1.3.1.2 - Frentes frias/Zonas de convergência'),
(16, 9, '1.3.2.1', 'Tempestade local/Convectiva', 'Tipo COBRADE 1.3.2.1 - Tempestade local/Convectiva'),
(17, 10, '1.3.3.1', 'Onda de calor', 'Tipo COBRADE 1.3.3.1 - Onda de calor'),
(18, 10, '1.3.3.2', 'Onda de frio', 'Tipo COBRADE 1.3.3.2 - Onda de frio'),
(19, 11, '1.4.1.1', 'Estiagem', 'Tipo COBRADE 1.4.1.1 - Estiagem'),
(20, 11, '1.4.1.2', 'Seca', 'Tipo COBRADE 1.4.1.2 - Seca'),
(21, 11, '1.4.1.3', 'Incêndio florestal', 'Tipo COBRADE 1.4.1.3 - Incêndio florestal'),
(22, 11, '1.4.1.4', 'Baixa umidade do ar', 'Tipo COBRADE 1.4.1.4 - Baixa umidade do ar'),
(23, 12, '1.5.1.1', 'Doenças infecciosas virais', 'Tipo COBRADE 1.5.1.1 - Doenças infecciosas virais'),
(24, 12, '1.5.1.2', 'Doenças infecciosas bacterianas', 'Tipo COBRADE 1.5.1.2 - Doenças infecciosas bacterianas'),
(25, 12, '1.5.1.3', 'Doenças infecciosas parasíticas', 'Tipo COBRADE 1.5.1.3 - Doenças infecciosas parasíticas'),
(26, 12, '1.5.1.4', 'Doenças infecciosas fúngicas', 'Tipo COBRADE 1.5.1.4 - Doenças infecciosas fúngicas'),
(27, 13, '1.5.2.1', 'Infestações de animais', 'Tipo COBRADE 1.5.2.1 - Infestações de animais'),
(28, 13, '1.5.2.2', 'Infestações de algas', 'Tipo COBRADE 1.5.2.2 - Infestações de algas'),
(29, 13, '1.5.2.3', 'Outras infestações', 'Tipo COBRADE 1.5.2.3 - Outras infestações'),
(30, 14, '2.1.1.1', 'Queda de satélite (radionuclídeos)', 'Tipo COBRADE 2.1.1.1 - Queda de satélite (radionuclídeos)'),
(31, 15, '2.1.2.1', 'Fontes radioativas em processos de produção', 'Tipo COBRADE 2.1.2.1 - Fontes radioativas em processos de produção'),
(32, 16, '2.1.3.1', 'Outras fontes de liberação de radionuclídeos para o meio ambiente', 'Tipo COBRADE 2.1.3.1 - Outras fontes de liberação de radionuclídeos para o meio ambiente'),
(33, 17, '2.2.1.1', 'Liberação de produtos químicos para a atmosfera causada por explosão ou incêndio', 'Tipo COBRADE 2.2.1.1 - Liberação de produtos químicos para a atmosfera causada por explosão ou incêndio'),
(34, 18, '2.2.2.1', 'Liberação de produtos químicos nos sistemas de água potável', 'Tipo COBRADE 2.2.2.1 - Liberação de produtos químicos nos sistemas de água potável'),
(35, 18, '2.2.2.2', 'Derramamento de produtos químicos em ambiente lacustre, fluvial, marinho e aquífero', 'Tipo COBRADE 2.2.2.2 - Derramamento de produtos químicos em ambiente lacustre, fluvial, marinho e aquífero'),
(36, 19, '2.2.3.1', 'Liberação de produtos químicos e contaminação como consequência de ações militares', 'Tipo COBRADE 2.2.3.1 - Liberação de produtos químicos e contaminação como consequência de ações militares'),
(37, 20, '2.2.4.1', 'Transporte rodoviário', 'Tipo COBRADE 2.2.4.1 - Transporte rodoviário'),
(38, 20, '2.2.4.2', 'Transporte ferroviário', 'Tipo COBRADE 2.2.4.2 - Transporte ferroviário'),
(39, 20, '2.2.4.3', 'Transporte aéreo', 'Tipo COBRADE 2.2.4.3 - Transporte aéreo'),
(40, 20, '2.2.4.4', 'Transporte dutoviário', 'Tipo COBRADE 2.2.4.4 - Transporte dutoviário'),
(41, 20, '2.2.4.5', 'Transporte marítimo', 'Tipo COBRADE 2.2.4.5 - Transporte marítimo'),
(42, 20, '2.2.4.6', 'Transporte aquaviário', 'Tipo COBRADE 2.2.4.6 - Transporte aquaviário'),
(43, 21, '2.3.1.1', 'Incêndios em plantas e distritos industriais, parques e depósitos', 'Tipo COBRADE 2.3.1.1 - Incêndios em plantas e distritos industriais, parques e depósitos'),
(44, 21, '2.3.1.2', 'Incêndios em aglomerados residenciais', 'Tipo COBRADE 2.3.1.2 - Incêndios em aglomerados residenciais'),
(45, 22, '2.4.1.0', 'Colapso de edificações', 'Tipo COBRADE 2.4.1.0 - Colapso de edificações'),
(46, 23, '2.4.2.0', 'Rompimento/colapso de barragens', 'Tipo COBRADE 2.4.2.0 - Rompimento/colapso de barragens'),
(47, 24, '2.5.1.0', 'Transporte rodoviário', 'Tipo COBRADE 2.5.1.0 - Transporte rodoviário'),
(48, 25, '2.5.2.0', 'Transporte ferroviário', 'Tipo COBRADE 2.5.2.0 - Transporte ferroviário'),
(49, 26, '2.5.3.0', 'Transporte aéreo', 'Tipo COBRADE 2.5.3.0 - Transporte aéreo'),
(50, 27, '2.5.4.0', 'Transporte marítimo', 'Tipo COBRADE 2.5.4.0 - Transporte marítimo'),
(51, 28, '2.5.5.0', 'Transporte aquaviário', 'Tipo COBRADE 2.5.5.0 - Transporte aquaviário');

INSERT INTO cobrade_subtipos (id, tipo_id, codigo, nome, descricao, simbologia, origem, versao) VALUES
(1, 1, '1.1.1.1.0', 'Tremor de terra', 'Vibrações do terreno que provocam oscilações verticais e horizontais na superfície da Terra (ondas sísmicas). Pode ser natural (tectônica) ou induzido (explosões, injeção profunda de líquidos e gás, extração de fluidos, alívio de carga de minas, enchimento de lagos artificiais).', 'cobrade_simbologia/simbologia_cobrade_1_1_1_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(2, 2, '1.1.1.2.0', 'Tsunami', 'Série de ondas geradas por deslocamento de um grande volume de água causado geralmente por terremotos, erupções vulcânicas ou movimentos de massa.', 'cobrade_simbologia/simbologia_cobrade_1_1_1_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(3, 3, '1.1.2.0.0', 'Emanação vulcânica', 'Produtos/materiais vulcânicos lançados na atmosfera a partir de erupções vulcânicas.', 'cobrade_simbologia/simbologia_cobrade_1_1_2_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(4, 4, '1.1.3.1.1', 'Blocos', 'As quedas de blocos são movimentos rápidos e acontecem quando materiais rochosos diversos e de volumes variáveis se destacam de encostas muito íngremes, num movimento tipo queda livre. Os tombamentos de blocos são movimentos de massa em que ocorre rotação de um bloco de solo ou rocha em torno de um ponto ou abaixo do centro de gravidade da massa desprendida. Rolamentos de blocos são movimentos de blocos rochosos ao longo de encostas, que ocorrem geralmente pela perda de apoio (descalçamento).', 'cobrade_simbologia/simbologia_cobrade_1_1_3_1_1.png', 'Base COBRADE oficial', '2026-06-10'),
(5, 4, '1.1.3.1.2', 'Lascas', 'As quedas de lascas são movimentos rápidos e acontecem quando fatias delgadas formadas pelos fragmentos de rochas se destacam de encostas muito íngremes, num movimento tipo queda livre.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_1_2.png', 'Base COBRADE oficial', '2026-06-10'),
(6, 4, '1.1.3.1.3', 'Matacães', 'Os rolamentos de matacães são caracterizados por movimentos rápidos e acontecem quando materiais rochosos diversos e de volumes variáveis se destacam de encostas e movimentam-se num plano inclinado.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_1_3.png', 'Base COBRADE oficial', '2026-06-10'),
(7, 4, '1.1.3.1.4', 'Lajes', 'As quedas de lajes são movimentos rápidos e acontecem quando fragmentos de rochas extensas de superfície mais ou menos plana e de pouca espessura se destacam de encostas muito íngremes, num movimento tipo queda livre.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_1_4.png', 'Base COBRADE oficial', '2026-06-10'),
(8, 5, '1.1.3.2.1', 'Deslizamentos de solo e/ou rocha', 'São movimentos rápidos de solo ou rocha, apresentando superfície de ruptura bem definida, de duração relativamente curta, de massas de terreno geralmente bem definidas quanto ao seu volume, cujo centro de gravidade se desloca para baixo e para fora do talude. Frequentemente, os primeiros sinais desses movimentos são a presença de fissuras.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_2_1.png', 'Base COBRADE oficial', '2026-06-10'),
(9, 6, '1.1.3.3.1', 'Solo/Lama', 'Ocorrem quando, por índices pluviométricos excepcionais, o solo/lama, misturado com a água, tem comportamento de líquido viscoso, de extenso raio de ação e alto poder destrutivo.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_3_1.png', 'Base COBRADE oficial', '2026-06-10'),
(10, 6, '1.1.3.3.2', 'Rocha/Detrito', 'Ocorrem quando, por índices pluviométricos excepcionais, rocha/detrito, misturado com a água, tem comportamento de líquido viscoso, de extenso raio de ação e alto poder destrutivo.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_3_2.png', 'Base COBRADE oficial', '2026-06-10'),
(11, 7, '1.1.3.4.0', 'Subsidências e colapsos', 'Afundamento rápido ou gradual do terreno devido ao colapso de cavidades, redução da porosidade do solo ou deformação de material argiloso.', 'cobrade_simbologia/simbologia_cobrade_1_1_3_4_0.png', 'Base COBRADE oficial', '2026-06-10'),
(12, 8, '1.1.4.1.0', 'Erosão costeira/Marinha', 'Processo de desgaste (mecânico ou químico) que ocorre ao longo da linha da costa (rochosa ou praia) e se deve à ação das ondas, correntes marinhas e marés.', 'cobrade_simbologia/simbologia_cobrade_1_1_4_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(13, 9, '1.1.4.2.0', 'Erosão de margem fluvial', 'Desgaste das encostas dos rios que provoca desmoronamento de barrancos.', 'cobrade_simbologia/simbologia_cobrade_1_1_4_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(14, 10, '1.1.4.3.1', 'Laminar', 'Remoção de uma camada delgada e uniforme do solo superficial provocada por fluxo hídrico não concentrado.', 'cobrade_simbologia/simbologia_cobrade_1_1_4_3_1.png', 'Base COBRADE oficial', '2026-06-10'),
(15, 10, '1.1.4.3.2', 'Ravinas', 'Evolução, em tamanho e profundidade, da desagregação e remoção das partículas do solo de sulcos provocada por escoamento hídrico superficial concentrado.', 'cobrade_simbologia/simbologia_cobrade_1_1_4_3_2.png', 'Base COBRADE oficial', '2026-06-10'),
(16, 10, '1.1.4.3.3', 'Boçorocas', 'Evolução do processo de ravinamento, em tamanho e profundidade, em que a desagregação e remoção das partículas do solo são provocadas por escoamento hídrico superficial e subsuperficial (escoamento freático) concentrado.', 'cobrade_simbologia/simbologia_cobrade_1_1_4_3_3.png', 'Base COBRADE oficial', '2026-06-10'),
(17, 11, '1.2.1.0.0', 'Inundações', 'Submersão de áreas fora dos limites normais de um curso de água em zonas que normalmente não se encontram submersas. O transbordamento ocorre de modo gradual, geralmente ocasionado por chuvas prolongadas em áreas de planície.', 'cobrade_simbologia/simbologia_cobrade_1_2_1_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(18, 12, '1.2.2.0.0', 'Enxurradas', 'Escoamento superficial de alta velocidade e energia, provocado por chuvas intensas e concentradas, normalmente em pequenas bacias de relevo acidentado. Caracterizada pela elevação súbita das vazões de determinada drenagem e transbordamento brusco da calha fluvial. Apresenta grande poder destrutivo.', 'cobrade_simbologia/simbologia_cobrade_1_2_2_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(19, 13, '1.2.3.0.0', 'Alagamentos', 'Extrapolação da capacidade de escoamento de sistemas de drenagem urbana e consequente acúmulo de água em ruas, calçadas ou outras infraestruturas urbanas, em decorrência de precipitações intensas.', 'cobrade_simbologia/simbologia_cobrade_1_2_3_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(20, 14, '1.3.1.1.1', 'Ventos costeiros (mobilidade de dunas)', 'Intensificação dos ventos nas regiões litorâneas, movimentando dunas de areia sobre construções na orla.', 'cobrade_simbologia/simbologia_cobrade_1_3_1_1_1.png', 'Base COBRADE oficial', '2026-06-10'),
(21, 14, '1.3.1.1.2', 'Marés de tempestade (ressaca)', 'São ondas violentas que geram uma maior agitação do mar próximo à praia. Ocorrem quando rajadas fortes de vento fazem subir o nível do oceano em mar aberto e essa intensificação das correntes marítimas carrega uma enorme quantidade de água em direção ao litoral. Em consequência, as praias inundam, as ondas se tornam maiores e a orla pode ser devastada alagando ruas e destruindo edificações.', 'cobrade_simbologia/simbologia_cobrade_1_3_1_1_2.png', 'Base COBRADE oficial', '2026-06-10'),
(22, 15, '1.3.1.2.0', 'Frentes frias/Zonas de convergência', 'Frente fria é uma massa de ar frio que avança sobre uma região, provocando queda brusca da temperatura local, com período de duração inferior à friagem. Zona de convergência é uma região que está ligada à tempestade causada por uma zona de baixa pressão atmosférica, provocando forte deslocamento de massas de ar, vendavais, chuvas intensas e até queda de granizo.', 'cobrade_simbologia/simbologia_cobrade_1_3_1_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(23, 16, '1.3.2.1.1', 'Tornados', 'Coluna de ar que gira de forma violenta e muito perigosa, estando em contato com a terra e a base de uma nuvem de grande desenvolvimento vertical. Essa coluna de ar pode percorrer vários quilômetros e deixa um rastro de destruição pelo caminho percorrido.', 'cobrade_simbologia/simbologia_cobrade_1_3_2_1_1.png', 'Base COBRADE oficial', '2026-06-10'),
(24, 16, '1.3.2.1.2', 'Tempestade de raios', 'Tempestade com intensa atividade elétrica no interior das nuvens, com grande desenvolvimento vertical.', 'cobrade_simbologia/simbologia_cobrade_1_3_2_1_2.png', 'Base COBRADE oficial', '2026-06-10'),
(25, 16, '1.3.2.1.3', 'Granizo', 'Precipitação de pedaços irregulares de gelo.', 'cobrade_simbologia/simbologia_cobrade_1_3_2_1_3.png', 'Base COBRADE oficial', '2026-06-10'),
(26, 16, '1.3.2.1.4', 'Chuvas intensas', 'São chuvas que ocorrem com acumulados significativos, causando múltiplos desastres (ex.: inundações, movimentos de massa, enxurradas, etc.).', 'cobrade_simbologia/simbologia_cobrade_1_3_2_1_4.png', 'Base COBRADE oficial', '2026-06-10'),
(27, 16, '1.3.2.1.5', 'Vendaval', 'Forte deslocamento de uma massa de ar em uma região.', 'cobrade_simbologia/simbologia_cobrade_1_3_2_1_5.png', 'Base COBRADE oficial', '2026-06-10'),
(28, 17, '1.3.3.1.0', 'Onda de calor', 'É um período prolongado de tempo excessivamente quente e desconfortável, onde as temperaturas ficam acima de um valor normal esperado para aquela região em determinado período do ano. Geralmente é adotado um período mínimo de três dias com temperaturas 5°C acima dos valores máximos médios.', 'cobrade_simbologia/simbologia_cobrade_1_3_3_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(29, 18, '1.3.3.2.1', 'Friagem', 'Período de tempo que dura, no mínimo, de três a quatro dias, e os valores de temperatura mínima do ar ficam abaixo dos valores esperados para determinada região em um período do ano.', 'cobrade_simbologia/simbologia_cobrade_1_3_3_2_1.png', 'Base COBRADE oficial', '2026-06-10'),
(30, 18, '1.3.3.2.2', 'Geadas', 'Formação de uma camada de cristais de gelo na superfície ou na folhagem exposta.', 'cobrade_simbologia/simbologia_cobrade_1_3_3_2_2.png', 'Base COBRADE oficial', '2026-06-10'),
(31, 19, '1.4.1.1.0', 'Estiagem', 'Período prolongado de baixa ou nenhuma pluviosidade, em que a perda de umidade do solo é superior à sua reposição.', 'cobrade_simbologia/simbologia_cobrade_1_4_1_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(32, 20, '1.4.1.2.0', 'Seca', 'A seca é uma estiagem prolongada, durante o período de tempo suficiente para que a falta de precipitação provoque grave desequilíbrio hidrológico.', 'cobrade_simbologia/simbologia_cobrade_1_4_1_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(33, 21, '1.4.1.3.1', 'Incêndios em parques, áreas de proteção ambiental e áreas de preservação permanente nacionais, estaduais ou municipais', 'Propagação de fogo sem controle, em qualquer tipo de vegetação situada em áreas legalmente protegidas.', 'cobrade_simbologia/simbologia_cobrade_1_4_1_3_1.png', 'Base COBRADE oficial', '2026-06-10'),
(34, 21, '1.4.1.3.2', 'Incêndios em áreas não protegidas, com reflexos na qualidade do ar', 'Propagação de fogo sem controle, em qualquer tipo de vegetação que não se encontre em áreas sob proteção legal, acarretando queda da qualidade do ar.', 'cobrade_simbologia/simbologia_cobrade_1_4_1_3_2.png', 'Base COBRADE oficial', '2026-06-10'),
(35, 22, '1.4.1.4.0', 'Baixa umidade do ar', 'Queda da taxa de vapor de água suspensa na atmosfera para níveis abaixo de 20%.', 'cobrade_simbologia/simbologia_cobrade_1_4_1_4_0.png', 'Base COBRADE oficial', '2026-06-10'),
(36, 23, '1.5.1.1.0', 'Doenças infecciosas virais', 'Aumento brusco, significativo e transitório da ocorrência de doenças infecciosas geradas por vírus.', 'cobrade_simbologia/simbologia_cobrade_1_5_1_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(37, 24, '1.5.1.2.0', 'Doenças infecciosas bacterianas', 'Aumento brusco, significativo e transitório da ocorrência de doenças infecciosas geradas por bactérias.', 'cobrade_simbologia/simbologia_cobrade_1_5_1_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(38, 25, '1.5.1.3.0', 'Doenças infecciosas parasíticas', 'Aumento brusco, significativo e transitório da ocorrência de doenças infecciosas geradas por parasitas.', 'cobrade_simbologia/simbologia_cobrade_1_5_1_3_0.png', 'Base COBRADE oficial', '2026-06-10'),
(39, 26, '1.5.1.4.0', 'Doenças infecciosas fúngicas', 'Aumento brusco, significativo e transitório da ocorrência de doenças infecciosas geradas por fungos.', 'cobrade_simbologia/simbologia_cobrade_1_5_1_4_0.png', 'Base COBRADE oficial', '2026-06-10'),
(40, 27, '1.5.2.1.0', 'Infestações de animais', 'Infestações por animais que alterem o equilíbrio ecológico de uma região, bacia hidrográfica ou bioma afetado por suas ações predatórias.', 'cobrade_simbologia/simbologia_cobrade_1_5_2_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(41, 28, '1.5.2.2.1', 'Marés vermelhas', 'Aglomeração de microalgas em água doce ou em água salgada suficiente para causar alterações físicas, químicas ou biológicas em sua composição, caracterizada por uma mudança de cor, tornando-se amarela, laranja, vermelha ou marrom.', 'cobrade_simbologia/simbologia_cobrade_1_5_2_2_1.png', 'Base COBRADE oficial', '2026-06-10'),
(42, 28, '1.5.2.2.2', 'Cianobactérias em reservatórios', 'Aglomeração de cianobactérias em reservatórios receptores de descargas de dejetos domésticos, industriais e/ou agrícolas, provocando alterações das propriedades físicas, químicas ou biológicas da água.', 'cobrade_simbologia/simbologia_cobrade_1_5_2_2_2.png', 'Base COBRADE oficial', '2026-06-10'),
(43, 29, '1.5.2.3.0', 'Outras infestações', 'Infestações que alterem o equilíbrio ecológico de uma região, bacia hidrográfica ou bioma afetado por suas ações predatórias.', 'cobrade_simbologia/simbologia_cobrade_1_5_2_3_0.png', 'Base COBRADE oficial', '2026-06-10'),
(44, 30, '2.1.1.1.0', 'Queda de satélite (radionuclídeos)', 'Queda de satélites que possuem, na sua composição, motores ou corpos radioativos, podendo ocasionar a liberação deste material.', 'cobrade_simbologia/simbologia_cobrade_2_1_1_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(45, 31, '2.1.2.1.0', 'Fontes radioativas em processos de produção', 'Escapamento acidental de radiação que excede os níveis de segurança estabelecidos na norma NN 3.01/006:2011 da CNEN.', 'cobrade_simbologia/simbologia_cobrade_2_1_2_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(46, 32, '2.1.3.1.0', 'Outras fontes de liberação de radionuclídeos para o meio ambiente', 'Escapamento acidental ou não acidental de radiação originária de fontes radioativas diversas e que excede os níveis de segurança estabelecidos na norma NN 3.01/006:2011 e NN 3.01/011:2011 da CNEN.', 'cobrade_simbologia/simbologia_cobrade_2_1_3_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(47, 33, '2.2.1.1.0', 'Liberação de produtos químicos para a atmosfera causada por explosão ou incêndio', 'Liberação de produtos químicos diversos para o ambiente, provocada por explosão/incêndio em plantas industriais ou outros sítios.', 'cobrade_simbologia/simbologia_cobrade_2_2_1_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(48, 34, '2.2.2.1.0', 'Liberação de produtos químicos nos sistemas de água potável', 'Derramamento de produtos químicos diversos em um sistema de abastecimento de água potável, que pode causar alterações nas qualidades físicas, químicas, biológicas.', 'cobrade_simbologia/simbologia_cobrade_2_2_2_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(49, 35, '2.2.2.2.0', 'Derramamento de produtos químicos em ambiente lacustre, fluvial, marinho e aquífero', 'Derramamento de produtos químicos diversos em lagos, rios, mar e reservatórios subterrâneos de água, que pode causar alterações nas qualidades físicas, químicas e biológicas.', 'cobrade_simbologia/simbologia_cobrade_2_2_2_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(50, 36, '2.2.3.1.0', 'Liberação de produtos químicos e contaminação como consequência de ações militares', 'Agente de natureza nuclear ou radiológica, química ou biológica, considerado como perigoso, e que pode ser utilizado intencionalmente por terroristas ou grupamentos militares em atentados ou em caso de guerra.', 'cobrade_simbologia/simbologia_cobrade_2_2_3_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(51, 37, '2.2.4.1.0', 'Transporte rodoviário', 'Extravasamento de produtos perigosos transportados no modal rodoviário.', 'cobrade_simbologia/simbologia_cobrade_2_2_4_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(52, 38, '2.2.4.2.0', 'Transporte ferroviário', 'Extravasamento de produtos perigosos transportados no modal ferroviário.', 'cobrade_simbologia/simbologia_cobrade_2_2_4_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(53, 39, '2.2.4.3.0', 'Transporte aéreo', 'Extravasamento de produtos perigosos transportados no modal aéreo.', 'cobrade_simbologia/simbologia_cobrade_2_2_4_3_0.png', 'Base COBRADE oficial', '2026-06-10'),
(54, 40, '2.2.4.4.0', 'Transporte dutoviário', 'Extravasamento de produtos perigosos transportados no modal dutoviário.', 'cobrade_simbologia/simbologia_cobrade_2_2_4_4_0.png', 'Base COBRADE oficial', '2026-06-10'),
(55, 41, '2.2.4.5.0', 'Transporte marítimo', 'Extravasamento de produtos perigosos transportados no modal marítimo.', 'cobrade_simbologia/simbologia_cobrade_2_2_4_5_0.png', 'Base COBRADE oficial', '2026-06-10'),
(56, 42, '2.2.4.6.0', 'Transporte aquaviário', 'Extravasamento de produtos perigosos transportados no modal aquaviário.', 'cobrade_simbologia/simbologia_cobrade_2_2_4_6_0.png', 'Base COBRADE oficial', '2026-06-10'),
(57, 43, '2.3.1.1.0', 'Incêndios em plantas e distritos industriais, parques e depósitos', 'Propagação descontrolada do fogo em plantas e distritos industriais, parques e depósitos.', 'cobrade_simbologia/simbologia_cobrade_2_3_1_1_0.png', 'Base COBRADE oficial', '2026-06-10'),
(58, 44, '2.3.1.2.0', 'Incêndios em aglomerados residenciais', 'Propagação descontrolada do fogo em conjuntos habitacionais de grande densidade.', 'cobrade_simbologia/simbologia_cobrade_2_3_1_2_0.png', 'Base COBRADE oficial', '2026-06-10'),
(59, 45, '2.4.1.0.0', 'Colapso de edificações', 'Queda de estrutura civil.', 'cobrade_simbologia/simbologia_cobrade_2_4_1_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(60, 46, '2.4.2.0.0', 'Rompimento/colapso de barragens', 'Rompimento ou colapso de barragens.', 'cobrade_simbologia/simbologia_cobrade_2_4_2_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(61, 47, '2.5.1.0.0', 'Transporte rodoviário', 'Acidente no modal rodoviário envolvendo o transporte de passageiros ou cargas não perigosas.', 'cobrade_simbologia/simbologia_cobrade_2_5_1_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(62, 48, '2.5.2.0.0', 'Transporte ferroviário', 'Acidente com a participação direta de veículo ferroviário de transporte de passageiros ou cargas não perigosas.', 'cobrade_simbologia/simbologia_cobrade_2_5_2_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(63, 49, '2.5.3.0.0', 'Transporte aéreo', 'Acidente no modal aéreo envolvendo o transporte de passageiros ou cargas não perigosas.', 'cobrade_simbologia/simbologia_cobrade_2_5_3_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(64, 50, '2.5.4.0.0', 'Transporte marítimo', 'Acidente com embarcações marítimas destinadas ao transporte de passageiros e cargas não perigosas.', 'cobrade_simbologia/simbologia_cobrade_2_5_4_0_0.png', 'Base COBRADE oficial', '2026-06-10'),
(65, 51, '2.5.5.0.0', 'Transporte aquaviário', 'Acidente com embarcações destinadas ao transporte de passageiros e cargas não perigosas.', 'cobrade_simbologia/simbologia_cobrade_2_5_5_0_0.png', 'Base COBRADE oficial', '2026-06-10');

-- Admin inicial: gerar hash com PHP antes de executar em producao.
-- Exemplo:
-- php -r "echo password_hash('SENHA_TEMPORARIA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
--
-- INSERT INTO usuarios (perfil_id, nome, email, senha_hash, ativo, trocar_senha_proximo_acesso)
-- VALUES (1, 'Administrador DGD', 'admin@dgd.local', 'SUBSTITUIR_POR_HASH_GERADO_NO_PHP', 1, 1);


-- Carga de municÃ­pios do ParÃ¡ a partir de terit/PA_Municipios_2025/para_municipios_com_geolocalizacao.csv
INSERT INTO municipios (codigo_ibge, nome, uf, latitude, longitude) VALUES
(1507706, 'SÃ£o SebastiÃ£o da Boa Vista', 'PA', -1.434161, -49.680293),
(1507755, 'Sapucaia', 'PA', -6.838878, -49.56976),
(1507102, 'SÃ£o Caetano de Odivelas', 'PA', -0.864988, -48.025702),
(1506906, 'SantarÃ©m Novo', 'PA', -0.913345, -47.332944),
(1507003, 'Santo AntÃ´nio do TauÃ¡', 'PA', -1.093275, -48.182749),
(1507300, 'SÃ£o FÃ©lix do Xingu', 'PA', -7.229439, -52.252334),
(1507201, 'SÃ£o Domingos do Capim', 'PA', -1.882518, -47.771478),
(1507151, 'SÃ£o Domingos do Araguaia', 'PA', -5.657594, -48.73059),
(1506559, 'Santa Luzia do ParÃ¡', 'PA', -1.656645, -46.926969),
(1506609, 'Santa Maria do ParÃ¡', 'PA', -1.374484, -47.521639),
(1506583, 'Santa Maria das Barreiras', 'PA', -8.633865, -50.314308),
(1506401, 'Santa Cruz do Arari', 'PA', -0.583354, -49.313859),
(1506500, 'Santa Izabel do ParÃ¡', 'PA', -1.365201, -48.129308),
(1506807, 'SantarÃ©m', 'PA', -2.679423, -55.238417),
(1506708, 'Santana do Araguaia', 'PA', -9.363998, -50.611173),
(1506005, 'Prainha', 'PA', -2.120136, -53.660192),
(1506112, 'Quatipuru', 'PA', -0.86008, -47.015453),
(1506104, 'Primavera', 'PA', -0.952416, -47.131369),
(1505908, 'Porto de Moz', 'PA', -2.184588, -52.547693),
(1506302, 'Salvaterra', 'PA', -0.796644, -48.629978),
(1506351, 'Santa BÃ¡rbara do ParÃ¡', 'PA', -1.194335, -48.250515),
(1506161, 'Rio Maria', 'PA', -7.340613, -49.894928),
(1506138, 'RedenÃ§Ã£o', 'PA', -8.069347, -50.198159),
(1506203, 'SalinÃ³polis', 'PA', -0.68395, -47.351701),
(1506195, 'RurÃ³polis', 'PA', -4.241044, -55.210708),
(1506187, 'Rondon do ParÃ¡', 'PA', -4.516948, -48.459952),
(1505536, 'Parauapebas', 'PA', -6.151771, -50.489528),
(1505502, 'Paragominas', 'PA', -3.202963, -47.602292),
(1505494, 'Palestina do ParÃ¡', 'PA', -5.927231, -48.370174),
(1505486, 'PacajÃ¡', 'PA', -3.684119, -50.631523),
(1505601, 'Peixe-Boi', 'PA', -1.130707, -47.271986),
(1505551, 'Pau D''Arco', 'PA', -7.737006, -50.123629),
(1505403, 'OurÃ©m', 'PA', -1.495916, -47.139184),
(1505437, 'OurilÃ¢ndia do Norte', 'PA', -7.5201, -51.430328),
(1505809, 'Portel', 'PA', -2.565532, -50.955659),
(1505650, 'Placas', 'PA', -3.889766, -54.500659),
(1505635, 'PiÃ§arra', 'PA', -6.54105, -49.011746),
(1505700, 'Ponta de Pedras', 'PA', -1.117618, -49.077274),
(1505007, 'Nova Timboteua', 'PA', -1.156431, -47.392153),
(1504976, 'Nova Ipixuna', 'PA', -4.981053, -49.230786),
(1505064, 'Novo Repartimento', 'PA', -4.502798, -50.27141),
(1505031, 'Novo Progresso', 'PA', -8.060499, -55.612221),
(1504901, 'MuanÃ¡', 'PA', -1.355705, -49.309246),
(1504950, 'Nova EsperanÃ§a do PiriÃ¡', 'PA', -2.392499, -46.899912),
(1505304, 'OriximinÃ¡', 'PA', 0.25435, -57.150148),
(1505106, 'Ã“bidos', 'PA', -0.169575, -55.67456),
(1505205, 'Oeiras do ParÃ¡', 'PA', -2.27888, -49.902381),
(1504505, 'MelgaÃ§o', 'PA', -1.607588, -51.103781),
(1504455, 'MedicilÃ¢ndia', 'PA', -3.160197, -53.195031),
(1504422, 'Marituba', 'PA', -1.39745, -48.31979),
(1504406, 'Marapanim', 'PA', -0.837411, -47.709831),
(1504752, 'MojuÃ­ dos Campos', 'PA', -3.07041, -54.575156),
(1504802, 'Monte Alegre', 'PA', -1.074156, -54.353115),
(1504604, 'Mocajuba', 'PA', -2.576682, -49.466345),
(1504703, 'Moju', 'PA', -2.683466, -49.073955),
(1504000, 'Limoeiro do Ajuru', 'PA', -1.8642, -49.469156),
(1504059, 'MÃ£e do Rio', 'PA', -1.991013, -47.512784),
(1503903, 'Juruti', 'PA', -2.62284, -56.22122),
(1504208, 'MarabÃ¡', 'PA', -5.630007, -50.016291),
(1504307, 'MaracanÃ£', 'PA', -0.810132, -47.497449),
(1504109, 'MagalhÃ£es Barata', 'PA', -0.809854, -47.635209),
(1503457, 'Ipixuna do ParÃ¡', 'PA', -2.805383, -47.921161),
(1503507, 'Irituia', 'PA', -1.81155, -47.39986),
(1503309, 'IgarapÃ©-Miri', 'PA', -2.062675, -49.130744),
(1503408, 'Inhangapi', 'PA', -1.46467, -47.922661),
(1503705, 'Itupiranga', 'PA', -5.11486, -49.86959),
(1503804, 'JacundÃ¡', 'PA', -4.598519, -49.18119),
(1503754, 'Jacareacanga', 'PA', -7.442519, -57.303966),
(1503606, 'Itaituba', 'PA', -5.868899, -56.496689),
(1502954, 'Eldorado do CarajÃ¡s', 'PA', -6.069598, -49.245371),
(1502939, 'Dom Eliseu', 'PA', -4.186683, -47.898105),
(1503044, 'Floresta do Araguaia', 'PA', -7.548225, -49.575938),
(1503002, 'Faro', 'PA', -1.15842, -57.806436),
(1502806, 'Curralinho', 'PA', -1.583474, -49.988013),
(1502905, 'CuruÃ§Ã¡', 'PA', -0.74274, -47.863887),
(1502855, 'CuruÃ¡', 'PA', -1.845755, -55.113486),
(1503200, 'IgarapÃ©-AÃ§u', 'PA', -1.145006, -47.565982),
(1503101, 'GurupÃ¡', 'PA', -1.149308, -51.553851),
(1503093, 'GoianÃ©sia do ParÃ¡', 'PA', -4.027101, -49.005561),
(1503077, 'GarrafÃ£o do Norte', 'PA', -2.168263, -47.075984),
(1502509, 'Chaves', 'PA', -0.032582, -49.837767),
(1502301, 'CapitÃ£o PoÃ§o', 'PA', -2.042009, -47.233429),
(1502400, 'Castanhal', 'PA', -1.268531, -47.876979),
(1502707, 'ConceiÃ§Ã£o do Araguaia', 'PA', -8.182317, -49.51264),
(1502772, 'CurionÃ³polis', 'PA', -6.228866, -49.624639),
(1502764, 'Cumaru do Norte', 'PA', -8.457465, -51.220703),
(1502756, 'ConcÃ³rdia do ParÃ¡', 'PA', -1.881699, -47.97466),
(1502608, 'Colares', 'PA', -0.93584, -48.273711),
(1501956, 'Cachoeira do PiriÃ¡', 'PA', -1.988655, -46.443227),
(1501907, 'Bujaru', 'PA', -1.632973, -48.089869),
(1502004, 'Cachoeira do Arari', 'PA', -0.869494, -48.877233),
(1501808, 'Breves', 'PA', -1.120975, -50.628133),
(1501782, 'Breu Branco', 'PA', -3.733455, -49.371195),
(1502202, 'Capanema', 'PA', -1.142074, -47.117901),
(1502152, 'CanaÃ£ dos CarajÃ¡s', 'PA', -6.424263, -50.085646),
(1502103, 'CametÃ¡', 'PA', -2.252964, -49.51352),
(1501402, 'BelÃ©m', 'PA', -1.240708, -48.459917),
(1501501, 'Benevides', 'PA', -1.339619, -48.275622),
(1501451, 'Belterra', 'PA', -3.191014, -54.992818),
(1501303, 'Barcarena', 'PA', -1.498616, -48.634232),
(1501758, 'Brejo Grande do Araguaia', 'PA', -5.73545, -48.43183),
(1501725, 'Brasil Novo', 'PA', -3.291974, -52.685349),
(1501709, 'BraganÃ§a', 'PA', -1.193057, -46.763597),
(1501600, 'Bonito', 'PA', -1.387331, -47.302277),
(1501576, 'Bom Jesus do Tocantins', 'PA', -5.054238, -48.763499),
(1500909, 'Augusto CorrÃªa', 'PA', -1.104701, -46.507414),
(1500958, 'Aurora do ParÃ¡', 'PA', -2.231147, -47.726308),
(1500800, 'Ananindeua', 'PA', -1.334079, -48.383548),
(1500859, 'Anapu', 'PA', -3.996764, -51.301705),
(1501253, 'Bannach', 'PA', -7.486378, -50.634395),
(1501204, 'BaiÃ£o', 'PA', -3.129751, -49.698981),
(1501006, 'Aveiro', 'PA', -3.667346, -56.016314),
(1501105, 'Bagre', 'PA', -2.373154, -50.181875),
(1500404, 'Alenquer', 'PA', -0.604435, -55.039098),
(1500347, 'Ãgua Azul do Norte', 'PA', -6.773037, -50.430146),
(1500305, 'AfuÃ¡', 'PA', -0.262834, -50.725847),
(1500701, 'AnajÃ¡s', 'PA', -0.82718, -49.961323),
(1500503, 'Almeirim', 'PA', 0.284724, -53.893561),
(1500602, 'Altamira', 'PA', -6.481135, -53.88526),
(1508100, 'TucuruÃ­', 'PA', -3.856256, -49.821114),
(1508084, 'TucumÃ£', 'PA', -6.820189, -51.391346),
(1508050, 'TrairÃ£o', 'PA', -5.13021, -56.003469),
(1508159, 'UruarÃ¡', 'PA', -3.583548, -53.807358),
(1508126, 'UlianÃ³polis', 'PA', -3.809805, -47.493503),
(1507961, 'Terra Alta', 'PA', -1.002593, -47.848292),
(1507953, 'TailÃ¢ndia', 'PA', -2.907748, -48.736701),
(1508035, 'Tracuateua', 'PA', -1.040925, -46.938985),
(1508001, 'TomÃ©-AÃ§u', 'PA', -2.636004, -48.267631),
(1507979, 'Terra Santa', 'PA', -1.951487, -56.458444),
(1508357, 'VitÃ³ria do Xingu', 'PA', -3.135272, -51.974004),
(1508308, 'Viseu', 'PA', -1.522958, -46.452988),
(1500131, 'Abel Figueiredo', 'PA', -4.95732, -48.424597),
(1500107, 'Abaetetuba', 'PA', -1.730259, -48.883052),
(1508407, 'Xinguara', 'PA', -6.917719, -49.63502),
(1500206, 'AcarÃ¡', 'PA', -2.035256, -48.411411),
(1508209, 'Vigia', 'PA', -0.933302, -48.104928),
(1507607, 'SÃ£o Miguel do GuamÃ¡', 'PA', -1.551534, -47.610726),
(1507458, 'SÃ£o Geraldo do Araguaia', 'PA', -6.189253, -48.748185),
(1507409, 'SÃ£o Francisco do ParÃ¡', 'PA', -1.206004, -47.746847),
(1507508, 'SÃ£o JoÃ£o do Araguaia', 'PA', -5.447375, -48.696753),
(1507474, 'SÃ£o JoÃ£o de Pirabas', 'PA', -0.783763, -47.217928),
(1507466, 'SÃ£o JoÃ£o da Ponta', 'PA', -0.870471, -47.964468),
(1507805, 'Senador JosÃ© PorfÃ­rio', 'PA', -4.169317, -51.774056),
(1507904, 'Soure', 'PA', -0.447912, -48.697724);

-- DGD - Carga oficial de COMPDECs a partir do arquivo compdecs.sql
-- Fonte: dump do sistema Multirriscos / Defesa Civil
-- Gerado para uso no DGD com charset utf8mb4.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS compdecs (
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
DELETE FROM compdecs;
INSERT INTO `compdecs` (`id`, `municipio_codigo`, `municipio`, `regiao_integracao`, `tem_compdec`, `prefeito`, `ubm_nome`, `coordenador`, `foto_coordenador`, `telefone`, `email`, `endereco`, `data_atualizacao`, `latitude`, `longitude`, `fonte_hash`, `sincronizado_em`, `criado_em`, `atualizado_em`) VALUES
(1, '1500107', 'Abaetetuba', 'Tocantins', 1, 'Francineti Maria Carvalho', '15Âº GBM - Abaetetuba', 'Marcio de Jesus Costa NegrÃ£o', '/uploads/compdec/coordenadores/coord_defesa_civil_1781698697_f8fcdded453394f5.jpg', '91991066706', 'defesacivilabaetetuba@gmail.com', 'Tv. JosÃ© Latino LÃ­dio da Silva, NÂº 1497 - Bairro: Santa Rosa / CEP: 68.440-000', '19/11/2025', -1.72489554, -48.89021516, '8ff1c8dcf949df1f06ac693f4a7b699c5f6cffeb090af7db7bc346508f2b4798', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 09:18:17'),
(2, '1500131', 'Abel Figueiredo', 'CarajÃ¡s', 0, 'Marcone Pereira Lacerda', '5Âº GBM - MarabÃ¡', NULL, NULL, NULL, NULL, NULL, '09/06/2025', -4.95239943, -48.39401156, 'bfb08da893acc4ae8ef58df6b22c3ffd9b74866cbfa2e20be30f44d9c6b0bbf9', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:25:47'),
(3, '1500206', 'AcarÃ¡', 'Tocantins', 1, 'Pedro Paulo Gouvea Moraes', '6Âº GBM - Barcarena', 'Edson Abreu dos Santos', '/uploads/compdec/coordenadores/coord_defesa_civil_1781699296_c5faed22275de1b8.jpg', '91999822367', 'edsonabreu305@hotmail.com', 'TV. SÃ£o JosÃ© S/N, bairro: Centro', '12/12/2025', -1.96089642, -48.19869711, '2d69317b9eca2948d754750d771d8e7df612d50a3fa2f5952bb79ee3ee1a6531', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 09:28:16'),
(4, '1500305', 'AfuÃ¡', 'MarajÃ³', 1, 'Henrique Sandro Lopes da Cunha', '11Â° GBM - Breves', 'Max SerrÃ£o de Oliveira', NULL, '(91) 99334-8202', 'maxoliveira0891@gmail.com', '-', '01/02/2026', -0.15481835, -50.39176042, 'c1f33dcafa73c0a11e2f9f70e4638bbce8a1e3839d9d2d5a4f01f9f3dee49937', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:26:30'),
(5, '1500347', 'Ãgua Azul do Norte', 'Araguaia', 1, 'Isvandires Martins Ribeiro', '10Â° GBM - RedenÃ§Ã£o', 'juarez costa correa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781699383_cc2542719db91134.jpg', '94992734119', 'gabinetedoprefeito.aguaazul@gmail.com', 'Avenida Lago Azul, S/N CEP: 68.533-000', '19/02/2026', -6.80240579, -50.48271112, 'c2984abd242df181328b3147cfe1462120056b40d66f8aaa36542dbc3e612ca0', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 09:29:43'),
(6, '1500404', 'Alenquer', 'Baixo Amazonas', 1, 'Heverton dos Santos Silva', '4Â° GBM - SantarÃ©m', 'Roger Rodrigues da Costa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781699422_3df8d7ef44e0eed3.jpg', '93991333731', 'alenquerdefesacivil@hotmail.com', 'Rua Pedro Vicente, Bairro: Centro, antiga DEPOL / CEP: 68.200-000', '09/07/2025', -1.95408213, -54.73992807, 'f4ec1e7b8b0e4d12979d39117425482ab5bb35d0363712f62336e6a41bf96a4c', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 09:30:22'),
(7, '1500503', 'Almeirim', 'Baixo Amazonas', 1, 'MARIA LUCIDALVA BEZERRA DE CARVALHO', '32Â° GBM - Almeirim', 'Bruno Deniel Brilhantes dos Santos', NULL, '94980413136', 'brbrilhante@yahoo.com.br', NULL, '15/04/2025', -1.52835220, -52.57724885, '28d64df0c73a8ed49c8c80d60f721bc5a4eccb958ca1ff6ec2a671bc20804270', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 17:20:49'),
(8, '1500602', 'Altamira', 'Xingu', 1, 'Loredan de Andrade Mello', '9Âº GBM - Altamira', 'Rivan Rodrigues dos Santos', '/uploads/compdec/coordenadores/coord_defesa_civil_1781699461_5186b6a1774d9602.jpg', '93988137037', 'cdc@altamira.pa.gov.br', 'R. Abel Figueiredo - Boa EsperanÃ§a CEP: 68377-430', '05/01/2026', -3.20666102, -52.21870385, 'd5add2fbcebe1593eb1902af2e72b6540b5a52b8f1879f89b5edb4dde2d5e4b0', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 09:31:01'),
(9, '1500701', 'AnajÃ¡s', 'MarajÃ³', 1, 'VIVALDO MENDES CONCEIÃ‡ÃƒO', '11Â° GBM - Breves', 'Ana LucrÃ©cia Silva de Souza', NULL, '(91) 98474-8160', 'anajasdefesacivil@gmail.com', 'Tv. PlÃ¡cido Soares Pinto', '01/02/2026', -0.98422699, -49.93822757, '2371cce7db60402c2534e1042534f3d93870bc96306918ec159dd65ae1f9de1d', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:28:11'),
(10, '1500800', 'Ananindeua', 'GuajarÃ¡', 1, 'DANIEL BARBOSA SANTOS', '3Âº GBM - Ananindeua', 'Cristiane Chagas Ataide', '/uploads/compdec/coordenadores/coord_defesa_civil_1781703238_cecca85578c0242f.jpg', '91981476949', 'defesacivilananindeua@gmail.com', 'Av. ClÃ¡udio Sanders, 10147, esquina com a rua bom sossego', '23/09/2025', -1.36613078, -48.37207112, '2a991507076702ee0f33bb660595bcef73fc99913106e495cc8dc3b3829bd808', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:33:58'),
(11, '1500859', 'Anapu', 'Xingu', 1, 'Luiz Carlos Aguiar Leite', '9Âº GBM - Altamira', 'Elionay Barros dos Santos', NULL, '91993859278', 'defesacivil.anapu@gmail.com', 'Av. GetÃºlio Vargas, NÂ° 98 - Bairro: Centro', '28/11/2025', -3.46879636, -51.20194543, '1f0090652df902cb919a986dd4b9c4c6e363bd275b51946362b6b4cca8aeeb76', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:28:47'),
(12, '1500909', 'Augusto CorrÃªa', 'Rio CaetÃ©', 1, 'FRANCISCO EDINALDO QUEIROZ DE OLIVEIRA', '24Âº GBM - BraganÃ§a', 'Alex Alves Assis Dos Reis', NULL, '(91) 98860-6256', NULL, 'PraÃ§a SÃ£o Miguel, NÂº 60 Bairro: Centro', '14/04/2025', -1.02165563, -46.63553089, 'f3e87e3193d134954e8642a7901354a0c5cd5123ccf1046d7015eab871265f6a', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:29:32'),
(13, '1500958', 'Aurora do ParÃ¡', 'Rio Capim', 1, 'VANESSA GUSMÃƒO MIRANDA', '27Âº GBM - Paragominas', 'Pedro Alex de Souza', NULL, '91987516478', 'pedroalex.pastor@gmail.com', 'Rua Raimunda Mendes, S/N - Bairro: Vila Nova ', '26/09/2025', -2.12910463, -47.56620176, '312f563aec98cb126b981aa93086eb283257360e0cad4ab662f6c54c2c36640d', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:29:52'),
(14, '1501006', 'Aveiro', 'TapajÃ³s', 1, 'JoÃ£o Gerdal Paiva Diniz Junior', '11Â° GBM - Breves', 'Leonardo Martins Cardoso', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705221_99ac3adf9ae0cda6.jpg', '93984223507', 'defesacivilaveiro2023@gmail.com', 'Av. Maj. TeotÃ´nio C.guimaraes - Aveiro, PA, 68150-000, Brasil', '11/2/2026', -3.60404962, -55.33205548, 'cb48f8d9d30f311cecf402b3d124139d2ff7d351a8d943bafecc096f24195bb1', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:07:01'),
(15, '1501105', 'Bagre', 'MarajÃ³', 1, NULL, '11Â° GBM - Breves', NULL, NULL, '93984223507', 'defesacivilmodelo2023@gmail.com', 'Av. MagalhÃ£es Barata, NÂ° 237, Bairro: Centro / CEP 68.150-000', '11/02/2026', -1.90053357, -50.21159813, 'ce25e9b21b91b503b4cfb2447c1b7faf0b1fa64e22e454705224f3d870e1efbb', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:30:34'),
(16, '1501204', 'BaiÃ£o', 'Tocantins', 1, 'LOURIVAL MENEZES FILHO', '22Âº GBM - CametÃ¡', 'Humberto Nunes da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705352_26f89166a06d670d.jpg', '91984369325', 'defesacivilbaiao@gmail.com', 'Rua Lauro SodrÃ©, fundos ao centro administrativo municipal', '15/12/2025', -2.79101516, -49.67245573, 'dd6f6fd4fcb066f112dbf7e8d52b44d549c1b1680c4a3726c555061b8b605e50', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:09:12'),
(17, '1501253', 'Bannach', 'Araguaia', 1, 'Valbetanio Barbosa Milhomem', '10Â° GBM - RedenÃ§Ã£o', 'Neury Maciel Alves', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704266_deefbfed18cff16c.jpg', '94991598848', 'neuryalves2012@hotmail.com', 'Av. ParanÃ¡, 27 - Centro, Belo Horizonte - MG, 30120-020, Brasil', '02/12/2025', -7.35288224, -50.40812482, 'e0c292a2419e17e7674c83d73c673d33580dc58b0ac6af49520100686d4195e9', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:51:06'),
(18, '1501303', 'Barcarena', 'Tocantins', 1, 'JOSÃ‰ RENATO OGAWA RODRIGUES', '6Âº GBM - Barcarena', 'Jose Antonio Rodrigues da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705365_f885cf9931f76e7b.jpg', '91982225157', 'defesa.civil@barcarena.pa.gov.br', 'PA-481, SN - Sub Prefeitura, Bairro: SÃ£o Francisco, Cep- 68447000', '05/01/2026', -1.50462077, -48.62562189, '30bb53ca61b2128957945ff72b2a44d36ec99c01c2e535cc14ca5742a1d287a4', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:09:25'),
(19, '1501402', 'BelÃ©m', 'GuajarÃ¡', 1, 'IGOR WANDER CENTENO NORMANDO', '30Â° GBM - QCG', 'Superintendente - MÃRCIO ROGÃ‰RIO ALVES PEREIRA', NULL, '91982411400', 'seopdec@segbel.pmb.pa.gov.br', 'Complexo Aldeia Cabana - Av. Pedro Miranda, s/n - Bairro: Pedreira / CEP: 66.080-000', '19/03/2026', -1.45457734, -48.50207448, '2a2597c497790f41333e9d043b837745ec38e170e0b4f488e852788c117082bf', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:32:27'),
(20, '1501451', 'Belterra', 'Baixo Amazonas', 1, 'ULISSES JOSÃ‰ MEDEIROS ALVES', '4Â° GBM - SantarÃ©m', 'Erica Keila Santos da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781703320_36ffb846f9d61794.jpg', '93992315415', 'defesacivil@belterra.pa.gov.br', 'Estrada 08, NÂ° 1053 Casa B, SÃ£o JosÃ©/68.143-00', '15/04/2025', -2.63714965, -54.94247107, '30e73df6314a5c9b998ff769d074f94865b69b3176e2e17f14b426e6fa8c24bd', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:35:20'),
(21, '1501501', 'Benevides', 'GuajarÃ¡', 1, 'LUZIANE DE LIMA SOLON OLIVEIRA', '25Âº GBM - Marituba', 'Pedro Paulo Azevedo da Silva', NULL, '(91) 9275-1020', 'dwapi.projeto@gmail.com', NULL, '01/02/2026', -1.36041365, -48.24337833, '3bce69f957dd943b2d2a5262f971f4b535c4df3c1b0761624fd4006bba683160', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:34:31'),
(22, '1501576', 'Bom Jesus do Tocantins', 'CarajÃ¡s', 1, 'Jeilson dos Reis Santos', '5Âº GBM - MarabÃ¡', 'Nandiel Silva Nascimento', NULL, '(94) 99145-3948', 'defesacivilbjt2019@gmail.com', 'Av. Jarbas Passarinho, S/N - Bairro: Centro', '01/02/2026', -5.04848008, -48.60711475, '020ba297ce0896ed4a6579bec8d825dc59b07aa2033e6779f8ecc6efa46108e6', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:35:04'),
(23, '1501600', 'Bonito', 'Rio CaetÃ©', 1, 'Alex Souza da Silva', '19Âº GBM - Capanema', 'Francisco Vilmar Pinheiro', NULL, '91989261262', 'vilmar_pinheiro@hotmail.com', 'Av. Charles Assad, PrÃ©dio da Prefeitura', '12/09/2025', -4.59121787, -49.03437938, 'b1c0b6d7fd9d14bc440fc6dfe9e810e60ce341c87e02a1b5446503467a6537c9', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:35:39'),
(24, '1501709', 'BraganÃ§a', 'Rio CaetÃ©', 1, 'Mario Ribeiro da Silva JÃºnior', '24Âº GBM - BraganÃ§a', 'Analina Silva da Costa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704966_6e82d441eb11ccdc.jpg', '91985765757', 'compdecbrg@gmail.com', 'Avenida Conselheiro JoÃ£o Alfredo, NÂº 1501 - Bairro: Centro / CEP: 68.600-00', '12/09/2025', -1.06674633, -46.76188709, '4dced30a6081af8e6087c265f21d58093f1f65fbea02b3198b1dbecb5fdfd2f7', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:02:46'),
(25, '1501725', 'Brasil Novo', 'Xingu', 1, 'WEDER MAKES CARNEIRO', '9Âº GBM - Altamira', 'SOLIMAR MACHADO DA SILVA', NULL, '93992430445', 'compdecbn@gmail.com', 'Av. Castelo Branco, 821 - Centro, Brasil Novo', '08/05/2026', -3.30256099, -52.54038931, '7d235743bb2b9c9ed517fa3fe52dca06e87e6fc1f3a09d02af642353677832ca', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:37:01'),
(26, '1501758', 'Brejo Grande do Araguaia', 'CarajÃ¡s', 0, 'Marcos Dias do Nascimento', '5Âº GBM - MarabÃ¡', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -5.70390880, -48.40653480, '31f2a4bd28a09215b7cdfdb33a90fdd4be4e3afba8c938aedf5c806d88b6e328', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:37:29'),
(27, '1501782', 'Breu Branco', 'Lago de TucuruÃ­', 1, 'FLAVIO MARCOS MEZZOMO', '8Âº GBM - TucuruÃ­', 'Cleidiane Rodrigues Batista', NULL, '(94) 99108-1786', 'gabinete@breubranco.pa.gov.br', 'Av. BelÃ©m, S/N - Bairro: Centro / CEP: 68.488-000', '01/02/2026', -3.77780707, -49.56900355, '4c4ba7156c7f8bb7acc7a5c06dd7aabdcee80af906bbcd8c6dfc0296b5084de9', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:38:14'),
(28, '1501808', 'Breves', 'MarajÃ³', 1, 'JOSÃ‰ ANTONIO AZEVEDO LEÃƒO', '11Â° GBM - Breves', 'Jorge Claudio Balierio Sardinha', NULL, '(91) 98543-7381', 'jc.sardinha73@gmail.com', 'Tv. Castilhos FranÃ§a NÂº 1685 - aeroporto', '01/02/2026', -1.69105809, -50.48303182, '7254d53441ab88da663fb42fcac3defdea587c3d4e8428fbe23119fbd20d2198', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:38:36'),
(29, '1501907', 'Bujaru', 'Rio Capim', 1, 'MIGUEL BERNARDO DA COSTA JUNIOR', '12Âº GBM - Santa Izabel do ParÃ¡', 'Edenilson', NULL, '(91) 98893-5821', 'defesacivilbujaru@gmail.com', 'Av. D.Pedro ll, NÂº 38 - Bairro: Centro / CEP: 68.670-000', '01/02/2026', -1.51529932, -48.04657588, 'd9f1e254e0cfc14f08d56349889d2e46dc7b9c6190b0b60699fce4b00200edaa', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:39:24'),
(30, '1502004', 'Cachoeira do Arari', 'MarajÃ³', 1, 'JAIME DA SILVA BARBOSA', '18Âº GBM - Salvaterra', 'Hildo Araujo de FranÃ§a', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704848_55eb7624ef62f994.jpg', '91985044354', 'hildo_franca@yahoo.com.br', 'Av. Dep. JosÃ© Rodrigues Viana, NÂº 560 - Bairro: Centro / CEP: 68.840-000', '21/05/2025', -1.00962311, -48.96118899, 'c140cf6237f3a538ed635cd27b48b42d03cd6be8e325dcaa2d0488a024a83816', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:00:48'),
(31, '1501956', 'Cachoeira do PiriÃ¡', 'Rio CaetÃ©', 1, 'MARIA BERNADETE BESSA DO NASCIMENTO', '19Âº GBM - Capanema', 'Paulo Souza da Cruz', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704974_9a7a11a32dcf21de.jpg', '91984543104', 'cruzpaulinho2005@gmail.com', 'Av. GetÃºlio Vargas - Bairro: Centro', '12/09/2025', -1.75811528, -46.54391243, 'defd31688a5c4089bd7c39599c4553e39d25e246b50efd6bbb107fff430da534', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:02:54'),
(32, '1502103', 'CametÃ¡', 'Tocantins', 1, 'VICTOR CORREA CASSIANO', '22Âº GBM - CametÃ¡', 'Jair dos Santos Costa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705372_2ab508c91adb5287.jpg', '91986228167', 'defesacivilcametapa@gmail.com', 'Av Gentil Bittencourt, 01 Centro - 68400-000', '17/12/2025', -2.24287049, -49.49804271, 'cfa5909f7774ce7bc717035317930779d494a456794e19d766b60b256374aa93', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:09:32'),
(33, '1502152', 'CanaÃ£ dos CarajÃ¡s', 'CarajÃ¡s', 0, 'JOSEMIRA RAIMUNDA DINIZ', '16Âº GBM - CanaÃ£ dos CarajÃ¡s', NULL, NULL, NULL, NULL, NULL, '01/02/2026', -6.53101732, -49.84955591, 'd5297814352cd34f40a494a110a24652f03858eed487f654a03878fb5a908541', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:41:56'),
(34, '1502202', 'Capanema', 'Rio CaetÃ©', 1, 'CLAUDIONOR MOREIRA DA COSTA', '19Âº GBM - Capanema', 'ALEX ALLAN MOREIRA SOUZA', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704983_c3175b73fb2111ef.jpg', '91982919101', 'semma@capanema.pa.gov.br', 'Funciona em consonÃ¢ncia com a SEMMA', '12/09/2025', -1.19384836, -47.17885228, '2675d462d2158bc9d1b47e282ccb42c3bc1981b8a6efcfb0f61a3fdb35a9d52a', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:03:03'),
(35, '1502301', 'CapitÃ£o PoÃ§o', 'Rio Capim', 1, 'Fernanda Oliveira Lima', '19Âº GBM - Capanema', 'SebastiÃ£o Vieira Alves', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705094_3e23343e04e9814f.jpg', '91983610011', 'meioambiente@capitaopoco.pa.gov.br', 'Tv. JosÃ© Barros da Silva, S/N - Bairro: Centro / CEP: 68.650-000', '02/10/2025', -1.74454959, -47.06437365, '4ca2914959149ca34f1928d3d9640606f4adfe7deb7027e16225eb008da84b77', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:04:54'),
(36, '1502400', 'Castanhal', 'GuamÃ¡', 1, 'HÃ©lio Leite da Silva', '2Âº GBM - Castanhal', 'Marcos Ferreira dos Santos', NULL, '91988665001', 'pastormarcossantos.ms@gmail.com', 'R. Rui Barbosa, 4, Nova Olinda - TO, 77790-000, Brasil', '17/11/2025', -1.29361039, -47.92579478, '938e6e027fdee63a6688d1b064538e967ccf301d8c37bdc674783b12eef89fb8', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:43:49'),
(37, '1502509', 'Chaves', 'MarajÃ³', 1, 'JOSE RIBAMAR SOUSA DA SILVA', '11Â° GBM - Breves', 'Deyvid Silva da Costa', NULL, '(91) 98313-4388', 'deyvidsilvadacosta2023@hotmail.com', NULL, '01/02/2026', -0.16406278, -49.98081337, '4467e528d94521d71a05c962c81f1e655f97ae87bd0451d2d2de205eb83c2726', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:45:15'),
(38, '1502608', 'Colares', 'GuamÃ¡', 1, 'MARIA LUCIMAR BARATA', '17Â° GBM - Vigia de NazarÃ©', 'Rafael Rodrigues Sacramento', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704608_6b4923d363dc02ea.jpg', '91989882087', 'defesacivilcolares@gmail.com', 'Trav. mal. Deodoro da Fonseca, S/N', '19/05/2025', -0.93020114, -48.28842483, 'b3ff675eb92ab3b134b605d5fc83446151f344a6320aaaaef19b6e6941b6308d', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:56:48'),
(39, '1502707', 'ConceiÃ§Ã£o do Araguaia', 'Araguaia', 1, 'Elida Elena Moreira', '10Â° GBM - RedenÃ§Ã£o', 'Roberto Francisco Marques de Sales', NULL, '(94) 98159-3599/ (91) 9188-3500', 'robertosalespa@hotmail.com', 'Av. CarajÃ¡s, NÂ° 3112, SÃ£o LuÃ­s / CEP: 68.540 000.', '30/05/2025', -8.26297813, -49.26285353, '120e0e394edaec1fa45a2254ed56854f2c466802e8ff80c33e26e36d2b86e93a', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-16 18:50:15'),
(40, '1502756', 'ConcÃ³rdia do ParÃ¡', 'Rio Capim', 1, 'Elisangela Paiva Celestino', '12Âº GBM - Santa Izabel do ParÃ¡', 'Paulo ConceiÃ§Ã£o de Paiva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705102_724825afc5df2cf6.jpg', '91991489938', 'defesacivilconcordia@gmail.com', 'Av. Marechal Deodoro da Fonseca, PrÃ©dio da Prefeitura Municipal', '07/10/2025', -1.99384213, -47.94531759, 'f3324f780b9bee8412d40bd1757f581a9de328cb60ee829a23cd4e1cf4d0d96d', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:02'),
(41, '1502764', 'Cumaru do Norte', 'Araguaia', 1, 'CELIO MARCOS CORDEIRO', '10Â° GBM - RedenÃ§Ã£o', 'Cherlis Regino Silva Neto', NULL, '(94) 99183-3389', 'adm2021@pmcn.pa.gov.br', NULL, '01/02/2026', -7.81022434, -50.76545290, '0ac5a4593f7f4d631bb732421c716f7986f875c0312aa54086bf306f572a6f05', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 09:57:21'),
(42, '1502772', 'CurionÃ³polis', 'CarajÃ¡s', 0, 'MARIANA AZEVEDO DE MARQUEZ', '23Â° GBM - Parauapebas', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -6.09780811, -49.60505651, 'e62b77a33c6030884cd23c4a58180d12f3ff344099dde1b2995393f7eea226bf', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:00:37'),
(43, '1502806', 'Curralinho', 'MarajÃ³', 1, 'CLEBER EDSON DOS SANTOS RODRIGUES', '11Â° GBM - Breves', 'Esmael Lopes dos Santos', NULL, '(91) 99268-9570', 'esmael81curralinho@gmail.com', 'Av. Jarbas Passarinho, S/N ao lado dos Correios', '01/02/2026', -1.81475485, -49.79889610, 'e8ceeb07edb6d6ecba9d683bb1558e8245e35c7795dc84f6ab40920f4755e477', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:01:05'),
(44, '1502855', 'CuruÃ¡', 'Baixo Amazonas', 1, 'JAIR DE SOUSA DAMASCENO', '4Â° GBM - SantarÃ©m', 'Manoel Gilvan Pereira', '/uploads/compdec/coordenadores/coord_defesa_civil_1781703352_c4b747d13e8ae434.jpg', '93991621676', 'defesacivilcurua@gmail.com', 'Terminal hidroviÃ¡rio de curuÃ¡, sala 01', '30/03/2026', -1.88859126, -55.11915555, 'a4f72896b4dbb346ad4bc25f3f455334d4169daa0b10b0453655ef31000304a6', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:35:52'),
(45, '1502905', 'CuruÃ§Ã¡', 'GuamÃ¡', 1, 'HAMILTON BRITO DOS SANTOS ALVES', '2Âº GBM - Castanhal', 'Ailson Modesto de Souza', NULL, '(91) 98849-9943', 'defesacivilcuruca@curuca.pa.gov.br', NULL, '01/02/2026', -0.72966806, -47.84910138, '1ea8304a604e974709c253f843fa6450183418d05645047686f0fe8f788045fd', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:02:51'),
(46, '1502939', 'Dom Eliseu', 'Rio Capim', 1, 'GERSILON DA SILVA GAMA', '27Âº GBM - Paragominas', 'CELSO HENRIQUE HOLANDA SILVA', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705112_834484a641ba6194.jpg', '94981352757', 'defesacivil@domeliseu.pa.gov.br', 'Rua GonÃ§alves Dias, NÂ° 637 - Bairro: centro', '09/06/2026', -4.29869912, -47.55570568, 'ab61fe170735cc525a56afd828b4c36bccc55c8ea97a5ad8a91395c22d205b09', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:12'),
(47, '1502954', 'Eldorado dos CarajÃ¡s', 'CarajÃ¡s', 1, 'Wagne Costa Machado', '16Âº GBM - CanaÃ£ dos CarajÃ¡s', 'Edson Cunha Ramalho', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704444_7ecc852ef5ff9733.jpg', '94991929555', 'defesacivileldoradodocarajas@gmail.com', 'Rua Rio Vermelho, S/N, prefeitura municipal de Eldorado dos CarajÃ¡s, sala anexo.', '19/05/2025', -6.10280892, -49.37205431, '1a0e01ebda7942d073db97dcae22111307dad1926e837e01c9b2852e3660bd1f', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:54:04'),
(48, '1503002', 'Faro', 'Baixo Amazonas', 1, 'PAULO VITOR MILEO CARVALHO', '4Â° GBM - SantarÃ©m', 'Francisco Pinto FeijÃ³', '/uploads/compdec/coordenadores/coord_defesa_civil_1781703375_17126047aa12819d.jpg', '92994639126', 'defesacivil.pmf@gmail.com', 'Rua Duque de Caxias, Bairro: Campina', '16/04/2025', -2.17419418, -56.74651047, '37297dee193d3644aa33889736289ced9fa888fe94b69e2794e58d9ff71791e9', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:36:15'),
(49, '1503044', 'Floresta do Araguaia', 'Araguaia', 0, 'MAJORRI CERQUEIRA SANTIAGO', '10Â° GBM - RedenÃ§Ã£o', NULL, NULL, NULL, NULL, NULL, '01/02/2026', -7.56331095, -49.70632958, '52bd9373a4238aabdecd37b3a0e14425f3aef324fcf8bff75acba16e20ec87dd', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:10:21'),
(50, '1503077', 'GarrafÃ£o do Norte', 'Rio Capim', 1, 'Marcones Farias Do Nascimento', '19Âº GBM - Capanema', 'Francisco de Assis Teixeira de Souza', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705121_6a75573991aa1e8b.jpg', '91984224331', 'aldinhogarrafao@gmail.com', 'Avenida sete de setembro NÂ° 517, pedrinhas', '02/10/2025', -1.93642911, -47.04636722, 'eebf7cfd92646be89c594d40f599f91175e498874ead8a3ba749ff1584f8f0bc', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:21'),
(51, '1503093', 'GoianÃ©sia do ParÃ¡', 'Lago de TucuruÃ­', 1, 'Francisco Eduardo Oliveira Silva', '14Âº GBM - TailÃ¢ndia', 'Amanda da Silva Borges', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704750_9e5ec4c97d8fe61a.jpg', '94991092105', 'defesacivil@goianesia.pa.gov.br', 'Rua Pedro Soares de Oliveira, SN-colegial', '29/01/2026', -3.83814877, -49.09927701, 'c554a6b4bd14b840c87ff2cf84b3036aef2cfaf7f910995cfded498b0587f928', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:59:10'),
(52, '1503101', 'GurupÃ¡', 'MarajÃ³', 1, 'Maria Iracilda De Almeida Alho', '11Â° GBM - Breves', 'Iran Carlos Pinheiro de Lima', NULL, '(91) 999838973', 'iranhelena@gmail.com', NULL, '01/02/2026', -1.40113248, -51.64656788, 'a825399911e5ffa471ecd5a3e46e07bbd3579eff602a0cdb3f34696ab7d34641', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:12:12'),
(53, '1503200', 'IgarapÃ©-AÃ§u', 'GuamÃ¡', 1, 'MÃ¡rcio Nogueira Lopes', '2Âº GBM - Castanhal', 'Cristiani Friaes Chaves', NULL, '91984724740', 'friaescristiani743@gmail.com', NULL, '19/05/2025', -1.12771049, -47.62438012, 'f2a48ebda45e5283c278334873bcf0fce54efea7e793f8937218bf594da501fb', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:12:41'),
(54, '1503309', 'IgarapÃ©-Miri', 'Tocantins', 1, 'ROBERTO PINA OLIVEIRA', '15Âº GBM - Abaetetuba', 'Wladimir Santa Maria Afonso', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705382_20cf4dfb0720a1ce.jpg', '91987423465', 'defesaciviligarapemiri@gmail.com', 'MaiauatÃ¡, IgarapÃ©-Miri - PA, 68430-000, Brasil', '26/01/2026', -1.97240590, -48.96223734, '59518c0a68cc94bb23bf734bb55e07fe64fb6f89452499196c70b36d8fa63819', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:09:42'),
(55, '1503408', 'Inhangapi', 'GuamÃ¡', 0, 'JOSÃ‰ ALVES FEITOSA OLIVEIRA JÃšNIOR', '2Âº GBM - Castanhal', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -1.43048669, -47.91006800, '4c4f482b2b598b2124e40a0c84dc21d852638c9cbbe62b4d43613d445bc5c86e', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:13:38'),
(56, '1503457', 'Ipixuna do ParÃ¡', 'Rio Capim', 1, 'ARTEMES SILVA DE OLIVEIRA', '27Âº GBM - Paragominas', 'Marcus Vinicius Moraes Castelo Branco', NULL, '(91) 98880-5457', 'comdecipx@gmail.com', 'Tv. CristÃ³vÃ£o Colombo', '01/02/2026', -2.55442377, -47.49517021, '0325a0ea3e69cf9e9b84374681f60cf5333ce26f09c70d6175c2db62abf30dc4', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:14:02'),
(57, '1503507', 'Irituia', 'Rio Capim', 1, 'Pio X Sampaio Leite Junior', '28Â° GBM - SÃ£o Miguel do GuamÃ¡', 'Risonaldo lima de Souza', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705133_3995ca4aaa3783bc.jpg', '09191875728', 'rysolima3@gmail.com', 'Siqueira Campos, NÂº 44 - Bairro: Centro / 68.655-000', '24/07/2025', -1.77088138, -47.43732064, '5a51b2efabe1e14f11927f0c847c7b8242c0e8dfb69b430840d23d77b63c2cd3', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:33'),
(58, '1503606', 'Itaituba', 'TapajÃ³s', 1, 'Nicodemos Alves De Aguiar', '7Â° GBM - Itaituba', 'Ricardo Douglas da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705232_1a799ad4b637ecd7.jpg', '93992126590', 'ricardodouglasitb@gmail.com', 'rodovia transamazonica km-1, anexo ao ginÃ¡sio municipal de itaituba', '11/2/2026', -4.26571241, -55.99176397, 'e4d99dad309c07cda455ed1cdc3296913b2fd4e84ab5d0e8663c9ef9ec9c0630', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:07:12'),
(59, '1503705', 'Itupiranga', 'Lago de TucuruÃ­', 1, 'Wagno da Silva Godoy', '5Âº GBM - MarabÃ¡', 'Nandiel Silva do Nascimento', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704758_7018ab09feae06f4.jpg', '94991453948', 'seplad@itupiranga.pa.gov.br', 'Av. Quatorze de Julho, 12, Itupiranga - PA, 68580-000, Brasil', '12/02/2026', -5.13416761, -49.32795827, '593d83c50cded4fa0c2c5084571e4afa42311880b6afd402eb643306ef072611', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:59:18'),
(60, '1503754', 'Jacareacanga', 'TapajÃ³s', 1, 'SEBASTIÃƒO AURIVALDO PEREIRA', '7Â° GBM - Itaituba', 'JosÃ© da Silva Cavalcante', NULL, '(93) 991805920', 'gabinete@jacareacanga.pa.gov.br', 'Av. Brigadeiro Haroldo Veloso, NÂº 34 - Bairro: Centro', '01/02/2026', -6.22184662, -57.75635268, '3125aeb213905fa5fa896bac15aaad9fa15fee0ad3518b1b686a705fa30ddb5f', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:19:23'),
(61, '1503804', 'JacundÃ¡', 'Lago de TucuruÃ­', 1, 'ITONIR APARECIDO TAVARES', '14Âº GBM - TailÃ¢ndia', 'ILDO MATOS LIMA', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704768_8d7d5d58d8e580c5.jpg', '94991441644', 'defesaciviljacunda@gmail.com', 'Rua Pinto Silva S/N, Centro Administrativo dentro da prefeitura', '12/01/2026', -4.44740248, -49.11206192, '37198ba45fabceead4f2ff797bafbb561ceb8377d54dbebae5faa3af9d841fca', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:59:28'),
(62, '1503903', 'Juruti', 'Baixo Amazonas', 1, 'LUCIDIA BENITAH DE ABREU BATISTA', '4Â° GBM - SantarÃ©m', 'JoÃ£o Paulo Vieira do Santos', '/uploads/compdec/coordenadores/coord_defesa_civil_1781703915_f43d5b4a6ecf1818.jpg', '93981000505', 'defesaciviljuruti@gmail.com', 'Tv TurÃ­bio Vieira, Bairro: Centro', '05/05/2025', -2.17012944, -56.09635885, '5247977856b5597a7b80ed6fb39da6de9bacccf6b08d267d9bbad2ae74d1ed88', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:45:15'),
(63, '1504000', 'Limoeiro do Ajuru', 'Tocantins', 1, 'ALCIDES ABREU BARRA', '22Âº GBM - CametÃ¡', 'Reginaldo Fayal de Freitas', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705390_c6a9c51ab7276b89.jpg', '91992650478', 'defesacivil@limoeirodoajuru.pa.gov.br', 'Marechal Rondon. Matinha. 68415-000', '12/01/2026', -1.89434277, -49.38138706, '0dabaad2764e190e0077187c920aeab747e1cc143c5d37705393418a511316cb', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:09:50'),
(64, '1504059', 'MÃ£e do Rio', 'Rio Capim', 1, 'Bruno Anderson Dos Anjos Rabelo', '28Â° GBM - SÃ£o Miguel do GuamÃ¡', 'Paulo Silva de Aviz Junior', NULL, '(91)988627008', 'pauloavizjr@gmail.com', 'Rua Pedro Vieira, 192, Bairro NazarÃ© em MÃ£e do Rio-PA.', '01/02/2026', -2.05403244, -47.54558730, 'b297d28d9ebc531854f71b01d883f5bcfff3b75af878c01743236bfa3539de0e', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:22:05'),
(65, '1504109', 'MagalhÃ£es Barata', 'GuamÃ¡', 1, 'Gerson Miranda Lopes', '2Âº GBM - Castanhal', 'Rivaldo Nunes Barata', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704621_a10f29936bfbc421.jpg', '91984910661', 'comdecmb@gmail.com', 'MagalhÃ£es Barata-PA, rua VerÃ­ssimo Pinto, S/N - Centro; CEP - 6872200', '06/05/2025', -0.79442912, -47.59894619, 'f68e732ec0b7fd01b115b0c86a28c4f4e0bc11ee2771c7d3bfd502bf45b95d1b', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:57:01'),
(66, '1504208', 'MarabÃ¡', 'CarajÃ¡s', 1, 'Antonio Carlos Cunha SÃ¡', '5Âº GBM - MarabÃ¡', 'Marcus Victor Lima Norat', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704454_d82f1ee2ae4937e1.jpg', '93933006252', 'compdec@maraba.pa.gov.br', 'Rua Sete de junho, NÂ°1020 - Bairro: Velha MarabÃ¡ / CEP: 68.500-300', '19/05/2025', -5.34768990, -49.09846834, '46330c913c136f211cf58940b06e332036c88a1e6f332339c77b1abfc127fbc5', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:54:14'),
(67, '1504307', 'MaracanÃ£', 'GuamÃ¡', 1, 'REGINALDO DE ALCANTARA CARRERA', '2Âº GBM - Castanhal', 'BRUNO OLIVEIRA DA SILVA', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704641_9a36eb5002b0ee53.jpg', '21994835651', 'defesacivilmaracana2025@gmail.com', 'rua espirito santo, sn-Centro-MaracanÃ£-PA', '06/03/2026', -0.76270656, -47.45640123, 'ecea599924522fa4d586f141bc2437611a49d9ad5abfb5c3c0cb8413918ea39f', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:57:21'),
(68, '1504406', 'Marapanim', 'GuamÃ¡', 1, 'CLEITON ANDERSON FERREIRA', '2Âº GBM - Castanhal', 'AndrÃ© Borges', NULL, '91999034315', NULL, NULL, '19/05/2025', -0.71611151, -47.69942168, '5cd10f0a528658912977ee234a3630cf4203788300cab869e98878a7031ec4c7', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:24:00'),
(69, '1504422', 'Marituba', 'GuajarÃ¡', 1, 'PATRICIA RONIELLY RAMOS MENDES', '25Âº GBM - Marituba', 'Marcos Santos da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704536_a4fb94183ce4c6e9.jpg', '91981138799', 'defesacivil@marituba.pa.gov.br', 'Rua Bezerra falcÃ£o, NÂ° 1754, Decouville / CEP: 67.200-000', '10/09/2025', -1.36413858, -48.33670722, 'a2c15c7ed691bf59b0211aa94d1b59dfc94aac96d7624b865551a53336d3a7d5', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:55:36'),
(70, '1504455', 'MedicilÃ¢ndia', 'Xingu', 1, 'JULIO CESAR DO EGITO', '9Âº GBM - Altamira', 'WESLLEYN HOFFMANN INÃCIO', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705470_ca7c444f81d8ad97.jpg', '93992133014', NULL, NULL, '26/03/2026', -3.44627312, -52.88950914, '614a11852a10f03ecbd163b381f550af40e57e55ae01ac175d41e752a455ecbf', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:11:10'),
(71, '1504505', 'MelgaÃ§o', 'MarajÃ³', 0, 'JOSE FRANCISCO VIEGAS DIAS', '11Â° GBM - Breves', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -1.80719419, -50.71276359, '2949cdcd42d702bc7021401d6485db6bdb9dd352fcdd7c3d6fc6a717720a2956', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:25:06'),
(72, '1504604', 'Mocajuba', 'Tocantins', 1, 'AluÃ­sio Valente Vieira', '22Âº GBM - CametÃ¡', 'Domingos Fayal da Silva', NULL, '91 982479606', NULL, NULL, '01/02/2026', -2.58370751, -49.51073877, '030c0df0ece05d6009ba4a3ae65895d58dacc3ea7e5f0231c2a662a990d9bb3d', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:25:25'),
(73, '1504703', 'Moju', 'Tocantins', 1, 'Rubens de Sousa Teixeira', '29Âº GBM - Moju', 'Marcelo Fabrico de Lima Azevedo', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705400_10919c432f3dba47.jpg', '91993933886', 'compdec@moju.pa.gov.br', 'Av. das Palmeiras nÂº 35/ bairro AviaÃ§Ã£o/ 68450-000', '22/04/2025', -1.88401889, -48.76791718, 'd9c73335fcc409edd769b1faf8d7f2d4f6335f326b2c6ba042726da52c103007', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:10:00'),
(74, '1504752', 'MojuÃ­ dos Campos', 'Baixo Amazonas', 1, 'JAILSON DA COSTA ALVES', '4Â° GBM - SantarÃ©m', 'Guilherme Dourado Viana', '/uploads/compdec/coordenadores/coord_defesa_civil_1781703987_fe383ff776b93342.jpg', '93991148485', 'defesacivil@mojuidoscampos.pa.gov.br', 'PA-431, Alto Alegre, S/N - Secretaria Municipal de Infraestrutura / CEP: 68.129-000', '15/04/2025', -2.68187562, -54.64179312, '19cb09d506667f720564eec357d8fbf43743ff6d07dde13ce8be0d041dd1d420', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:46:27'),
(75, '1504802', 'Monte Alegre', 'Baixo Amazonas', 1, 'JOSE ALFREDO SILVA HAGE JÃšNIOR', '4Â° GBM - SantarÃ©m', 'Geziel Wallace Lemos da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704005_23d739629975a265.jpg', '93984093736', 'defesacivil@montealegre.pa.gov.br', 'PraÃ§a Tiradentes, Bairro: Cidade Baixa NÂ° 100', '15/04/2025', -2.00752494, -54.07033466, 'a9eaf5705b82b418c5fff7e96584274c19c0554a9f255eba6f6dc745043c2ae8', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:46:45'),
(76, '1504901', 'MuanÃ¡', 'MarajÃ³', 1, 'Marcos Paulo Barbosa Pantoja', '18Âº GBM - Salvaterra', 'Gabriel Pereira Cruz', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704862_3b9a191854d85de2.jpg', '91984884824', 'compdecmuana@gmail.com', 'Camarodromo Municipal, Av. Manoel Izidro da Silva, S/N - Centro', '21/05/2025', -1.52903127, -49.21705269, '14feebe0c86cefce7d3799e21977879d77105c8e2f877b7a73be729b4b8ee621', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:02'),
(77, '1504950', 'Nova EsperanÃ§a do PiriÃ¡', 'Rio Capim', 1, 'ALCINEIA CARMO DOS SANTOS', '19Âº GBM - Capanema', 'Ramiro Celso Pereira Mendes', NULL, '91984314643', 'ramiro_mendes3@hotmail.com', NULL, '01/02/2026', -2.27058073, -46.96709820, '5febc67e87ce66c27e12ffe4db6bb6e317e2dc0248851b0715c64f0b6f65fe04', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:28:50'),
(78, '1504976', 'Nova Ipixuna', 'Lago de TucuruÃ­', 1, 'Everton Macias Freitas', '5Âº GBM - MarabÃ¡', 'Wilson Saraiva Nabate', NULL, '(94) 9248-4740/ (91) 99245-1313/ (91) 99180-2650', 'compdecipixuna@gmail.com', NULL, '01/02/2026', -4.91090609, -49.07368657, 'cd1668af993cc819fb82cd6dbd63b4c8b5dfbb1a39f8791444a70035467b9d06', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:29:09'),
(79, '1505007', 'Nova Timboteua', 'Rio CaetÃ©', 1, 'Aline Costa Da Silva', '19Âº GBM - Capanema', 'Carlos Matheus Silva Lima', NULL, '91985573286', 'defesacivilnt@gmail.com', 'Avenidade BarÃ£o do Rio Branco - Centro', '09/12/2025', -1.20584731, -47.38843666, '1006edb66ab767a34409b0a91df96ea56f0e1b2c1a19f97e574dfc7f6c0369e5', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:29:34'),
(80, '1505031', 'Novo Progresso', 'TapajÃ³s', 1, 'GELSON LUIZ DILL', '33Â° GBM - Novo Progresso', 'Silas Silva Lima', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705242_4b924645e14f619f.jpg', '93981089445', 'defesacivil@novoprogresso.pa.gov.br', 'Tv. BelÃ©m, 768 - Jardim Europa, Novo Progresso - PA, 68193-000, Brasil', '17/12/2025', -7.03778629, -55.40640609, '8a88c2a4c2ea4762787f602e1e59243c882c54779f0b1fcb179a40dffe7af245', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 17:29:30'),
(81, '1505064', 'Novo Repartimento', 'Lago de TucuruÃ­', 1, 'VALDIR LEMES MACHADO', '8Âº GBM - TucuruÃ­', 'Ricardo Lopes Leite', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704779_af60a587ac7f8700.jpg', '94992812244', 'novorepartimentodefesacivil@gmail.com', 'antiga br 230, bairro vila tucurui, secretÃ¡ria de obras', '12/12/2025', -4.24823360, -49.95123872, '1c878b3bc8d75a53a9126edf0913bf2ca0b9b282d23c78be2231726d8d3715bb', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:59:39'),
(82, '1505106', 'Ã“bidos', 'Baixo Amazonas', 1, 'JAIME BARBOSA DA SILVA', '4Â° GBM - SantarÃ©m', 'IGOR EWERTON VASCONCELOS PINTO', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704042_035944025991bb53.jpg', '93991067772', 'igor.epv.28@icloud.com', 'Av. Nelson Souza, S/N, bairro de FÃ¡tima - Ã“bidos. CEP 68250-000', '15/04/2025', -1.91632100, -55.51585189, 'a0b15939e5058902db85827e1a290ba1ef281ace243c7dc5d39c44b4709183e8', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:47:22'),
(83, '1505205', 'Oeiras do ParÃ¡', 'Tocantins', 1, 'GILMA DRAGO RIBEIRO', '22Âº GBM - CametÃ¡', 'Roberth Carlim da Silva Barbosa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705412_b9bc45e4a9cb90a3.jpg', '91992378192', 'roberthcbarbosa@gmail.com', 'PrÃ©dio da prefeitura AV XV de novembro, centro', '23/01/2026', -2.00351286, -49.85463974, '828364902bd55406798d38db30e6912303e5b7a9961bdf1aed34ace3a5a364e1', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:10:12'),
(84, '1505304', 'OriximinÃ¡', 'Baixo Amazonas', 1, 'JOSE WILLIAN SIQUEIRA DA FONSECA', '4Â° GBM - SantarÃ©m', 'JosÃ© Paulo Pereira PaixÃ£o', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704057_00dd9be7c4cb766c.jpg', '93991390051', 'jpppaixao@hotmail.com', 'Tv. Santa Luzia, NÂº 1866, Bairro: Nossa senhora das GraÃ§as / CEP: 68.270-000', '20/05/2025', -1.76951661, -55.86653308, 'b72c1d9c6d25e098b2a391d5957efbba3ad85be3ea1bd3b7425ee62ec79df1b0', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:47:37'),
(85, '1505403', 'OurÃ©m', 'Rio Capim', 1, 'VALDEMIRO FERNANDES COELHO JUNIOR', '19Âº GBM - Capanema', 'Marinalva dos Reis Sales', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705143_01a4ddeef1e61b57.jpg', '91983393812', 'defesacivil@ourem.pa.gov.br', 'Rua Hermenegildo Alves, bairro: centro S/N', '20/05/2025', -1.55049683, -47.11371222, 'cfed8f014f0c25546f73a979ab1d35d56784ca8ca637ee7d8bf5802d062493e2', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:43'),
(86, '1505437', 'OurilÃ¢ndia do Norte', 'Araguaia', 1, 'JULIO CESAR DAIREL', '10Â° GBM - RedenÃ§Ã£o', 'Eduardo Henrique Lima Vieira', NULL, '94991643311', 'eduhlv95@gmail.com', NULL, '02/06/2026', -6.75395324, -51.06720130, '5a4db8bec82657d8df0d0b39202f514ed845b868daa95173e99d04516161c5f3', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:32:19'),
(87, '1505486', 'PacajÃ¡', 'Xingu', 1, 'FREDSON PEREIRA DA SILVA', '10Â° GBM - RedenÃ§Ã£o', 'Andre Fontes Rodrigues', NULL, '(94) 99166-0405', 'andrefontes.gabinete@gmail.com', NULL, '01/02/2026', -3.83044350, -50.64055553, '87c300563b18b0c1c3c42a5a1355ac6589a6be231da24a616c4b53b173c218aa', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:32:35'),
(88, '1505494', 'Palestina do ParÃ¡', 'CarajÃ¡s', 1, 'MÃRCIO DIAS DO NASCIMENTO', '8Âº GBM - TucuruÃ­', 'Valquiria de Souza Nascimento', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704463_646fdbb3ed00c7bc.jpg', '94984052295', 'palestinadc22@gmail.com', 'Rua Sargento Ibraim, em frente ao posto de saÃºde, Centro', '21/05/2025', -5.74713110, -48.31667785, '44c3bfdc5ee849ba636082fdc50858967c36d864629f84d7b99f377b11bf717e', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:54:23'),
(89, '1505502', 'Paragominas', 'Rio Capim', 1, 'Marcio Dias Do Nascimento', '5Âº GBM - MarabÃ¡', 'OZIEL MORAES DA SILVA', NULL, '91984749119', 'ozielbm@gmail.com', NULL, '30/03/2026', -2.99982417, -47.35534459, 'e211c61b0867d99b3a8a5fc2f3aceee1f27b7344eba6b301c4d67ce6eaa31ea1', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:43:45'),
(90, '1505536', 'Parauapebas', 'CarajÃ¡s', 1, 'Aurelio Ramos de Oliveira Neto', '27Âº GBM - Paragominas', 'Walter Viana de Carvalho Filho', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704471_d2401ecb58079dbf.jpg', '94992739929', 'defesa.civil@parauapebas.pa.gov.br', 'Avenida Milton Ribeiro, QD 73, Lt 53, Parque dos CarajÃ¡s, Parauapebas - PA', '06/05/2025', -6.09190449, -49.89052965, '10e6dd28ee4f99849b250465e5f9d36a58936e56f7690c00575bb32c988f165a', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:54:31'),
(91, '1505551', 'Pau D\'Arco', 'Araguaia', 1, 'Domingos Guedes Neto', '23Â° GBM - Parauapebas', 'JoÃ£o Vitor monteiro', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704281_6950b15d4ef0f9d9.jpg', '94991395996', 'assistenciasocial.paudarco@gmail.com', 'Av. Boa sorte S/N, centro', '05/01/2026', -7.83011437, -50.04027795, 'a5bfd32838ff38964f2325523ffe55695c04ab1c86020726b7dfac7f7b644e6e', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:51:21'),
(92, '1505601', 'Peixe-Boi', 'Rio CaetÃ©', 1, 'JOÃƒO PEREIRA DA SILVA NETO', '19Âº GBM - Capanema', 'Edilson Sabino da Silva', NULL, '(091) 98428-6263 /(91) 93821-1281', 'edilson.sabino@yahoo.com.br', NULL, '01/02/2026', -1.19331937, -47.31690536, '85e4bd8e346126f1ee32f1632098f0984ef48730cddb54ff7b7f562ae18d2692', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:45:08'),
(93, '1505635', 'PiÃ§arra', 'CarajÃ¡s', 1, 'LAANE BARROS LUCENA FERNANDES', '16Âº GBM - CanaÃ£ dos CarajÃ¡s', 'Ellany Val Porto Guida Lima', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704491_ae4484de9645c525.jpg', '94991441991', 'micilene_santos@yahoo.com', NULL, '12/12/2025', -6.44287487, -48.86261266, '014728babf99781d702535f343da89afce8b2e8b6f788680763fa3db91d31f99', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:54:51'),
(94, '1505650', 'Placas', 'Xingu', 1, 'ARTHUR POSSIMOSER DO SOCORRO', '7Â° GBM - Itaituba', 'JoÃ£o Paulo Coelho do Nascimento', NULL, '93984045036', 'jpengenheiro99@gmail.com', NULL, '01/02/2026', -3.86369580, -54.21610741, 'bd787fbf19346cd947544462088f123208f7a044ba1b532658ae8a0b83d638c9', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:45:54'),
(95, '1505700', 'Ponta de Pedras', 'MarajÃ³', 1, 'CONSUELO MARIA DA SILVA CASTRO', '18Âº GBM - Salvaterra', 'Cristiano Costa Paula', NULL, '(91) 98440-3841', 'cristianocpaula2008@hotmail.com', NULL, '01/02/2026', -1.39372139, -48.87116115, 'c4b2b1275e71ffd601066f0cbc9803bdc5b33b4cec4a9e334853b51e15f75986', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:46:21'),
(96, '1505809', 'Portel', 'MarajÃ³', 1, 'VICENTE DE PAULO FERREIRA OLIVEIRA', '11Â° GBM - Breves', 'Handerson Antunes Borges', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704873_300417eb0e39a13f.jpg', '91985409770', 'dcportelpara@gmail.com', 'Rua Augusto Monte Negro, bairro: centro', '21/05/2025', -1.93893500, -50.82396691, 'd2d334d5cd3fb33f8b2a0730f7825eb0a385fc07c82a70ddc906f8baee69384c', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:13'),
(97, '1505908', 'Porto de Moz', 'Xingu', 1, 'Rivaldo Salviano Campos', '9Âº GBM - Altamira', 'Lineu Viana Franco', NULL, '(91) 98417-5940', 'bvlineu@hotmail.com', 'AerÃ³dromo Municipal, Rua RepÃºblica', '01/02/2026', -1.75384695, -52.23960432, '93b4a01dfe7b0807c021e0730e57bc307ea715db7534539a19d3fdcb9660716c', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:46:55'),
(98, '1506005', 'Prainha', 'Baixo Amazonas', 1, 'GANDOR CALIL HAGE NETO', '4Â° GBM - SantarÃ©m', 'Pedro Paulo da Silva Farias', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704129_d64c7b6e589662ea.jpg', '93984144721', 'compdec.prainha@gmail.com', 'Centro administrativo municipal PA, 419, S/N SÃ£o SebastiÃ£o, Prainha', '18/03/2026', -1.78754171, -53.48424550, 'd32e5bd2a4218cf0bb5996f582439a40d5f8f87b46de6267e3e4c5dce93f8e64', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:48:49'),
(99, '1506104', 'Primavera', 'Rio CaetÃ©', 1, 'ÃUREO BEZERRA GOMES', '19Âº GBM - Capanema', 'Elcinei Alexandre da Silva', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704997_6fec064884efe924.jpg', NULL, 'elcineialexandre@gmail.com', NULL, '12/12/2025', -0.93999020, -47.11717826, '76c0f41612ceb7ee6e0e57758791e3d4ccfb0da2950d5345ed5023ead68188a7', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:03:17'),
(100, '1506112', 'Quatipuru', 'Rio CaetÃ©', 1, 'JOSÃ‰ AUGUSTO DIAS DA SILVA', '19Âº GBM - Capanema', 'ANTÃ”NIO CLEBSON SIQUEIRA DA COSTA', NULL, '91998344643', 'clebsonsemma@gamail.com', 'R. CÃ´nego Siqueira Mendes - Quatipuru, PA, 68709-000, Brasil', '09/04/2025', -0.89848627, -47.00489167, 'd2926c926ee0f4dff5d15760d2265399b3c4ba9f29973e0e571507523e9d24b2', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:47:59'),
(101, '1506138', 'RedenÃ§Ã£o', 'Araguaia', 1, 'Rener De Santana Miranda', '10Â° GBM - RedenÃ§Ã£o', 'LUCIANO CARVALHO DUARTE', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704292_6c48cc322c55a159.jpg', '94993010000', 'lucianoduartegabinete@gmail.com', 'Vila Paulista, RedenÃ§Ã£o - PA, 253, 3Â° andar', '13/01/2026', -8.03586593, -50.03566772, '3e151a353543f7f1cb4198f8dd40c6413f0ca8b468c4062b4f0312baa90a4c6d', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:51:32'),
(102, '1506161', 'Rio Maria', 'Araguaia', 1, 'MÃRCIA FERREIRA LOPES', '10Â° GBM - RedenÃ§Ã£o', 'Selthon Sthwart Reis Alencar', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704303_42e1cac5483a5d78.jpg', '94992998468', 'defesacivil@riomaria.pa.gov.br', 'Avenida Rio Maria, esquina com a rua JoÃ£o Paulo II, Setor Planalto', '05/01/2026', -7.31423273, -50.04660239, '61d47714b40856600611cfee46bf43ce9899c47eafe9997c0b5046c61b54c421', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:51:43'),
(103, '1506187', 'Rondon do ParÃ¡', 'Rio Capim', 1, 'ADRIANA ANDRADE OLIVEIRA', '5Âº GBM - MarabÃ¡', 'Desthene Dias de Moura JÃºnior', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705152_52c1484551006bbe.jpg', '94991482630', 'comdecrondon@gmail.com', 'Rua GonÃ§alves Dias, 400', '07/10/2025', -4.77892956, -48.06676696, '78babcdbda0715e613677502d5a5e8ed4eda92b6fda96c5b036dec43a775f74b', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:52'),
(104, '1506195', 'RurÃ³polis', 'TapajÃ³s', 1, 'JOSE FILHO CUNHA DE OLIVEIRA', '7Â° GBM - Itaituba', 'ROBSON ALVES DA SILVA', NULL, '(93)991847734', 'robsonruropolis@gmail.com', NULL, '01/02/2026', -4.09712497, -54.90781562, '178356ef2e6ca2886f2f910de9739c6c8ad5bea4336b55377a5ab8c2c570893a', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:50:02'),
(105, '1506203', 'SalinÃ³polis', 'Rio CaetÃ©', 1, 'CARLOS ALBERTO DE SENA FILHO', '13Âº GBM - SalinÃ³polis', 'Vladson Michel Monteiro Nunes', NULL, '91981772507', 'vladsonmichel@gmail.com', 'Av. Doutor Miguel de Santa BrÃ­gida, 180', '07/10/2025', -0.61427069, -47.35651648, '3a8ac0f82703ce03705d5d6f9b911cee8dbfe6aaf1154282ffa55a61a1581784', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:51:00'),
(106, '1506302', 'Salvaterra', 'MarajÃ³', 1, 'Valentim Lucas de Oliveira', '18Âº GBM - Salvaterra', 'SALLAN MELO DOS SANTOS', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704880_14b395ec2efbcc77.jpg', '91981663761', 'defesacivil@prefeituradesalvaterra.pa.gov.br', 'Avenida Victor Engelhard, NÂ°123 - Bairro: Centro / CEP: 68.860-000', '21/05/2025', -0.75506892, -48.51488149, '5b9880b33520e5f06b617a2ffd69507ca2bb73b39046d0a947f753a49fd216c3', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:20'),
(107, '1506351', 'Santa BÃ¡rbara do ParÃ¡', 'GuajarÃ¡', 1, 'MARCUS LEÃ‚O COLARES', '25Âº GBM - Marituba', 'Jhonathan Oliveira Lardosa', NULL, '(91) 99371-0082', 'pref.sbp.gabinete@gmail.com', NULL, '01/02/2026', -1.22823433, -48.29182139, '0b0820f796c2c72cf9ea559fc23433038a90fc1ffab4a49bf7f1aea431302bea', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:52:53'),
(108, '1506401', 'Santa Cruz do Arari', 'MarajÃ³', 1, 'NICOLAU PAMPLONA', '18Âº GBM - Salvaterra', 'Adailton Nascimento Cruz', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704889_96539780cfad054d.jpg', '91985509012', 'defesacivilsantacruzdoarari@gmail.com', 'Travessa LÃ­dia Leal S/N - centro', '26/02/2026', -0.66434989, -49.17153270, '7f8a2b1b44aee0b54ef2a7597056d55521ccbfdec321647b80f9a071af5ae1b3', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:29'),
(109, '1506500', 'Santa Isabel do ParÃ¡', 'GuamÃ¡', 1, 'JOSE ALBERTO TAVARES DA TRINDADE', '12Âº GBM - Santa Izabel do ParÃ¡', 'Paulo SÃ©rgio AraÃºjo Barreto', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704654_4031afa9729714bf.jpg', '91988179130', 'protecaocivilsantaizabeldopara@gmail.com', 'barretofluvia@hotmail.com', '05/05/2025', -1.29786212, -48.16267952, '58732459dc961677f8623e4557f7288933176317ff89a0afa5e1a7006d31766b', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:57:34'),
(110, '1506559', 'Santa Luzia do ParÃ¡', 'Rio CaetÃ©', 1, 'ADAMOR AIRES DE OLIVEIRA', '19Âº GBM - Capanema', 'Antonio Bruno Rodrigues de Sousa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705008_f6a53830d1fea78b.jpg', '91992161203', 'compdecsantaluziapa@gmail.com', 'Tv. Manoel Gaia, Santa Luzia do ParÃ¡ - PA, 68644-000, Brasil', '26/09/2025', -1.52450470, -46.89812417, '4b15ec5bad33bd7fc055943db3642deb687057e975d5979d27c9390f52bc1eac', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:03:28'),
(111, '1506583', 'Santa Maria das Barreiras', 'Araguaia', 1, 'Jose Barbosa de Faria', '10Â° GBM - RedenÃ§Ã£o', 'RAFAEL DE OLIVEIRA LUZ', NULL, '94999019588', 'josecarlosabr@gmail.com', NULL, '18/06/2025', -8.87054938, -49.71635980, '63620b7c693465a1084ee5d65ad8f28185bd89116fdb5292101a31fb4f919a85', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:54:33'),
(112, '1506609', 'Santa Maria do ParÃ¡', 'GuamÃ¡', 0, 'ALCIR COSTA DA SILVA', '28Â° GBM - SÃ£o Miguel do GuamÃ¡', NULL, NULL, NULL, NULL, NULL, '01/02/2026', -1.34906757, -47.57575163, '98780b56282fb5830837947ab296de434b9e376a437a7cb746e69d8853e013e1', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:56:34'),
(113, '1506708', 'Santana do Araguaia', 'Araguaia', 1, 'EDUARDO ALVES CONTI', '10Â° GBM - RedenÃ§Ã£o', 'Alexandro Pereira lopes', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704345_5f74daad9cebb616.jpg', '94991750998', 'defesacivilsantana@gmail.com', 'Avenida Dr Raul Claudio Prates S/N, bairro Biblia', '12/01/2026', -9.33913119, -50.33698939, '5350207b1aa172e48b6a75cb605d821b6c8c60e43f367f875d9a9baf467b74db', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:56:51');

INSERT INTO `compdecs` (`id`, `municipio_codigo`, `municipio`, `regiao_integracao`, `tem_compdec`, `prefeito`, `ubm_nome`, `coordenador`, `foto_coordenador`, `telefone`, `email`, `endereco`, `data_atualizacao`, `latitude`, `longitude`, `fonte_hash`, `sincronizado_em`, `criado_em`, `atualizado_em`) VALUES
(114, '1506807', 'SantarÃ©m', 'Baixo Amazonas', 1, 'JOSE MARIA TAPAJOS', '4Â° GBM - SantarÃ©m', 'Darlison Rego Maia', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704147_f42e670b07c03441.jpg', '93991228882', 'comdec@santarem.pa.gov.br', NULL, '16/04/2025', -2.41970033, -54.72769142, 'fcf577d475d1678f8dc4710ee85859f0d3b29153fe007eb3bd0b9b7b1f1bbf56', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:57:17'),
(115, '1506906', 'SantarÃ©m Novo', 'Rio CaetÃ©', 0, 'THIAGO REIS PIMENTEL', '13Âº GBM - SalinÃ³polis', NULL, NULL, NULL, NULL, NULL, '01/02/2026', -0.92840128, -47.39890490, '6656e6e164a2238cfac8acec5f4cdbccf4d5f443d449a86a1a321c2c78f81e73', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:57:40'),
(116, '1507003', 'Santo AntÃ´nio do TauÃ¡', 'GuamÃ¡', 0, 'Rodrigo de Amorim Pinto', '12Âº GBM - Santa Izabel do ParÃ¡', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -1.15180045, -48.12993498, '2691a6d1b744ba613da6eb84d99575ea77bfb94a26518669d67fb7e6bf92f7ec', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:58:04'),
(117, '1507102', 'SÃ£o Caetano de Odivelas', 'GuamÃ¡', 1, 'FELIPA RODRIGUES DOS SANTOS RENDEIRO', '17Â° GBM - Vigia de NazarÃ©', 'AELSON JOSÃ‰ FARIAS PEREIRA', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704669_5858fb9de75b16e4.jpg', '91986369401', 'defesacivilsco2025@gmail.com', 'Av. Floriano Peixoto NÂº01 Centro - PrÃ©dio da Prefeitura', '23/01/2026', -0.74693393, -48.01971397, 'b171a0c07ad222e08383cc006ecb0e5b9ed54b3119ccc06cfe33f2b305716f2c', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:58:21'),
(118, '1507151', 'SÃ£o Domingos do Araguaia', 'CarajÃ¡s', 1, 'ELIZANE SOARES DA SILVA', '5Âº GBM - MarabÃ¡', 'Carlan Martins Lima', NULL, '(94) 99134-9519', 'jgmaroto21@gmail.com', NULL, '01/02/2026', -5.54058128, -48.73056315, '1159da6124d7663fa62d758dcdfc68cace64e632b53de123467df1f2a8264bef', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:58:39'),
(119, '1507201', 'SÃ£o Domingos do Capim', 'GuamÃ¡', 1, 'ORIVALDO DAS NEVES OLIVEIRA', '2Âº GBM - Castanhal', 'Odir JosÃ© das Neves Oliveira', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704678_da576157a234f710.jpg', '91993065483', 'seinfra@saodomingosdocapim.pa.gov.br', 'Rua da caixa d\'Ã¡gua do pexilinga, S/N, Bairro: ponto certo', '12/2/2026', -1.67418724, -47.77217003, 'd2cfbf2843c70ddf08cfd5464d81b2ee4feecc178a829d235002adde669625ef', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 10:59:06'),
(120, '1507300', 'SÃ£o FÃ©lix do Xingu', 'Araguaia', 1, 'FABRICIO BATISTA FERREIRA', '31Â° GBM - SÃ£o FÃ©lix do Xingu', 'Mauro Sousa Costa', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704360_d96a1ae77ebaa084.jpg', '94981352944', 'defesacivil-saofelixdoxingu@hotmail.com', 'Avenida Antonio Marques Ribeiro, nÂ°1227, esquina com a travessa Antonio Coelho de sousa, bairro: centro, SÃ£o FÃ©lix do Xingu - PA, Cep: 68380000', '21/01/2026', -6.64215766, -51.99437725, '2bf5dfa03bd3eabd3824390c7814960c4e1c8e5c50a1ccce786e2e76d3190555', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 17:36:45'),
(121, '1507409', 'SÃ£o Francisco do ParÃ¡', 'GuamÃ¡', 1, 'ANTONIO RONALDO NOBRE DO NASCIMENTO', '2Âº GBM - Castanhal', 'FRANCISCO RONALDO COSME LEAL', '/uploads/compdec/coordenadores/coord_defesa_civil_1781714174_5c89de2b20430c55.jpg', '91999037030', 'RONALDOLEALPEDAGOGIA@GMAIL.COM', 'BARÃƒO DO RIO BRANCO,760, BAIRRO CENTRO', '01/02/2026', -1.16948529, -47.79730562, '3e4cadd3ea1d895edb27e33db279667ec43b14cdb474f8bd2e626fffcb38ea04', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 13:36:14'),
(122, '1507458', 'SÃ£o Geraldo do Araguaia', 'CarajÃ¡s', 1, 'JEFFERSON DOUGLAS JESUS OLIVEIRA', '5Âº GBM - MarabÃ¡', 'Leidiane dos Santos Pires Vieira', NULL, '63999599369', 'defesacivil@saogeraldodoaraguaia.pa.gov.br', 'avenida vereador antÃ´nio predosa', '23/01/2026', -6.38676455, -48.56100396, '0857ddcf0093967f30592d58c87cdd7dbc8e657cea06fe390e1579e6edd8bac8', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:00:02'),
(123, '1507466', 'SÃ£o JoÃ£o da Ponta', 'GuamÃ¡', 0, 'LIDIANE DE SOUSA CARVALHO', '17Â° GBM - Vigia de NazarÃ©', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -0.84978213, -47.92363589, '520cab1679d176a12501fb04b8a3326a80c7e1a707c223b2033841617e0cc277', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:00:18'),
(124, '1507474', 'SÃ£o JoÃ£o de Pirabas', 'Rio CaetÃ©', 1, 'KAMILY MARIA FERREIRA ARAUJO', '13Âº GBM - SalinÃ³polis', 'CLAUDIO JUNIOR SALDANHA ARAUJO', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705018_6078977c2f3f28c2.jpg', '91984352453', 'semmasjppirabas@gmail.com', 'R. PlÃ¡cido Nascimento, 625 - SÃ£o JoÃ£o de Pirabas, PA, 68719-000, Brasil', '10/02/2026', -0.76881275, -47.17389789, 'e60dabf99b19f955973600ae9f1b6e889e2c99a009d24ab35ef25ca1ac1184d8', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:03:38'),
(125, '1507508', 'SÃ£o JoÃ£o do Araguaia', 'CarajÃ¡s', 1, 'MARCELLANNE MARTINS', '5Âº GBM - MarabÃ¡', 'Fabiano Lima Queiroz', NULL, '(94) 98404-9467', 'fabianoqueiroz10777@gmail.com', 'Rodovia Pedro Carneiro - Centro ', '01/02/2026', -5.35528889, -48.78901020, 'c09e5c55f3190291a1d4940f81a3a77492a9281fe8a4d1654492329b59ce6d23', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:00:52'),
(126, '1507607', 'SÃ£o Miguel do GuamÃ¡', 'GuamÃ¡', 1, 'EDUARDO SAMPAIO GOMES LEITE', '28Â° GBM - SÃ£o Miguel do GuamÃ¡', 'CLAYTON JOSÃ‰ DE JESUS NUNES', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704689_699a48fb8d14b9f6.jpg', '91982281747', 'defesacivilsmg56@gmail.com', 'Avenida AmÃ©rico Lopes - S/N. Bairro: SÃ£o Manoel', '22/04/2025', -1.62435681, -47.48498279, 'd2637e776ee6dc6c9a097faade5c1f3b7437d6b723454a309a381cca8d658447', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:08'),
(127, '1507706', 'SÃ£o SebastiÃ£o da Boa Vista', 'MarajÃ³', 1, 'GETÃšLIO BRABO DE SOUZA', '11Â° GBM - Breves', 'Brenda Iris GonÃ§alves Pinheiro', NULL, NULL, 'getulio.brabo10@gmail.com', NULL, '02/06/2026', -1.71843332, -49.53277849, '0c2a39076dfc212bf1b550e3a5f58052893120464f97258ec4f7b9419b6d4677', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:23'),
(128, '1507755', 'Sapucaia', 'Araguaia', 1, 'WILTON MIRANDA DE LIMA', '10Â° GBM - RedenÃ§Ã£o', 'Thaisy Oliveira de Sales', NULL, '94992396737', 'defesacivilmunicipal@sapucaia.pa.gov.br', 'Rua DÃ¡lia NÂ°77 - Centro', '09/12/2025', -6.42930799, -49.74909855, 'e42e0fe9e07107d153607a7aa82ea92bc9f6866494fa9d6f8aae2536e3a61394', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:39'),
(129, '1507805', 'Senador JosÃ© PorfÃ­rio', 'Xingu', 1, 'LEONALDO ALBUQUERQUE DE SOUSA', '9Âº GBM - Altamira', 'Carlos AndrÃ© de Souza Machado', NULL, '(93) 99171-5360', NULL, 'Rua 13 de maio, Casa dos Conselhos', '01/02/2026', -2.59099509, -51.95494388, 'e32784a174b551235189aa2d9627b8f39c59ab78bb63efe41645ed37a2a5effc', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:01:55'),
(130, '1507904', 'Soure', 'MarajÃ³', 1, 'PAULO VICTOR SILVA DE LIMA', '18Âº GBM - Salvaterra', 'Marivalda Nunes Vitor', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704900_75d6d5e8f976ccee.jpg', '91980750126', 'compdecsoure@gmail.com', 'TV. 15-entre segunda e terceira rua, anexo a secretaria de assistÃªncia social.', '19/02/2026', -0.72908241, -48.52062700, '50bbc5c6a9f506a4371e71e1c7beab2d8a3e1ae4fadde43780e9ea9f071cdc01', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:02:15'),
(131, '1507953', 'TailÃ¢ndia', 'Tocantins', 0, 'LAURO FERRAZ HOFFMANN', '14Âº GBM - TailÃ¢ndia', NULL, NULL, NULL, NULL, NULL, '01/02/2026', -2.94624355, -48.95329815, 'f003aaea41088f414aad80aaa4d280d604a6f0490a4705210090b6982174ae04', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:02:40'),
(132, '1507961', 'Terra Alta', 'GuamÃ¡', 0, 'MICHEL PESSOA DO NASCIMENTO', '2Âº GBM - Castanhal', NULL, NULL, ' ', NULL, NULL, '01/02/2026', -1.03889628, -47.90748660, 'c55f5f68de4a649afcde57f92ee829d4f28a115067f1415ea98f1d1b193882c3', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:03:00'),
(133, '1507979', 'Terra Santa', 'Baixo Amazonas', 1, 'EDSON SIQUEIRA DA FONSECA', '4Â° GBM - SantarÃ©m', 'Domingos SÃ¡vio Malheiro Ribeiro', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704159_862c7833c8a78a1e.jpg', '93991431870', 'defesacivil@terrasanta.pa.gov.br', 'R. Dr. Lauro SodrÃ©, 527 - Centro, Terra Santa - PA, 68285-000, Brasil', '22/04/2025', -1.75341888, -56.49169941, 'b087f9336b20c6cc0e97c79bd690c2adff3bf0c2b58aa8d06a8fae53155470fb', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:03:21'),
(134, '1508001', 'TomÃ©-AÃ§u', 'Rio Capim', 1, 'CARLOS ANTONIO VIEIRA', '12Âº GBM - Santa Izabel do ParÃ¡', 'Daniel Berg AlÃ©m Sarges', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705161_25abccc4e82809f0.jpg', '91992519104', 'danielbergsarges@gmail.com', 'Av. TrÃªs Poderes, TomÃ©-AÃ§u - PA, 68680-000, Brasil', '02/10/2025', -2.42037288, -48.15099716, 'f975911f249100c4f3c9f721cc77cfd23621ef83d814188c9583fd82573b6eec', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:06:01'),
(135, '1508035', 'Tracuateua', 'Rio CaetÃ©', 0, 'JOSÃ‰ BRAULIO DA COSTA', '24Âº GBM - BraganÃ§a', NULL, NULL, NULL, NULL, NULL, '01/02/2026', -1.07397033, -46.89374925, 'e6d5b1c05afb6ce11129bec617dd6d5b86a3cc48fff52ee31f0a228f8c93ac0b', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:04:06'),
(136, '1508050', 'TrairÃ£o', 'TapajÃ³s', 1, 'HENRIQUE BORGES DA SILVA', '7Â° GBM - Itaituba', 'ADEILSON ARAUJO DA SILVA', NULL, '93984064205', NULL, 'Av. Fernando Guilhon s/n, bairro Bela Vista - TrairÃ£o/PA', '05/01/2026', -4.70393995, -55.99657449, 'fbd2d22b06f6b502df5e893111b1f4fe32403915c02f44ab9cda31f4a1c00b06', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:04:22'),
(137, '1508084', 'TucumÃ£', 'Araguaia', 1, 'CELSO LOPES CARDOSO', '10Â° GBM - RedenÃ§Ã£o', 'Clebeson cruz silva Carvalho', NULL, '94991967314', 'cleniltonoliveira44@gmail.com', NULL, '01/02/2026', -6.75290855, -51.15524118, '632206444d6c81110fb2ef9dec9b63e8e948edc4727f2a8c46b5bc80bc640e6e', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:04:44'),
(138, '1508100', 'TucuruÃ­', 'Lago de TucuruÃ­', 1, 'ALEXANDRE FRANCA SIQUEIRA', '8Âº GBM - TucuruÃ­', 'Ingridy Souza Ribeiro', NULL, '94991212996', 'defesa.civil@tucurui.pa.gov.br', 'Av. Veridiano Cardoso, Bairro Bela Vista', '01/02/2026', -3.76598977, -49.67024248, '25c84b2685552c52cfd563c6d59bb772fc1c408ae9f6305fa818aef1f0e746fe', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:05:12'),
(139, '1508126', 'UlianÃ³polis', 'Rio Capim', 1, 'KELLY CRISTINA DESTRO', '27Âº GBM - Paragominas', 'CLENILTON SILVA OLIVEIRA', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705172_d667a778a1025cd5.jpg', '91983845632', 'defesacivil.ulianopolis@gmail.com', 'Av. Pres. Vargas, 2677, UlianÃ³polis - PA, 68632-000, Brasil', '10/02/2026', -3.75725118, -47.50059657, 'ec99d6eda215aeca163da9494026905e8fb9ff4d295a4ffbb63b2b959b654973', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:06:47'),
(140, '1508159', 'UruarÃ¡', 'Xingu', 1, 'CARLOS ANTONIO ZANCAN', '9Âº GBM - Altamira', 'Poliana Felix de Souza', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705484_ad97b210257fec35.jpg', '93992334638', 'pfelix.cdp@gmail.com', 'AV CENTRAL, 136', '26/11/2025', -3.72321754, -53.73448000, '57afec0da05b26346602d82470a6e08bf9ed8768375a3dc3ed29d09d08f379df', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:11:24'),
(141, '1508209', 'Vigia', 'GuamÃ¡', 1, 'JOB XAVIER PALHETA JUNIOR', '17Â° GBM - Vigia de NazarÃ©', 'Beatriz de Vilhena Medeiros', NULL, '(91) 98134-4276', 'beatrizvilhena76@gmail.com', NULL, '01/02/2026', -0.85199912, -48.14478810, '5c7d0c566f60df1290f5f587812f390dee4927c456c5ea34f8c9c7f8ecaa86ef', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:07:11'),
(142, '1508308', 'Viseu', 'Rio CaetÃ©', 1, 'CRISTIANO DUTRA VALE', '24Âº GBM - BraganÃ§a', 'Rosinaldo Viana dos Santos', '/uploads/compdec/coordenadores/coord_defesa_civil_1781705031_7b660e9a749018ce.jpg', '91987691965', 'defesacivilviseu@gmail.com', 'Rua nova S/N - centro - andar de cima do prÃ©dio da Secretaria Municipal de AdministraÃ§Ã£o, anexo ao Bradesco', '07/10/2025', -1.20659074, -46.13929046, 'f2d36b0a160090de06747e4d411aafe23e22e93f6549091f8701d55776462e77', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:07:42'),
(143, '1508357', 'VitÃ³ria do Xingu', 'Xingu', 1, 'MARCIO VIANA ROCHA', '9Âº GBM - Altamira', 'ELSA LAIRE DALL ACQUA MAIA', NULL, '93 9137-7952', 'def.civilvtx@gmail.com', 'Rua Isabel Leocadia NÂ° 546 B. Jardim Dalaqua / CEP: 68.383-000', '01/02/2026', -2.88246166, -52.01330046, 'c5f1489d24a386834902fafa9ce303435a7be68d462b4f319a85de190399b081', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 11:07:55'),
(144, '1508407', 'Xinguara', 'Araguaia', 1, 'OSVALDO DE OLIVEIRA ASSUNÃ‡ÃƒO JUNIOR', '34Â° GBM - Xinguara', 'Gilmar Pires Pereira', '/uploads/compdec/coordenadores/coord_defesa_civil_1781704373_c45766ee2b126ec3.jpg', '94991295519', 'defesacivilxinguara@gmail.com', 'Rua Marechal Cordeiro de Farias, PraÃ§a VitÃ³ria RÃ©gia S/N - CEP: 68.555-221', '13/01/2026', -7.10431256, -49.94616208, 'f626bc601277e4cebf938fbb769b1de013c090738f3747586270097a534ee8b8', '2026-06-16 18:18:41', '2026-06-16 16:42:56', '2026-06-17 17:25:33');
ALTER TABLE compdecs AUTO_INCREMENT = 433;

DELETE FROM ubms WHERE descricao IN ('Fonte: Multirriscos COMPDEC', 'Fonte: COMPDEC DGD');

INSERT INTO ubms (municipio_id, codigo, nome, descricao, ativo)
SELECT m.id, NULL, c.ubm_nome, 'Fonte: COMPDEC DGD', 1
FROM compdecs c
INNER JOIN municipios m ON m.codigo_ibge = CAST(c.municipio_codigo AS UNSIGNED)
LEFT JOIN ubms existente ON existente.municipio_id = m.id AND existente.nome = c.ubm_nome
WHERE c.ubm_nome IS NOT NULL
  AND TRIM(c.ubm_nome) <> ''
  AND existente.id IS NULL;

-- DGD - Views operacionais

SET NAMES utf8mb4;

DROP VIEW IF EXISTS vw_painel_resumo;
DROP VIEW IF EXISTS vw_decretos_listagem;

CREATE VIEW vw_decretos_listagem AS
SELECT
    d.id,
    d.protocolo_dgd,
    d.protocolo_ano,
    d.protocolo_sequencial,
    d.municipio_id,
    m.nome AS municipio,
    d.ubm_id,
    u.nome AS ubm_atuante,
    d.compdec_id,
    d.compdec_regiao_integracao,
    d.compdec_prefeito,
    d.compdec_coordenador,
    d.compdec_telefone,
    d.compdec_email,
    td.nome AS tipo_decreto,
    cs.codigo AS cobrade_codigo,
    cs.nome AS cobrade_subtipo,
    cs.simbologia AS cobrade_simbologia,
    ct.nome AS cobrade_tipo,
    csg.nome AS cobrade_subgrupo,
    cg.nome AS cobrade_grupo,
    d.data_desastre,
    d.protocolo_s2id,
    d.numero_decreto_municipal,
    d.data_decreto_municipal,
    CASE
        WHEN d.data_decreto_municipal IS NULL THEN NULL
        ELSE DATEDIFF(CURRENT_DATE, d.data_decreto_municipal)
    END AS total_dias_decreto,
    d.homologacao_status_id,
    sh.codigo AS homologacao_codigo,
    sh.nome AS homologacao,
    d.reconhecimento_status_id,
    sr.codigo AS reconhecimento_codigo,
    sr.nome AS reconhecimento,
    d.protocolo_pae_pge,
    d.data_envio_pge,
    d.data_conclusao_pge,
    CASE
        WHEN d.data_envio_pge IS NULL OR sep.codigo IN ('NAO_REGISTRADO', 'NAO_ENVIADO', 'EM_PREPARACAO') THEN NULL
        ELSE DATEDIFF(COALESCE(d.data_conclusao_pge, CURRENT_DATE), d.data_envio_pge)
    END AS duracao_pge_dias,
    d.status_envio_pge_id,
    sep.codigo AS status_envio_pge_codigo,
    sep.nome AS status_envio_pge,
    CASE
        WHEN sep.codigo = 'CONCLUIDO' THEN 'CONCLUÍDO'
        WHEN sh.codigo = 'HOMOLOGADO' THEN 'CONCLUÍDO'
        WHEN sep.codigo IN ('NAO_REGISTRADO', 'NAO_ENVIADO', 'EM_PREPARACAO') THEN 'NAO INICIADO'
        WHEN d.data_envio_pge IS NULL THEN 'NAO INICIADO'
        WHEN DATEDIFF(COALESCE(d.data_conclusao_pge, CURRENT_DATE), d.data_envio_pge) BETWEEN 0 AND 7 THEN 'NO PRAZO'
        WHEN DATEDIFF(COALESCE(d.data_conclusao_pge, CURRENT_DATE), d.data_envio_pge) > 7 THEN 'PENDENTE'
        ELSE 'NAO INICIADO'
    END AS status_prazo_pge_calculado,
    d.analista_id,
    analista.nome AS analista,
    rr.nome AS recurso_resposta,
    rc.nome AS recurso_reconstrucao,
    d.numero_obitos,
    d.numero_feridos,
    d.numero_enfermos,
    d.numero_desabrigados,
    d.numero_desalojados,
    d.numero_outros_afetados,
    d.total_afetados,
    d.ativo,
    d.criado_em,
    d.atualizado_em,
    d.excluido_em
FROM desastres d
INNER JOIN municipios m ON m.id = d.municipio_id
LEFT JOIN ubms u ON u.id = d.ubm_id
INNER JOIN tipos_decreto td ON td.id = d.tipo_decreto_id
INNER JOIN cobrade_subtipos cs ON cs.id = d.cobrade_subtipo_id
INNER JOIN cobrade_tipos ct ON ct.id = cs.tipo_id
INNER JOIN cobrade_subgrupos csg ON csg.id = ct.subgrupo_id
INNER JOIN cobrade_grupos cg ON cg.id = csg.grupo_id
INNER JOIN status_homologacao sh ON sh.id = d.homologacao_status_id
INNER JOIN status_reconhecimento sr ON sr.id = d.reconhecimento_status_id
INNER JOIN status_envio_pge sep ON sep.id = d.status_envio_pge_id
INNER JOIN status_recurso rr ON rr.id = d.recurso_resposta_status_id
INNER JOIN status_recurso rc ON rc.id = d.recurso_reconstrucao_status_id
LEFT JOIN usuarios analista ON analista.id = d.analista_id
WHERE d.excluido_em IS NULL;

CREATE VIEW vw_painel_resumo AS
SELECT
    YEAR(CURRENT_DATE) AS ano_referencia,
    COUNT(*) AS total_desastres,
    SUM(CASE WHEN numero_decreto_municipal IS NOT NULL AND numero_decreto_municipal <> '' THEN 1 ELSE 0 END) AS total_decretos_municipais,
    SUM(CASE WHEN homologacao_codigo = 'SOLICITADO' THEN 1 ELSE 0 END) AS homologacoes_solicitadas,
    SUM(CASE WHEN homologacao_codigo = 'HOMOLOGADO' THEN 1 ELSE 0 END) AS homologados,
    SUM(CASE WHEN homologacao_codigo = 'NAO_HOMOLOGADO' THEN 1 ELSE 0 END) AS nao_homologados,
    SUM(CASE WHEN reconhecimento_codigo = 'SOLICITADO' THEN 1 ELSE 0 END) AS reconhecimentos_solicitados,
    SUM(CASE WHEN reconhecimento_codigo = 'RECONHECIDO' THEN 1 ELSE 0 END) AS reconhecidos,
    SUM(CASE WHEN status_envio_pge_codigo = 'ENVIADO_PGE' THEN 1 ELSE 0 END) AS enviados_pge,
    SUM(CASE WHEN status_prazo_pge_calculado = 'PENDENTE' THEN 1 ELSE 0 END) AS pendentes_pge,
    SUM(total_afetados) AS total_afetados
FROM vw_decretos_listagem
WHERE ativo = 1
  AND protocolo_ano = YEAR(CURRENT_DATE);
