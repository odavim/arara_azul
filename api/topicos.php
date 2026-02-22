<?php

header("Content-Type: application/json");
require '../config/conexao.php';

try {
    $erros = [];
    
    if (!isset($_GET['materia_id'])) {
        $erros[] = "materia_id é obrigatório";
    } else {
        $materia_id = filter_input(INPUT_GET, 'materia_id', FILTER_VALIDATE_INT);
        if ($materia_id === false || $materia_id <= 0) {
            $erros[] = "materia_id deve ser um número inteiro válido";
        }
    }

    if (!empty($erros)) {
        http_response_code(400);
        echo json_encode(["erros" => $erros], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "SELECT id, nome FROM topico WHERE materia_id = ? ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$materia_id]);
    $topicos = $stmt->fetchAll();

    if (empty($topicos)) {
        http_response_code(404);
        echo json_encode(["mensagem" => "Nenhum tópico encontrado para esta matéria"]);
        exit;
    }

    echo json_encode($topicos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao buscar tópicos"
    ]);
    error_log("Erro em topicos.php: " . $e->getMessage());
}