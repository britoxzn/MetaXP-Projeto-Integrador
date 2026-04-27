-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql212.infinityfree.com
-- Tempo de geração: 27/04/2026 às 13:51
-- Versão do servidor: 11.4.10-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_38657243_plataforma_de_planejamento_metas_pessoais`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `xp_reward` int(11) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `rarity` enum('common','uncommon','rare','epic','legendary') DEFAULT 'common'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `achievements`
--

INSERT INTO `achievements` (`id`, `name`, `description`, `xp_reward`, `icon`, `rarity`) VALUES
(1, 'Primeiros Passos', 'Complete seu cadastro na plataforma', 50, 'user-check', 'common'),
(2, 'Explorador', 'Complete o tutorial da plataforma', 100, 'compass', 'common'),
(3, 'Meta Inicial', 'Complete sua primeira meta', 150, 'flag', 'uncommon'),
(4, 'Produtivo', 'Complete 5 metas em um dia', 200, 'zap', 'rare'),
(5, 'Mestre da Produtividade', 'Complete 20 metas em uma semana', 500, 'award', 'epic');

-- --------------------------------------------------------

--
-- Estrutura para tabela `conquistas`
--

CREATE TABLE `conquistas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `xp_recompensa` int(11) NOT NULL DEFAULT 0,
  `icon` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `conquistas`
--

INSERT INTO `conquistas` (`id`, `titulo`, `descricao`, `xp_recompensa`, `icon`) VALUES
(1, 'Primeira Meta', 'Concluiu a sua primeira meta!', 50, 'bi-check-lg'),
(2, '5 Metas Concluídas', 'Completou 5 metas com sucesso!', 150, 'bi-check2-all'),
(3, 'Maratona', 'Completou metas por 7 dias seguidos!', 200, 'bi-calendar-week'),
(4, 'Decano', 'Está ativo há 30 dias!', 300, 'bi-calendar3'),
(5, 'Persistente', 'Concluiu 10 metas no mês!', 250, 'bi-trophy'),
(6, 'Focado', 'Cumpriu metas 3 dias seguidos!', 100, 'bi-bullseye'),
(7, 'Produtivo', 'Finalizou 20 metas no total!', 400, 'bi-graph-up-arrow'),
(8, 'Mestre do Planeamento', 'Planeou 5 metas futuras!', 180, 'bi-journal-check'),
(9, 'Velocista', 'Terminou uma meta em menos de 1 dia!', 120, 'bi-lightning-charge'),
(10, 'Conquistador', 'Alcançou 50 metas concluídas!', 600, 'bi-award'),
(11, 'Motivado', 'Entrou 7 dias seguidos para ver as suas metas!', 90, 'bi-door-open'),
(12, 'Organizado', 'Utilizou categorias em 10 metas!', 110, 'bi-tags');

-- --------------------------------------------------------

--
-- Estrutura para tabela `goals`
--

CREATE TABLE `goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `deadline` date NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL,
  `current_streak` int(11) DEFAULT 0,
  `best_streak` int(11) DEFAULT 0,
  `xp_per_completion` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `habit_logs`
--

CREATE TABLE `habit_logs` (
  `id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `completed_date` date NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_recompensas`
--

CREATE TABLE `historico_recompensas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titulo_recompensa` varchar(255) NOT NULL,
  `custo_xp` int(11) NOT NULL,
  `data_resgate` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `historico_recompensas`
--

INSERT INTO `historico_recompensas` (`id`, `user_id`, `titulo_recompensa`, `custo_xp`, `data_resgate`) VALUES
(1, 10, 'Café Grátis', 100, '2026-03-02 14:47:46'),
(2, 11, 'Café Grátis', 100, '2026-03-04 14:48:15'),
(3, 6, 'Café Grátis', 100, '2026-03-09 16:18:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `html_templates`
--

CREATE TABLE `html_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(100) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `html_templates`
--

