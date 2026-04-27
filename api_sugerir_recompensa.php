<?php
header('Content-Type: application/json');

// Recebe os dados do usuário
$data = json_decode(file_get_contents('php://input'), true);
$nome = $data['nome'] ?? 'Guerreiro(a)';
$xp = (int)($data['xp'] ?? 0);

// Chave da API
$api_key = 'AIzaSyC18eD2GqvKg539xXb7z80p_WaTSq3krHM';

// URL do Gemini (Nota: Verifique se o modelo gemini-2.5 já está disponível, ou use o gemini-1.5-flash)
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key;

// Definimos a lista real de recompensas para a IA não inventar valores
$recompensas_loja = "
1. Café Grátis (100 XP)
2. Vale-Cinema (200 XP)
3. Agenda Personalizada (270 XP)
4. Almoço Especial (300 XP)
5. Kit Relaxamento (400 XP)
6. Gift Card R$50 (500 XP)
7. Assinatura Netflix (800 XP)
8. Fone Bluetooth (1000 XP)
9. Dia de Folga (2500 XP)
";

// O prompt agora é restritivo e inteligente
$prompt = "Você é o assistente do MetaXP. O usuário '$nome' tem ATUALMENTE $xp XP.
Aqui estão as recompensas disponíveis na loja:
$recompensas_loja

Siga estas REGRAS:
1. Se o usuário tiver XP suficiente para algo da lista, sugira EXATAMENTE uma dessas recompensas.
2. Se o usuário NÃO tiver XP suficiente para nada (menos de 100 XP), incentive-o a completar mais metas para chegar aos 100 XP e ganhar o 'Café Grátis'.
3. Responda com uma frase curta, motivadora e use 2 emojis. Seja direto.";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_status == 200) {
    $result_data = json_decode($response, true);
    if (isset($result_data['candidates'][0]['content']['parts'][0]['text'])) {
        $sugestao = trim($result_data['candidates'][0]['content']['parts'][0]['text']);
        echo json_encode(['success' => true, 'sugestao' => $sugestao]);
    } else {
        echo json_encode(['success' => false, 'erro' => 'Erro na resposta da IA.']);
    }
} else {
    echo json_encode(['success' => false, 'erro' => 'Erro na API.']);
}