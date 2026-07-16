-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 15/07/2026 às 14:08
-- Versão do servidor: 11.8.8-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u696029111_DefesaCivilPA`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `unidades_bombeiros_militares`
--

CREATE TABLE `unidades_bombeiros_militares` (
  `id` int(11) NOT NULL,
  `nome` varchar(180) NOT NULL,
  `municipio_codigo` varchar(7) DEFAULT NULL,
  `municipio` varchar(150) DEFAULT NULL,
  `regiao_integracao` varchar(100) DEFAULT NULL,
  `latitude` decimal(11,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `origem` varchar(40) NOT NULL DEFAULT 'COMPDEC',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `unidades_bombeiros_militares`
--

INSERT INTO `unidades_bombeiros_militares` (`id`, `nome`, `municipio_codigo`, `municipio`, `regiao_integracao`, `latitude`, `longitude`, `ativo`, `origem`, `criado_em`, `atualizado_em`) VALUES
(145, '30° GBM - Quartel do Comando Geral', '1501402', 'Belém', 'Guajará', -1.40693000, -48.46482000, 1, 'COMPDEC', '2026-06-16 17:10:03', '2026-06-16 17:10:03'),
(146, '15º GBM - Abaetetuba', '1500107', 'Abaetetuba', 'Tocantins', -1.72043470, -48.88210590, 1, 'COMPDEC', '2026-06-16 17:11:29', '2026-06-17 11:09:42'),
(147, '5º GBM - Marabá', '1500131', 'Abel Figueiredo', 'Carajás', -5.37016100, -49.13215560, 1, 'COMPDEC', '2026-06-16 17:12:56', '2026-06-17 11:05:52'),
(148, '6º GBM - Barcarena', '1500206', 'Acará', 'Tocantins', -1.54114920, -48.70047040, 1, 'COMPDEC', '2026-06-16 17:14:16', '2026-06-17 11:09:25'),
(149, '11° GBM - Breves', '1500305', 'Afuá', 'Marajó', -1.67301003, -50.47471048, 1, 'COMPDEC', '2026-06-16 17:21:54', '2026-06-17 11:07:01'),
(150, '10° GBM - Redenção', '1500347', 'Água Azul do Norte', 'Araguaia', -8.04527624, -50.01376213, 1, 'COMPDEC', '2026-06-16 17:25:01', '2026-06-17 11:08:10'),
(151, '4° GBM - Santarém', '1500404', 'Alenquer', 'Baixo Amazonas', -2.42788460, -54.70236550, 1, 'COMPDEC', '2026-06-16 17:41:35', '2026-06-17 11:03:21'),
(152, '30° GBM - QCG', '1501402', 'Belém', 'Guajará', -1.40693000, -48.46482000, 1, 'COMPDEC', '2026-06-16 17:44:40', '2026-06-16 18:32:27'),
(153, '9º GBM - Altamira', '1500602', 'Altamira', 'Xingu', -3.19974040, -52.20367680, 1, 'COMPDEC', '2026-06-16 18:05:34', '2026-06-17 11:11:24'),
(154, '3º GBM - Ananindeua', '1500800', 'Ananindeua', 'Guajará', -1.35003540, -48.40310380, 1, 'COMPDEC', '2026-06-16 18:07:02', '2026-06-17 10:33:58'),
(155, '24º GBM - Bragança', '1500909', 'Augusto Corrêa', 'Rio Caeté', -1.01590940, -46.77748130, 1, 'COMPDEC', '2026-06-16 18:08:32', '2026-06-17 11:07:42'),
(156, '27º GBM - Paragominas', '1500958', 'Aurora do Pará', 'Rio Capim', -3.01052990, -47.35744910, 1, 'COMPDEC', '2026-06-16 18:10:29', '2026-06-17 11:06:47'),
(157, '22º GBM - Cametá', '1501204', 'Baião', 'Tocantins', -2.24256920, -49.50900460, 1, 'COMPDEC', '2026-06-16 18:12:20', '2026-06-17 11:10:12'),
(446, '25º GBM - Marituba', '1501501', 'Benevides', 'Guajará', -1.36514160, -48.33223340, 1, 'COMPDEC', '2026-06-16 18:34:31', '2026-06-17 10:55:36'),
(447, '19º GBM - Capanema', '1501600', 'Bonito', 'Rio Caeté', -1.20771930, -47.17768720, 1, 'COMPDEC', '2026-06-16 18:35:39', '2026-06-17 11:05:43'),
(448, '8º GBM - Tucuruí', '1501782', 'Breu Branco', 'Lago de Tucuruí', -3.79133930, -49.67651800, 1, 'COMPDEC', '2026-06-16 18:38:14', '2026-06-17 11:05:12'),
(449, '12º GBM - Santa Izabel do Pará', '1501907', 'Bujaru', 'Rio Capim', -1.28788240, -48.15153070, 1, 'COMPDEC', '2026-06-16 18:39:24', '2026-06-17 11:06:01'),
(450, '18º GBM - Salvaterra', '1502004', 'Cachoeira do Arari', 'Marajó', -0.75238250, -48.52259940, 1, 'COMPDEC', '2026-06-16 18:40:05', '2026-06-17 11:02:15'),
(451, '16º GBM - Canaã dos Carajás', '1502152', 'Canaã dos Carajás', 'Carajás', -6.54678020, -49.85617390, 1, 'COMPDEC', '2026-06-16 18:41:56', '2026-06-17 10:54:51'),
(452, '2º GBM - Castanhal', '1502400', 'Castanhal', 'Guamá', -1.30294200, -47.92828910, 1, 'COMPDEC', '2026-06-16 18:43:49', '2026-06-17 13:36:14'),
(453, '17° GBM - Vigia de Nazaré', '1502608', 'Colares', 'Guamá', -0.85546683, -48.14249217, 1, 'COMPDEC', '2026-06-16 18:49:15', '2026-06-17 11:07:11'),
(454, '23° GBM - Parauapebas', '1502772', 'Curionópolis', 'Carajás', -6.07553154, -49.88468447, 1, 'COMPDEC', '2026-06-17 10:00:37', '2026-06-17 10:51:21'),
(455, '14º GBM - Tailândia', '1503093', 'Goianésia do Pará', 'Lago de Tucuruí', -2.91217480, -48.96233120, 1, 'COMPDEC', '2026-06-17 10:11:46', '2026-06-17 11:02:40'),
(456, '28° GBM - São Miguel do Guamá', '1503507', 'Irituia', 'Rio Capim', -1.61085728, -47.47831649, 1, 'COMPDEC', '2026-06-17 10:16:22', '2026-06-17 11:05:33'),
(457, '7° GBM - Itaituba', '1503606', 'Itaituba', 'Tapajós', -4.26670981, -55.99166312, 1, 'COMPDEC', '2026-06-17 10:18:16', '2026-06-17 11:07:22'),
(458, '29º GBM - Moju', '1504703', 'Moju', 'Tocantins', -1.88517150, -48.76902120, 1, 'COMPDEC', '2026-06-17 10:26:07', '2026-06-17 11:10:00'),
(459, '13º GBM - Salinópolis', '1506203', 'Salinópolis', 'Rio Caeté', -0.63827744, -47.33710522, 1, 'COMPDEC', '2026-06-17 10:51:00', '2026-06-17 11:03:38'),
(460, '32° GBM - Almeirim', '1500503', 'Almeirim', 'Baixo Amazonas', -1.52425903, -52.57869956, 1, 'COMPDEC', '2026-06-17 17:20:49', '2026-06-17 17:20:49'),
(461, '34° GBM - Xinguara', '1508407', 'Xinguara', 'Araguaia', -7.09780115, -49.93895426, 1, 'COMPDEC', '2026-06-17 17:25:33', '2026-06-17 17:25:33'),
(462, '33° GBM - Novo Progresso', '1505031', 'Novo Progresso', 'Tapajós', -7.03694966, -55.40621474, 1, 'COMPDEC', '2026-06-17 17:29:30', '2026-06-17 17:29:30'),
(463, '31° GBM - São Félix do Xingu', '1507300', 'São Félix do Xingu', 'Araguaia', -6.64167768, -51.96022077, 1, 'COMPDEC', '2026-06-17 17:36:45', '2026-06-17 17:36:45');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `unidades_bombeiros_militares`
--
ALTER TABLE `unidades_bombeiros_militares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ubm_nome_municipio` (`nome`,`municipio_codigo`),
  ADD UNIQUE KEY `uk_ubm_nome_municipio_nome` (`nome`,`municipio`),
  ADD KEY `idx_ubm_municipio` (`municipio_codigo`),
  ADD KEY `idx_ubm_regiao` (`regiao_integracao`),
  ADD KEY `idx_ubm_geo` (`latitude`,`longitude`),
  ADD KEY `idx_ubm_ativo` (`ativo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `unidades_bombeiros_militares`
--
ALTER TABLE `unidades_bombeiros_militares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=464;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
