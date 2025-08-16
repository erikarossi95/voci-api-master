<?php

namespace VociApi\Controllers;

use VociApi\Core\Request;
use VociApi\Core\Response;
use VociApi\Models\Content;
use VociApi\Models\MediaType; // Per la validazione della tipologia
use VociApi\Models\Author;    // Per la validazione delle autrici

class ContentController
{
    private Request $request;
    private Response $response;
    private Content $contentModel;
    private MediaType $mediaTypeModel;
    private Author $authorModel;

    /**
     * Costruttore del controller.
     * @param Request $request L'oggetto Request corrente.
     * @param Response $response L'oggetto Response corrente.
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->contentModel = new Content();
        $this->mediaTypeModel = new MediaType(); // Inizializza per la validazione
        $this->authorModel = new Author();       // Inizializza per la validazione
    }

    /**
     * Recupera tutti i contenuti, con opzioni di filtro.
     * Accetta parametri query 'author_id' e 'content_name'.
     */
    public function getAll(): void
    {
        $queryParams = $this->request->getQueryParams();
        $authorId = $queryParams['author_id'] ?? null;
        $contentName = $queryParams['content_name'] ?? null;

        // Assicurati che authorId sia un intero se presente
        if ($authorId !== null) {
            $authorId = (int) $authorId;
        }

        $contents = $this->contentModel->getAll($authorId, $contentName);
        $this->response->json($contents);
    }

    /**
     * Recupera un singolo contenuto per ID.
     * @param int $id L'ID del contenuto.
     */
    public function getById(int $id): void
    {
        $content = $this->contentModel->getById($id);
        if ($content) {
            $this->response->json($content);
        } else {
            $this->response->error("Contenuto non trovato.", 404);
        }
    }

    /**
     * Crea un nuovo contenuto multimediale.
     * Richiede un corpo JSON con 'name', 'description', 'media_type_id' e 'author_ids'.
     */
    public function create(): void
    {
        $body = $this->request->getBody();
        $name = $body['name'] ?? null;
        $description = $body['description'] ?? null;
        $mediaTypeId = $body['media_type_id'] ?? null;
        $authorIds = $body['author_ids'] ?? []; // Array di ID delle autrici

        if (!$name || !$description || !$mediaTypeId) {
            $this->response->error("Nome, descrizione e tipologia di media sono richiesti.", 400);
            return;
        }

        // Validazione della tipologia di media
        if (!$this->mediaTypeModel->getById((int) $mediaTypeId)) {
            $this->response->error("Tipologia di media non valida.", 400);
            return;
        }

        // Validazione degli ID delle autrici (opzionale ma consigliata)
        if (!is_array($authorIds)) {
            $this->response->error("Il campo 'author_ids' deve essere un array di ID.", 400);
            return;
        }
        foreach ($authorIds as $authorId) {
            if (!is_numeric($authorId) || !$this->authorModel->getById((int) $authorId)) {
                $this->response->error("Uno o più ID delle autrici non sono validi.", 400);
                return;
            }
        }

        $newId = $this->contentModel->create($name, $description, (int) $mediaTypeId, array_map('intval', $authorIds));
        if ($newId) {
            $this->response->json(['message' => 'Contenuto creato con successo.', 'id' => $newId], 201);
        } else {
            $this->response->error("Errore durante la creazione del contenuto.", 500);
        }
    }

    /**
     * Aggiorna un contenuto multimediale esistente.
     * Richiede un corpo JSON con 'name', 'description', 'media_type_id' e 'author_ids'.
     * @param int $id L'ID del contenuto da aggiornare.
     */
    public function update(int $id): void
    {
        $body = $this->request->getBody();
        $name = $body['name'] ?? null;
        $description = $body['description'] ?? null;
        $mediaTypeId = $body['media_type_id'] ?? null;
        $authorIds = $body['author_ids'] ?? [];

        if (!$name || !$description || !$mediaTypeId) {
            $this->response->error("Nome, descrizione e tipologia di media sono richiesti.", 400);
            return;
        }

        if (!$this->contentModel->getById($id)) {
            $this->response->error("Contenuto non trovato.", 404);
            return;
        }

        // Validazione della tipologia di media
        if (!$this->mediaTypeModel->getById((int) $mediaTypeId)) {
            $this->response->error("Tipologia di media non valida.", 400);
            return;
        }

        // Validazione degli ID delle autrici
        if (!is_array($authorIds)) {
            $this->response->error("Il campo 'author_ids' deve essere un array di ID.", 400);
            return;
        }
        foreach ($authorIds as $authorId) {
            if (!is_numeric($authorId) || !$this->authorModel->getById((int) $authorId)) {
                $this->response->error("Uno o più ID delle autrici non sono validi.", 400);
                return;
            }
        }

        $success = $this->contentModel->update($id, $name, $description, (int) $mediaTypeId, array_map('intval', $authorIds));
        if ($success) {
            $this->response->json(['message' => 'Contenuto aggiornato con successo.']);
        } else {
            $this->response->error("Errore durante l'aggiornamento del contenuto.", 500);
        }
    }

    /**
     * Elimina un contenuto multimediale.
     * @param int $id L'ID del contenuto da eliminare.
     */
    public function delete(int $id): void
    {
        if (!$this->contentModel->getById($id)) {
            $this->response->error("Contenuto non trovato.", 404);
            return;
        }

        $success = $this->contentModel->delete($id);
        if ($success) {
            $this->response->json(['message' => 'Contenuto eliminato con successo.'], 204);
        } else {
            $this->response->error("Errore durante l'eliminazione del contenuto.", 500);
        }
    }
}
