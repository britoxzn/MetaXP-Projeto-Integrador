<?php
// Blinda a sessão contra roubos de cookie via JavaScript
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// Inicia a sessão apenas se ela já não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se a variável de sessão 'user_id' existe. Se não existir, o usuário não está logado.
if (!isset($_SESSION['user_id'])) {
    // Redireciona o invasor de volta para a página de login
    header("Location: login.php");
    exit(); // Encerra a execução do script imediatamente
}
?>