-- DGD - Instalacao completa para phpMyAdmin
-- Gerado a partir de schema.sql, seed.sql e views.sql
-- Importe este arquivo dentro do banco criado para o sistema.

SET NAMES utf8mb4;
SET time_zone = '-03:00';


-- ============================================================
-- database\schema.sql
-- ============================================================

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
DROP TABLE IF EXISTS municipios;
DROP TABLE IF EXISTS login_logs;
DROP TABLE IF EXISTS usuarios_sessoes;
DROP TABLE IF EXISTS recuperacoes_senha;
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
    INDEX idx_desastres_cobrade_subtipo (cobrade_subtipo_id),
    INDEX idx_desastres_data_desastre (data_desastre),
    INDEX idx_desastres_data_decreto_municipal (data_decreto_municipal),
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

-- ============================================================
-- database\seed.sql
-- ============================================================

-- DGD - Seeds iniciais
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
(16, 'dominios.administrar', 'dominios', 'administrar', 'Administrar tabelas de dominio.');

INSERT INTO perfil_permissoes (perfil_id, permissao_id)
SELECT 1, id FROM permissoes;

INSERT INTO perfil_permissoes (perfil_id, permissao_id) VALUES
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 14), (2, 15);

INSERT INTO perfil_permissoes (perfil_id, permissao_id) VALUES
(3, 1), (3, 2), (3, 3), (3, 4), (3, 8), (3, 14);

INSERT INTO tipos_decreto (id, codigo, nome, duracao_padrao_dias, ordem) VALUES
(1, 'SITUACAO_EMERGENCIA', 'Situacao de Emergencia', 180, 1),
(2, 'ESTADO_CALAMIDADE_PUBLICA', 'Estado de Calamidade Publica', 180, 2);

