<?php

$host = '127.0.0.1';
$db   = 'arara_azul';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lança erro real
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // retorna array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // usa prepared real
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro na conexão com o banco.");
}
