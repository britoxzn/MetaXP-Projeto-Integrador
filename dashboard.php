<?php

// Chama o arquivo que verifica se o usuário está logado
require_once 'auth_check.php';

/*
--- GUIA DE DEPURAÇÃO COMPLETO ---
*/

// Ativar/desativar o modo de depuração
define('DEBUG_MODE', false);
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

session_start();

// Se o utilizador não estiver logado, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Configurações do DB
define('DB_HOST', 'sql212.infinityfree.com');
define('DB_NAME', 'if0_38657243_plataforma_de_planejamento_metas_pessoais');
define('DB_USER', 'if0_38657243');
define('DB_PASS', 'metaspessoais');
define('DB_CHARSET', 'utf8mb4');

// Conexão com o banco de dados
$pdo = null;
$db_error = null;

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    $db_error = "Erro de conexão com o banco de dados: " . $e->getMessage();
}

$user = null;
$metas = [];
$ranking_users = [];
$metasConcluidas = 0; 

if ($pdo) {
    try {
        // Busca os dados mais recentes do utilizador
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT username, email, foto_perfil, nivel, xp_total FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['foto_perfil'] = $user['foto_perfil'];
            $_SESSION['xp'] = $user['xp_total']; 
        }

        // Buscar metas pendentes
        $stmt_metas = $pdo->prepare("SELECT id, titulo, status, categoria, prazo FROM metas WHERE user_id = ? AND status != 'concluida' ORDER BY prazo ASC LIMIT 3");
        $stmt_metas->execute([$user_id]);
        $metas = $stmt_metas->fetchAll();
        
        // Buscar quantidade total de metas CONCLUÍDAS
        $stmt_concluidas = $pdo->prepare("SELECT COUNT(*) FROM metas WHERE user_id = ? AND status = 'concluida'");
        $stmt_concluidas->execute([$user_id]);
        $metasConcluidas = $stmt_concluidas->fetchColumn() ?: 0;
        
        // Buscar o ranking
        $stmt_ranking = $pdo->prepare("SELECT username, email, xp_total, foto_perfil FROM users ORDER BY xp_total DESC LIMIT 5");
        $stmt_ranking->execute();
        $ranking_users = $stmt_ranking->fetchAll();

        // --- BUSCA DADOS REAIS PARA O GRÁFICO (ÚLTIMOS 7 DIAS) ---
        $dados_grafico = [];
        $labels_dias = [];
        $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        for ($i = 6; $i >= 0; $i--) {
            $data_check = date('Y-m-d', strtotime("-$i days"));
            $labels_dias[] = $dias_semana[date('w', strtotime($data_check))];
            
            $stmt_xp = $pdo->prepare("SELECT SUM(xp_ganho) as total FROM xp_log WHERE user_id = ? AND data_ganho = ?");
            $stmt_xp->execute([$user_id, $data_check]);
            $res = $stmt_xp->fetch();
            $dados_grafico[] = (int)($res['total'] ?? 0);
        }
        $dados_grafico_json = json_encode($dados_grafico);
        $labels_json = json_encode($labels_dias);
        // --------------------------------------------------------------

    } catch (PDOException $e) {
        $db_error = "Erro na consulta ao banco de dados: " . $e->getMessage();
    }
}

// --- LÓGICA DO AVATAR (INICIAL DO NOME) ---
$nomeUsuario = htmlspecialchars($_SESSION['username'] ?? 'Usuário');
$emailUsuario = htmlspecialchars($_SESSION['email'] ?? 'email@exemplo.com');

$primeiroNome = explode(' ', trim($nomeUsuario))[0];
$letraInicial = strtoupper(substr($primeiroNome, 0, 1)); 

$fotoAtual = $_SESSION['foto_perfil'] ?? null;
$temFoto = ($fotoAtual && file_exists($fotoAtual));

