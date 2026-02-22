<?php

header("Content-Type: application/json");
require '../config/conexao.php';

try {
    $sql = "SELECT id, nome FROM materia ORDER BY nome ASC";
    $stmt = $pdo->query($sql);
    $materias = $stmt->fetchAll();

    echo json_encode($materias, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao buscar matérias"
    ]);
};