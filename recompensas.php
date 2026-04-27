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

// Busca os dados mais recentes do utilizador
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, foto_perfil, xp_total as xp FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['foto_perfil'] = $user['foto_perfil'];
    $_SESSION['xp'] = $user['xp'];
}

$nomeUsuario = htmlspecialchars($_SESSION['username'] ?? 'Usuário');
$emailUsuario = htmlspecialchars($_SESSION['email'] ?? 'email@exemplo.com');
$fotoAtual = $_SESSION['foto_perfil'] ?? null;
$fotoPerfil = ($fotoAtual && file_exists($fotoAtual)) ? htmlspecialchars($fotoAtual) : "https://i.pravatar.cc/150?u=" . urlencode($emailUsuario);
$xpAtualUsuario = $_SESSION['xp'] ?? 0;

// Array de recompensas
$recompensas = [
    ["titulo" => "Café Grátis", "descricao" => "Um café de sua escolha na cafetaria parceira.", "xp" => 100, "icon" => "bi-cup-hot-fill"],
    ["titulo" => "Dia de Folga", "descricao" => "Um dia livre para relaxar e recarregar as energias.", "xp" => 2500, "icon" => "bi-beach"],
    ["titulo" => "Gift Card R$50", "descricao" => "Vale-presente de R$ 50 para usar como quiser.", "xp" => 500, "icon" => "bi-gift-fill"],
    ["titulo" => "Almoço Especial", "descricao" => "Vale-refeição para um restaurante local.", "xp" => 300, "icon" => "bi-egg-fried"],
    ["titulo" => "Vale-Cinema", "descricao" => "Ingresso para uma sessão de cinema 2D.", "xp" => 200, "icon" => "bi-film"],
    ["titulo" => "Kit Relaxamento", "descricao" => "Inclui vela aromática e máscara para os olhos.", "xp" => 400, "icon" => "bi-universal-access-circle"],
    ["titulo" => "Assinatura Netflix", "descricao" => "1 mês de assinatura do plano básico.", "xp" => 800, "icon" => "bi-tv-fill"],
    ["titulo" => "Fone Bluetooth", "descricao" => "Fone sem fios para o seu dia a dia.", "xp" => 1000, "icon" => "bi-earbuds"],
    ["titulo" => "Agenda Personalizada", "descricao" => "Organize a sua vida com estilo.", "xp" => 270, "icon" => "bi-book-half"],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recompensas - MetaXP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <style>
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
            --success-color: #1cc88a;
            --hover-bg: #eef5ff;
            --border-color: #eee;
        }

        /* Variáveis Modo Escuro */
        body.dark-theme {
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

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0 0 0 var(--sidebar-width);
            transition: background-color 0.3s ease, color 0.3s ease, margin-left 0.3s ease;
        }
        body.sidebar-collapsed { margin-left: var(--sidebar-width-collapsed); }

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
        .nav-item a:hover, .nav-item a.active { background-color: var(--hover-bg); color: var(--primary); font-weight: 500; }
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
        
        #toggle-sidebar { background: transparent; border: none; cursor: pointer; font-size: 1.5rem; color: var(--grey); }
        
        #theme-toggle {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.3rem;
            color: var(--grey);
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #theme-toggle:hover { color: var(--primary); }

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
            border-radius: 12px; box-shadow: var(--shadow);
            padding: 0.5rem 0; z-index: 1000;
            opacity: 0; visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s, background-color 0.3s ease;
        }
        .user-menu:hover .dropdown-menu {
            opacity: 1; visibility: visible; transform: translateY(0);
        }

        .dropdown-header {
            padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 0.5rem; transition: border-color 0.3s ease;
        }
        .dropdown-header strong { font-weight: 600; color: var(--dark); transition: color 0.3s ease; }
        .dropdown-header small { color: var(--grey); font-size: 0.8rem; word-break: break-all; }
        
        .dropdown-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.5rem; color: var(--text-color);
            text-decoration: none; font-size: 0.9rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .dropdown-item:hover { background-color: var(--hover-bg); color: var(--primary); }
        .dropdown-item i { font-size: 1.1rem; }
        
        .main-content { padding: 2rem; flex-grow: 1; }

        .header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .header h1 { font-size: 1.8rem; font-weight: 600; margin: 0; color: var(--text-color); transition: color 0.3s ease; }
        .header p { color: var(--grey); margin-top: 0.5rem; transition: color 0.3s ease; }

        .xp-balance-card {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            color: white;
            text-align: center;
        }
        .xp-balance-card .label { font-size: 1rem; opacity: 0.8; margin-bottom: 0.5rem; }
        .xp-balance-card .value { font-size: 2.5rem; font-weight: 700; }

        /* BANNER IA */
        .ai-banner-card {
            background-color: var(--hover-bg);
            border-left: 5px solid var(--secondary);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.5s ease;
        }
        
        .ai-active {
            box-shadow: 0 0 20px rgba(75, 198, 177, 0.3);
            border-left-color: var(--primary);
        }

        .ai-banner-icon { font-size: 2.5rem; transition: transform 0.3s ease; }
        .ai-banner-card:hover .ai-banner-icon { transform: scale(1.1) rotate(5deg); }

        .ai-banner-content h5 { margin: 0 0 0.3rem 0; font-weight: 600; color: var(--dark); transition: color 0.3s ease; }
        .ai-banner-content p { margin: 0; font-size: 0.9rem; color: var(--text-color); min-height: 1.4em; transition: color 0.3s ease; }
        
        .btn-ai {
            margin-left: auto;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        .btn-ai:hover { opacity: 0.9; transform: scale(1.05); }
        .btn-ai:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Grid de Recompensas */
        .recompensas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .recompensa-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            display: flex; flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        }
        .recompensa-card:not([disabled]):hover {
             transform: translateY(-5px);
        }
        
        .card-content { padding: 1.5rem; flex-grow: 1; text-align: center; }
        .recompensa-icon { font-size: 3rem; color: var(--primary); margin-bottom: 1rem; }
        .recompensa-card h5 { font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--text-color); transition: color 0.3s ease; }
        .recompensa-card p { color: var(--grey); font-size: 0.9rem; margin-bottom: 1rem; transition: color 0.3s ease; }
        
        .card-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            transition: border-color 0.3s ease;
        }
        
        .btn-resgatar {
            width: 100%;
            padding: 0.7rem 1rem;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            background-size: 200%;
            color: white;
            transition: all 0.4s ease;
        }
         .btn-resgatar:hover:not(:disabled) {
            background-position: right;
            box-shadow: 0 4px 15px rgba(29, 132, 181, 0.3);
        }

        .btn-resgatar:disabled {
            background: var(--hover-bg);
            color: var(--grey);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-resgatado {
            background: var(--success-color) !important;
            color: white !important;
        }

        /* Botão Meus Resgates */
        .btn-meus-resgates {
            background-color: var(--card-bg); 
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
        .btn-meus-resgates:hover {
            background-color: var(--primary); 
            color: white;
        }
    </style>
</head>
<body class="sidebar-collapsed">

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
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
                <a href="recompensas.php" class="active" title="Recompensas">
                    <span class="nav-icon"><i class="bi bi-gift-fill"></i></span>
                    <span class="nav-text">Recompensas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="ranking.php" title="Ranking">
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
                
                <button id="theme-toggle" title="Alternar Tema">
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
                <div>
                    <h1>Loja de Recompensas</h1>
                    <p>Use o seu XP para resgatar prêmios incríveis!</p>
                </div>
                <a href="meus_resgates.php" class="btn-meus-resgates">
                    <i class="bi bi-clock-history"></i> Meus Resgates
                </a>
            </header>

            <div class="xp-balance-card">
                <div class="label">O seu saldo de XP</div>
                <div class="value" id="xp-balance"><?= number_format($xpAtualUsuario, 0, ',', '.') ?></div>
            </div>

            <div class="ai-banner-card" id="ai-card">
                <div class="ai-banner-icon" id="ai-icon">🤖</div>
                <div class="ai-banner-content">
                    <h5>Dúvida no resgate?</h5>
                    <p id="texto-sugestao-ia">A Meta.IA sugere o prêmio ideal para seus <strong><?= $xpAtualUsuario ?> XP</strong>.</p>
                </div>
                <button class="btn-ai" id="btn-sugerir-recompensa">
                    ✨ Sugerir agora
                </button>
            </div>

            <div class="recompensas-grid">
                <?php foreach ($recompensas as $rec): ?>
                    <?php $podeResgatar = $xpAtualUsuario >= $rec['xp']; ?>
                    <div class="recompensa-card">
                        <div class="card-content">
                            <div class="recompensa-icon">
                                <i class="bi <?= htmlspecialchars($rec["icon"]) ?>"></i>
                            </div>
                            <h5><?= htmlspecialchars($rec["titulo"]) ?></h5>
                            <p><?= htmlspecialchars($rec["descricao"]) ?></p>
                        </div>
                        <div class="card-footer">
                            <button class="btn-resgatar" data-cost="<?= $rec['xp'] ?>" data-titulo="<?= htmlspecialchars($rec['titulo']) ?>" <?= !$podeResgatar ? 'disabled' : '' ?>>
                                Resgatar por <?= $rec["xp"] ?> XP
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('toggle-sidebar').addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
        });

        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        const body = document.body;

        if (body.classList.contains('dark-theme')) {
            themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
        }

        themeToggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-theme');
            
            if (body.classList.contains('dark-theme')) {
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
            } else {
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
            }
        });

        // --- FUNÇÃO DE DIGITAÇÃO (TYPING EFFECT) ---
        function typeWriter(text, elementId) {
            const element = document.getElementById(elementId);
            element.innerHTML = "";
            let i = 0;
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, 20); // Velocidade da digitação
                }
            }
            type();
        }

        // --- SCRIPT DO BOTÃO DA IA ---
        document.addEventListener("DOMContentLoaded", function() {
            const btnSugerir = document.getElementById('btn-sugerir-recompensa');
            const textoSugestao = document.getElementById('texto-sugestao-ia');
            const aiCard = document.getElementById('ai-card');
            const nomeUsuario = "<?= $nomeUsuario ?>";
            const xpUsuario = "<?= $xpAtualUsuario ?>";

            if(btnSugerir && textoSugestao) {
                btnSugerir.addEventListener('click', function() {
                    // Estado inicial
                    btnSugerir.innerHTML = "⏳ Pensando...";
                    btnSugerir.disabled = true;
                    textoSugestao.innerHTML = "<em>Analisando seus hábitos...</em>";
                    aiCard.classList.remove('ai-active');

                    fetch('api_sugerir_recompensa.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nome: nomeUsuario, xp: xpUsuario })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            aiCard.classList.add('ai-active');
                            // Aplica o efeito de digitação na resposta da IA
                            typeWriter(`Dica: ${data.sugestao}`, 'texto-sugestao-ia');
                        } else {
                            textoSugestao.innerHTML = "A IA está ocupada agora. Tente novamente.";
                        }
                    })
                    .catch(error => {
                        textoSugestao.innerHTML = "Erro de conexão com a IA.";
                    })
                    .finally(() => {
                        btnSugerir.innerHTML = "🔄 Outra ideia";
                        btnSugerir.disabled = false;
                    });
                });
            }
        });

        // --- LÓGICA DE RESGATE E CONFETE ---
        if (document.querySelectorAll('.btn-resgatar').length > 0) {
            function launchConfetti(origin) {
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: origin,
                    colors: ['#1D84B5', '#4BC6B1', '#FFFFFF']
                });
            }

            const redeemButtons = document.querySelectorAll('.btn-resgatar');
            const xpBalanceElement = document.getElementById('xp-balance');
            let currentUserXP = parseInt(xpBalanceElement.textContent.replace(/\./g, ''));

            redeemButtons.forEach(button => {
                button.addEventListener('click', (event) => {
                    const cost = parseInt(button.dataset.cost);
                    const titulo = button.dataset.titulo;

                    button.disabled = true;
                    const originalText = button.textContent;
                    button.textContent = 'Processando...';

                    fetch('processar_resgate.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ custo: cost, titulo: titulo })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            currentUserXP = data.novo_xp;
                            xpBalanceElement.textContent = currentUserXP.toLocaleString('pt-BR');
                            button.textContent = 'Resgatado!';
                            button.classList.add('btn-resgatado');
                            
                            const rect = event.target.getBoundingClientRect();
                            const origin = {
                                x: (rect.left + rect.right) / 2 / window.innerWidth,
                                y: (rect.top + rect.bottom) / 2 / window.innerHeight
                            };
                            launchConfetti(origin);
                            
                            redeemButtons.forEach(btn => {
                                if (!btn.classList.contains('btn-resgatado')) {
                                    if (currentUserXP < parseInt(btn.dataset.cost)) btn.disabled = true;
                                }
                            });
                        } else {
                            alert("Erro: " + data.message);
                            button.disabled = false;
                            button.textContent = originalText;
                        }
                    })
                    .catch(error => {
                        alert("Erro no servidor.");
                        button.disabled = false;
                        button.textContent = originalText;
                    });
                });
            });
        }
    </script>
</body>
</html>