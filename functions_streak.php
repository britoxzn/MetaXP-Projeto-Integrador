<?php
function atualizarOfensiva($pdo, $user_id) {
    $hoje = date('Y-m-d');
    $ontem = date('Y-m-d', strtotime('-1 day'));

    // 1. Procurar os dados atuais do utilizador
    $stmt = $pdo->prepare("SELECT ultima_atividade, streak_atual FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $ultima_ativ = $user['ultima_atividade'];
    $streak = $user['streak_atual'];

    // 2. Lógica de atualização
    if ($ultima_ativ == $hoje) {
        // Já fez uma atividade hoje, não faz nada
        return;
    }

    if ($ultima_ativ == $ontem) {
        // Atividade consecutiva! Aumenta o streak
        $novo_streak = $streak + 1;
        $_SESSION['show_streak_toast'] = "Ofensiva mantida! 🔥 $novo_streak dias!";
    } else {
        // Quebrou o streak ou é a primeira vez
        $novo_streak = 1;
        $_SESSION['show_streak_toast'] = "Ofensiva iniciada! 🔥";
    }

    // 3. Salvar no banco de dados
    $upd = $pdo->prepare("UPDATE users SET ultima_atividade = ?, streak_atual = ? WHERE id = ?");
    $upd->execute([$hoje, $novo_streak, $user_id]);
}