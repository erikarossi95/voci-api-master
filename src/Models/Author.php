<?php
// src/Models/Author.php

namespace VociApi\Models;

use VociApi\Config\Database;
use PDO;
use PDOException;

class Author
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Recupera tutte le autrici.
     * @return array Array di autrici.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM authors");
        return $stmt->fetchAll();
    }

    /**
     * Recupera una singola autrice per ID.
     * @param int $id L'ID dell'autrice.
     * @return array|false L'autrice o false se non trovata.
     */
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM authors WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crea una nuova autrice.
     * @param string $name Il nome dell'autrice.
     * @param string $surname Il cognome dell'autrice.
     * @return int|false L'ID del nuovo record inserito o false in caso di errore.
     */
    public function create(string $name, string $surname)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO authors (name, surname) VALUES (:name, :surname)");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aggiorna un'autrice esistente.
     * @param int $id L'ID dell'autrice da aggiornare.
     * @param string $name Il nuovo nome.
     * @param string $surname Il nuovo cognome.
     * @return bool True se l'aggiornamento ha avuto successo, false altrimenti.
     */
    public function update(int $id, string $name, string $surname): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE authors SET name = :name, surname = :surname WHERE id = :id");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un'autrice per ID.
     * @param int $id L'ID dell'autrice da eliminare.
     * @return bool True se l'eliminazione ha avuto successo, false altrimenti.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM authors WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
