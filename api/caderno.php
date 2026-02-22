<?php

header("Content-Type: application/json");
require '../config/conexao.php';

try {
    $erros = [];

    if (!isset($_GET['usuario_id'])) {
        $erros[] = "usuario_id é obrigatório";
    } else {
        $usuario_id = filter_input(INPUT_GET, 'usuario_id', FILTER_VALIDATE_INT);
        if ($usuario_id === false || $usuario_id <= 0) {
            $erros[] = "usuario_id deve ser um número inteiro válido";
        }
    }

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

    $sql = "SELECT id, usuario_id, topico_id, total_questoes, total_acertos, percentual,
                   criado_em, finalizado_em
            FROM caderno 
            WHERE usuario_id = ? AND topico_id = ?
            ORDER BY criado_em DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $topico_id]);
    $cadernos = $stmt->fetchAll();

    if (empty($cadernos)) {
        http_response_code(404);
        echo json_encode(["mensagem" => "Nenhum caderno encontrado para este usuário e tópico"]);
        exit;
    }

    echo json_encode($cadernos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar cadernos"]);
    error_log("Erro em caderno.php: " . $e->getMessage());
}