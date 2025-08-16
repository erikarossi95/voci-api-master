<?php
// src/Models/MediaType.php

namespace VociApi\Models;

use VociApi\Config\Database;
use PDO;
use PDOException;

class MediaType
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Recupera tutte le tipologie di media.
     * @return array Array di tipologie di media.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM media_types");
        return $stmt->fetchAll();
    }

    /**
     * Recupera una singola tipologia di media per ID.
     * @param int $id L'ID della tipologia di media.
     * @return array|false La tipologia di media o false se non trovata.
     */
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM media_types WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crea una nuova tipologia di media.
     * @param string $name Il nome della tipologia di media.
     * @return int|false L'ID del nuovo record inserito o false in caso di errore.
     */
    public function create(string $name)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO media_types (name) VALUES (:name)");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aggiorna una tipologia di media esistente.
     * @param int $id L'ID della tipologia di media da aggiornare.
     * @param string $name Il nuovo nome.
     * @return bool True se l'aggiornamento ha avuto successo, false altrimenti.
     */
    public function update(int $id, string $name): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE media_types SET name = :name WHERE id = :id");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina una tipologia di media per ID.
     * @param int $id L'ID della tipologia di media da eliminare.
     * @return bool True se l'eliminazione ha avuto successo, false altrimenti.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM media_types WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
