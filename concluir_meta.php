<?php
session_start();
require_once 'auth_check.php';

// Configurações do DB
define('DB_HOST', 'sql212.infinityfree.com');
define('DB_NAME', 'if0_38657243_plataforma_de_planejamento_metas_pessoais');
define('DB_USER', 'if0_38657243');
define('DB_PASS', 'metaspessoais');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

// Verifica se recebeu o ID da meta
if (isset($_GET['id'])) {
    $meta_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    $xp_base_meta = 50; // XP padrão por concluir uma meta

    try {
        $pdo->beginTransaction();

        // 1. Verifica se a meta pertence ao usuário e não está concluída
        $stmt = $pdo->prepare("SELECT id FROM metas WHERE id = ? AND user_id = ? AND status != 'concluida'");
        $stmt->execute([$meta_id, $user_id]);
        $meta = $stmt->fetch();

        if ($meta) {
            // 2. Atualiza o status da meta para concluída
            $stmt_update = $pdo->prepare("UPDATE metas SET status = 'concluida' WHERE id = ?");
            $stmt_update->execute([$meta_id]);

            // ==========================================
            // 2.5 LÓGICA DE CONQUISTAS (CORRIGIDA E DINÂMICA)
            // ==========================================
            $xp_bonus_conquistas = 0;

            // Conta o total de metas concluídas por este usuário
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM metas WHERE user_id = ? AND status = 'concluida'");
            $stmt_count->execute([$user_id]);
            $total_metas_concluidas = (int)$stmt_count->fetchColumn();

            // Mapeamento de Conquistas: Nome EXATO no Banco => Quantidade de Metas Necessárias
            $regras_conquistas = [
                'Primeira Meta' => 1,
                '5 Metas Concluídas' => 5,
                'Produtivo' => 20,
                'Conquistador' => 50
            ];

            foreach ($regras_conquistas as $titulo_conquista => $meta_necessaria) {
                if ($total_metas_concluidas >= $meta_necessaria) {
                    
                    // Busca qual é o ID real dessa conquista no banco hoje
                    $stmt_get_id = $pdo->prepare("SELECT id, xp_recompensa FROM conquistas WHERE titulo = ?");
                    $stmt_get_id->execute([$titulo_conquista]);
                    $conquista_db = $stmt_get_id->fetch();

                    if ($conquista_db) {
                        $conquista_id = $conquista_db['id'];
                        $xp_da_conquista = (int)$conquista_db['xp_recompensa'];

                        // Verifica se o usuário JÁ POSSUI esta conquista
                        $stmt_check = $pdo->prepare("SELECT id FROM usuario_conquistas WHERE user_id = ? AND conquista_id = ?");
                        $stmt_check->execute([$user_id, $conquista_id]);
                        
                        if (!$stmt_check->fetch()) {
                            // Não possui! Destrava a conquista
                            $stmt_unlock = $pdo->prepare("INSERT INTO usuario_conquistas (user_id, conquista_id) VALUES (?, ?)");
                            $stmt_unlock->execute([$user_id, $conquista_id]);
                            
                            $xp_bonus_conquistas += $xp_da_conquista;

                            // ---> AQUI ESTÁ A MÁGICA DO POP-UP! <---
                            // Salva na sessão o nome da conquista para o Dashboard poder mostrar
                            $_SESSION['nova_conquista'] = $titulo_conquista; 
                        }
                    }
                }
            }
            
            // O total de XP ganho nesta ação é o XP da meta + XP de conquista
            $xp_total_ganho = $xp_base_meta + $xp_bonus_conquistas;
            // ==========================================

            // 3. Adiciona XP total ao usuário e calcula o Nível
            $stmt_user = $pdo->prepare("SELECT xp_total, nivel FROM users WHERE id = ?");
            $stmt_user->execute([$user_id]);
            $userData = $stmt_user->fetch(PDO::FETCH_ASSOC);

            $novo_xp = $userData['xp_total'] + $xp_total_ganho;
            $nivel_atual = $userData['nivel'];
            
            // Lógica de Level Up
            $xp_necessario = $nivel_atual * 1000; // Mantendo a regra de 1000 que vimos antes
            $novo_nivel = $nivel_atual;

            if ($novo_xp >= $xp_necessario) {
                $novo_nivel++;
            }

            $stmt_up_user = $pdo->prepare("UPDATE users SET xp_total = ?, nivel = ? WHERE id = ?");
            $stmt_up_user->execute([$novo_xp, $novo_nivel, $user_id]);

            // 4. Registra no Gráfico
            $stmt_log = $pdo->prepare("INSERT INTO xp_log (user_id, xp_ganho, data_ganho) VALUES (?, ?, CURDATE())");
            $stmt_log->execute([$user_id, $xp_total_ganho]);

            $pdo->commit();
            
            header("Location: dashboard.php?sucesso=1");
            exit();
        } else {
            $pdo->rollBack();
            header("Location: dashboard.php?erro=meta_invalida");
            exit();
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao processar: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit();
}