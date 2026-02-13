<?php
namespace Diablo\Data;
use PDO;
use PDOException;

class DataBaseConnection{
    public static function getInstance(): PDO{
        // Load environment variables
        $dbhostname = $_ENV['DB_HOST'] ?? "mariadb:10.11";
        $dbname = $_ENV['DB_NAME'] ?? "diablo";
        $dbusername = $_ENV['DB_USERNAME'] ?? "diablo";
        $dbpassword = $_ENV['DB_PASSWORD'] ?? null;
        $dbport = $_ENV['DB_PORT'] ?? 3306;

        // Create DSN string
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
            $dbhostname, $dbport, $dbname
        );
        
        // Attempt to create a PDO instance
        try{
            $instance = new PDO(
                dsn: $dsn,
                username: $dbusername,
                password: $dbpassword
            );

            return $instance;
        }catch (PDOException $error){
            throw new \PDOException('Database connection failed because of: ' . $error->getMessage(), (int)$error->getCode());
        }
    }
}