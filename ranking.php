<?php
session_start();

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
try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$ranking_users = [];

try {
    // Consulta para obter o ranking dos utilizadores ordenado por XP
    $stmt = $pdo->prepare("SELECT id, username, email, nivel, xp, foto_perfil FROM users ORDER BY xp DESC, nivel DESC");
    $stmt->execute();
    $ranking_users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar o ranking: " . $e->getMessage());
}

// Obtém os dados do utilizador da sessão para o layout
$nomeUsuario = htmlspecialchars($_SESSION['username'] ?? 'Usuário');
$emailUsuario = htmlspecialchars($_SESSION['email'] ?? 'email@exemplo.com');
$fotoAtual = $_SESSION['foto_perfil'] ?? null;
$fotoPerfil = ($fotoAtual && file_exists($fotoAtual)) ? htmlspecialchars($fotoAtual) : "https://i.pravatar.cc/150?u=" . urlencode($emailUsuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - MetaXP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1D84B5;
            --secondary: #4BC6B1;
            --light: #f8f9fa;
            --dark: #212529;
            --grey: #8a95a5;
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

        /* Variáveis do Dark Mode - Cores EXATAS extraídas da tela de Recompensas */
        body.dark-mode {
            --bg-color: #15161a;      /* Fundo principal mais escuro e levemente frio */
            --sidebar-bg: #1e2125;    /* Fundo da barra lateral */
            --card-bg: #1e2125;       /* Fundo dos cards */
            --text-color: #f8f9fa;
            --grey: #9ba4b5;          /* Cinza legível para textos de apoio */
            --shadow: none;           /* Remove a sombra forte no tema escuro */
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0 0 0 var(--sidebar-width);
            transition: background-color 0.3s ease, color 0.3s ease, margin-left 0.3s ease;
        }
        body.sidebar-collapsed { margin-left: var(--sidebar-width-collapsed); }

        /* Classes Utilitárias de Alinhamento (Faltava isso para alinhar o cabeçalho da tabela) */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            display: flex; flex-direction: column;
            transition: width 0.3s ease, background-color 0.3s ease;
            z-index: 100;
        }
        body.sidebar-collapsed .sidebar { width: var(--sidebar-width-collapsed); align-items: center; }

        .logo { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin-bottom: 2.5rem; white-space: nowrap; text-align: center; }
        .logo span { color: var(--secondary); }
        body.sidebar-collapsed .logo .text { display: none; }
        
        .sidebar-nav { flex-grow: 1; list-style: none; padding: 0; margin: 0; }
        .nav-item a { display: flex; align-items: center; padding: 0.9rem 1rem; color: var(--grey); text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.3s ease; white-space: nowrap; }
        .nav-item a:hover, .nav-item a.active { background-color: #eef5ff; color: var(--primary); font-weight: 500; }
        
        /* Ajuste do hover/active da sidebar no dark mode (Cinza idêntico ao original) */
        body.dark-mode .nav-item a:hover, 
        body.dark-mode .nav-item a.active { 
            background-color: #282c31; 
            color: var(--primary);
        }

        .nav-icon { font-size: 1.2rem; min-width: 24px; margin-right: 1.5rem; transition: margin-right 0.3s ease; }
        body.sidebar-collapsed .nav-icon { margin-right: 0; }
        .nav-text { opacity: 1; transition: opacity 0.2s ease; }
        body.sidebar-collapsed .nav-text { opacity: 0; width: 0; overflow: hidden; }

        .main-wrapper { display: flex; flex-direction: column; min-height: 100vh; }

        .top-navbar {
            background-color: var(--card-bg);
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--shadow);
            position: sticky; top: 0; z-index: 99;
            transition: background-color 0.3s ease;
        }
        
        /* Barra superior fundindo perfeitamente com o fundo no Dark Mode */
        body.dark-mode .top-navbar {
            background-color: var(--bg-color);
            box-shadow: none;
        }
        
        #toggle-sidebar { background: transparent; border: none; cursor: pointer; font-size: 1.5rem; color: var(--grey); }
        
        /* Botão de alternar tema */
        .theme-toggle-btn {
            background: transparent;
            border: none;
            color: var(--grey);
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .theme-toggle-btn:hover { color: var(--primary); }

        .navbar-right { display: flex; align-items: center; gap: 1.5rem; }
        
        .user-menu { position: relative; }
        .user-menu > img {
            width: 40px; height: 40px; border-radius: 50%;
            cursor: pointer; border: 2px solid transparent;
            transition: border-color 0.3s ease; object-fit: cover;
        }
        .user-menu:hover > img { border-color: var(--primary); }

        .dropdown-menu {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 240px; background-color: var(--card-bg);
            border-radius: 12px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0; z-index: 1000;
            opacity: 0; visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s, background-color 0.3s ease;
        }
        body.dark-mode .dropdown-menu { box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5); }
        .user-menu:hover .dropdown-menu {
            opacity: 1; visibility: visible; transform: translateY(0);
        }

        .dropdown-header {
            padding: 1rem 1.5rem; border-bottom: 1px solid #eee; margin-bottom: 0.5rem;
        }
        body.dark-mode .dropdown-header { border-bottom: 1px solid #333; }
        .dropdown-header strong { font-weight: 600; color: var(--text-color); }
        .dropdown-header small { color: var(--grey); font-size: 0.8rem; word-break: break-all; }
        
        .dropdown-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.5rem; color: var(--text-color);
            text-decoration: none; font-size: 0.9rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .dropdown-item:hover { background-color: #eef5ff; color: var(--primary); }
        body.dark-mode .dropdown-item:hover { background-color: #282c31; }
        .dropdown-item i { font-size: 1.1rem; }
        
        .main-content { padding: 2rem; flex-grow: 1; }

        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 1.8rem; font-weight: 600; margin: 0; }
        .header p { color: var(--grey); }

        .card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: background-color 0.3s ease;
        }

        .ranking-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; }
        .ranking-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            color: var(--grey);
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 500;
        }
        .ranking-table td {
            padding: 1rem;
            vertical-align: middle;
        }
        .ranking-table tbody tr {
            background-color: var(--card-bg);
            transition: background-color 0.3s;
        }
        .ranking-table tbody tr:hover { background-color: #f8f9fa; }
        body.dark-mode .ranking-table tbody tr:hover { background-color: #24272c; } 
        
        .ranking-table .rank {
            font-size: 1.2rem;
            font-weight: 700;
            width: 50px;
            text-align: center;
        }
        .ranking-table .rank-1 { color: var(--gold); }
        .ranking-table .rank-2 { color: var(--silver); }
        .ranking-table .rank-3 { color: var(--bronze); }
        
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .user-info img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .user-info .username { font-weight: 500; }
        
        .xp-value { font-weight: 600; }
        
        .current-user {
            background-color: #eef5ff !important;
            border-left: 4px solid var(--primary);
        }
        /* Destaque sutil e elegante do usuário logado no dark mode */
        body.dark-mode .current-user {
            background-color: rgba(29, 132, 181, 0.12) !important;
            border-left: 4px solid var(--primary); 
        }

    </style>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body class="sidebar-collapsed">

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>

    <aside class="sidebar">
        <div class="logo">M<span><span class="text">XP</span></span></div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="dashboard.php" title="Dashboard">
                    <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="ver_mais_metas.php" title="Minhas Metas">
                    <span class="nav-icon"><i class="bi bi-check2-circle"></i></span>
                    <span class="nav-text">Minhas Metas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="conquistas.php" title="Conquistas">
                    <span class="nav-icon"><i class="bi bi-trophy-fill"></i></span>
                    <span class="nav-text">Conquistas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="recompensas.php" title="Recompensas">
                    <span class="nav-icon"><i class="bi bi-gift-fill"></i></span>
                    <span class="nav-text">Recompensas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="ranking.php" class="active" title="Ranking">
                    <span class="nav-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <span class="nav-text">Ranking</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" title="Logout">
                    <span class="nav-icon"><i class="bi bi-box-arrow-left"></i></span>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="main-wrapper">
        <nav class="top-navbar">
            <button id="toggle-sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="navbar-right">
                <button id="theme-toggle" class="theme-toggle-btn" title="Alternar Tema">
                    <i class="bi bi-moon-fill"></i>
                </button>
                
                <div class="user-menu">
                    <img src="<?= $fotoPerfil ?>" alt="Foto do Perfil">
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            <strong><?= $nomeUsuario ?></strong><br>
                            <small><?= $emailUsuario ?></small>
                        </div>
                        <a href="editar_perfil.php" class="dropdown-item">
                            <i class="bi bi-person-fill"></i> Editar Perfil
                        </a>
                        <a href="alterar_senha.php" class="dropdown-item">
                            <i class="bi bi-key-fill"></i> Alterar Senha
                        </a>
                        <a href="logout.php" class="dropdown-item">
                           <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="header">
                <h1>Ranking de Utilizadores</h1>
                <p>Veja quem está no topo da jornada de produtividade!</p>
            </header>

            <div class="card">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th class="rank">#</th>
                            <th>Utilizador</th>
                            <th class="text-center">Nível</th>
                            <th class="text-right">XP Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking_users as $index => $ranked_user): ?>
                            <?php 
                                $rank = $index + 1;
                                $isCurrentUser = ($ranked_user['id'] == $user_id);
                                $userAvatar = ($ranked_user['foto_perfil'] && file_exists($ranked_user['foto_perfil'])) ? $ranked_user['foto_perfil'] : "https://i.pravatar.cc/150?u=" . urlencode($ranked_user['email']);
                            ?>
                            <tr class="<?= $isCurrentUser ? 'current-user' : '' ?>">
                                <td class="rank rank-<?= $rank <= 3 ? $rank : '' ?>">
                                    <?php if ($rank == 1): ?>
                                        <i class="bi bi-trophy-fill" style="color: var(--gold);"></i>
                                    <?php elseif ($rank == 2): ?>
                                        <i class="bi bi-trophy-fill" style="color: var(--silver);"></i>
                                    <?php elseif ($rank == 3): ?>
                                        <i class="bi bi-trophy-fill" style="color: var(--bronze);"></i>
                                    <?php else: ?>
                                        <?= $rank ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="Avatar">
                                        <span class="username"><?= htmlspecialchars($ranked_user['username']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?= $ranked_user['nivel'] ?></td>
                                <td class="xp-value text-right"><?= number_format($ranked_user['xp']) ?> XP</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Lógica da Sidebar
        document.getElementById('toggle-sidebar').addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
        });

        // Lógica do Dark Mode
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = themeToggleBtn.querySelector('i');

        if (document.body.classList.contains('dark-mode') || document.documentElement.classList.contains('dark-mode')) {
            themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
        }

        themeToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            document.documentElement.classList.toggle('dark-mode');
            
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
            } else {
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
            }
        });
    </script>
</body>
</html>