<?php
/**
 * API: Responder Questão
 * Método: POST
 * Descrição: Registra a resposta do usuário para uma questão do caderno
 * 
 * Parâmetros (JSON ou Form Data):
 * - caderno_id (int, obrigatório): ID do caderno
 * - questao_id (int, obrigatório): ID da questão
 * - resposta_usuario (string, obrigatório): Alternativa escolhida (A, B, C, D, E)
 * 
 * Retorno:
 * - 200: Resposta registrada (retorna se acertou)
 * - 400: Erro de validação
 * - 404: Caderno/questão não encontrada
 * - 500: Erro no servidor
 */

header("Content-Type: application/json");
require '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erros" => ["Método não permitido. Use POST."]]);
    exit;
}

try {
    $erros = [];
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Valida caderno_id
    if (!isset($input['caderno_id'])) {
        $erros[] = "caderno_id é obrigatório";
    } else {
        $caderno_id = filter_var($input['caderno_id'], FILTER_VALIDATE_INT);
        if ($caderno_id === false || $caderno_id <= 0) {
            $erros[] = "caderno_id deve ser um número inteiro válido";
        }
    }
    
    // Valida questao_id
    if (!isset($input['questao_id'])) {
        $erros[] = "questao_id é obrigatório";
    } else {
        $questao_id = filter_var($input['questao_id'], FILTER_VALIDATE_INT);
        if ($questao_id === false || $questao_id <= 0) {
            $erros[] = "questao_id deve ser um número inteiro válido";
        }
    }
    
    // Valida resposta_usuario (deve ser A, B, C, D ou E)
    if (!isset($input['resposta_usuario'])) {
        $erros[] = "resposta_usuario é obrigatória";
    } else {
        $resposta = strtoupper(trim($input['resposta_usuario']));
        if (!in_array($resposta, ['A', 'B', 'C', 'D', 'E'])) {
            $erros[] = "resposta_usuario deve ser A, B, C, D ou E";
        }
    }
    
    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verifica se a questão existe no caderno
    $sql = "SELECT cq.id, q.resposta_correta 
            FROM caderno_questao cq
            INNER JOIN questao q ON cq.questao_id = q.id
            WHERE cq.caderno_id = ? AND cq.questao_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$caderno_id, $questao_id]);
    $registro = $stmt->fetch();
    
    if (!$registro) {
        http_response_code(404);
        echo json_encode(["erros" => ["Questão não encontrada neste caderno"]]);
        exit;
    }
    
    // Verifica se a questão já foi respondida
    if ($registro['resposta_usuario'] !== null) {
        http_response_code(400);
        echo json_encode(["erros" => ["Esta questão já foi respondida"]]);
        exit;
    }
    
    // Determina se acertou
    $acertou = ($resposta === $registro['resposta_correta']) ? 1 : 0;
    
    // Atualiza a resposta
    $sql = "UPDATE caderno_questao 
            SET resposta_usuario = ?, acertou = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$resposta, $acertou, $registro['id']]);
    
    // Retorna resultado
    echo json_encode([
        "sucesso" => true,
        "acertou" => $acertou == 1,
        "resposta_correta" => $registro['resposta_correta'],
        "mensagem" => $acertou ? "Parabéns! Você acertou!" : "Que pena! Você errou."
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erros" => ["Erro ao registrar resposta"]]);
    error_log("Erro em responder_questao.php: " . $e->getMessage());
}