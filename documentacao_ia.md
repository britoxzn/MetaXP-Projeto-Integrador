# 🤖 Documentação de Integração da Inteligência Artificial

O MetaXP utiliza inteligência artificial generativa para combater a "paralisia de decisão" do usuário, transformando metas abstratas em planos de ação concretos.

### 1. Chamada e Fluxo de Dados
* **Gatilho:** O usuário insere o título da meta no frontend (`nova_meta.php`) e submete o formulário.
* **Processamento:** O backend (`api/ia_handler.php`) recebe a string, higieniza os dados e monta um *prompt* estruturado.
* **Requisição:** O serviço (`api/ia_service.php`) faz uma chamada cURL (HTTP POST) para a API da IA generativa configurada.

### 2. Dependências
* PHP 8.0+ com extensão `cURL` habilitada.
* Chave de API válida (injetada via variável de ambiente/configuração) para o provedor de IA (ex: Google Gemini / OpenAI).

### 3. Contrato de Resposta (Expected Output)
O prompt exige que a IA responda **estritamente em formato JSON**, contendo um array de tarefas lógicas. Exemplo de retorno esperado:
```json
{
  "tasks": [
    {"titulo": "Pesquisar materiais básicos", "dificuldade": "Fácil"},
    {"titulo": "Criar cronograma de estudos", "dificuldade": "Média"}
  ]
}
