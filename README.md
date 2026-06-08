# 🚀 MetaXP | Plataforma Gamificada de Gestão de Hábitos com IA

![Status](https://img.shields.io/badge/Status-Estável_e_Validado-success?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Banco_Relacional-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Documentação](https://img.shields.io/badge/Documentação-Atualizada-blue?style=for-the-badge)

> 🎓 **Projeto Integrador IV — Centro Universitário de Brasília (CEUB)**  
> 👨‍🏫 **Orientador:** Prof. Thiago Leite  
> 💻 **Curso:** Tecnologia em Análise e Desenvolvimento de Sistemas  

---

## 👥 Integrantes do Grupo

| Nome | RA | Responsabilidade Principal |
| :--- | :--- | :--- |
| **Luis Brito** | 22402920 | Engenharia de Backend, Integração de IA e Gamificação |
| **Henrique Assunção** | 22402116 | Modelagem de Dados e Versionamento Documental |
| **Arthur Schettini** | 22409361 | Arquitetura Técnica e Roteiros de Teste |

---

## 📋 Índice
1. [Visão Geral da Solução](#-visão-geral-da-solução)
2. [Documentação Técnica e Auditoria](#-documentação-técnica-e-auditoria)
3. [Arquitetura e Engenharia do Projeto](#-arquitetura-e-engenharia-do-projeto)
4. [Instruções de Instalação e Deploy](#-instruções-de-instalação-e-deploy)

---

## 💡 Visão Geral da Solução

O **MetaXP** é um ecossistema web focado no combate à procrastinação e na retenção de usuários através do cruzamento de duas vertentes principais:

* 🎮 **Motor de Gamificação:** Utilização de mecânicas de *Streaks* (ofensivas), acúmulo de pontos de experiência (XP) e desbloqueio de conquistas para garantir a constância do usuário.
* 🤖 **Integração Cognitiva (IA):** Uso de Inteligência Artificial generativa para a quebra automatizada de objetivos complexos em micro-tarefas acionáveis, mitigando a paralisia por análise.

---

## 📂 Documentação Técnica e Auditoria

Toda a fundamentação acadêmica, modelagem estrutural e validações de segurança do MetaXP encontram-se centralizadas e mapeadas dentro do diretório `/docs`:

* 📑 **[Resumo Executivo Oficial](./docs/resumo_executivo.pdf):** Definição de escopo, público-alvo e modelo de MVP.
* 💾 **[Modelagem e Dump do Banco de Dados](./docs/banco.sql):** Estrutura DDL/DML das tabelas relacionais e integridade referencial.
* 🤖 **[Integração e Fallback da IA](./docs/documentacao_ia.md):** Mapeamento do contrato de dados via JSON e estratégias de contingência do modelo preditivo.
* 🧪 **[Roteiro de Evidências e Testes](./docs/evidencias_teste.md):** Homologação e massa de dados dos fluxos principais da aplicação.

---

## 🛠️ Arquitetura e Engenharia do Projeto

O projeto adota uma arquitetura modularizada focada na separação de responsabilidades (Separation of Concerns), garantindo facilidade de manutenção e desacoplamento de código:

```text
📦 MetaXP-Projeto-Integrador
 ┣ 📂 action     # Processadores de formulários, segurança e requisições (POST/GET)
 ┣ 📂 api        # Endpoints assíncronos e processamento da Generative AI
 ┣ 📂 assets     # Recursos estáticos controlados (Folhas de Estilo CSS e Scripts JS)
 ┣ 📂 config     # Centralização de credenciais de ambiente e conexão com o Banco (MySQL)
 ┣ 📂 docs       # Assets acadêmicos, diagramações e documentações markdown
 ┣ 📂 includes   # Helpers de validação, controle de sessões e core de gamificação
 ┣ 📂 pages      # Camada de Apresentação (Views/Telas visíveis ao usuário final)
 ┣ 📜 LICENSE    # Licença de uso do repositório
 ┣ 📜 README.md  # Documentação técnica principal
 ┗ 📜 index.php  # Ponto de entrada do ecossistema (Bootstrapper com redirecionamento)

 🚀 Instruções de Instalação e Deploy
O sistema foi otimizado para rodar de forma nativa em ambientes de hospedagem compartilhada (ex: Apache/InfinityFree) ou servidores locais (XAMPP).

Pré-requisitos
- Servidor Web (Apache / Nginx)

- PHP 8.0 ou superior (com extensão cURL devidamente ativa para comunicação com a API de IA)

- MySQL 5.7+ ou MariaDB

Passo a Passo para Execução Local
1. Clonagem do Repositório:
git clone [https://github.com/britoxzn/MetaXP-Projeto-Integrador.git](https://github.com/britoxzn/MetaXP-Projeto-Integrador.git)

1. Setup do Banco de Dados:

- Acesse seu gerenciador de banco de dados (ex: phpMyAdmin).

- Crie um schema vazio utilizando a collation utf8mb4_general_ci.

- Importe o script estrutural localizado em /docs/banco.sql.

2. Parametrização do Sistema:

- Navegue até a pasta /config e configure o arquivo conexao.php com as credenciais do seu ambiente local (host, user, password, dbname).

3. Inicialização:

- Inicialize os serviços do seu servidor local.

- Acesse o projeto pelo navegador através do endereço local padrão. O arquivo raiz index.php se encarregará de rotear você automaticamente para a tela de autenticação segura em /pages/login.php.

MetaXP © 2024 - Desenvolvido para o Centro Universitário de Brasília.