INSERT INTO status_homologacao (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Nao registrado', 'status-neutro', 1),
(2, 'NAO_SOLICITADO', 'Nao solicitado', 'status-neutro', 2),
(3, 'SOLICITADO', 'Solicitado', 'status-info', 3),
(4, 'PENDENTE_DESPACHO', 'Pendente - despacho', 'status-alerta', 4),
(5, 'PENDENTE_PARECER', 'Pendente - parecer', 'status-alerta', 5),
(6, 'EM_ANALISE_DGD', 'Em analise DGD', 'status-info', 6),
(7, 'ENVIADO_PGE', 'Enviado PGE', 'status-info', 7),
(8, 'HOMOLOGADO', 'Homologado', 'status-sucesso', 8),
(9, 'NAO_HOMOLOGADO', 'Nao homologado', 'status-erro', 9);

INSERT INTO status_reconhecimento (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Nao registrado', 'status-neutro', 1),
(2, 'SOLICITADO', 'Solicitado', 'status-info', 2),
(3, 'AGUARDANDO_ANALISE', 'Aguardando analise', 'status-alerta', 3),
(4, 'EM_ANALISE_SEDEC', 'Em analise SEDEC', 'status-info', 4),
(5, 'ENVIADO_RECONHECIMENTO', 'Enviado para reconhecimento', 'status-info', 5),
(6, 'AGUARDANDO_AJUSTE_MUNICIPIO', 'Aguardando ajuste municipio', 'status-alerta', 6),
(7, 'REGISTRADO', 'Registrado', 'status-info', 7),
(8, 'RECONHECIDO', 'Reconhecido', 'status-sucesso', 8),
(9, 'NAO_RECONHECIDO', 'Nao reconhecido', 'status-erro', 9);

INSERT INTO status_recurso (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Nao registrado', 'status-neutro', 1),
(2, 'NAO_SOLICITADO', 'Nao solicitado', 'status-neutro', 2),
(3, 'SOLICITADO', 'Solicitado', 'status-info', 3),
(4, 'AGUARDANDO_AJUSTES', 'Aguardando ajustes', 'status-alerta', 4),
(5, 'EM_ANALISE_SEDEC', 'Em analise SEDEC', 'status-info', 5),
(6, 'PLANO_APROVADO', 'Plano aprovado', 'status-sucesso', 6),
(7, 'RECURSO_DEFERIDO', 'Recurso deferido', 'status-sucesso', 7),
(8, 'RECURSO_INDEFERIDO', 'Recurso indeferido', 'status-erro', 8),
(9, 'REGISTRO_REVISAO', 'Registro de revisao', 'status-alerta', 9),
(10, 'EMPENHO', 'Empenho', 'status-info', 10);

INSERT INTO status_envio_pge (id, codigo, nome, classe_css, ordem) VALUES
(1, 'NAO_REGISTRADO', 'Nao registrado', 'status-neutro', 1),
(2, 'NAO_ENVIADO', 'Nao enviado', 'status-neutro', 2),
(3, 'EM_PREPARACAO', 'Em preparacao', 'status-alerta', 3),
(4, 'ENVIADO_PGE', 'Enviado a PGE', 'status-info', 4),
(5, 'RETORNADO_AJUSTE', 'Retornado para ajuste', 'status-alerta', 5),
(6, 'CONCLUIDO', 'Concluido', 'status-sucesso', 6);

INSERT INTO tipos_anexo (id, codigo, nome, obrigatorio, ordem) VALUES
(1, 'DECRETO_MUNICIPAL', 'Decreto municipal', 1, 1),
(2, 'OFICIO_HOMOLOGACAO', 'Oficio de homologacao', 0, 2),
(3, 'PARECER_ESTADUAL', 'Parecer estadual', 0, 3),
(4, 'PARECER_MUNICIPAL', 'Parecer municipal', 0, 4),
(5, 'OUTROS_DOCUMENTOS', 'Outros documentos', 0, 5);

INSERT INTO configuracoes_sistema (chave, valor, tipo_dado, descricao) VALUES
('prazo_pge_dias', '7', 'integer', 'Prazo operacional em dias para calculo do status de prazo PGE.'),
('paginacao_padrao', '20', 'integer', 'Quantidade padrao e maxima de registros por pagina na listagem.'),
('upload_tamanho_maximo_mb', '20', 'integer', 'Tamanho maximo permitido por arquivo anexado.'),
('sistema_nome', 'DGD', 'string', 'Nome curto do sistema.'),
('sistema_orgao', 'CEDEC-PA', 'string', 'Orgao gestor do sistema.'),
('timezone', 'America/Belem', 'string', 'Fuso horario oficial da aplicacao.');

-- Seed COBRADE minimo para permitir teste estrutural ate a carga validada da base oficial.
INSERT INTO cobrade_grupos (id, codigo, nome, descricao) VALUES
(1, '1', 'Natural', 'Desastres naturais.'),
(2, '2', 'Tecnologico', 'Desastres tecnologicos.');

INSERT INTO cobrade_subgrupos (id, grupo_id, codigo, nome) VALUES
(1, 1, '1.2', 'Hidrologico');

INSERT INTO cobrade_tipos (id, subgrupo_id, codigo, nome) VALUES
(1, 1, '1.2.1', 'Inundacoes');

INSERT INTO cobrade_subtipos (id, tipo_id, codigo, nome, descricao, simbologia, origem, versao) VALUES
(1, 1, '1.2.1.0.0', 'Inundacoes', 'Registro inicial de exemplo para validacao estrutural. Substituir pela base COBRADE validada.', 'cobrade_simbologia/simbologia_cobrade_1_2_1_0_0.png', 'Base provisoria DGD', '1.0');

-- Admin inicial: gerar hash com PHP antes de executar em producao.
-- Exemplo:
-- php -r "echo password_hash('SENHA_TEMPORARIA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
--
-- INSERT INTO usuarios (perfil_id, nome, email, senha_hash, ativo, trocar_senha_proximo_acesso)
-- VALUES (1, 'Administrador DGD', 'admin@dgd.local', 'SUBSTITUIR_POR_HASH_GERADO_NO_PHP', 1, 1);

-- Carga de municipios do Para a partir de terit/PA_Municipios_2025/para_municipios_com_geolocalizacao.csv
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

-- ============================================================
-- database\views.sql
-- ============================================================

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
    td.nome AS tipo_decreto,
    cs.codigo AS cobrade_codigo,
    cs.nome AS cobrade_subtipo,
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
    CASE
        WHEN d.data_decreto_municipal IS NULL THEN NULL
        ELSE DATEDIFF(COALESCE(d.data_envio_pge, CURRENT_DATE), d.data_decreto_municipal)
    END AS duracao_pge_dias,
    d.status_envio_pge_id,
    sep.codigo AS status_envio_pge_codigo,
    sep.nome AS status_envio_pge,
    CASE
        WHEN sh.codigo = 'HOMOLOGADO' THEN 'APROVADO'
        WHEN d.data_decreto_municipal IS NULL THEN 'SEM DATA'
        WHEN DATEDIFF(COALESCE(d.data_envio_pge, CURRENT_DATE), d.data_decreto_municipal) BETWEEN 1 AND 7 THEN 'NO PRAZO'
        WHEN DATEDIFF(COALESCE(d.data_envio_pge, CURRENT_DATE), d.data_decreto_municipal) > 7 THEN 'PENDENTE'
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
