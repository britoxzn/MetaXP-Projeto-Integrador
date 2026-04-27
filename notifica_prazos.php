<?php
/**
 * TAREFA 3: SISTEMA DE LEMBRETES COM SEGURANÇA VIA URL
 */

// --- CAMADA DE SEGURANÇA ---
// Defina uma chave secreta. Só quem tiver essa chave na URL poderá rodar o script.
$chave_secreta = "MetaXP_Seguro_2024"; 

if (!isset($_GET['key']) || $_GET['key'] !== $chave_secreta) {
    header('Content-Type: text/plain');
    die("Acesso negado: Chave de seguranca invalida ou ausente.");
}

// --- CONFIGURAÇÕES DO BANCO ---
define('DB_HOST', 'sql212.infinityfree.com');
define('DB_NAME', 'if0_38657243_plataforma_de_planejamento_metas_pessoais');
define('DB_USER', 'if0_38657243');
define('DB_PASS', 'metaspessoais');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $hoje = date('Y-m-d');
    $amanha = date('Y-m-d', strtotime('+1 day'));

    $query = "SELECT m.titulo, m.prazo, u.username, u.email 
              FROM metas m 
              INNER JOIN users u ON m.user_id = u.id 
              WHERE (m.prazo = :hoje OR m.prazo = :amanha) 
              AND m.status != 'concluida'";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['hoje' => $hoje, 'amanha' => $amanha]);
    $lembretes = $stmt->fetchAll();

    if (count($lembretes) > 0) {
        foreach ($lembretes as $lembrete) {
            enviarEmailLembrete($lembrete['email'], $lembrete['username'], $lembrete['titulo'], $lembrete['prazo']);
        }
        echo "Sucesso: " . count($lembretes) . " lembretes processados.";
    } else {
        echo "Nenhuma meta para notificar hoje.";
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

function enviarEmailLembrete($para, $nome, $metaTitulo, $prazo) {
    $dataFormatada = date('d/m/Y', strtotime($prazo));
    $assunto = "=?UTF-8?B?".base64_encode("⚠️ Lembrete: $metaTitulo")."?=";
    
    $mensagem = "
    <html>
    <body style='font-family: sans-serif; color: #333;'>
        <div style='padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #1D84B5;'>Olá, $nome! 🚀</h2>
            <p>Sua meta <strong>\"$metaTitulo\"</strong> vence em: <span style='color: #FF6584;'>$dataFormatada</span>.</p>
            <p>Não deixe sua ofensiva (streak) resetar! Conclua agora.</p>
            <br>
            <a href='http://plataforma-de-planejamento.great-site.net/dashboard.php' 
               style='background:#1D84B5; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>
               Ver Dashboard
            </a>
        </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: MetaXP <contato@plataforma-de-planejamento.great-site.net>\r\n";

    return mail($para, $assunto, $mensagem, $headers);
}