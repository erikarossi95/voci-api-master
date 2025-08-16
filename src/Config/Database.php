<?php
// src/Config/Database.php

namespace VociApi\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    /**
     * Ottiene l'istanza PDO per la connessione al database.
     * @return PDO L'istanza PDO connessa al database.
     * @throws PDOException Se la connessione al database fallisce.
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbName = getenv('DB_NAME') ?: 'voci_db';
            $dbUser = getenv('DB_USER') ?: 'root';
            $dbPass = getenv('DB_PASS') ?: '';

            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $dbUser, $dbPass, $options);
            } catch (PDOException $e) {
                die("Errore di connessione al database: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
