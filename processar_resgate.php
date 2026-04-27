<?php
session_start();

// Define que a resposta será em formato JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

// Configurações do DB
define('DB_HOST', 'sql212.infinityfree.com');
define('DB_NAME', 'if0_38657243_plataforma_de_planejamento_metas_pessoais');
define('DB_USER', 'if0_38657243');
define('DB_PASS', 'metaspessoais');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

// Recebe os dados enviados pelo JavaScript (fetch)
$dados = json_decode(file_get_contents('php://input'), true);

$custo = isset($dados['custo']) ? (int)$dados['custo'] : 0;
$titulo = isset($dados['titulo']) ? trim($dados['titulo']) : '';
$user_id = $_SESSION['user_id'];

if ($custo <= 0 || empty($titulo)) {
    echo json_encode(['success' => false, 'message' => 'Dados de resgate inválidos.']);
    exit();
}

try {
    // 1. Inicia uma transação para garantir que tudo seja feito com segurança
    $pdo->beginTransaction();

    // 2. Busca o XP atual do usuário
    $stmt = $pdo->prepare("SELECT xp_total FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
        exit();
    }

    $xp_atual = (int)$user['xp_total'];

    // 3. Verifica se tem saldo suficiente
    if ($xp_atual < $custo) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'XP insuficiente para este resgate.']);
        exit();
    }

    // 4. Desconta o XP
    $novo_xp = $xp_atual - $custo;
    $stmt_update = $pdo->prepare("UPDATE users SET xp_total = ? WHERE id = ?");
    $stmt_update->execute([$novo_xp, $user_id]);

    // 5. REGISTRA O HISTÓRICO DE RESGATE NO BANCO DE DADOS
    $stmt_historico = $pdo->prepare("INSERT INTO historico_recompensas (user_id, titulo_recompensa, custo_xp, data_resgate) VALUES (?, ?, ?, NOW())");
    $stmt_historico->execute([$user_id, $titulo, $custo]);

    // 6. Confirma as alterações no banco de dados
    $pdo->commit();

    // 7. Atualiza a sessão
    $_SESSION['xp'] = $novo_xp;

    // Retorna sucesso para o JavaScript
    echo json_encode([
        'success' => true,
        'novo_xp' => $novo_xp,
        'message' => 'Resgate realizado com sucesso!'
    ]);

} catch (Exception $e) {
    // Se der algum erro, desfaz tudo
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro interno ao processar resgate.']);
}