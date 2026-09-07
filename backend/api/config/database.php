<?php
class Database {
    private string $driver = 'mysql';
    private string $host = 'localhost';
    private string $port = '3306';
    private string $db_name = 'el_maestro';
    private string $username = 'root';
    private string $password = '';
    private string $sslmode = 'prefer';
    public ?PDO $conn = null;

    public function __construct() {
        $this->loadEnv();
        $this->configure();
    }

    private function loadEnv(): void {
        $paths = [
            __DIR__ . '/../.env',
            __DIR__ . '/../../.env'
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || strpos($line, '#') === 0 || !str_contains($line, '=')) continue;
                    $parts = explode('=', $line, 2);
                    if (count($parts) === 2) {
                        $key = trim($parts[0]);
                        $val = trim($parts[1], " \t\n\r\0\x0B\"'");
                        $_ENV[$key] = $val;
                        putenv("$key=$val");
                    }
                }
                break;
            }
        }
    }

    private function configure(): void {
        // 1. Support direct DATABASE_URL (Supabase / Neon / Render / Railway)
        $databaseUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: null;
        
        if ($databaseUrl) {
            $parsed = parse_url($databaseUrl);
            if ($parsed) {
                $scheme = strtolower($parsed['scheme'] ?? 'mysql');
                if (str_starts_with($scheme, 'postgres')) {
                    $this->driver = 'pgsql';
                    $this->port = '5432';
                    $this->sslmode = 'require';
                } else {
                    $this->driver = 'mysql';
                    $this->port = '3306';
                }

                $this->host = $parsed['host'] ?? 'localhost';
                if (isset($parsed['port'])) {
                    $this->port = (string)$parsed['port'];
                }
                $this->username = isset($parsed['user']) ? urldecode($parsed['user']) : '';
                $this->password = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
                $this->db_name = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';

                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $queryParams);
                    if (isset($queryParams['sslmode'])) {
                        $this->sslmode = $queryParams['sslmode'];
                    }
                }
                return;
            }
        }

        // 2. Individual Environment Variables
        $this->driver = strtolower($_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'mysql');
        if ($this->driver === 'postgres' || $this->driver === 'postgresql') {
            $this->driver = 'pgsql';
        }

        $this->host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $defaultPort = ($this->driver === 'pgsql') ? '5432' : '3306';
        $this->port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: $defaultPort;
        $this->db_name = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?: 'el_maestro';
        $this->username = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $this->password = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?: '';
        $this->sslmode = $_ENV['DB_SSLMODE'] ?? $_SERVER['DB_SSLMODE'] ?? getenv('DB_SSLMODE') ?: ($this->driver === 'pgsql' ? 'require' : 'prefer');
    }

    public function getConnection(): ?PDO {
        $this->conn = null;
        try {
            if ($this->driver === 'pgsql') {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};sslmode={$this->sslmode}";
                $this->conn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } else {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
            }
        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
