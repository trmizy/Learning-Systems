<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv; // Ensure Dotenv class is available

// Load .env if present but don't throw if missing (safeLoad)
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Helper to read environment variables with fallbacks
function env_var(string $key, $default = null) {
    if (isset($_ENV[$key])) return $_ENV[$key];
    $val = getenv($key);
    return $val !== false ? $val : $default;
}

// Define DB constants with safe fallbacks to avoid fatal errors when .env is missing
define('DB_HOST', env_var('DB_HOST', '127.0.0.1'));
define('DB_NAME', env_var('DB_NAME', 'learning_systems'));
define('DB_USER', env_var('DB_USER', 'root'));
define('DB_PASS', env_var('DB_PASS', ''));

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    private function __clone() {}   // Ngăn clone object
    public function __wakeup() {}   // Ngăn unserialize object
}
