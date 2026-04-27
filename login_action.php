<?php
session_start();
require_once 'config.php'; // Sua conexão com o banco ($pdo)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    // 1. Busca o usuário
    $stmt = $pdo->prepare("SELECT id, nome, senha, tentativas_login, ultimo_bloqueio FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $agora = new DateTime();
        
        // 2. Verifica se está bloqueado (bloqueio de 15 minutos)
        if ($user['ultimo_bloqueio']) {
            $bloqueio = new DateTime($user['ultimo_bloqueio']);
            $intervalo = $agora->diff($bloqueio);
            $minutos_passados = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

            if ($minutos_passados < 15) {
                die("Conta temporariamente bloqueada por segurança. Tente novamente em " . (15 - $minutos_passados) . " minutos.");
            }
        }

        // 3. Verifica a senha (usando password_verify para senhas com hash)
        // Se suas senhas ainda forem texto puro, use: if ($senha == $user['senha']) 
        // Mas o ideal para a apresentação é usar:
        if (password_verify($senha, $user['senha'])) {
            
            // Sucesso! Reseta as tentativas
            $update = $pdo->prepare("UPDATE users SET tentativas_login = 0, ultimo_bloqueio = NULL WHERE id = ?");
            $update->execute([$user['id']]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            
            header("Location: dashboard.php");
            exit;

        } else {
            // Senha incorreta: Incrementa tentativas
            $tentativas = $user['tentativas_login'] + 1;
            $bloqueio_sql = ($tentativas >= 5) ? ", ultimo_bloqueio = NOW()" : "";
            
            $update = $pdo->prepare("UPDATE users SET tentativas_login = ? $bloqueio_sql WHERE id = ?");
            $update->execute([$tentativas, $user['id']]);

            die("E-mail ou senha incorretos. Tentativas: $tentativas de 5.");
        }
    } else {
        die("Usuário não encontrado.");
    }
}