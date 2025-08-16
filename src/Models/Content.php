<?php
// src/Models/Content.php

namespace VociApi\Models;

use VociApi\Config\Database;
use PDO;
use PDOException;

class Content
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Recupera tutti i contenuti, con opzioni di filtro per autrice o nome.
     * @param int|null $authorId L'ID dell'autrice per il filtro.
     * @param string|null $contentName Il nome (o parte del nome) del contenuto per il filtro.
     * @return array Array di contenuti.
     */
    public function getAll(?int $authorId = null, ?string $contentName = null): array
    {
        $sql = "
            SELECT
                c.id,
                c.name,
                c.description,
                mt.name AS media_type_name,
                c.created_at,
                c.updated_at,
                GROUP_CONCAT(DISTINCT CONCAT(a.name, ' ', a.surname) SEPARATOR ', ') AS authors
            FROM
                contents c
            JOIN
                media_types mt ON c.media_type_id = mt.id
            LEFT JOIN
                content_authors ca ON c.id = ca.content_id
            LEFT JOIN
                authors a ON ca.author_id = a.id
        ";

        $whereClauses = [];
        $params = [];

        if ($authorId !== null) {
            $whereClauses[] = "ca.author_id = :author_id";
            $params[':author_id'] = $authorId;
        }

        if ($contentName !== null) {
            $whereClauses[] = "c.name LIKE :content_name";
            $params[':content_name'] = '%' . $contentName . '%';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Recupera un singolo contenuto per ID.
     * @param int $id L'ID del contenuto.
     * @return array|false Il contenuto o false se non trovato.
     */
    public function getById(int $id)
    {
        $sql = "
            SELECT
                c.id,
                c.name,
                c.description,
                mt.name AS media_type_name,
                c.created_at,
                c.updated_at,
                GROUP_CONCAT(DISTINCT CONCAT(a.name, ' ', a.surname) SEPARATOR ', ') AS authors,
                GROUP_CONCAT(DISTINCT a.id) AS author_ids_raw
            FROM
                contents c
            JOIN
                media_types mt ON c.media_type_id = mt.id
            LEFT JOIN
                content_authors ca ON c.id = ca.content_id
            LEFT JOIN
                authors a ON ca.author_id = a.id
            WHERE c.id = :id
            GROUP BY c.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $content = $stmt->fetch();

        if ($content && $content['author_ids_raw']) {
            $content['author_ids'] = array_map('intval', explode(',', $content['author_ids_raw']));
            unset($content['author_ids_raw']);
        } else if ($content) {
            $content['author_ids'] = [];
        }

        return $content;
    }

    /**
     * Crea un nuovo contenuto multimediale e lo associa alle autrici.
     * @param string $name Il nome del contenuto.
     * @param string $description La descrizione del contenuto.
     * @param int $mediaTypeId L'ID della tipologia di media.
     * @param array $authorIds Array di ID delle autrici.
     * @return int|false L'ID del nuovo record inserito o false in caso di errore.
     */
    public function create(string $name, string $description, int $mediaTypeId, array $authorIds = [])
    {
        $this->db->beginTransaction();
        try {
            // Inserisci il contenuto principale
            $stmt = $this->db->prepare("INSERT INTO contents (name, description, media_type_id) VALUES (:name, :description, :media_type_id)");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':media_type_id', $mediaTypeId, PDO::PARAM_INT);
            $stmt->execute();
            $contentId = $this->db->lastInsertId();

            // Associa le autrici
            if ($contentId && !empty($authorIds)) {
                $sqlAuthors = "INSERT INTO content_authors (content_id, author_id) VALUES ";
                $values = [];
                foreach ($authorIds as $authorId) {
                    $values[] = "({$contentId}, {$authorId})";
                }
                $sqlAuthors .= implode(", ", $values);
                $this->db->exec($sqlAuthors); // Usiamo exec perché i valori sono già sanitizzati (int)
            }

            $this->db->commit();
            return (int)$contentId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating content: " . $e->getMessage()); // Logga l'errore per il debug
            return false;
        }
    }

    /**
     * Aggiorna un contenuto multimediale esistente e le sue associazioni con le autrici.
     * @param int $id L'ID del contenuto da aggiornare.
     * @param string $name Il nuovo nome.
     * @param string $description La nuova descrizione.
     * @param int $mediaTypeId La nuova ID della tipologia di media.
     * @param array $authorIds Il nuovo array di ID delle autrici.
     * @return bool True se l'aggiornamento ha avuto successo, false altrimenti.
     */
    public function update(int $id, string $name, string $description, int $mediaTypeId, array $authorIds = []): bool
    {
        $this->db->beginTransaction();
        try {
            // Aggiorna il contenuto principale
            $stmt = $this->db->prepare("UPDATE contents SET name = :name, description = :description, media_type_id = :media_type_id WHERE id = :id");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':media_type_id', $mediaTypeId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Rimuovi le associazioni autrice-contenuto esistenti
            $stmtDelete = $this->db->prepare("DELETE FROM content_authors WHERE content_id = :content_id");
            $stmtDelete->bindParam(':content_id', $id, PDO::PARAM_INT);
            $stmtDelete->execute();

            // Inserisci le nuove associazioni autrice-contenuto
            if (!empty($authorIds)) {
                $sqlAuthors = "INSERT INTO content_authors (content_id, author_id) VALUES ";
                $values = [];
                foreach ($authorIds as $authorId) {
                    $values[] = "({$id}, {$authorId})";
                }
                $sqlAuthors .= implode(", ", $values);
                $this->db->exec($sqlAuthors);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating content: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un contenuto multimediale e le sue associazioni con le autrici.
     * @param int $id L'ID del contenuto da eliminare.
     * @return bool True se l'eliminazione ha avuto successo, false altrimenti.
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            // L'eliminazione in cascata è gestita dalle FOREIGN KEY nel DB (ON DELETE CASCADE)
            // sulla tabella content_authors, ma possiamo essere espliciti per chiarezza.
            $stmtDeleteAuthors = $this->db->prepare("DELETE FROM content_authors WHERE content_id = :content_id");
            $stmtDeleteAuthors->bindParam(':content_id', $id, PDO::PARAM_INT);
            $stmtDeleteAuthors->execute();

            $stmtDeleteContent = $this->db->prepare("DELETE FROM contents WHERE id = :id");
            $stmtDeleteContent->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmtDeleteContent->execute();

            $this->db->commit();
            return $success;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting content: " . $e->getMessage());
            return false;
        }
    }
}
