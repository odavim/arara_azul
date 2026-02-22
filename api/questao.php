<?php

header("Content-Type: application/json");
require '../config/conexao.php';

try {
    if(!isset($_GET['topico_id'])) {
        http_response_code(400);
        echo json_encode(["erro"=>"topico_id é obrigatório" ], JSON_UNESCAPED_UNICODE);
     }
exit;

    $sql = "SELECT id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e FROM questoes WHERE topico_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['topico_id']]);
    $questao = $stmt->fetchAll();

    echo json_encode($questao, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao buscar tópicos"
    ]);
};