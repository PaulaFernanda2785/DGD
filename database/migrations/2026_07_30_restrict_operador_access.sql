-- Remove do perfil Operador a permissao de criar decretos.
-- Idempotente: pode ser executada novamente sem produzir erro.

START TRANSACTION;

DELETE perfil_permissao
FROM perfil_permissoes AS perfil_permissao
INNER JOIN perfis AS perfil
    ON perfil.id = perfil_permissao.perfil_id
INNER JOIN permissoes AS permissao
    ON permissao.id = perfil_permissao.permissao_id
WHERE perfil.codigo = 'OPERADOR'
  AND permissao.codigo = 'decretos.criar';

UPDATE perfis
SET descricao = 'Consulta operacional controlada.'
WHERE codigo = 'OPERADOR';

COMMIT;
