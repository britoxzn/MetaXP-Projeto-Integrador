<?php
// --- CONFIGURAÇÃO DE ERROS ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Configurações do Banco de Dados
define('DB_HOST', 'sql212.infinityfree.com');
define('DB_NAME', 'if0_38657243_plataforma_de_planejamento_metas_pessoais');
define('DB_USER', 'if0_38657243');
define('DB_PASS', 'metaspessoais');
define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME', 'MetaXP');
define('SITE_URL', 'https://plataforma.metaxp.com'); // Ajuste para sua URL real se necessário

$message = '';
$message_type = ''; // 'success' ou 'danger'
$debug_link = '';

// Conexão
$pdo = null;
try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    $message = "Erro de conexão com o banco de dados.";
    $message_type = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Por favor, insira um e-mail válido.";
        $message_type = 'danger';
    } else {
        try {
            // 1. Verificar se o e-mail existe (usando tabela 'users' corrigida)
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // 2. Gerar Token
                $token = bin2hex(random_bytes(50));
                $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hora

                // 3. Tentar inserir na tabela de tokens
                // Verifica se a tabela existe primeiro para evitar o erro fatal
                $sql = "INSERT INTO tokens_recuperacao (user_id, token, expira_em) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user['id'], $token, $expires]);

                // 4. Enviar E-mail
                $resetLink = SITE_URL . "/reset_password.php?token=" . $token;
                $subject = "Recuperação de Senha - " . SITE_NAME;
                $body = "Olá " . $user['username'] . ",\n\nClique no link abaixo para redefinir sua senha:\n" . $resetLink . "\n\nSe não solicitou isso, ignore este e-mail.";
                $headers = "From: no-reply@metaxp.com";

                // Tenta enviar o e-mail
                if (@mail($email, $subject, $body, $headers)) {
                    $message = "Um link de recuperação foi enviado para o seu e-mail.";
                    $message_type = 'success';
                } else {
                    // Fallback para servidores gratuitos que bloqueiam e-mail
                    $message = "Não foi possível enviar o e-mail (limitação do servidor).";
                    $message_type = 'warning';
                    $debug_link = $resetLink; // Mostra o link na tela para você testar
                }
            } else {
                // Mensagem genérica por segurança
                $message = "Se este e-mail estiver cadastrado, você receberá um link.";
                $message_type = 'success';
            }

        } catch (PDOException $e) {
            // Se o erro for "Table doesn't exist", avisamos o usuário
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $message = "Erro Crítico: A tabela 'tokens_recuperacao' não existe no banco. Execute o script de setup.";
            } else {
                $message = "Ocorreu um erro no servidor: " . $e->getMessage();
            }
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | MetaXP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1D84B5;
            --secondary: #4BC6B1;
            --accent: #FF6584;
            --light: #F8F9FA;
            --dark: #212529;
            --danger: #DC3545;
            --success: #1cc88a;
            --warning: #ffc107;
            --grey: #6c757d;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Animação de Fundo */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite linear;
            z-index: -1;
        }
        body::before { width: 30vw; height: 30vw; bottom: -15vw; left: -15vw; }
        body::after { width: 40vw; height: 40vw; top: -20vw; right: -20vw; animation-duration: 25s; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-120vh) rotate(360deg); }
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo { 
            font-size: 2.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--primary); 
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .logo span { color: var(--secondary); }
        .logo i { font-size: 1.8rem; }
        
        .subtitle { font-size: 0.95rem; color: var(--grey); margin-bottom: 2rem; font-weight: 400; line-height: 1.5; }

        .form-group { position: relative; margin-bottom: 1.5rem; text-align: left; }

        .form-input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem; /* Espaço para ícone */
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            background: white;
            transition: all 0.3s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(29, 132, 181, 0.15);
        }

        .form-label {
            position: absolute;
            top: 50%; left: 3rem;
            transform: translateY(-50%);
            color: var(--grey);
            pointer-events: none;
            transition: all 0.2s;
            font-size: 0.95rem;
            background-color: transparent;
            padding: 0 4px;
        }
        
        .form-input:focus ~ .form-label,
        .form-input:not(:placeholder-shown) ~ .form-label {
            top: -10px; left: 10px; font-size: 0.75rem; background: white; color: var(--primary);
        }

        .icon-left {
            position: absolute; top: 50%; left: 1rem;
            transform: translateY(-50%); color: var(--grey);
            transition: color 0.3s; font-size: 1.1rem;
        }
        .form-input:focus ~ .icon-left { color: var(--primary); }

        .submit-btn {
            width: 100%; padding: 0.9rem;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            background-size: 200%; color: white;
            border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: all 0.4s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 15px rgba(29, 132, 181, 0.3);
            margin-top: 1rem;
        }
        .submit-btn:hover {
            background-position: right;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(29, 132, 181, 0.4);
        }

        .login-link { margin-top: 1.5rem; font-size: 0.9rem; color: var(--grey); }
        .login-link a { color: var(--primary); text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .login-link a:hover { color: #156a92; text-decoration: underline; }

        .alert {
            padding: 0.8rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: left; font-size: 0.9rem;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-danger { background-color: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); color: var(--danger); }
        .alert-success { background-color: rgba(28, 200, 138, 0.1); border: 1px solid rgba(28, 200, 138, 0.3); color: #0f6848; }
        .alert-warning { background-color: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); color: #856404; }

        .debug-box {
            margin-top: 15px; padding: 10px; background: #f8f9fa; border: 1px dashed var(--grey); border-radius: 8px; font-size: 0.8rem; color: var(--dark); text-align: left; word-break: break-all;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="logo"><i class="bi bi-shield-lock-fill"></i> Meta<span>XP</span></div>
        <p class="subtitle">Insira o seu e-mail abaixo e enviaremos instruções para recuperar o acesso.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?php if($message_type == 'danger'): ?><i class="bi bi-exclamation-triangle-fill"></i><?php endif; ?>
                <?php if($message_type == 'success'): ?><i class="bi bi-check-circle-fill"></i><?php endif; ?>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($debug_link)): ?>
            <div class="debug-box">
                <strong>(Modo Teste) Link gerado:</strong><br>
                <a href="<?= htmlspecialchars($debug_link) ?>"><?= htmlspecialchars($debug_link) ?></a>
            </div>
        <?php endif; ?>

        <form action="forgot-password.php" method="POST">
            <div class="form-group">
                <input type="email" id="email" name="email" class="form-input" placeholder=" " required>
                <label for="email" class="form-label">Seu E-mail</label>
                <i class="bi bi-envelope icon-left"></i>
            </div>

            <button type="submit" class="submit-btn">
                Enviar Link <i class="bi bi-send-fill"></i>
            </button>
        </form>

        <div class="login-link">
            Lembrou da senha? <a href="login.php">Voltar para Login</a>
        </div>
    </div>

</body>
</html>