<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Infrastructure\Config\EnvironmentLoader;
use App\Infrastructure\Config\TimezoneConfigurator;

echo "1) Carregando variáveis de um arquivo .env temporário\n";

$tempFile = tempnam(sys_get_temp_dir(), 'env');
file_put_contents($tempFile, "TESTE_VARIAVEL=ola mundo\nTESTE_NUMERO=42\n");

$loader = new EnvironmentLoader();
$loader->load($tempFile);

unlink($tempFile);

echo sprintf(
    "   TESTE_VARIAVEL = %s | TESTE_NUMERO = %s\n",
    $_ENV['TESTE_VARIAVEL'],
    $_ENV['TESTE_NUMERO'],
);

echo "\n   Arquivo que não existe (deve ser ignorado sem erro)\n";

$loader->load('/caminho/que/nao/existe/.env');

echo "   OK, nenhum erro\n";

echo "\n2) Configurando o fuso horário\n";

$configurator = new TimezoneConfigurator();

foreach (['America/Sao_Paulo', 'UTC', 'Marte/Olimpo'] as $timezone) {
    try {
        $configurator->configure($timezone);

        echo sprintf(
            "   \"%s\" -> fuso ativo: %s | agora: %s\n",
            $timezone,
            date_default_timezone_get(),
            (new DateTimeImmutable())->format('Y-m-d H:i:s T'),
        );
    } catch (InvalidArgumentException $exception) {
        echo sprintf("   \"%s\" -> RECUSADO: %s\n", $timezone, $exception->getMessage());
    }
}