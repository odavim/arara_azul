<?php

header("Content-Type: application/json");
require '../config/conexao.php';

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

    // questao_id agora é opcional - se não fornecido, retorna todas do caderno
    $questao_id = null;
    if (isset($_GET['questao_id'])) {
        $questao_id = filter_input(INPUT_GET, 'questao_id', FILTER_VALIDATE_INT);
        if ($questao_id === false || $questao_id <= 0) {
            $erros[] = "questao_id deve ser um número inteiro válido";
        }
    }

    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($questao_id) {
        // Busca uma questão específica
        $sql = "SELECT id, caderno_id, questao_id, resposta_usuario, acertou 
                FROM caderno_questao 
                WHERE caderno_id = ? AND questao_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$caderno_id, $questao_id]);
    } else {
        // Busca todas as questões do caderno
        $sql = "SELECT cq.id, cq.caderno_id, cq.questao_id, cq.resposta_usuario, cq.acertou,
                       q.enunciado, q.alternativa_a, q.alternativa_b, q.alternativa_c, 
                       q.alternativa_d, q.alternativa_e, q.resposta_correta
                FROM caderno_questao cq
                INNER JOIN questao q ON cq.questao_id = q.id
                WHERE cq.caderno_id = ?
                ORDER BY cq.id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$caderno_id]);
    }

    $resultados = $stmt->fetchAll();

    if (empty($resultados)) {
        http_response_code(404);
        $mensagem = $questao_id ? "Questão não encontrada neste caderno" : "Nenhuma questão encontrada para este caderno";
        echo json_encode(["mensagem" => $mensagem]);
        exit;
    }

    echo json_encode($resultados, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar questões do caderno"]);
    error_log("Erro em caderno_questoes.php: " . $e->getMessage());
}