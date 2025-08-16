<?php
// src/Core/Response.php

namespace VociApi\Core;

class Response
{
    /**
     * Invia una risposta JSON.
     * @param mixed $data I dati da includere nella risposta.
     * @param int $statusCode Il codice di stato HTTP da impostare.
     */
    public function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Invia una risposta di errore JSON.
     * @param string $message Il messaggio di errore.
     * @param int $statusCode Il codice di stato HTTP (di solito 4xx o 5xx).
     */
    public function error(string $message, int $statusCode): void
    {
        $this->json(['error' => $message], $statusCode);
    }
}
