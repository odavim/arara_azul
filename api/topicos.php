<?php

header("Content-Type: application/json");
require '../config/conexao.php';

try {
    if(!isset($_GET['materia_id'])) {
        http_response_code(400);
        echo json_encode(["erro"=>"materia_id é obrigatório" ], JSON_UNESCAPED_UNICODE);
     }
exit;

    $sql = "SELECT id, nome FROM topicos WHERE materia_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['materia_id']]);
    $topicos = $stmt->fetchAll();

    echo json_encode($topicos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao buscar tópicos"
    ]);
};