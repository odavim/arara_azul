<?php
/**
 * API: Finalizar Caderno
 * Método: PUT
 * Descrição: Calcula resultado e marca caderno como concluído
 * 
 * Parâmetros (JSON):
 * - caderno_id (int, obrigatório): ID do caderno a finalizar
 * 
 * Retorno:
 * - 200: Caderno finalizado (retorna estatísticas)
 * - 400: Erro de validação
 * - 404: Caderno não encontrado
 * - 409: Caderno já finalizado
 * - 500: Erro no servidor
 */

header("Content-Type: application/json");
require '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["erros" => ["Método não permitido. Use PUT."]]);
    exit;
}

try {
    $erros = [];
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['caderno_id'])) {
        $erros[] = "caderno_id é obrigatório";
    } else {
        $caderno_id = filter_var($input['caderno_id'], FILTER_VALIDATE_INT);
        if ($caderno_id === false || $caderno_id <= 0) {
            $erros[] = "caderno_id deve ser um número inteiro válido";
        }
    }
    
    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verifica se caderno existe e pega dados atuais
    $sql = "SELECT id, finalizado_em FROM caderno WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$caderno_id]);
    $caderno = $stmt->fetch();
    
    if (!$caderno) {
        http_response_code(404);
        echo json_encode(["erros" => ["Caderno não encontrado"]]);
        exit;
    }
    
    // Verifica se já foi finalizado
    if ($caderno['finalizado_em'] !== null) {
        http_response_code(409);
        echo json_encode(["erros" => ["Este caderno já foi finalizado"]]);
        exit;
    }
    
    // Calcula total de acertos
    $sql = "SELECT 
                COUNT(*) as total_respondidas,
                SUM(CASE WHEN acertou = 1 THEN 1 ELSE 0 END) as total_acertos
            FROM caderno_questao 
            WHERE caderno_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$caderno_id]);
    $resultado = $stmt->fetch();
    
    $total_respondidas = $resultado['total_respondidas'];
    $total_acertos = $resultado['total_acertos'] ?? 0;
    
    // Verifica se todas as 10 questões foram respondidas
    if ($total_respondidas < 10) {
        http_response_code(400);
        echo json_encode([
            "erros" => [
                "Caderno incompleto. Faltam " . (10 - $total_respondidas) . " questões para responder."
            ]
        ]);
        exit;
    }
    
    // Calcula percentual
    $percentual = ($total_acertos / 10) * 100;
    
    // Atualiza caderno
    $sql = "UPDATE caderno 
            SET total_acertos = ?, percentual = ?, finalizado_em = NOW() 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$total_acertos, $percentual, $caderno_id]);
    
    // Retorna resultado final
    echo json_encode([
        "sucesso" => true,
        "caderno_id" => $caderno_id,
        "total_acertos" => $total_acertos,
        "total_questoes" => 10,
        "percentual" => round($percentual, 2),
        "mensagem" => "Caderno finalizado com sucesso!"
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erros" => ["Erro ao finalizar caderno"]]);
    error_log("Erro em finalizar_caderno.php: " . $e->getMessage());
}