INSERT INTO `html_templates` (`id`, `template_name`, `content`, `created_at`) VALUES
(1, '404_page', '<!DOCTYPE html>...todo o seu código HTML aqui...', '2025-04-14 21:20:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_atividade`
--

CREATE TABLE `logs_atividade` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `pontos` int(11) DEFAULT 0,
  `data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `metas`
--

CREATE TABLE `metas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('pendente','em_andamento','concluida') DEFAULT 'pendente',
  `prazo` date DEFAULT NULL,
  `categoria` varchar(50) NOT NULL DEFAULT 'Pessoal',
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `metas`
--

INSERT INTO `metas` (`id`, `user_id`, `titulo`, `descricao`, `status`, `prazo`, `categoria`, `criado_em`) VALUES
(1, 1, 'Estudar', 'Estudar para prova importante', 'concluida', '2025-09-26', 'Outro', '2025-09-13 11:58:36'),
(3, 1, 'Comprar um livro', 'Ler', 'em_andamento', '2025-10-11', 'Financeiro', '2025-09-13 12:10:43'),
(5, 4, 'Playstation 5', 'Comprar playstation', 'concluida', '2025-12-31', 'Pessoal', '2025-10-22 12:13:38'),
(6, 5, '50kg no supino', 'Quero ficar forte', 'em_andamento', '2025-11-19', 'Saúde', '2025-11-12 11:56:08'),
(23, 6, 'Aprender a programar', '*   **1. Defina Seu Primeiro Alvo Laser (e Porquê):**\r\n    *   Escolha UMA linguagem de programação específica (ex: Python para automação, JavaScript para desenvolvimento web, etc.). Não tente aprender tudo de uma vez.\r\n    *   Defina um micro-projeto inicial e tangível que te motive (ex: um script para organizar arquivos, uma calculadora simples, uma página web básica). Este é seu \"porquê\" e seu primeiro marco de sucesso.\r\n\r\n*   **2. Crie Seu Ritual de Código Inegociável:**\r\n    *   Bloqueie na sua agenda 30-60 minutos, em um horário fixo e diário (ou 5 vezes por semana), exclusivamente para programar. Trate-o como um compromisso inadiável.\r\n    *   Escolha UM recurso principal (um curso online, um livro, uma plataforma de desafios) e siga-o consistentemente. Evite a \"paralisia por análise\" de muitos recursos.\r\n\r\n*   **3. Codifique Ativamente, Não Apenas Observe:**\r\n    *   Para cada conceito novo que aprender, PARE e codifique-o imediatamente. Escreva o código, execute-o, modifique-o, e teste suas próprias ideias.\r\n    *   Dedique a maior parte do seu tempo (80%) a escrever código, resolver problemas e trabalhar no seu micro-projeto. Os outros 20% são para consumir conteúdo. Erre, depure e aprenda com os erros.', 'concluida', '2026-04-10', 'Educação', '2026-03-09 23:01:15'),
(8, 8, 'Fazer Tatuagem', 'Juntar 1800 reais', 'em_andamento', '2026-03-31', 'Pessoal', '2025-12-16 18:01:52'),
(9, 8, 'ps5', 'comprar', 'em_andamento', '2026-12-12', 'Pessoal', '2025-12-16 22:11:22'),
(16, 11, 'Ler um livro novo', '', 'pendente', '2026-03-14', 'Educação', '2026-02-24 23:32:29'),
(14, 10, 'Quero aprender a falar inglês', '', 'pendente', '2026-03-14', 'Educação', '2026-02-23 23:28:16'),
(15, 10, 'Ler um livro novo', '', 'em_andamento', '2026-03-03', 'Educação', '2026-02-24 22:53:55'),
(17, 11, 'Aprender a programar', '', 'concluida', '2026-03-11', 'Profissional', '2026-02-25 00:29:54'),
(18, 10, 'Aprender a programar', '', 'pendente', '2026-04-11', 'Educação', '2026-03-02 22:24:18'),
(19, 10, 'Guardar R$5.000', '', 'em_andamento', '2026-04-24', 'Financeiro', '2026-03-02 22:24:38'),
(20, 10, 'Estudar', '', 'em_andamento', '2026-04-10', 'Educação', '2026-03-02 22:24:51'),
(21, 10, 'Aprender a tocar violão', '**1. Estruturar o Início**\r\n*   **Adquira o Essencial:** Garanta um violão adequado para iniciantes e acessórios básicos (afinador digital, palhetas).\r\n*   **Escolha Sua Fonte:** Selecione UMA plataforma de aprendizado primária (ex: aplicativo interativo para iniciantes, curso online estruturado ou aulas com professor). Evite sobrecarga de informação inicial.\r\n*   **Agende o Hábito:** Bloqueie na sua agenda 15-30 minutos diários de prática. Trate este compromisso como inegociável, preferencialmente no mesmo horário, para construir consistência.\r\n\r\n**2. Prática Deliberada e Focada**\r\n*   **Domine os Fundamentos:** Concentre-se em postura correta, formação de 3-4 acordes abertos essenciais (ex: C, G, D, Em) e um padrão de batida simples.\r\n*   **Use o Metrônomo:** Incorpore um metrônomo desde o início para desenvolver ritmo e tempo. Comece devagar e aumente gradualmente.\r\n*   **Qualidade sobre Quantidade:** Dedique 5-10 minutos por acorde ou técnica, garantindo que cada nota soe limpa antes de avançar. Grave-se ocasionalmente para identificar falhas e celebrar progressos.\r\n\r\n**3. Aplicação e Expansão**\r\n*   **Toque Músicas Simples:** Assim que os acordes básicos estiverem razoáveis, aprenda 1-2 músicas simples que os utilizem. Isso reforça o aprendizado, aumenta a motivação e dá um propósito à sua prática.\r\n*   **Defina Micrometras:** A cada semana, estabeleça uma meta pequena e alcançável (ex: \"aprender um novo acorde\", \"tocar uma música sem parar\", \"praticar 5 minutos de troca de acordes\").\r\n*   **Evolua Gradualmente:** Comece a introduzir novos acordes, técnicas (como pestanas) e explore variações rítmicas. Nunca pare de revisitar e aprimorar o que já aprendeu.', 'em_andamento', '2026-07-31', 'Pessoal', '2026-03-02 23:26:45'),
(22, 10, 'Aprender a tocar guitarra', '*   **1. Preparar e Agendar:** Adquira uma guitarra (acústica para iniciantes é ideal), um afinador digital e algumas palhetas. Bloqueie 15-20 minutos diários em sua agenda para \"Prática de Guitarra\", preferencialmente no mesmo horário, para criar um hábito.\r\n*   **2. Fundamentos Focados (Semanas 1-4):** Nas primeiras 4 semanas, concentre-se em aprender 2-3 acordes básicos (ex: G, C, D ou E, A, D) e um padrão de batida simples. Utilize tutoriais gratuitos no YouTube ou aplicativos para aprender a posição correta dos dedos e pratique as transições entre esses acordes diariamente até que sejam suaves.\r\n*   **3. Aplicar e Expandir (Semanas 5+):** Escolha 1-2 músicas simples que utilizem os acordes que você já domina e pratique tocá-las. A cada 2 semanas, adicione um novo acorde ou uma técnica básica (como um riff simples). Considere aulas online ou presenciais após o segundo mês para feedback e aceleração do aprendizado.', 'pendente', '2026-09-02', 'Pessoal', '2026-03-02 23:41:54'),
(24, 6, 'Guardar R$5.000', '', 'em_andamento', '2026-04-11', 'Financeiro', '2026-03-09 23:02:48'),
(25, 6, 'Aprender inglês', '', 'concluida', '2026-04-08', 'Educação', '2026-03-09 23:03:18'),
(26, 12, 'Realizar a correção da documentação dos projetos integradores', '*   **1. Mapear e Reunir:** Liste todos os documentos dos projetos integradores que precisam de correção. Reúna as diretrizes de correção, templates e os arquivos atuais. Crie uma planilha simples com o nome de cada documento e seu status.\r\n*   **2. Priorizar e Agendar:** Ordene os documentos por urgência ou impacto. Agende blocos de tempo dedicados e ininterruptos na sua agenda (ex: 2 horas por sessão) exclusivamente para a tarefa de correção.\r\n*   **3. Executar Focado:** Durante cada bloco agendado, trabalhe em 1 ou 2 documentos, focando 100% na correção. Utilize um checklist ou as diretrizes como referência constante. Marque o documento como \"Corrigido\" na sua planilha após cada conclusão.\r\n*   **4. Revisão e Entrega:** Ao finalizar as correções de um conjunto de documentos ou de todos, faça uma revisão final rápida para verificar consistência e erros óbvios. Organize e entregue a documentação revisada conforme os canais estabelecidos.', 'concluida', '2026-03-16', 'Profissional', '2026-03-09 23:10:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `paginas`
--

CREATE TABLE `paginas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `conteudo` longtext NOT NULL,
  `rota` varchar(50) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `paginas`
--

INSERT INTO `paginas` (`id`, `titulo`, `conteudo`, `rota`, `data_criacao`) VALUES
(1, 'Template SB Admin 2 - Blank', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<meta charset=\"utf-8\">\n... RESTANTE DO SEU HTML ...\n</body>\n</html>', 'blank', '2025-04-14 21:25:28'),
(2, '', '<h1>Página não encontrada</h1>', '404', '2025-04-14 23:17:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfil`
--

CREATE TABLE `perfil` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `preferencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_gamificacao`
--

CREATE TABLE `progresso_gamificacao` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nivel` int(11) DEFAULT 1,
  `pontos` int(11) DEFAULT 0,
  `data_ultimo_login` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recompensas`
--

CREATE TABLE `recompensas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `custo_moedas` int(11) NOT NULL,
  `icone` varchar(50) DEFAULT 'gift'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `resgates`
--

CREATE TABLE `resgates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `custo` int(11) DEFAULT NULL,
  `data_resgate` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sistema_arquivos`
--

CREATE TABLE `sistema_arquivos` (
  `id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `tipo_arquivo` varchar(50) NOT NULL,
  `conteudo` longtext NOT NULL,
  `caminho` varchar(255) NOT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `site_pages`
--

CREATE TABLE `site_pages` (
  `id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_content` longtext NOT NULL,
  `page_type` enum('default','custom','system') NOT NULL DEFAULT 'default',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `subtasks`
--

CREATE TABLE `subtasks` (
  `id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `due_days` int(11) DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sugestoes_ia`
--

CREATE TABLE `sugestoes_ia` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `sugestao` text DEFAULT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `meta_id` int(11) NOT NULL,
  `descricao` varchar(500) NOT NULL,
  `concluida` tinyint(1) NOT NULL DEFAULT 0,
  `xp_recompensa` int(11) NOT NULL DEFAULT 10,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pendente'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `tarefas`
--

INSERT INTO `tarefas` (`id`, `meta_id`, `descricao`, `concluida`, `xp_recompensa`, `criado_em`, `status`) VALUES
(7, 14, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-02-23 23:28:16', 'pendente'),
(6, 14, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-02-23 23:28:16', 'pendente'),
(5, 14, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-02-23 23:28:16', 'pendente'),
(8, 14, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-02-23 23:28:16', 'pendente'),
(9, 15, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-02-24 22:53:55', 'pendente'),
(10, 15, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-02-24 22:53:55', 'pendente'),
(11, 15, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-02-24 22:53:55', 'pendente'),
(12, 15, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-02-24 22:53:55', 'pendente'),
(13, 16, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-02-24 23:32:29', 'pendente'),
(14, 16, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-02-24 23:32:29', 'pendente'),
(15, 16, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-02-24 23:32:29', 'pendente'),
(16, 16, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-02-24 23:32:29', 'pendente'),
(17, 17, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-02-25 00:29:54', 'pendente'),
(18, 17, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-02-25 00:29:54', 'pendente'),
(19, 17, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-02-25 00:29:54', 'pendente'),
(20, 17, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-02-25 00:29:54', 'pendente'),
(21, 18, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-03-02 22:24:18', 'pendente'),
(22, 18, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-03-02 22:24:18', 'pendente'),
(23, 18, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-03-02 22:24:18', 'pendente'),
(24, 18, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-03-02 22:24:18', 'pendente'),
(25, 19, 'Anotar todos os gastos fixos e variáveis do mês', 0, 10, '2026-03-02 22:24:38', 'pendente'),
(26, 19, 'Definir um valor exato para guardar por semana', 0, 10, '2026-03-02 22:24:38', 'pendente'),
(27, 19, 'Pesquisar alternativas mais baratas para despesas atuais', 0, 10, '2026-03-02 22:24:38', 'pendente'),
(28, 19, 'Fazer a primeira transferência para a poupança/investimento', 0, 10, '2026-03-02 22:24:38', 'pendente'),
(29, 20, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-03-02 22:24:51', 'pendente'),
(30, 20, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-03-02 22:24:51', 'pendente'),
(31, 20, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-03-02 22:24:51', 'pendente'),
(32, 20, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-03-02 22:24:51', 'pendente'),
(33, 21, 'Pesquisar e selecionar o melhor material/curso inicial', 0, 10, '2026-03-02 23:26:45', 'pendente'),
(34, 21, 'Montar um cronograma de estudos semanal de 2 horas', 0, 10, '2026-03-02 23:26:45', 'pendente'),
(35, 21, 'Assistir à primeira aula ou ler o primeiro capítulo', 0, 10, '2026-03-02 23:26:45', 'pendente'),
(36, 21, 'Fazer um resumo dos pontos principais aprendidos', 0, 10, '2026-03-02 23:26:45', 'pendente'),
(37, 22, '1. Preparar e Agendar: Adquira uma guitarra (acústica para iniciantes é ideal), um afinador digital e algumas palhetas. Bloqueie 15-20 minutos diários em sua agenda para \"Prática de Guitarra\", preferencialmente no mesmo horário, para criar um hábito.', 0, 10, '2026-03-02 23:41:54', 'pendente'),
(38, 22, '2. Fundamentos Focados (Semanas 1-4): Nas primeiras 4 semanas, concentre-se em aprender 2-3 acordes básicos (ex: G, C, D ou E, A, D) e um padrão de batida simples. Utilize tutoriais gratuitos no YouTube ou aplicativos para aprender a posição correta ', 0, 10, '2026-03-02 23:41:54', 'pendente'),
(39, 22, '3. Aplicar e Expandir (Semanas 5+): Escolha 1-2 músicas simples que utilizem os acordes que você já domina e pratique tocá-las. A cada 2 semanas, adicione um novo acorde ou uma técnica básica (como um riff simples). Considere aulas online ou presenci', 0, 10, '2026-03-02 23:41:54', 'pendente'),
(40, 23, '1. Defina Seu Primeiro Alvo Laser (e Porquê):', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(41, 23, 'Escolha UMA linguagem de programação específica (ex: Python para automação, JavaScript para desenvolvimento web, etc.). Não tente aprender tudo de uma vez.', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(42, 23, 'Defina um micro-projeto inicial e tangível que te motive (ex: um script para organizar arquivos, uma calculadora simples, uma página web básica). Este é seu \"porquê\" e seu primeiro marco de sucesso.', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(43, 23, '2. Crie Seu Ritual de Código Inegociável:', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(44, 23, 'Bloqueie na sua agenda 30-60 minutos, em um horário fixo e diário (ou 5 vezes por semana), exclusivamente para programar. Trate-o como um compromisso inadiável.', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(45, 23, 'Escolha UM recurso principal (um curso online, um livro, uma plataforma de desafios) e siga-o consistentemente. Evite a \"paralisia por análise\" de muitos recursos.', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(46, 23, '3. Codifique Ativamente, Não Apenas Observe:', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(47, 23, 'Para cada conceito novo que aprender, PARE e codifique-o imediatamente. Escreva o código, execute-o, modifique-o, e teste suas próprias ideias.', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(48, 23, 'Dedique a maior parte do seu tempo (80%) a escrever código, resolver problemas e trabalhar no seu micro-projeto. Os outros 20% são para consumir conteúdo. Erre, depure e aprenda com os erros.', 0, 10, '2026-03-09 23:01:15', 'pendente'),
(49, 24, 'Dar o primeiro passo para: Guardar R$5.000', 0, 10, '2026-03-09 23:02:48', 'pendente'),
(50, 25, 'Dar o primeiro passo para: Aprender inglês', 0, 10, '2026-03-09 23:03:18', 'pendente'),
(51, 26, '1. Mapear e Reunir: Liste todos os documentos dos projetos integradores que precisam de correção. Reúna as diretrizes de correção, templates e os arquivos atuais. Crie uma planilha simples com o nome de cada documento e seu status.', 0, 10, '2026-03-09 23:10:31', 'pendente'),
(52, 26, '2. Priorizar e Agendar: Ordene os documentos por urgência ou impacto. Agende blocos de tempo dedicados e ininterruptos na sua agenda (ex: 2 horas por sessão) exclusivamente para a tarefa de correção.', 0, 10, '2026-03-09 23:10:31', 'pendente'),
(53, 26, '3. Executar Focado: Durante cada bloco agendado, trabalhe em 1 ou 2 documentos, focando 100% na correção. Utilize um checklist ou as diretrizes como referência constante. Marque o documento como \"Corrigido\" na sua planilha após cada conclusão.', 0, 10, '2026-03-09 23:10:31', 'pendente'),
(54, 26, '4. Revisão e Entrega: Ao finalizar as correções de um conjunto de documentos ou de todos, faça uma revisão final rápida para verificar consistência e erros óbvios. Organize e entregue a documentação revisada conforme os canais estabelecidos.', 0, 10, '2026-03-09 23:10:31', 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `templates`
--

CREATE TABLE `templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `conteudo` longtext NOT NULL,
  `tipo` enum('dashboard','login','formulario','relatorio') NOT NULL DEFAULT 'dashboard',
  `rota` varchar(50) DEFAULT NULL,
  `versao` varchar(20) DEFAULT '1.0',
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens_recuperacao`
--

CREATE TABLE `tokens_recuperacao` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `tokens_recuperacao`
--

INSERT INTO `tokens_recuperacao` (`id`, `user_id`, `token`, `expira_em`) VALUES
(1, 2, '0c7fce600df519cccf7f7a6550cebddeaead322c86ce18d6fd16eaa9c21af4d61b6f1f04203e48e93c07ab40a3621bf04c14', '2025-09-17 09:17:50'),
(2, 2, '0fe4bf0b005785822a5a5a4e84e3374b124553ce2a38b8642bbfcb7613721c9c0d585b3ec3d9bf1660900c4c0a76359e58b8', '2025-09-17 09:17:54'),
(3, 2, '57bc302215f8a6268a6119c6cffa22539649c7170d0b294c66078e2ad25ba44bc6e98ae08907b84b27cd6ef8acd45fd35912', '2025-09-17 09:19:15'),
(4, 2, 'e9ab3a0c254be09241541a8b0dba5197a07ec8beba3ede0781120ed52adcefd65f2c1bcbae9dbda2514091807678574b9afe', '2025-09-17 09:19:27'),
(5, 2, '65c54a5b681f98dc2ec6586fdf18665a60e3cc9dea4395be209b26998d43d7dd7b67f57d7f99dbcdeabea7769263530b211e', '2025-09-17 09:19:29'),
(6, 6, 'a9854added261e2c7c16a2d86ed80e5f6fc88d04c1289da3b88ea06d38943cd6b7715b98436268296504923fd63bcaa535b3', '2026-02-09 18:46:51'),
(7, 6, '0fbe6c15943d00442e18a499d6cc30b7b5fdbeb4c2e04e58bd1707397668cb03530370d11ab1a0068fd41b77b6e251842967', '2026-02-09 18:47:03'),
(8, 6, '3601f0df92f66be153cfde8b50fef1916e1192cf131eb7aa6328fc9f31cf6ce85bb97d61c3413f193f9bbc59741a42bbd70b', '2026-02-09 19:22:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nivel` int(11) NOT NULL DEFAULT 1,
  `xp` int(11) NOT NULL DEFAULT 0,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `xp_total` int(11) DEFAULT 0,
  `tentativas_login` int(11) DEFAULT 0,
  `ultimo_bloqueio` datetime DEFAULT NULL,
  `ultima_atividade` date DEFAULT NULL,
  `streak_atual` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `nivel`, `xp`, `foto_perfil`, `criado_em`, `xp_total`, `tentativas_login`, `ultimo_bloqueio`, `ultima_atividade`, `streak_atual`) VALUES
(7, 'Flaviana', 'flaviana.ucb@gmail.com', '$2y$10$JyO.B0Q6/R6hbgboaVpqBel31RLyHsG1YBW6f5uuO4eI8Af6IbZzW', 1, 0, NULL, '2025-12-10 11:58:45', 0, 0, NULL, NULL, 0),
(6, 'Luis Brito', 'luis.brito@sempreceub.com', '$2y$10$Y1WKGyAXrb/2OlcGfCPDbOlQDzTMIKSOwgq1bL3EJYm0lBMahbkfe', 1, 0, 'uploads/user_6_693960277f306.jpg', '2025-12-10 11:50:12', 50, 0, NULL, NULL, 0),
(5, 'joaobnddrj', 'joao.cp@sempreceub.com', '$2y$10$X4mrRj1A7E/jXwcdqTy8eeDKao/sS5MLUqvdXYzSxuAxZa5huXJOK', 1, 0, 'uploads/user_5_69147646b1686.jpg', '2025-11-12 11:47:20', 0, 0, NULL, NULL, 0),
(11, 'Henrique', 'henriferreira2005@sempreceub.com', '$2y$10$EsCXCeT7lya3xuZi/H6eue2IsPqSdM2rn5TyWYEF6N2OHcz3S2jwu', 1, 0, NULL, '2026-02-24 23:03:43', 0, 0, NULL, NULL, 0),
(8, 'schetto', 'arthur.rodrigues@engesoftware.com.br', '$2y$10$SaP6AqBdS9k92SuRCz945ucobrHaQN5bKHh5ax5fpfAIQrtZnQ/2C', 1, 0, NULL, '2025-12-16 17:45:37', 0, 0, NULL, NULL, 0),
(10, 'Brito', 'luisfcdebrito@gmail.com', '$2y$10$OvHyfelNbeWXQp1aBSSKo.DBZ7iRsFybWs4w5u7TXpj8B0IjXfnbK', 3, 0, NULL, '2026-02-06 03:26:21', 50, 0, NULL, NULL, 0),
(12, 'Tiago Leite', 'tiagolei.tl@gmail.com', '$2y$10$gR.6wca7cRfyXXOFiYXmXuTrTkWP6mmVCD.e6wV7f2NWWolVX71iO', 1, 0, NULL, '2026-03-09 23:08:33', 0, 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `unlocked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_conquistas`
--

CREATE TABLE `user_conquistas` (
  `user_id` int(11) NOT NULL,
  `conquista_id` int(11) NOT NULL,
  `data_desbloqueio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `xp` int(11) NOT NULL DEFAULT 0,
  `coins` int(11) NOT NULL DEFAULT 100,
  `streak_days` int(11) NOT NULL DEFAULT 0,
  `last_active_date` date DEFAULT NULL,
  `theme` varchar(20) DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `xp` int(11) DEFAULT 0,
  `nivel` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `xp_total` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_conquistas`
--

CREATE TABLE `usuario_conquistas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conquista_id` int(11) NOT NULL,
  `data_conquista` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuario_conquistas`
--

INSERT INTO `usuario_conquistas` (`id`, `user_id`, `conquista_id`, `data_conquista`) VALUES
(1, 10, 1, '2026-03-02 09:11:14'),
(2, 10, 2, '2026-03-02 14:24:57'),
(3, 11, 1, '2026-03-04 14:47:54'),
(4, 6, 1, '2026-03-09 16:15:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_recompensas`
--

CREATE TABLE `usuario_recompensas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recompensa_id` int(11) NOT NULL,
  `data_resgate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `xp`
--

CREATE TABLE `xp` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `pontos` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `tipo` enum('conquista','meta') DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `xp_log`
--

CREATE TABLE `xp_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `xp_ganho` int(11) NOT NULL,
  `data_ganho` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `xp_log`
--

INSERT INTO `xp_log` (`id`, `user_id`, `xp_ganho`, `data_ganho`) VALUES
(1, 10, 50, '2026-03-02'),
(2, 10, 50, '2026-03-02'),
(3, 10, 50, '2026-03-02'),
(4, 10, 50, '2026-03-02'),
(5, 10, -50, '2026-03-02'),
(6, 10, -50, '2026-03-02'),
(7, 10, 100, '2026-03-02'),
(8, 10, -50, '2026-03-02'),
(9, 10, 50, '2026-03-02'),
(10, 10, 50, '2026-03-02'),
(11, 10, -50, '2026-03-02'),
(12, 10, -50, '2026-03-02'),
(13, 10, 50, '2026-03-02'),
(14, 10, 50, '2026-03-02'),
(15, 10, -50, '2026-03-02'),
(16, 10, -50, '2026-03-02'),
(17, 10, 50, '2026-03-02'),
(18, 10, -50, '2026-03-02'),
(19, 10, 50, '2026-03-02'),
(20, 10, 50, '2026-03-02'),
(21, 10, 50, '2026-03-02'),
(22, 10, 50, '2026-03-02'),
(23, 10, 200, '2026-03-02'),
(24, 10, -50, '2026-03-02'),
(25, 10, -50, '2026-03-02'),
(26, 10, -50, '2026-03-02'),
(27, 10, -50, '2026-03-02'),
(28, 10, -50, '2026-03-02'),
(29, 10, 50, '2026-03-02'),
(30, 10, 50, '2026-03-02'),
(31, 10, 50, '2026-03-02'),
(32, 10, 50, '2026-03-02'),
(33, 10, 50, '2026-03-02'),
(34, 10, -50, '2026-03-02'),
(35, 10, 50, '2026-03-02'),
(36, 10, 50, '2026-03-02'),
(37, 10, 50, '2026-03-03'),
(38, 11, 100, '2026-03-04'),
(39, 6, 100, '2026-03-09'),
(40, 6, 50, '2026-03-09');

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `conquistas`
--
ALTER TABLE `conquistas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `habit_logs`
--
ALTER TABLE `habit_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `habit_id` (`habit_id`,`completed_date`);

--
-- Índices de tabela `historico_recompensas`
--
ALTER TABLE `historico_recompensas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `html_templates`
--
ALTER TABLE `html_templates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `logs_atividade`
--
ALTER TABLE `logs_atividade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `metas`
--
ALTER TABLE `metas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `paginas`
--
ALTER TABLE `paginas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `progresso_gamificacao`
--
ALTER TABLE `progresso_gamificacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `recompensas`
--
ALTER TABLE `recompensas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `resgates`
--
ALTER TABLE `resgates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `sistema_arquivos`
--
ALTER TABLE `sistema_arquivos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `site_pages`
--
ALTER TABLE `site_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_name_unique` (`page_name`);

--
-- Índices de tabela `subtasks`
--
ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Índices de tabela `sugestoes_ia`
--
ALTER TABLE `sugestoes_ia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meta_id` (`meta_id`);

--
-- Índices de tabela `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rota` (`rota`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Índices de tabela `user_conquistas`
--
ALTER TABLE `user_conquistas`
  ADD PRIMARY KEY (`user_id`,`conquista_id`),
  ADD KEY `conquista_id` (`conquista_id`);

--
-- Índices de tabela `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `usuario_conquistas`
--
ALTER TABLE `usuario_conquistas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `conquista_id` (`conquista_id`);

--
-- Índices de tabela `usuario_recompensas`
--
ALTER TABLE `usuario_recompensas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recompensa_id` (`recompensa_id`);

--
-- Índices de tabela `xp`
--
ALTER TABLE `xp`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `xp_log`
--
ALTER TABLE `xp_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `conquistas`
--
ALTER TABLE `conquistas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `goals`
--
ALTER TABLE `goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `habits`
--
ALTER TABLE `habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `habit_logs`
--
ALTER TABLE `habit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_recompensas`
--
ALTER TABLE `historico_recompensas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `html_templates`
--
ALTER TABLE `html_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `logs_atividade`
--
ALTER TABLE `logs_atividade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `metas`
--
ALTER TABLE `metas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `paginas`
--
ALTER TABLE `paginas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `perfil`
--
ALTER TABLE `perfil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `progresso_gamificacao`
--
ALTER TABLE `progresso_gamificacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recompensas`
--
ALTER TABLE `recompensas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `resgates`
--
ALTER TABLE `resgates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sistema_arquivos`
--
ALTER TABLE `sistema_arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `site_pages`
--
ALTER TABLE `site_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `subtasks`
--
ALTER TABLE `subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sugestoes_ia`
--
ALTER TABLE `sugestoes_ia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de tabela `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario_conquistas`
--
ALTER TABLE `usuario_conquistas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuario_recompensas`
--
ALTER TABLE `usuario_recompensas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `xp`
--
ALTER TABLE `xp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `xp_log`
--
ALTER TABLE `xp_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Restrições para dumps de tabelas
--

--
-- Restrições para tabelas `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `habits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `habit_logs`
--
ALTER TABLE `habit_logs`
  ADD CONSTRAINT `habit_logs_ibfk_1` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`);

--
-- Restrições para tabelas `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
