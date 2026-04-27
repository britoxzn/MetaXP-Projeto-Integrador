<?php
/*
--- GUIA DE DEPURAÇÃO (DEBUG GUIDE) ---
Se esta página estiver a dar um erro 500 ou "página não funciona", a causa
mais provável é que o ficheiro `helpers/gamification_helper.php` não foi encontrado.

SOLUÇÃO:
1. Certifique-se de que tem uma pasta chamada `helpers` na raiz do seu projeto.
2. Certifique-se de que o ficheiro `gamification_helper.php` está DENTRO dessa pasta.

Se o problema persistir, verifique se a sua tabela `metas` tem a coluna `categoria`.
*/

session_start();

// Se o utilizador não estiver logado, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Inclui a função de verificação de conquistas
if (file_exists('helpers/gamification_helper.php')) {
    include_once 'helpers/gamification_helper.php';
} else {
    // Função de fallback para evitar erro fatal se o ficheiro não for encontrado
    if (!function_exists('verificar_e_atribuir_conquista')) {
        function verificar_e_atribuir_conquista($pdo, $user_id, $conquista_chave) {
            // Apenas regista um erro, não faz nada
            error_log("Ficheiro gamification_helper.php não encontrado.");
            return false;
        }
    }
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

$errors = [];
$titulo = '';
$descricao = '';
$prazo = '';
$categoria = 'Pessoal'; // Valor padrão
$status = 'pendente'; // Valor padrão

// --- Processamento do Formulário ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $prazo = $_POST['prazo'] ?? '';
    $categoria = $_POST['categoria'] ?? 'Pessoal';
    $status = $_POST['status'] ?? 'pendente';
    $user_id = $_SESSION['user_id'];

    // Validação
    if (empty($titulo)) {
        $errors[] = 'O título da meta é obrigatório.';
    }
    if (empty($prazo)) {
        $errors[] = 'A data limite é obrigatória.';
    }

    if (empty($errors)) {
        try {
            // 1. Insere a Meta
            $sql = "INSERT INTO metas (user_id, titulo, descricao, status, prazo, categoria) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $titulo, $descricao, $status, $prazo, $categoria]);
            $new_meta_id = $pdo->lastInsertId();

            // 2. Extrair tarefas da descrição (que a IA gerou no textarea)
            $tarefas_geradas = [];
            $linhas = explode("\n", $descricao); // Separa o texto linha por linha
            
            foreach ($linhas as $linha) {
                $linha = trim($linha);
                // Procura linhas que começam com "* ", "- " ou "1. "
                if (preg_match('/^(\*|-|\d+\.)\s+(.+)$/', $linha, $matches)) {
                    // Limpa a formatação em negrito (**) que a IA costuma mandar
                    $tarefa_limpa = str_replace(['**', '*'], '', $matches[2]);
                    $tarefa_limpa = trim($tarefa_limpa);
                    
                    // Se a linha não ficou vazia, adiciona na lista
                    if (!empty($tarefa_limpa) && strlen($tarefa_limpa) > 3) {
                        $tarefas_geradas[] = $tarefa_limpa;
                    }
                }
            }

            // Se a IA não tiver gerado uma lista (ou o usuário não usou a IA), coloca uma tarefa padrão
            if (empty($tarefas_geradas)) {
                $tarefas_geradas[] = "Dar o primeiro passo para: " . $titulo;
            }

            // 3. Inserir as tarefas reais no banco vinculadas à meta
            $stmt_tarefa = $pdo->prepare("INSERT INTO tarefas (meta_id, descricao, status) VALUES (?, ?, 'pendente')");
            foreach ($tarefas_geradas as $tarefa_desc) {
                // Corta o texto para caber no banco (se for muito longo)
                $tarefa_desc = mb_substr($tarefa_desc, 0, 250); 
                $stmt_tarefa->execute([$new_meta_id, $tarefa_desc]);
            }

            // ** LÓGICA DE CONQUISTA **
            verificar_e_atribuir_conquista($pdo, $user_id, 'PRIMEIRA_META_CRIADA');

            // Prepara uma mensagem de sucesso para a próxima página
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Nova meta criada! Seu objetivo foi dividido em ' . count($tarefas_geradas) . ' tarefas.'];

            // Redireciona para a página de ver mais metas
            header("Location: ver_mais_metas.php");
            exit();

        } catch (PDOException $e) {
            $errors[] = "Erro ao salvar a meta: " . $e->getMessage();
        }
    }
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
    <title>Nova Meta - MetaXP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1D84B5;
            --secondary: #4BC6B1;
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
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            display: flex; flex-direction: column;
            transition: width 0.3s ease;
            z-index: 100;
        }
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

        .top-navbar {
            background-color: var(--card-bg);
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--shadow);
            position: sticky; top: 0; z-index: 99;
        }
        
        #toggle-sidebar { background: transparent; border: none; cursor: pointer; font-size: 1.5rem; color: var(--grey); }
        .navbar-right { display: flex; align-items: center; gap: 1.5rem; }
        
        /* Dropdown Menu Styles */
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
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
        }
        .user-menu:hover .dropdown-menu {
            opacity: 1; visibility: visible; transform: translateY(0);
        }

        .dropdown-header {
            padding: 1rem 1.5rem; border-bottom: 1px solid #eee; margin-bottom: 0.5rem;
        }
        .dropdown-header strong { font-weight: 600; color: var(--dark); }
        .dropdown-header small { color: var(--grey); font-size: 0.8rem; word-break: break-all; }
        
        .dropdown-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.5rem; color: var(--text-color);
            text-decoration: none; font-size: 0.9rem;
        }
        .dropdown-item:hover { background-color: #eef5ff; color: var(--primary); }
        .dropdown-item i { font-size: 1.1rem; }
        
        .main-content { padding: 2rem; flex-grow: 1; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header h1 { font-size: 1.8rem; font-weight: 600; margin: 0; }

        .btn-primary {
            background: linear-gradient(90deg, var(--secondary), var(--primary)); background-size: 200%;
            color: white; border: none; border-radius: 8px; font-weight: 500;
            padding: 0.7rem 1.5rem;
            transition: all 0.4s ease;
            text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;
            cursor: pointer;
        }
        .btn-primary:hover { background-position: right; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(29, 132, 181, 0.3); }
        
        .btn-secondary {
            background-color: #e9ecef;
            color: var(--grey);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            padding: 0.7rem 1.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-secondary:hover { background-color: #d8dde3; }

        .form-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-label { font-weight: 500; margin-bottom: 0.5rem; display: block; }
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(29, 132, 181, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 5px solid var(--danger);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        /* --- ESTILOS NOVOS DA META.IA --- */
        .ai-banner { 
            background: linear-gradient(135deg, rgba(29,132,181,0.05) 0%, rgba(75,198,177,0.1) 100%); 
            border-left: 4px solid var(--primary); 
            padding: 1rem 1.5rem; 
            border-radius: 8px; 
            margin-bottom: 2rem; 
            display: flex; 
            align-items: center; 
            gap: 1rem; 
        }
        .ai-banner i { font-size: 1.5rem; color: var(--primary); }
        .ai-banner p { margin: 0; font-size: 0.9rem; color: var(--dark); line-height: 1.5; }

        #loading-ai { display: none; text-align: center; margin-top: 1rem; width: 100%; padding: 1rem 0; }
        .spinner { 
            display: inline-block; width: 1.5rem; height: 1.5rem; 
            border: 3px solid rgba(29,132,181,0.2); 
            border-radius: 50%; border-top-color: var(--primary); 
            animation: spin 1s ease-in-out infinite; 
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Helpers de Layout */
        .row { display: flex; flex-wrap: wrap; margin: -0.75rem; }
        .col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0.75rem; }
        .d-flex { display: flex; }
        .justify-content-end { justify-content: flex-end; }
        .gap-2 { gap: 0.5rem; }
        .mt-4 { margin-top: 1.5rem; }
        .mb-0 { margin-bottom: 0; }
        @media (max-width: 768px) { .col-md-6 { flex: 0 0 100%; max-width: 100%; } }

        /* Botão Mágico IA */
        #btn-ia-planejar {
            background: linear-gradient(90deg, #4BC6B1, #1D84B5);
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        #btn-ia-planejar:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(29, 132, 181, 0.3);
        }
        #btn-ia-planejar:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body class="sidebar-collapsed">

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
                <a href="ver_mais_metas.php" class="active" title="Minhas Metas">
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
                <h1>Criar Nova Meta</h1>
            </header>
            
            <div class="form-card">
                <?php if (!empty($errors)): ?>
                    <div class="alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="ai-banner">
                    <i class="bi bi-robot"></i>
                    <p><strong>Meta.IA Integrada:</strong> Descreva o seu grande objetivo. Nossa inteligência artificial vai quebrá-lo automaticamente em tarefas menores e executáveis para você começar hoje.</p>
                </div>

                <form action="nova_meta.php" method="POST" id="formMeta" onsubmit="mostrarLoading()">
                    <div class="form-group">
                        <label for="titulo" class="form-label">Título da Meta</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" value="<?= htmlspecialchars($titulo) ?>" required placeholder="Ex: Aprender a programar, Guardar R$5000...">
                    </div>
                    
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="descricao" class="form-label" style="margin-bottom: 0;">Descrição (Opcional)</label>
                            <button type="button" id="btn-ia-planejar">
                                ✨ IA: Me ajude a planejar
                            </button>
                        </div>
                        <textarea id="descricao" name="descricao" class="form-control" rows="6" placeholder="A IA pode gerar os passos práticos e preencher isso para você..."><?= htmlspecialchars($descricao) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoria" class="form-label">Categoria</label>
                                <select id="categoria" name="categoria" class="form-control">
                                    <option value="Pessoal" <?= $categoria === 'Pessoal' ? 'selected' : '' ?>>Pessoal</option>
                                    <option value="Profissional" <?= $categoria === 'Profissional' ? 'selected' : '' ?>>Profissional</option>
                                    <option value="Financeiro" <?= $categoria === 'Financeiro' ? 'selected' : '' ?>>Financeiro</option>
                                    <option value="Educação" <?= $categoria === 'Educação' ? 'selected' : '' ?>>Educação</option>
                                    <option value="Saúde" <?= $categoria === 'Saúde' ? 'selected' : '' ?>>Saúde</option>
                                    <option value="Outro" <?= $categoria === 'Outro' ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="prazo" class="form-label">Data Limite</label>
                                <input type="date" id="prazo" name="prazo" class="form-control" value="<?= htmlspecialchars($prazo) ?>" required min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="status" class="form-label">Status Inicial</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pendente" <?= $status === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="em_andamento" <?= $status === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4" id="botoes-form">
                        <a href="ver_mais_metas.php" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary"><i class="bi bi-magic"></i> Salvar e Gerar Tarefas</button>
                    </div>

                    <div id="loading-ai">
                        <div class="spinner"></div>
                        <p style="color: var(--grey); margin-top: 0.5rem; font-size: 0.9rem;">Salvando as tarefas...</p>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script>
        // Mantém a sidebar funcionando
        document.getElementById('toggle-sidebar').addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
        });

        // Função que esconde o botão e mostra o carregamento geral do formulário
        function mostrarLoading() {
            document.getElementById('botoes-form').style.display = 'none';
            document.getElementById('loading-ai').style.display = 'block';
        }

        // --- SCRIPT DO BOTÃO MÁGICO DA IA ---
        document.addEventListener("DOMContentLoaded", function() {
            const btnIa = document.getElementById('btn-ia-planejar');
            const inputTitulo = document.getElementById('titulo'); 
            const textareaDescricao = document.getElementById('descricao'); 

            if(btnIa && inputTitulo && textareaDescricao) {
                btnIa.addEventListener('click', function() {
                    const titulo = inputTitulo.value.trim();
                    
                    if(!titulo) {
                        alert("Por favor, digite um título para a meta primeiro!");
                        inputTitulo.focus();
                        return;
                    }

                    // Muda o texto do botão para mostrar que está carregando
                    const textoOriginal = btnIa.innerHTML;
                    btnIa.innerHTML = "⏳ Pensando no plano...";
                    btnIa.disabled = true;

                    // Chama o arquivo PHP que se comunica com a IA (ex: Gemini)
                    fetch('api_planejar_meta.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ titulo: titulo })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Preenche a caixa de descrição com a resposta da IA!
                            textareaDescricao.value = data.plano;
                            // Dá um foco no campo para o usuário ver que foi preenchido
                            textareaDescricao.focus();
                        } else {
                            alert("Ops: " + data.erro);
                        }
                    })
                    .catch(error => {
                        alert("Erro ao conectar com a IA. Tente novamente mais tarde.");
                        console.error(error);
                    })
                    .finally(() => {
                        // Volta o botão ao normal
                        btnIa.innerHTML = textoOriginal;
                        btnIa.disabled = false;
                    });
                });
            }
        });
    </script>
</body>
</html>