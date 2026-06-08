# ✅ Relatório de Evidências de Teste Manual

Todos os fluxos principais foram testados e estabilizados no ambiente de produção (hospedagem InfinityFree) e ambiente local (XAMPP).

| ID | Cenário de Teste | Status | Evidência / Observação |
|---|---|---|---|
| **CT-01** | Login com incremento de Ofensiva (Streak) | ✅ APROVADO | O campo `ultimo_acesso` é atualizado corretamente no MySQL e o painel reflete o streak_dias +1. |
| **CT-02** | Cadastro de Nova Meta com IA | ✅ APROVADO | O painel de loading exibe o processamento, e o banco recebe as submetas formatadas do JSON da API. |
| **CT-03** | Teste de Fallback (Simulação de erro na API) | ✅ APROVADO | Ao remover temporariamente a chave da API, o sistema não travou e gerou as 3 metas genéricas de contingência. |
| **CT-04** | Bloqueio de URL do CronJob | ✅ APROVADO | Acesso direto a `notifica_prazos.php` sem a `?key=` retorna erro 403 (Acesso Negado). |

*Nota: Os testes comprovam a estabilidade do fluxo principal exigida na avaliação, isolando a lógica de negócio das visões.*
