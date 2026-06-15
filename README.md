# 🚀 MetaXP | Plataforma Gamificada de Gestão de Hábitos com IA

![Status](https://img.shields.io/badge/Status-Vers%C3%A3o_Final_Est%C3%A1vel-success?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Banco_Relacional-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Acesso](https://img.shields.io/badge/Acesso_Professor-Liberado_vias_tiagolei-blueviolet?style=for-the-badge)

> 🎓 **Projeto Integrador IV — Centro Universitário de Brasília (CEUB)** > 👨‍🏫 **Orientador:** Prof. Thiago Leite  
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
2. [Evolução do Projeto (Ajustes pós-Feedback)](#-evolução-do-projeto-ajustes-pós-feedback)
3. [Documentação Técnica e Negocial](#-documentação-técnica-e-negocial)
4. [Arquitetura e Engenharia de Diretórios](#-arquitetura-e-engenharia-de-diretórios)
5. [Instruções de Instalação e Execução](#-instruções-de-instalação-e-execução)
6. [Quadro de Contribuição Individual](#-quadro-de-contribuição-individual)

---

## 💡 Visão Geral da Solução

O **MetaXP** é um ecossistema web voltado ao combate à procrastinação e otimização da produtividade pessoal. A plataforma atua através do cruzamento de duas vertentes principais:
* **Motor de Gamificação (Dimensão Negocial):** Engajamento do usuário através de acúmulo de pontos de experiência (XP), manutenção de ofensivas (*streaks*) e resgate de recompensas/conquistas.
* **Integração Cognitiva (Dimensão Técnica):** Uso de Inteligência Artificial generativa via requisições assíncronas para decompor objetivos complexos e abstratos em micro-tarefas perfeitamente acionáveis.

---

## 🔄 Evolução do Projeto (Ajustes pós-Feedback)

Atendendo às orientações e feedbacks coletados ao longo do semestre, as seguintes melhorias foram implementadas para esta versão final:
1. **Refatoração e Modularização:** O código foi totalmente desacoplado da raiz. Telas migraram para `/pages`, processamentos para `/action` e integrações para `/api`.
2. **Tratamento de Contingência (IA):** Implementação de camada de *fallback* para tratamento de falhas em chamadas de API externas da IA, evitando travamentos na aplicação.
3. **Segurança de Sessão:** Centralização dos validadores de escopo e autenticação dentro da pasta `/includes`.
4. **Padronização Documental:** Revisão completa do modelo do banco relacional e unificação dos roteiros de teste no diretório oficial de documentação.

---

## 📂 Documentação Técnica e Negocial

Toda a propriedade intelectual e as evidências de engenharia do projeto estão centralizadas na pasta `/docs`:

* 📑 **[Resumo Executivo Oficial (PDF)](./docs/resumo_executivo.pdf):** Documento oficial atualizado com base no template institucional (Público-alvo, Problema, Benefícios e Proposta de Valor).
* 💾 **[Modelagem e Script do Banco de Dados](./docs/banco.sql):** Estrutura DDL/DML das tabelas relacionais do MySQL.
* 🤖 **[Documentação de Integração da IA](./docs/documentacao_ia.md):** Contrato de dados JSON e logs de comportamento do modelo preditivo.
* 🧪 **[Roteiro de Evidências e Testes](./docs/evidencias_teste.md):** Homologação dos casos de teste de uso individuais e fluxos críticos.

---

## 🛠️ Arquitetura e Engenharia de Diretórios

```text
📦 MetaXP-Projeto-Integrador
 ┣ 📂 action     # Processadores de formulários e requisições imperativas (POST/GET)
 ┣ 📂 api        # Endpoints assíncronos e motores da inteligência artificial
 ┣ 📂 assets     # Recursos estáticos controlados (Folhas de estilo CSS e Javascript)
 ┣ 📂 config     # Centralização de credenciais de ambiente e drivers do banco
 ┣ 📂 docs       # Assets acadêmicos, diagramas e relatórios técnicos (PDF/MD)
 ┣ 📂 includes   # Helpers de validação, segurança e core de regras da gamificação
 ┣ 📂 pages      # Camada de Apresentação (Views/Telas do usuário final)
 ┗ 📜 index.php  # Ponto de entrada (Bootstrapper com redirecionamento limpo)
```

🚀 Instruções de Instalação e Execução
📋 Pré-requisitos
- Servidor Web: Apache ou Nginx (ambiente local como XAMPP/MAMP ou ambiente de produção como InfinityFree).

- PHP: Versão 8.0+ com a extensão cURL devidamente habilitada.

- Banco de Dados: SGBD MySQL ou MariaDB.

💻 Instalação Passo a Passo
1. **Clone o repositório no diretório do seu servidor local ou ambiente de hospedagem:
git clone [https://github.com/britoxzn/MetaXP-Projeto-Integrador.git](https://github.com/britoxzn/MetaXP-Projeto-Integrador.git)

2. **Importe o banco de dados** utilizando o script estrutural contido em `/docs/banco.sql` através do seu gerenciador (como phpMyAdmin).
3. **Configure as credenciais** de acesso ao banco editando os parâmetros dentro de `/config/conexao.php`.
4. **Execute a aplicação** acessando o endereço raiz no seu navegador. O arquivo `index.php` tratará o roteamento inicial seguro.

---

## 📊 Quadro de Contribuição Individual

Em conformidade com as diretrizes de avaliação, segue o detalhamento de escopo técnico desenvolvido por cada integrante, validado pelo histórico de commits e interações do repositório:

| Integrante | Atividades desenvolvidas | Evidências ou observações |
| :--- | :--- | :--- |
| **Luis Brito** | Engenharia de Backend, arquitetura das rotas da API, consumo assíncrono do modelo de IA e lógica matemática do motor de gamificação. | Commits estruturais nas pastas `/api`, `/action` e `/includes`. |
| **Henrique Ferreira** | Modelagem física e lógica do banco de dados, mapeamento de restrições de integridade, padronização do ambiente e versionamento documental. | Commits de setup na pasta `/config`, `/docs` e arquivo `banco.sql`. |
| **Arthur Schettini** | Construção e integração visual das Views (telas), amarração de rotas estáticas e execução física do roteiro de testes homologados. | Commits de interface na pasta `/pages`, `/assets` e arquivo `evidencias_teste.md`. |

---
*MetaXP © 2026 - Desenvolvido para o Centro Universitário de Brasília.*
