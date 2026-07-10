-- Inclui a data de homologação/não homologação no fluxo automático da PGE.

SET NAMES utf8mb4;

UPDATE desastres d
INNER JOIN status_homologacao sh ON sh.id = d.homologacao_status_id
SET d.data_conclusao_pge = COALESCE(d.data_decreto_homologacao, d.data_conclusao_pge)
WHERE sh.codigo IN ('HOMOLOGADO', 'NAO_HOMOLOGADO')
  AND d.data_decreto_homologacao IS NOT NULL;


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
    d.numero_decreto_homologacao_estadual,
    d.data_decreto_homologacao,
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
        WHEN d.data_envio_pge IS NULL THEN NULL
        ELSE DATEDIFF(COALESCE(d.data_conclusao_pge, d.data_decreto_homologacao, CURRENT_DATE), d.data_envio_pge)
    END AS duracao_pge_dias,
    (SELECT id FROM status_envio_pge WHERE codigo = (CASE
        WHEN sh.codigo = 'HOMOLOGADO' THEN 'APROVADO'
        WHEN sh.codigo = 'NAO_HOMOLOGADO' THEN 'REPROVADO'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL
             AND DATEDIFF(CURRENT_DATE, d.data_envio_pge) > 7 THEN 'PENDENTE'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL THEN 'NO_PRAZO'
        ELSE 'NAO_REGISTRADO'
    END) COLLATE utf8mb4_unicode_ci LIMIT 1) AS status_envio_pge_id,
    (CASE
        WHEN sh.codigo = 'HOMOLOGADO' THEN 'APROVADO'
        WHEN sh.codigo = 'NAO_HOMOLOGADO' THEN 'REPROVADO'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL
             AND DATEDIFF(CURRENT_DATE, d.data_envio_pge) > 7 THEN 'PENDENTE'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL THEN 'NO_PRAZO'
        ELSE 'NAO_REGISTRADO'
    END) COLLATE utf8mb4_unicode_ci AS status_envio_pge_codigo,
    (CASE
        WHEN sh.codigo = 'HOMOLOGADO' THEN 'Aprovado'
        WHEN sh.codigo = 'NAO_HOMOLOGADO' THEN 'Reprovado'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL
             AND DATEDIFF(CURRENT_DATE, d.data_envio_pge) > 7 THEN 'Pendente'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL THEN 'No prazo'
        ELSE 'Não registrado'
    END) COLLATE utf8mb4_unicode_ci AS status_envio_pge,
    (CASE
        WHEN sh.codigo = 'HOMOLOGADO' THEN 'APROVADO'
        WHEN sh.codigo = 'NAO_HOMOLOGADO' THEN 'REPROVADO'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL
             AND DATEDIFF(CURRENT_DATE, d.data_envio_pge) > 7 THEN 'PENDENTE'
        WHEN sh.codigo = 'ENVIADO_PGE'
             AND d.data_envio_pge IS NOT NULL THEN 'NO PRAZO'
        ELSE 'NAO REGISTRADO'
    END) COLLATE utf8mb4_unicode_ci AS status_prazo_pge_calculado,
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
    SUM(CASE WHEN homologacao_codigo = 'ENVIADO_PGE' THEN 1 ELSE 0 END) AS enviados_pge,
    SUM(CASE WHEN status_prazo_pge_calculado = 'PENDENTE' THEN 1 ELSE 0 END) AS pendentes_pge,
    SUM(total_afetados) AS total_afetados
FROM vw_decretos_listagem
WHERE ativo = 1
  AND protocolo_ano = YEAR(CURRENT_DATE);
