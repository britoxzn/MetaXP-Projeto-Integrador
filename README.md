# 🚀 MetaXP | Plataforma Gamificada de Gestão de Hábitos com IA

![Status](https://img.shields.io/badge/Status-Estável_e_Validado-success?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Banco_Relacional-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Documentação](https://img.shields.io/badge/Documentação-Atualizada-blue?style=for-the-badge)

> **Projeto Integrador IV - Centro Universitário de Brasília (CEUB)** > **Orientador:** Prof. Thiago Leite  
> **Curso:** Tecnologia em Análise e Desenvolvimento de Sistemas  
## 👥 Integrantes do Grupo
* **Luis Brito** - RA: 22402920
* **Henrique Assunção** - RA: 22402116
* **Arthur Schettini** - RA: [RA do Arthur]

---

## 📋 Índice
1. [Visão Geral da Solução](#-visão-geral-da-solução)
2. [Documentação Técnica e Auditoria](#-documentação-técnica-e-auditoria)
3. [Arquitetura e Engenharia do Projeto](#-arquitetura-e-engenharia-do-projeto)
4. [Instruções de Instalação e Deploy](#-instruções-de-instalação-e-deploy)
5. [Equipe e Rastreabilidade](#-equipe-e-rastreabilidade)

---

## 💡 Visão Geral da Solução

O **MetaXP** é um ecossistema web focado no combate à procrastinação e na retenção de usuários através do cruzamento de duas vertentes principais:
1. **Motor de Gamificação:** Utilização de mecânicas de *Streaks* (ofensivas), pontos de experiência (XP) e desbloqueio de conquistas para garantir constância.
2. **Integração Cognitiva (IA):** Uso de Inteligência Artificial generativa para a quebra automatizada de objetivos complexos em micro-tarefas acionáveis, reduzindo a carga cognitiva do usuário.

---

## 📂 Documentação Técnica e Auditoria

Atendendo aos rigorosos critérios acadêmicos e de engenharia, toda a documentação que fundamenta as decisões de negócio e infraestrutura do MetaXP encontra-se centralizada no diretório `/docs`:

* 📑 **[Resumo Executivo Oficial](./docs/resumo_executivo.pdf):** Definição de escopo, público-alvo e MVP.
* 💾 **[Modelagem e Dump do Banco de Dados](./docs/banco.sql):** Estrutura DDL/DML das tabelas relacionais.
* 🏛️ **[Arquitetura e Regras de Negócio](./docs/arquitetura_e_regras.md):** Fundamentação da stack (PHP/MySQL) e lógicas de cálculo de XP/Streak.
* 🤖 **[Integração e Fallback da IA](./docs/documentacao_ia.md):** Mapeamento do contrato de dados via JSON e estratégias de contingência.
* 🧪 **[Roteiro de Evidências e Testes](./docs/evidencias_teste.md):** Homologação dos fluxos principais da aplicação.

---

## 🛠️ Arquitetura e Engenharia do Projeto

Visando a escalabilidade e a redução de acoplamento (separação de responsabilidades), o repositório foi modularizado nas seguintes camadas:

```text
📦 MetaXP-Projeto-Integrador
 ┣ 📂 /api        # Endpoints assíncronos e processamento de IA (Generative AI)
 ┣ 📂 /config     # Centralização de credenciais (Variáveis de Ambiente / DB)
 ┣ 📂 /docs       # Assets acadêmicos e diagramações
 ┣ 📂 /includes   # Helpers de validação, autenticação e core de gamificação
 ┣ 📂 /assets     # Recursos estáticos controlados (CSS, JS)
 ┗ 📜 *.php       # Camada de Apresentação (Views/Telas visíveis ao usuário na raiz)
