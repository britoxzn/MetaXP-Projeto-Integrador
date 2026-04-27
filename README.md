# 🚀 MetaXP - Sistema Gamificado de Gestão de Metas com IA

O **MetaXP** é uma plataforma inovadora que une produtividade e gamificação. O sistema transforma a gestão de tarefas pessoais em uma experiência interativa, utilizando Inteligência Artificial para planejamento e mecânicas de RPG para combater a procrastinação.

## 🛠 Funcionalidades Principais

1.  **Dashboard Inteligente:** Painel centralizado com métricas de desempenho e visão geral de progresso.
2.  **Sistema de Ofensivas (Streaks):** Contador de "foguinhos" que monitora dias consecutivos de atividade para incentivar a consistência.
3.  **Planejamento com IA (Meta.IA):** Integração com inteligência artificial para gerar planos de ação automáticos baseados nos objetivos do usuário.
4.  **Sistema de XP e Níveis:** Ganho de experiência por tarefas concluídas, permitindo a evolução de nível do perfil.
5.  **Gráficos de Atividade:** Visualização dinâmica da evolução semanal utilizando a biblioteca Chart.js.
6.  **Lembretes Automáticos:** Sistema de notificações por e-mail para metas próximas ao vencimento.
7.  **Ranking Global:** Gamificação social com exibição dos líderes de XP da plataforma.
8.  **Gestão de Metas CRUD:** Interface completa para criar, listar, editar e excluir objetivos pessoais.
9.  **Categorização de Objetivos:** Organização de metas por áreas como Pessoal, Profissional, Saúde e Educação.
10. **Segurança via Chave de API:** Scripts críticos (como notificações) protegidos por tokens de segurança para acesso via Cron Jobs externos.
11. **Autenticação Segura:** Sistema de login e proteção de rotas verificando sessão do usuário (auth_check).
12. **Histórico de Conquistas:** Galeria de troféus desbloqueados por marcos alcançados no sistema.
13. **Upload de Perfil:** Personalização de conta com suporte a fotos de perfil dos usuários.
14. **Design Responsivo com Dark Mode:** Interface adaptável para dispositivos móveis e suporte a tema escuro.
15. **Arquitetura de Banco de Dados Relacional:** Estrutura otimizada em MySQL para rastreabilidade de metas e logs de XP.

## 🚀 Tecnologias Utilizadas
* **Linguagem:** PHP 8.x
* **Banco de Dados:** MySQL (Hospedado via InfinityFree)
* **Frontend:** HTML5, CSS3, JavaScript (Chart.js, Canvas-confetti)
* **Segurança:** PDO (PHP Data Objects) e Chaves de Segurança URL

## 📋 Como executar o projeto
1. Realize o upload dos arquivos para um servidor PHP.
2. Importe o banco de dados SQL disponível na pasta `/docs`.
3. Configure as credenciais no arquivo `conexao.php`.
4. Acesse via navegador para criar sua conta.
