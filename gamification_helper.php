<?php
// helpers/gamification_helper.php

/**
 * Verifica se um utilizador já tem uma conquista e, se não tiver, atribui-a.
 *
 * @param PDO $pdo A instância da conexão PDO com a base de dados.
 * @param int $user_id O ID do utilizador a ser verificado.
 * @param string $conquista_nome O nome exato da conquista (ex: 'Primeiro Passo').
 * @return bool Retorna true se a conquista foi atribuída, false caso contrário.
 */
function verificar_e_atribuir_conquista($pdo, $user_id, $conquista_nome) {
    try {
        // 1. Obter os detalhes da conquista usando o 'nome' (que cadastramos no banco)
        $stmt_conquista = $pdo->prepare("SELECT id, xp_recompensa FROM conquistas WHERE nome = ?");
        $stmt_conquista->execute([$conquista_nome]);
        $conquista = $stmt_conquista->fetch();

        if (!$conquista) {
            return false; // A conquista não existe na base de dados
        }

        $conquista_id = $conquista['id'];
        $xp_recompensa = $conquista['xp_recompensa'];

        // 2. Verificar se o utilizador já tem esta conquista na tabela 'usuario_conquistas'
        $stmt_check = $pdo->prepare("SELECT id FROM usuario_conquistas WHERE user_id = ? AND conquista_id = ?");
        $stmt_check->execute([$user_id, $conquista_id]);
        
        if ($stmt_check->fetch()) {
            return false; // O utilizador já tem esta conquista
        }

        // 3. Atribuir a conquista e o XP (Usando Transação para garantir segurança)
        $pdo->beginTransaction();

        $stmt_insert = $pdo->prepare("INSERT INTO usuario_conquistas (user_id, conquista_id) VALUES (?, ?)");
        $stmt_insert->execute([$user_id, $conquista_id]);

        // Atualiza a coluna correta: 'xp_total' na tabela 'users'
        $stmt_update_xp = $pdo->prepare("UPDATE users SET xp_total = xp_total + ? WHERE id = ?");
        $stmt_update_xp->execute([$xp_recompensa, $user_id]);

        $pdo->commit();

        return true; // Conquista atribuída com sucesso

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Erro na função de gamificação: " . $e->getMessage());
        return false;
    }
}
?>