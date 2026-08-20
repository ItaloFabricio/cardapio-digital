<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Connection\ConnectionFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

$pdo = ConnectionFactory::fromEnvironment()->connect();

$migrationsPath = dirname(__DIR__) . '/src/Infrastructure/Migration';
$migrationFiles = glob($migrationsPath . '/*.sql');
sort($migrationFiles);

if ($migrationFiles === []) {
    echo "Nenhuma migration encontrada em {$migrationsPath}." . PHP_EOL;
    exit(0);
}

foreach ($migrationFiles as $file) {
    $name = basename($file);
    echo "Executando: {$name} ... ";

    $sql = file_get_contents($file);

    try {
        $pdo->exec($sql);
        echo "OK" . PHP_EOL;
    } catch (\PDOException $exception) {
        echo "FALHOU" . PHP_EOL;
        echo "Erro: " . $exception->getMessage() . PHP_EOL;
        exit(1);
    }
}

echo PHP_EOL . 'Todas as migrations foram executadas com sucesso.' . PHP_EOL;