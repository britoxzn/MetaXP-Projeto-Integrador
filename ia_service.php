<?php
// ia_service.php

function gerarPlanoDeMeta($metaUsuario) {
    $apiKey = "SUA_CHAVE_API_AQUI";
    $url = "https://api.openai.com/v1/chat/completions"; // Exemplo com OpenAI

    $prompt = "Você é o assistente do MetaXP. O usuário quer: '$metaUsuario'. 
               Retorne um JSON com: 
               'subtarefas' (lista de 3 a 5 itens), 
               'prazos_dias' (sugestão de dias para cada) 
               e 'dificuldade' (1 a 5).";

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "Você responde apenas em JSON estruturado."],
            ["role" => "user", "content" => $prompt]
        ],
        "response_format" => ["type" => "json_object"]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}