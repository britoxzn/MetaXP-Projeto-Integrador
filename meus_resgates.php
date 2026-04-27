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

// Busca os dados do utilizador para o layout
$stmt = $pdo->prepare("SELECT username, email, foto_perfil FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$nomeUsuario = htmlspecialchars($user['username'] ?? 'Usuário');
$emailUsuario = htmlspecialchars($user['email'] ?? 'email@exemplo.com');
$fotoAtual = $user['foto_perfil'] ?? null;
$fotoPerfil = ($fotoAtual && file_exists($fotoAtual)) ? htmlspecialchars($fotoAtual) : "https://i.pravatar.cc/150?u=" . urlencode($emailUsuario);

// Busca o histórico de resgates do utilizador, ordenado do mais recente para o mais antigo
$stmtHist = $pdo->prepare("SELECT titulo_recompensa, custo_xp, data_resgate FROM historico_recompensas WHERE user_id = ? ORDER BY data_resgate DESC");
$stmtHist->execute([$user_id]);
$historico = $stmtHist->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Resgates - MetaXP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Reutilizando as cores e estrutura base do seu projeto */
        :root {
            --primary: #1D84B5;
            --secondary: #4BC6B1;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --grey: #8a95a5;
            --bg-color: #f4f7fc;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #34495e;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 80px;
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

        /* Estilos da Sidebar e Topbar (iguais aos outros arquivos) */
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: var(--sidebar-width); background-color: var(--sidebar-bg); box-shadow: var(--shadow); padding: 1.5rem; display: flex; flex-direction: column; transition: width 0.3s ease; z-index: 100; }
        body.sidebar-collapsed .sidebar { width: var(--sidebar-width-collapsed); align-items: center; }
        .logo { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin-bottom: 2.5rem; white-space: nowrap; text-align: center; }
        .logo span { color: var(--secondary); }
        body.sidebar-collapsed .logo .text { display: none; }
        .sidebar-nav { flex-grow: 1; list-style: none; padding: 0; margin: 0; }
        .nav-item a { display: flex; align-items: center; padding: 0.9rem 1rem; color: var(--grey); text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.3s ease; white-space: nowrap; }
        .nav-item a:hover, .nav-item a.active { background-color: #eef5ff; color: var(--primary); font-weight: 500; }
        .nav-icon { font-size: 1.2rem; min-width: 24px; margin-right: 1.5rem; transition: margin-right 0.3s ease; }
        body.sidebar-collapsed .nav-icon { margin-right: 0; }
        .nav-text { opacity: 1; transition: opacity 0.2s ease; }
        body.sidebar-collapsed .nav-text { opacity: 0; width: 0; overflow: hidden; }

        .main-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .top-navbar { background-color: var(--card-bg); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow); position: sticky; top: 0; z-index: 99; }
        #toggle-sidebar { background: transparent; border: none; cursor: pointer; font-size: 1.5rem; color: var(--grey); }
        .navbar-right { display: flex; align-items: center; gap: 1.5rem; }
        .user-menu { position: relative; }
        .user-menu > img { width: 40px; height: 40px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: border-color 0.3s ease; object-fit: cover; }
        
        /* Estilos específicos para a tabela de histórico */
        .main-content { padding: 2rem; flex-grow: 1; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header h1 { font-size: 1.8rem; font-weight: 600; margin: 0; }
        .header p { color: var(--grey); margin-top: 5px; }
        
        .btn-voltar {
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-voltar:hover { background-color: var(--primary); color: white; }

        .historico-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1.2rem 1.5rem; text-align: left; }
        th { background-color: #f8fbff; color: var(--dark); font-weight: 600; font-size: 0.95rem; border-bottom: 2px solid #eef5ff; }
        td { border-bottom: 1px solid #f0f0f0; color: var(--text-color); font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #fafbfc; }

        .xp-custo { color: var(--primary); font-weight: 600; }
        .data-hora { color: var(--grey); font-size: 0.85rem; }
        
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--grey); }
        .empty-state i { font-size: 3rem; color: #d1d9e6; margin-bottom: 1rem; display: block; }
    </style>
</head>
<body class="sidebar-collapsed">

    <aside class="sidebar">
        <div class="logo">M<span><span class="text">XP</span></span></div>
        <ul class="sidebar-nav">
            <li class="nav-item"><a href="dashboard.php"><span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span><span class="nav-text">Dashboard</span></a></li>
            <li class="nav-item"><a href="ver_mais_metas.php"><span class="nav-icon"><i class="bi bi-check2-circle"></i></span><span class="nav-text">Minhas Metas</span></a></li>
            <li class="nav-item"><a href="conquistas.php"><span class="nav-icon"><i class="bi bi-trophy-fill"></i></span><span class="nav-text">Conquistas</span></a></li>
            <li class="nav-item"><a href="recompensas.php" class="active"><span class="nav-icon"><i class="bi bi-gift-fill"></i></span><span class="nav-text">Recompensas</span></a></li>
            <li class="nav-item"><a href="ranking.php"><span class="nav-icon"><i class="bi bi-bar-chart-line-fill"></i></span><span class="nav-text">Ranking</span></a></li>
            <li class="nav-item"><a href="logout.php"><span class="nav-icon"><i class="bi bi-box-arrow-left"></i></span><span class="nav-text">Logout</span></a></li>
        </ul>
    </aside>

    <div class="main-wrapper">
        <nav class="top-navbar">
            <button id="toggle-sidebar"><i class="bi bi-list"></i></button>
            <div class="navbar-right">
                <div class="user-menu"><img src="<?= $fotoPerfil ?>" alt="Foto do Perfil"></div>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Meus Resgates</h1>
                    <p>Histórico de tudo o que você já conquistou com seu esforço.</p>
                </div>
                <a href="recompensas.php" class="btn-voltar">
                    <i class="bi bi-arrow-left"></i> Voltar para a Loja
                </a>
            </header>

            <div class="historico-card">
                <?php if (count($historico) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Recompensa</th>
                                <th>XP Investido</th>
                                <th>Data do Resgate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $item): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($item['titulo_recompensa']) ?></strong></td>
                                    <td class="xp-custo">- <?= number_format($item['custo_xp'], 0, ',', '.') ?> XP</td>
                                    <td class="data-hora">
                                        <?= date('d/m/Y \à\s H:i', strtotime($item['data_resgate'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Você ainda não fez nenhum resgate. Acumule XP e aproveite a loja!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('toggle-sidebar').addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>