$coresAvatar = ['#4fd1c5', '#f6ad55', '#fc8181', '#68d391', '#f6e05e', '#63b3ed', '#b794f4'];
$corFundoAvatar = $coresAvatar[strlen($primeiroNome) % count($coresAvatar)];

// --- LÓGICA DE NOTIFICAÇÕES (SUCESSO E PRAZO) ---
$metaConcluidaRecente = (isset($_GET['sucesso']) && $_GET['sucesso'] == '1');
$metasExpirando = [];
$hoje = date('Y-m-d');

if (!empty($metas)) {
    foreach ($metas as $meta) {
        if ($meta['prazo'] <= $hoje) {
            $metasExpirando[] = $meta['titulo'];
        }
    }
}

// --- LÓGICA DE NÍVEIS ---
$xpTotal = $_SESSION['xp'] ?? 0;
$nivelAtual = floor($xpTotal / 1000) + 1;
$xpNoNivelAtual = $xpTotal % 1000; 
$xpNecessarioParaProximo = 1000;
$progressoXP = ($xpNoNivelAtual / $xpNecessarioParaProximo) * 100;

// Integração da IA
$primeiraMetaTitulo = !empty($metas) ? $metas[0]['titulo'] : null;
function getDicaIA($meta) {
    if (!$meta) return "🤖 Crie sua primeira meta para eu te ajudar!";
    $dicas = [
        "Para a meta '{$meta}', tente a regra dos 2 minutos!",
        "Divida '{$meta}' em tarefas menores.",
        "Dê um pequeno passo hoje em direção a '{$meta}'!"
    ];
    return "🤖 " . $dicas[array_rand($dicas)];
}
$dicaIA = getDicaIA($primeiraMetaTitulo);

