<?php

class DBWorker
{
    private string $dbPath = __DIR__ . "/../database/database.sqlite";
    private ?PDO $conn = null;

    public function __construct(?string $dbPath)
    {
        if (!is_null($dbPath)) {
            $this->dbPath = realpath($dbPath);
        }

        try {
            $this->conn = new PDO("sqlite:" . $this->dbPath);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        $this->setup();
    }

    /**
     * Возвращает коннектор
     */
    public function getConnection(): PDO
    {
        return $this->conn;
    }


    /**
     * Выполняет запрос к БД с защитой от инъекций
     */
    public function execute(string $sql, array $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }


    /**
     * Создаёт необходимую таблицу в БД, если она пустая
     */
    private function setup(): void
    {
        $sqlExp = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            name STRING NOT NULL,
            phone STRING NOT NULL UNIQUE,
            email STRING NOT NULL UNIQUE,
            password text NOT NULL
            )
            ";
        $this->execute($sqlExp);
    }
}