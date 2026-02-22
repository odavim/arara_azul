<?php
/**
 * API: Criar Novo Caderno
 * Método: POST
 * Descrição: Cria um novo caderno com 10 questões aleatórias de um tópico
 * 
 * Parâmetros (JSON ou Form Data):
 * - usuario_id (int, obrigatório): ID do usuário
 * - topico_id (int, obrigatório): ID do tópico
 * 
 * Retorno:
 * - 201: Caderno criado com sucesso (retorna ID do caderno)
 * - 400: Erro de validação
 * - 404: Tópico não encontrado ou sem questões suficientes
 * - 500: Erro no servidor
 */

header("Content-Type: application/json");
require '../config/conexao.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erros" => ["Método não permitido. Use POST."]]);
    exit;
}

try {
    $erros = [];
    
    // Pega dados do corpo da requisição (JSON ou Form Data)
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST; // fallback para form-data
    }
    
    // Valida usuario_id
    if (!isset($input['usuario_id'])) {
        $erros[] = "usuario_id é obrigatório";
    } else {
        $usuario_id = filter_var($input['usuario_id'], FILTER_VALIDATE_INT);
        if ($usuario_id === false || $usuario_id <= 0) {
            $erros[] = "usuario_id deve ser um número inteiro válido";
        }
    }
    
    // Valida topico_id
    if (!isset($input['topico_id'])) {
        $erros[] = "topico_id é obrigatório";
    } else {
        $topico_id = filter_var($input['topico_id'], FILTER_VALIDATE_INT);
        if ($topico_id === false || $topico_id <= 0) {
            $erros[] = "topico_id deve ser um número inteiro válido";
        }
    }
    
    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verifica se o tópico existe
    $stmt = $pdo->prepare("SELECT id FROM topico WHERE id = ?");
    $stmt->execute([$topico_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(["erros" => ["Tópico não encontrado"]]);
        exit;
    }
    
    // Verifica se existem pelo menos 10 questões no tópico
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM questao WHERE topico_id = ?");
    $stmt->execute([$topico_id]);
    $total_questoes = $stmt->fetch()['total'];
    
    if ($total_questoes < 10) {
        http_response_code(400);
        echo json_encode(["erros" => ["Tópico tem apenas $total_questoes questões. São necessárias 10."]]);
        exit;
    }
    
    // Inicia transação
    $pdo->beginTransaction();
    
    // 1. Cria o caderno
    $sql = "INSERT INTO caderno (usuario_id, topico_id, total_questoes, criado_em) 
            VALUES (?, ?, 10, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $topico_id]);
    $caderno_id = $pdo->lastInsertId();
    
    // 2. Seleciona 10 questões aleatórias DISTINTAS do tópico
    $sql = "SELECT id FROM questao 
            WHERE topico_id = ? 
            ORDER BY RAND() 
            LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$topico_id]);
    $questoes = $stmt->fetchAll();
    
    // 3. Insere cada questão no caderno_questao
    $sql = "INSERT INTO caderno_questao (caderno_id, questao_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($questoes as $questao) {
        $stmt->execute([$caderno_id, $questao['id']]);
    }
    
    // Confirma transação
    $pdo->commit();
    
    // Retorna sucesso
    http_response_code(201);
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Caderno criado com sucesso",
        "caderno_id" => $caderno_id,
        "total_questoes" => 10
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // Em caso de erro, desfaz transação
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(["erros" => ["Erro ao criar caderno"]]);
    error_log("Erro em criar_caderno.php: " . $e->getMessage());
}