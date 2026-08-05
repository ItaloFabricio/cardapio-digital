<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Connection;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Responsavel exclusivamente por criar uma conexao PDO configurada.
 *
 * Por que uma Factory e nao um Singleton?
 * Um Singleton esconderia estado global e dificultaria testes (nao daria
 * para injetar uma conexao "fake" em um teste unitario sem truques).
 * A Factory apenas fabrica o objeto; quem controla o ciclo de vida da
 * conexao e quem a recebe via injecao de dependencia.
 *
 * SRP: esta classe faz uma unica coisa - montar a string DSN e abrir
 * a conexao. Nao formata dados, nao executa query, nao sabe de negocio.
 */
final class ConnectionFactory
{
    public function __construct(
        private readonly string $host,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly int $port = 3306,
        private readonly string $charset = 'utf8mb4',
    ) {
    }

    /**
     * Cria a partir das variaveis de ambiente ($_ENV), mantendo o
     * conhecimento de "como ler configuracao" fora do restante do sistema.
     */
    public static function fromEnvironment(): self
    {
        return new self(
            host: $_ENV['DB_HOST'] ?? '127.0.0.1',
            database: $_ENV['DB_DATABASE'] ?? '',
            username: $_ENV['DB_USERNAME'] ?? 'root',
            password: $_ENV['DB_PASSWORD'] ?? '',
            port: (int) ($_ENV['DB_PORT'] ?? 3306),
        );
    }

    public function connect(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->charset,
        );

        try {
            return new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            // Nunca expomos a mensagem original da PDOException ao cliente
            // (ela pode conter host, usuario ou detalhes da infraestrutura).
            throw new RuntimeException('Nao foi possivel conectar ao banco de dados.', previous: $exception);
        }
    }
}
