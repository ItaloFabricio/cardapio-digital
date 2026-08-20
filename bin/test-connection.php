<?php

declare(strict_types=1);

/**
 * Script de DIAGNOSTICO, isolado de proposito - nao usa Composer nem
 * as classes do projeto. Serve so para confirmar que o PHP consegue
 * se conectar ao MySQL antes de testarmos a arquitetura completa.
 */

$host = '127.0.0.1';
$port = 3306;
$database = 'cardapio_digital';
$username = 'root';
$password = '';

echo "Tentando conectar em {$host}:{$port}, banco '{$database}'..." . PHP_EOL;

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
    );

    echo 'Conectado com sucesso!' . PHP_EOL;

    $statement = $pdo->query('SHOW TABLES');
    $tables = $statement->fetchAll(PDO::FETCH_COLUMN);

    if ($tables === []) {
        echo 'Nenhuma tabela encontrada no banco ainda.' . PHP_EOL;
    } else {
        echo 'Tabelas encontradas: ' . implode(', ', $tables) . PHP_EOL;
    }
} catch (PDOException $exception) {
    echo 'FALHA na conexão: ' . $exception->getMessage() . PHP_EOL;
}