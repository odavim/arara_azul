<?php

header("Content-Type: application/json");
require '../config/conexao.php';

try {
    $erros = [];
    
    if (!isset($_GET['topico_id'])) {
        $erros[] = "topico_id é obrigatório";
    } else {
        $topico_id = filter_input(INPUT_GET, 'topico_id', FILTER_VALIDATE_INT);
        if ($topico_id === false || $topico_id <= 0) {
            $erros[] = "topico_id deve ser um número inteiro válido";
        }
    }

    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }
        
    $sql = "SELECT id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e 
            FROM questao 
            WHERE topico_id = ? 
            ORDER BY RAND() 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$topico_id]);
    $questao = $stmt->fetch();
    
    if (!$questao) {
        http_response_code(404);
        echo json_encode(["erro" => "Nenhuma questão encontrada para este tópico"]);
        exit;
    }
    
    echo json_encode($questao, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao buscar questão"
    ]);
    error_log("Erro em questao.php: " . $e->getMessage());
}