// --- DETECÇÃO DE LEVEL UP ---
$subiuDeNivel = false;
if (isset($_SESSION['ultimo_nivel_visto']) && $nivelAtual > $_SESSION['ultimo_nivel_visto']) {
    $subiuDeNivel = true;
}
$_SESSION['ultimo_nivel_visto'] = $nivelAtual;
// ----------------------------

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MetaXP</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    
    <style>
        :root {
            --primary: #1D84B5;
            --secondary: #4BC6B1;
            --accent: #FF6584;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --grey: #8a95a5;
            --danger: #dc3545;
            --bg-color: #f4f7fc;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #34495e;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 80px;
            --gold: #ffd700;
            --silver: #c0c0c0;
            --bronze: #cd7f32;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0 0 0 var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        body.sidebar-collapsed { margin-left: var(--sidebar-width-collapsed); }

        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width); background-color: var(--sidebar-bg);
            box-shadow: var(--shadow); padding: 1.5rem;
            display: flex; flex-direction: column; transition: width 0.3s ease; z-index: 100;
        }
        
        body.sidebar-collapsed .sidebar { width: var(--sidebar-width-collapsed); align-items: center; }

        .logo { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin-bottom: 2.5rem; text-align: center; white-space: nowrap; }
        .logo span { color: var(--secondary); }
        body.sidebar-collapsed .logo .text { display: none; }
        
        .sidebar-nav { flex-grow: 1; list-style: none; padding: 0; margin: 0; }
        .nav-item a {
            display: flex; align-items: center; padding: 0.9rem 1rem; color: var(--grey);
            text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.3s ease; white-space: nowrap;
        }
        .nav-item a:hover, .nav-item a.active { background-color: #eef5ff; color: var(--primary); font-weight: 500; }
        .nav-icon { font-size: 1.2rem; min-width: 24px; margin-right: 1.5rem; transition: margin-right 0.3s ease; }
        body.sidebar-collapsed .nav-icon { margin-right: 0; }
        .nav-text { opacity: 1; transition: opacity 0.2s ease; }
        body.sidebar-collapsed .nav-text { opacity: 0; width: 0; overflow: hidden; }

        .main-wrapper { display: flex; flex-direction: column; min-height: 100vh; }

        .top-navbar {
            background-color: var(--card-bg); padding: 1rem 2rem; display: flex;
            justify-content: space-between; align-items: center; box-shadow: var(--shadow);
            position: sticky; top: 0; z-index: 99;
        }
        
        #toggle-sidebar { background: transparent; border: none; cursor: pointer; font-size: 1.5rem; color: var(--grey); }
        .navbar-right { display: flex; align-items: center; gap: 1.5rem; }
        
        .user-menu { position: relative; display: flex; align-items: center;}
        
        /* Estilos ajustados para a imagem de perfil e avatar inicial no menu */
        .user-menu > img, .avatar-inicial { 
            width: 40px; height: 40px; border-radius: 50%; cursor: pointer; object-fit: cover; 
            border: 2px solid transparent; transition: 0.3s; 
        }
        .user-menu:hover > img, .user-menu:hover .avatar-inicial { border-color: var(--primary); }
        
        /* Estilo específico do Avatar Inicial */
        .avatar-inicial {
            display: flex; justify-content: center; align-items: center;
            color: white; font-weight: 600; font-size: 1.2rem;
            box-shadow: var(--shadow-sm); text-transform: uppercase;
        }

        .dropdown-menu {
            position: absolute; top: calc(100% + 10px); right: 0; width: 240px;
            background-color: var(--card-bg); border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            padding: 0.5rem 0; opacity: 0; visibility: hidden; transform: translateY(10px); transition: 0.3s ease; z-index: 1000;
        }
        .user-menu:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; margin-bottom: 0.5rem; }
        .dropdown-header strong { font-weight: 600; color: var(--dark); }
        .dropdown-header small { color: var(--grey); font-size: 0.8rem; word-break: break-all; }
        .dropdown-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: var(--text-color); text-decoration: none; font-size: 0.9rem; }
        .dropdown-item:hover { background-color: #eef5ff; color: var(--primary); }

        .main-content { padding: 2rem; flex-grow: 1; }
        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 1.8rem; font-weight: 600; margin: 0; }
        .header p { color: var(--grey); }

        .btn-primary { 
            background: linear-gradient(90deg, var(--secondary), var(--primary)); background-size: 200%; color: white; border: none;
            padding: 0.6rem 1.2rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 500; transition: 0.4s;
        }
        .btn-primary:hover { background-position: right; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(29, 132, 181, 0.3); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background-color: var(--card-bg); border-radius: 16px; padding: 1.5rem; box-shadow: var(--shadow); display: flex; align-items: center; gap: 1.5rem; }
        .stat-card .icon { font-size: 2rem; padding: 0.8rem; border-radius: 50%; display: grid; place-items: center; }
        .stat-card .icon.level { background-color: #ffc10720; color: #ffc107; }
        .stat-card .icon.xp { background-color: #1cc88a20; color: #1cc88a; }
        .stat-card .icon.goals { background-color: #4e73df20; color: #4e73df; }
        .stat-card .value { font-size: 1.5rem; font-weight: 600; }
        .stat-card .label { font-size: 0.9rem; color: var(--grey); }

        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
        .main-column { display: flex; flex-direction: column; gap: 1.5rem; }
        .side-column { display: flex; flex-direction: column; gap: 1.5rem; }
        .card { background-color: var(--card-bg); border-radius: 16px; padding: 1.5rem; box-shadow: var(--shadow); }
        .card-header { font-size: 1.1rem; font-weight: 600; padding: 0; margin-bottom: 1.5rem; display: flex; align-items: center; color: var(--dark); }
        .card-header .icon { margin-right: 0.75rem; color: var(--primary); }
        
        .xp-progress { background-color: #e9ecef; border-radius: 30px; height: 8px; overflow: hidden; }
        .xp-progress-bar { height: 100%; width: 0; background: linear-gradient(90deg, var(--secondary), var(--primary)); border-radius: 30px; transition: width 1s ease-in-out; }
        
        .meta-progress-item { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #f0f0f0; }
        #lista-metas .meta-progress-item:last-child { border-bottom: none; padding-bottom: 0; }
        #lista-metas .meta-progress-item:first-child { padding-top: 0; }
         
        .meta-info strong { display: block; font-weight: 500; }
        .meta-category-badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 20px; color: white; display: inline-block; margin-bottom: 0.25rem; }
        .category-pessoal { background-color: #6f42c1; }
        .category-profissional { background-color: #0d6efd; }
        .category-financeiro { background-color: #198754; }
        .category-educação { background-color: #fd7e14; }
        .category-saúde { background-color: #dc3545; }

        .ranking-list .rank-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; }
        .ranking-list .rank { font-weight: 600; color: var(--grey); width: 20px; text-align: center; }
        .ranking-list .rank-1 { color: var(--gold); }
        .ranking-list .rank-2 { color: var(--silver); }
        .ranking-list .rank-3 { color: var(--bronze); }
        .ranking-list img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .ranking-list .xp { margin-left: auto; font-weight: 500; }

        .view-all-link { display: block; text-align: center; margin-top: 1rem; font-weight: 500; color: var(--primary); text-decoration: none; }
        .alert-danger { background-color: rgba(220, 53, 69, 0.1); border-left: 5px solid var(--danger); padding: 1.5rem; border-radius: 8px; color: var(--dark); }

        .gamification-btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem; }
        .gamification-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem; border-radius: 12px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .gamification-btn i { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .btn-conquistas { background-color: rgba(29, 132, 181, 0.1); color: var(--primary); }
        .btn-conquistas:hover { background-color: var(--primary); color: white; transform: translateY(-3px); }
        .btn-recompensas { background-color: rgba(75, 198, 177, 0.1); color: var(--secondary); }
        .btn-recompensas:hover { background-color: var(--secondary); color: white; transform: translateY(-3px); }

        @media (max-width: 1200px) { .main-column, .side-column { grid-column: span 12; } }
        @media (max-width: 768px) { body { margin-left: var(--sidebar-width-collapsed); } .sidebar { width: var(--sidebar-width-collapsed); align-items: center; } .logo .text, .nav-text { display: none; } .nav-icon { margin-right: 0; } }

        /* --- ESTILOS PARA O MODAL DE LEVEL UP --- */
        #modal-levelup {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.8); z-index: 10000;
            display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.5s;
        }
        .modal-content-levelup {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 3rem; border-radius: 20px; text-align: center; color: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            transform: scale(0.7); transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        #modal-levelup.show { opacity: 1; display: flex; }
        #modal-levelup.show .modal-content-levelup { transform: scale(1); }
        .lvl-badge-icon { font-size: 5rem; margin-bottom: 10px; color: #FFD700; text-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .lvl-number { font-size: 4rem; font-weight: 800; line-height: 1; margin-bottom: 10px; }
        .btn-close-lvl {
            margin-top: 20px; background: white; color: var(--primary);
            border: none; padding: 10px 30px; border-radius: 30px;
            font-weight: 600; font-size: 1rem; cursor: pointer; transition: 0.3s;
        }
        .btn-close-lvl:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        /* --- ESTILOS DAS NOTIFICAÇÕES (TOASTS) --- */
        .toast-container {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
            display: flex; flex-direction: column; gap: 15px;
        }
        .toast {
            background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            padding: 15px 20px; display: flex; align-items: center; gap: 15px; width: 320px;
            transform: translateX(150%); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-left: 6px solid #4fd1c5;
        }
        .toast.show { transform: translateX(0); }
        .toast.warning { border-left-color: #ed8936; }
        .toast-icon { font-size: 1.8rem; }
        .toast-content h4 { margin: 0; font-size: 1rem; color: #2d3748; font-weight: 600; }
        .toast-content p { margin: 0.2rem 0 0 0; font-size: 0.85rem; color: #718096; line-height: 1.3; }
        .close-toast { background: none; border: none; font-size: 1.5rem; color: #a0aec0; cursor: pointer; margin-left: auto; padding: 0; }
        .close-toast:hover { color: #4a5568; }

        /* --- TEMA ESCURO (DARK MODE) --- */
        [data-theme="dark"] {
            --bg-color: #121418;
            --sidebar-bg: #1e2126;
            --card-bg: #1e2126;
            --text-color: #e4e6eb;
            --dark: #ffffff;
            --grey: #a0aab5;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            --border-color: #2d3139;
            --hover-bg: #2d3139;
        }

        /* Ajustes específicos para elementos que tinham cores fixas no código */
        [data-theme="dark"] .dropdown-header { border-bottom: 1px solid var(--border-color); }
        [data-theme="dark"] .dropdown-item:hover, 
        [data-theme="dark"] .nav-item a:hover, 
        [data-theme="dark"] .nav-item a.active { background-color: var(--hover-bg); color: var(--secondary); }
        [data-theme="dark"] .meta-progress-item { border-bottom: 1px solid var(--border-color); }
        [data-theme="dark"] #box-treinador-ia { background: linear-gradient(135deg, #1e2126, #181f29) !important; border-left-color: var(--secondary) !important; }
        [data-theme="dark"] .toast { background: var(--card-bg); color: var(--text-color); }
        [data-theme="dark"] .toast-content h4 { color: var(--text-color); }

        /* Estilo para o botão de alternar tema */
        .btn-theme-toggle {
            background: transparent; border: none; font-size: 1.3rem; 
            color: var(--grey); cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; justify-content: center;
            padding: 0.5rem; border-radius: 50%;
        }
        .btn-theme-toggle:hover { color: var(--primary); background-color: rgba(0,0,0,0.05); }
        [data-theme="dark"] .btn-theme-toggle:hover { background-color: rgba(255,255,255,0.1); }

    </style>
</head>
<body class="sidebar-collapsed">

    <div id="modal-levelup">
        <div class="modal-content-levelup">
            <i class="bi bi-star-fill lvl-badge-icon"></i>
            <h2 style="font-weight: 700; font-family: 'Poppins', sans-serif;">NÍVEL ALCANÇADO!</h2>
            <div class="lvl-number"><?= $nivelAtual ?></div>
            <p>Parabéns, <?= $nomeUsuario ?>! Continue focando nas suas metas.</p>
            <button class="btn-close-lvl" onclick="fecharLevelUp()">Incrível!</button>
        </div>
    </div>

    <aside class="sidebar">
        <div class="logo">M<span><span class="text">XP</span></span></div>
        <ul class="sidebar-nav">
            <li class="nav-item"><a href="dashboard.php" class="active" title="Dashboard"><span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span><span class="nav-text">Dashboard</span></a></li>
            <li class="nav-item"><a href="ver_mais_metas.php" title="Minhas Metas"><span class="nav-icon"><i class="bi bi-check2-circle"></i></span><span class="nav-text">Minhas Metas</span></a></li>
            <li class="nav-item"><a href="conquistas.php" title="Conquistas"><span class="nav-icon"><i class="bi bi-trophy-fill"></i></span><span class="nav-text">Conquistas</span></a></li>
            <li class="nav-item"><a href="recompensas.php" title="Recompensas"><span class="nav-icon"><i class="bi bi-gift-fill"></i></span><span class="nav-text">Recompensas</span></a></li>
            <li class="nav-item"><a href="ranking.php" title="Ranking"><span class="nav-icon"><i class="bi bi-bar-chart-line-fill"></i></span><span class="nav-text">Ranking</span></a></li>
            <li class="nav-item"><a href="logout.php" title="Logout"><span class="nav-icon"><i class="bi bi-box-arrow-left"></i></span><span class="nav-text">Logout</span></a></li>
        </ul>
    </aside>

    <div class="main-wrapper">
        <nav class="top-navbar">
            <button id="toggle-sidebar"><i class="bi bi-list"></i></button>
            <div class="navbar-right">
                
                <button id="theme-toggle" class="btn-theme-toggle" title="Alternar Tema">
                    <i class="bi bi-moon-fill"></i>
                </button>
                
                <a href="nova_meta.php" class="btn-primary"><i class="bi bi-plus-lg"></i> Nova Meta</a>
                <div class="user-menu">
                    <?php if ($temFoto): ?>
                        <img src="<?= htmlspecialchars($fotoAtual) ?>" alt="Foto do Perfil">
                    <?php else: ?>
                        <div class="avatar-inicial" style="background-color: <?= $corFundoAvatar ?>;">
                            <?= $letraInicial ?>
                        </div>
                    <?php endif; ?>
                    <div class="dropdown-menu">
                        <div class="dropdown-header"><strong><?= $nomeUsuario ?></strong><br><small><?= $emailUsuario ?></small></div>
                        <a href="editar_perfil.php" class="dropdown-item"><i class="bi bi-person-fill"></i> Editar Perfil</a>
                        <a href="alterar_senha.php" class="dropdown-item"><i class="bi bi-key-fill"></i> Alterar Senha</a>
                        <a href="logout.php" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <main class="main-content">
            <?php if ($db_error): ?>
                <div class="alert-danger">
                    <h4>Ocorreu um erro na Base de Dados</h4><p><?= $db_error ?></p>
                </div>
            <?php else: ?>
                <header class="header">
                    <h1>Olá, <?= $nomeUsuario ?>!</h1>
                    <p>Pronto para conquistar os seus objetivos hoje?</p>
                </header>

                <div id="box-treinador-ia" style="background: linear-gradient(135deg, #f0f7ff, #e6f0ff); padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border-left: 5px solid var(--primary); display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow);">
                    <div style="font-size: 35px;">🤖</div>
                    <div>
                        <h4 style="margin: 0; color: var(--dark); font-size: 16px;">Meta.IA</h4>
                        <p id="texto-treinador-ia" style="margin: 5px 0 0 0; color: var(--grey); font-size: 14px;">
                            <em>A IA está pensando na melhor estratégia para o seu dia...</em> ⏳
                        </p>
                    </div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon level"><i class="bi bi-star-fill"></i></div>
                        <div><div class="value">Nível <?= $nivelAtual ?></div><div class="label">Seu Nível Atual</div></div>
                    </div>
                    <div class="stat-card">
                        <div class="icon xp"><i class="bi bi-lightning-fill"></i></div>
                        <div><div class="value"><?= number_format($xpTotal) ?></div><div class="label">Pontos de Experiência</div></div>
                    </div>
                    <div class="stat-card">
                         <div class="icon goals"><i class="bi bi-check-circle-fill"></i></div>
                         <div><div class="value"><?= $metasConcluidas ?></div><div class="label">Metas Concluídas</div></div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="main-column">
                        <div class="card">
                            <div class="card-header"><span class="icon"><i class="bi bi-list-check"></i></span> Progresso Atual</div>
                            <div id="lista-metas">
                                <?php if (empty($metas)): ?>
                                    <p class="text-muted">Nenhuma meta cadastrada ainda. Que tal criar uma?</p>
                                <?php else: ?>
                                    <?php foreach ($metas as $meta): ?>
                                        <div class="meta-progress-item">
                                            <div class="meta-info">
                                                <span class="meta-category-badge category-<?= strtolower(htmlspecialchars($meta['categoria'])) ?>">
                                                    <?= htmlspecialchars($meta['categoria']) ?>
                                                </span>
                                                <strong><?= htmlspecialchars($meta['titulo']) ?></strong>
                                            </div>
                                            <div class="meta-due-date" style="display: flex; align-items: center; gap: 15px;">
                                                <div>
                                                    <i class="bi bi-calendar-event"></i>
                                                    <span style="<?= $meta['prazo'] <= $hoje ? 'color: red; font-weight: bold;' : '' ?>">
                                                        <?= date('d/m/Y', strtotime($meta['prazo'])) ?>
                                                    </span>
                                                </div>
                                                <a href="concluir_meta.php?id=<?= $meta['id'] ?>" title="Concluir Meta" style="color: var(--secondary); text-decoration: none;">
                                                    <i class="bi bi-check-circle-fill" style="font-size: 1.8rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                         <div class="card">
                             <div class="card-header"><span class="icon"><i class="bi bi-graph-up"></i></span> Progresso Semanal</div>
                            <div style="height: 250px;">
                                <canvas id="graficoProgresso"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="side-column">
                        <div class="card">
                            <div class="card-header"><span class="icon"><i class="bi bi-joystick"></i></span> Gamificação</div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.85rem;">
                                    <strong>Para Nível <?= $nivelAtual + 1 ?></strong>
                                    <span class="text-muted"><?= $xpNoNivelAtual ?> / <?= $xpNecessarioParaProximo ?> XP</span>
                                </div>
                                <div class="xp-progress">
                                    <div class="xp-progress-bar" style="width: <?= $progressoXP ?>%;"></div>
                                </div>
                                <div class="gamification-btn-group">
                                    <a href="conquistas.php" class="gamification-btn btn-conquistas">
                                        <i class="bi bi-trophy-fill"></i><span>Conquistas</span>
                                    </a>
                                    <a href="recompensas.php" class="gamification-btn btn-recompensas">
                                        <i class="bi bi-gift-fill"></i><span>Recompensas</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header"><span class="icon"><i class="bi bi-bar-chart-line-fill"></i></span> Top 5 Utilizadores</div>
                            <div class="ranking-list">
                                <?php foreach ($ranking_users as $index => $ranked_user): ?>
                                    <?php 
                                        $rank = $index + 1;
                                        // Usa o mesmo fallback da inicial se quiser depois, mas por hora mantive a lógica antiga no ranking
                                        $ranked_user_avatar = ($ranked_user['foto_perfil'] && file_exists($ranked_user['foto_perfil'])) ? htmlspecialchars($ranked_user['foto_perfil']) : "https://i.pravatar.cc/150?u=" . urlencode($ranked_user['email']);
                                    ?>
                                    <div class="rank-item">
                                        <span class="rank rank-<?= $rank <= 3 ? $rank : '' ?>"><?= $rank ?></span>
                                        <img src="<?= $ranked_user_avatar ?>" alt="Avatar">
                                        <span><?= htmlspecialchars($ranked_user['username']) ?></span>
                                        <span class="xp"><?= number_format($ranked_user['xp_total']) ?> XP</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="ranking.php" class="view-all-link">Ver Ranking Completo</a>
                        </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Lógica da Sidebar
        document.getElementById('toggle-sidebar').addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
        });

        // O Gráfico agora recebe os dados REAIS dos últimos 7 dias
        if (document.getElementById("graficoProgresso")) {
            const ctx = document.getElementById("graficoProgresso").getContext("2d");
            const gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, 'rgba(75, 198, 177, 0.6)');
            gradient.addColorStop(1, 'rgba(75, 198, 177, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= $labels_json ?>,
                    datasets: [{
                        label: "XP ganho no dia",
                        data: <?= $dados_grafico_json ?>,
                        borderColor: "var(--secondary)",
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'var(--secondary)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: 'var(--secondary)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
                }
            });
        }
        
        // --- SCRIPT DO MODAL E FOGOS ---
        function fecharLevelUp() {
            const modal = document.getElementById('modal-levelup');
            modal.style.opacity = '0';
            setTimeout(() => { modal.style.display = 'none'; }, 500);
        }

        <?php if ($subiuDeNivel): ?>
        window.onload = function() {
            // Mostra o Modal
            document.getElementById('modal-levelup').classList.add('show');

            // Dispara Fogos
            var duration = 5 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10001 };

            function randomInRange(min, max) { return Math.random() * (max - min) + min; }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) return clearInterval(interval);

                var particleCount = 50 * (timeLeft / duration);
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);
        };
        <?php endif; ?>
        
        // --- SCRIPT DOS TOASTS (NOTIFICAÇÕES DE SUCESSO E PRAZO) ---
        function showToast(type, title, message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = type === 'success' ? '🏆' : '⏰';
            
            toast.innerHTML = `
                <div class="toast-icon">${icon}</div>
                <div class="toast-content">
                    <h4>${title}</h4>
                    <p>${message}</p>
                </div>
                <button class="close-toast" onclick="this.parentElement.remove()">&times;</button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 50);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400); 
            }, 6000);
        }

        <?php if ($metaConcluidaRecente): ?>
            showToast('success', 'Excelente trabalho!', 'Meta concluída com sucesso. Você ganhou XP!');
            window.history.replaceState(null, null, window.location.pathname);
        <?php endif; ?>

        <?php if (!empty($metasExpirando)): ?>
            <?php foreach($metasExpirando as $titulo_meta): ?>
                setTimeout(() => {
                    showToast('warning', 'Atenção ao Prazo!', 'A meta "<?= addslashes($titulo_meta) ?>" vence hoje ou está atrasada.');
                }, 500); 
            <?php endforeach; ?>
        <?php endif; ?>

    </script>

    <?php if (isset($_SESSION['nova_conquista'])): ?>
        <div id="conquistaToast" style="position: fixed; bottom: 30px; right: 30px; background-color: #4BC6B1; color: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 9999; display: flex; align-items: center; gap: 15px; transform: translateX(150%); transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <i class="bi bi-trophy-fill" style="font-size: 2rem; color: #fffde7;"></i>
            <div>
                <strong style="display: block; font-size: 1.1rem;">Conquista Desbloqueada!</strong>
                <span style="font-size: 0.95rem;"><?= htmlspecialchars($_SESSION['nova_conquista']) ?></span>
            </div>
        </div>
        
        <script>
            // Faz o Toast deslizar para dentro da tela logo após a página carregar
            setTimeout(() => {
                const toast = document.getElementById('conquistaToast');
                if(toast) toast.style.transform = 'translateX(0)';
            }, 100);

            // Esconde o Toast depois de 5 segundos
            setTimeout(() => {
                const toast = document.getElementById('conquistaToast');
                if(toast) toast.style.transform = 'translateX(150%)';
            }, 5000);
        </script>
        
        <?php unset($_SESSION['nova_conquista']); // Limpa ?>
    <?php endif; ?> 
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Pega o nome real do usuário vindo do PHP
        let nomeUsuario = "<?= $nomeUsuario ?>"; 
        
        // Chamada da IA
        fetch('api_treinador_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome: nomeUsuario })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('texto-treinador-ia').innerText = data.sugestao;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('texto-treinador-ia').innerText = "Bora conquistar o mundo hoje! 🚀";
        });

        // --- SCRIPT DO DARK MODE ---
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        
        // 1. Verificar a preferência guardada no LocalStorage
        const currentTheme = localStorage.getItem('metaXP_theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        updateIcon(currentTheme);

        // 2. Lógica ao clicar no botão
        themeToggleBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            let newTheme = theme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('metaXP_theme', newTheme);
            updateIcon(newTheme);
        });

        // 3. Função para trocar o ícone (Lua vs Sol)
        function updateIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
                themeIcon.style.color = '#f6e05e'; // Amarelinho para o sol
            } else {
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
                themeIcon.style.color = ''; // Volta à cor padrão
            }
        }
    });
    </script>
</body>
</html>