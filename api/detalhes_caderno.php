<?php
/**
 * API: Detalhes do Caderno
 * Método: GET
 * Descrição: Retorna informações completas de um caderno com todas as questões
 * 
 * Parâmetros (GET):
 * - caderno_id (int, obrigatório): ID do caderno
 * 
 * Retorno:
 * - 200: Dados do caderno + questões
 * - 400: Erro de validação
 * - 404: Caderno não encontrado
 * - 500: Erro no servidor
 */

header("Content-Type: application/json");
require '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["erros" => ["Método não permitido. Use GET."]]);
    exit;
}

try {
    $erros = [];
    
    if (!isset($_GET['caderno_id'])) {
        $erros[] = "caderno_id é obrigatório";
    } else {
        $caderno_id = filter_input(INPUT_GET, 'caderno_id', FILTER_VALIDATE_INT);
        if ($caderno_id === false || $caderno_id <= 0) {
            $erros[] = "caderno_id deve ser um número inteiro válido";
        }
    }
    
    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Busca dados do caderno
    $sql = "SELECT c.*, 
                   u.nome as usuario_nome,
                   t.nome as topico_nome,
                   m.nome as materia_nome
            FROM caderno c
            INNER JOIN usuario u ON c.usuario_id = u.id
            INNER JOIN topico t ON c.topico_id = t.id
            INNER JOIN materia m ON t.materia_id = m.id
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$caderno_id]);
    $caderno = $stmt->fetch();
    
    if (!$caderno) {
        http_response_code(404);
        echo json_encode(["erros" => ["Caderno não encontrado"]]);
        exit;
    }
    
    // Busca todas as questões do caderno com detalhes completos
    $sql = "SELECT 
                cq.id as caderno_questao_id,
                cq.questao_id,
                cq.resposta_usuario,
                cq.acertou,
                q.enunciado,
                q.alternativa_a,
                q.alternativa_b,
                q.alternativa_c,
                q.alternativa_d,
                q.alternativa_e,
                q.resposta_correta
            FROM caderno_questao cq
            INNER JOIN questao q ON cq.questao_id = q.id
            WHERE cq.caderno_id = ?
            ORDER BY cq.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$caderno_id]);
    $questoes = $stmt->fetchAll();
    
    // Conta quantas já foram respondidas
    $respondidas = 0;
    foreach ($questoes as $q) {
        if ($q['resposta_usuario'] !== null) {
            $respondidas++;
        }
    }
    
    // Monta resposta completa
    $resposta = [
        "caderno" => [
            "id" => $caderno['id'],
            "usuario_id" => $caderno['usuario_id'],
            "usuario_nome" => $caderno['usuario_nome'],
            "topico_id" => $caderno['topico_id'],
            "topico_nome" => $caderno['topico_nome'],
            "materia_nome" => $caderno['materia_nome'],
            "total_questoes" => $caderno['total_questoes'],
            "total_acertos" => $caderno['total_acertos'],
            "percentual" => $caderno['percentual'],
            "criado_em" => $caderno['criado_em'],
            "finalizado_em" => $caderno['finalizado_em'],
            "status" => $caderno['finalizado_em'] ? "finalizado" : "em_andamento",
            "questoes_respondidas" => $respondidas,
            "questoes_restantes" => 10 - $respondidas
        ],
        "questoes" => $questoes
    ];
    
    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erros" => ["Erro ao buscar detalhes do caderno"]]);
    error_log("Erro em caderno_detalhe.php: " . $e->getMessage());
}