-- Corrige textos exibidos em seletores de domínio para pt-BR.
-- Mantém códigos e IDs para preservar relacionamentos e regras existentes.

SET NAMES utf8mb4;

UPDATE tipos_decreto SET nome = 'Situação de Emergência' WHERE codigo = 'SITUACAO_EMERGENCIA';
UPDATE tipos_decreto SET nome = 'Estado de Calamidade Pública' WHERE codigo = 'ESTADO_CALAMIDADE_PUBLICA';

UPDATE status_homologacao SET nome = 'Não registrado' WHERE codigo = 'NAO_REGISTRADO';
UPDATE status_homologacao SET nome = 'Não solicitado' WHERE codigo = 'NAO_SOLICITADO';
UPDATE status_homologacao SET nome = 'Em análise DGD' WHERE codigo = 'EM_ANALISE_DGD';
UPDATE status_homologacao SET nome = 'Enviado à PGE' WHERE codigo = 'ENVIADO_PGE';
UPDATE status_homologacao SET nome = 'Não homologado' WHERE codigo = 'NAO_HOMOLOGADO';

UPDATE status_reconhecimento SET nome = 'Não registrado' WHERE codigo = 'NAO_REGISTRADO';
UPDATE status_reconhecimento SET nome = 'Aguardando análise' WHERE codigo = 'AGUARDANDO_ANALISE';
UPDATE status_reconhecimento SET nome = 'Em análise SEDEC' WHERE codigo = 'EM_ANALISE_SEDEC';
UPDATE status_reconhecimento SET nome = 'Aguardando ajuste município' WHERE codigo = 'AGUARDANDO_AJUSTE_MUNICIPIO';
UPDATE status_reconhecimento SET nome = 'Não reconhecido' WHERE codigo = 'NAO_RECONHECIDO';

UPDATE status_recurso SET nome = 'Não registrado' WHERE codigo = 'NAO_REGISTRADO';
UPDATE status_recurso SET nome = 'Não solicitado' WHERE codigo = 'NAO_SOLICITADO';
UPDATE status_recurso SET nome = 'Em análise SEDEC' WHERE codigo = 'EM_ANALISE_SEDEC';
UPDATE status_recurso SET nome = 'Registro de revisão' WHERE codigo = 'REGISTRO_REVISAO';

UPDATE status_envio_pge SET nome = 'Não registrado' WHERE codigo = 'NAO_REGISTRADO';
UPDATE status_envio_pge SET nome = 'Não enviado' WHERE codigo = 'NAO_ENVIADO';
UPDATE status_envio_pge SET nome = 'Em preparação' WHERE codigo = 'EM_PREPARACAO';
UPDATE status_envio_pge SET nome = 'Enviado à PGE' WHERE codigo = 'ENVIADO_PGE';
UPDATE status_envio_pge SET nome = 'Concluído' WHERE codigo = 'CONCLUIDO';

UPDATE tipos_anexo SET nome = 'Ofício de homologação' WHERE codigo = 'OFICIO_HOMOLOGACAO';
