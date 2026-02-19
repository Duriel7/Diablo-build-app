<?php
namespace Diablo\Data;
use PDO;
use PDOException;

class DataBaseConnection{
    private static ?PDO $instance = null;
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            // Load environment variables
            $dbhostname = $_ENV['DB_HOSTNAME'] ?? "db";
            $dbname = $_ENV['DB_NAME'] ?? "diablo";
            $dbusername = $_ENV['DB_USERNAME'] ?? "diablo";
            $dbpassword = $_ENV['DB_PASSWORD'] ?? null;
            $dbport = $_ENV['DB_PORT'] ?? 3306;

            // Create DSN string
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $dbhostname, $dbport, $dbname
            );
            
            // Options for PDO connection
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Attempt to create a PDO instance
            try{
                self::$instance = new PDO(
                    $dsn,
                    $dbusername,
                    $dbpassword,
                    $options
                );
            }catch (PDOException $error){
                throw new \PDOException('Database connection failed because of: ' . $error->getMessage(), (int)$error->getCode());
            }
        }
        
        return self::$instance;
    }
}