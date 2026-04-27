<?php
// ia_handler.php

function pedirSugestaoIA($metaTitulo) {
    // Exemplo de prompt estruturado para retorno JSON
    $prompt = "A meta é: '$metaTitulo'. Como um mentor de RPG, divida isso em 3 subtarefas realistas. 
               Retorne APENAS um JSON no formato: 
               {\"dificuldade\": 3, \"subtarefas\": [{\"desc\": \"passo 1\", \"dias\": 2}, {\"desc\": \"passo 2\", \"dias\": 5}]}";

    // Aqui iria o seu cURL para a API (OpenAI/Gemini)
    // Para teste imediato, vamos simular uma resposta da IA:
    $resuldoSimulado = '{
        "dificuldade": 3,
        "subtarefas": [
            {"desc": "Pesquisar fundamentos básicos", "dias": 2},
            {"desc": "Praticar 30 minutos por dia", "dias": 7},
            {"desc": "Revisar progresso semanal", "dias": 1}
        ]
    }';

    return json_decode($resuldoSimulado, true);
}