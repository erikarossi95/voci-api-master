<?php


namespace VociApi\Controllers;

use VociApi\Core\Request;
use VociApi\Core\Response;
use VociApi\Models\Author;

class AuthorController
{
    private Request $request;
    private Response $response;
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
        $this->authorModel = new Author();
    }

    /**
     * Recupera tutte le autrici.
     */
    public function getAll(): void
    {
        $authors = $this->authorModel->getAll();
        $this->response->json($authors);
    }

    /**
     * Recupera una singola autrice per ID.
     * @param int $id L'ID dell'autrice.
     */
    public function getById(int $id): void
    {
        $author = $this->authorModel->getById($id);
        if ($author) {
            $this->response->json($author);
        } else {
            $this->response->error("Autrice non trovata.", 404);
        }
    }

    /**
     * Crea una nuova autrice.
     * Richiede un corpo JSON con le proprietà 'name' e 'surname'.
     */
    public function create(): void
    {
        $body = $this->request->getBody();
        $name = $body['name'] ?? null;
        $surname = $body['surname'] ?? null;

        if (!$name || !$surname) {
            $this->response->error("Nome e cognome sono richiesti.", 400);
            return;
        }

        $newId = $this->authorModel->create($name, $surname);
        if ($newId) {
            $this->response->json(['message' => 'Autrice creata con successo.', 'id' => $newId], 201);
        } else {
            $this->response->error("Errore durante la creazione dell'autrice.", 500);
        }
    }

    /**
     * Aggiorna un'autrice esistente.
     * Richiede un corpo JSON con le proprietà 'name' e 'surname'.
     * @param int $id L'ID dell'autrice da aggiornare.
     */
    public function update(int $id): void
    {
        $body = $this->request->getBody();
        $name = $body['name'] ?? null;
        $surname = $body['surname'] ?? null;

        if (!$name || !$surname) {
            $this->response->error("Nome e cognome sono richiesti.", 400);
            return;
        }

        if (!$this->authorModel->getById($id)) {
            $this->response->error("Autrice non trovata.", 404);
            return;
        }

        $success = $this->authorModel->update($id, $name, $surname);
        if ($success) {
            $this->response->json(['message' => 'Autrice aggiornata con successo.']);
        } else {
            $this->response->error("Errore durante l'aggiornamento dell'autrice.", 500);
        }
    }

    /**
     * Elimina un'autrice.
     * @param int $id L'ID dell'autrice da eliminare.
     */
    public function delete(int $id): void
    {
        if (!$this->authorModel->getById($id)) {
            $this->response->error("Autrice non trovata.", 404);
            return;
        }

        $success = $this->authorModel->delete($id);
        if ($success) {
            $this->response->json(['message' => 'Autrice eliminata con successo.'], 204);
        } else {
            $this->response->error("Errore durante l'eliminazione dell'autrice.", 500);
        }
    }
}
