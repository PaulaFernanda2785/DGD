-- Carga inicial do catálogo DGD. Pode ser executada mais de uma vez com segurança.
INSERT INTO tipos_ajuda (nome, unidade_medida, ativo, criado_em, atualizado_em) VALUES
('Cesta Básica', 'Cesta Básica', 1, '2026-04-30 11:07:23', '2026-05-04 19:18:09'),
('Água Potável', 'Litro', 1, '2026-04-30 13:30:09', '2026-05-04 19:17:49'),
('Higiene', 'Kit Higiene', 1, '2026-04-30 13:30:26', '2026-05-04 19:18:40'),
('Dormitório', 'Kit Dormitório', 1, '2026-04-30 13:30:49', '2026-05-04 19:17:34'),
('Colchão', 'Colchão', 1, '2026-04-30 13:31:15', '2026-05-04 19:18:25'),
('Limpeza', 'Kit Limpeza', 1, '2026-04-30 13:31:32', '2026-05-04 19:18:53'),
('Programa Recomeçar', 'Salário Mínimo', 1, '2026-04-30 21:12:14', '2026-05-04 19:19:23'),
('Desc. Téc. Imóveis', 'DTI', 1, '2026-05-08 10:12:05', '2026-05-15 16:53:50')
ON DUPLICATE KEY UPDATE unidade_medida = VALUES(unidade_medida), ativo = VALUES(ativo), atualizado_em = VALUES(atualizado_em);
