<?php


namespace VociApi\Controllers;

use VociApi\Core\Request;
use VociApi\Core\Response;
use VociApi\Models\MediaType;

class MediaTypeController
{
    private Request $request;
    private Response $response;
    private MediaType $mediaTypeModel;

    /**
     * Costruttore del controller.
     * @param Request $request L'oggetto Request corrente.
     * @param Response $response L'oggetto Response corrente.
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->mediaTypeModel = new MediaType();
    }

    /**
     * Recupera tutte le tipologie di media.
     */
    public function getAll(): void
    {
        $mediaTypes = $this->mediaTypeModel->getAll();
        $this->response->json($mediaTypes);
    }

    /**
     * Recupera una singola tipologia di media per ID.
     * @param int $id L'ID della tipologia di media.
     */
    public function getById(int $id): void
    {
        $mediaType = $this->mediaTypeModel->getById($id);
        if ($mediaType) {
            $this->response->json($mediaType);
        } else {
            $this->response->error("Tipologia di media non trovata.", 404);
        }
    }

    /**
     * Crea una nuova tipologia di media.
     * Richiede un corpo JSON con la proprietà 'name'.
     */
    public function create(): void
    {
        $body = $this->request->getBody();
        $name = $body['name'] ?? null;

        if (!$name) {
            $this->response->error("Il nome è richiesto.", 400);
            return;
        }

        $newId = $this->mediaTypeModel->create($name);
        if ($newId) {
            $this->response->json(['message' => 'Tipologia di media creata con successo.', 'id' => $newId], 201);
        } else {
            $this->response->error("Errore durante la creazione della tipologia di media. Potrebbe esistere già un nome uguale.", 500);
        }
    }

    /**
     * Aggiorna una tipologia di media esistente.
     * Richiede un corpo JSON con la proprietà 'name'.
     * @param int $id L'ID della tipologia di media da aggiornare.
     */
    public function update(int $id): void
    {
        $body = $this->request->getBody();
        $name = $body['name'] ?? null;

        if (!$name) {
            $this->response->error("Il nome è richiesto.", 400);
            return;
        }

        if (!$this->mediaTypeModel->getById($id)) {
            $this->response->error("Tipologia di media non trovata.", 404);
            return;
        }

        $success = $this->mediaTypeModel->update($id, $name);
        if ($success) {
            $this->response->json(['message' => 'Tipologia di media aggiornata con successo.']);
        } else {
            $this->response->error("Errore durante l'aggiornamento della tipologia di media.", 500);
        }
    }

    /**
     * Elimina una tipologia di media.
     * @param int $id L'ID della tipologia di media da eliminare.
     */
    public function delete(int $id): void
    {
        if (!$this->mediaTypeModel->getById($id)) {
            $this->response->error("Tipologia di media non trovata.", 404);
            return;
        }

        $success = $this->mediaTypeModel->delete($id);
        if ($success) {
            $this->response->json(['message' => 'Tipologia di media eliminata con successo.'], 204);
        } else {
            $this->response->error("Errore durante l'eliminazione della tipologia di media.", 500);
        }
    }
}
