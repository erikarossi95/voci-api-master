<?php
// src/Core/Request.php

namespace VociApi\Core;

class Request
{
    /**
     * Ottiene il metodo HTTP della richiesta.
     * @return string Il metodo HTTP (GET, POST, PUT, DELETE).
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Ottiene l'URI della richiesta, rimuovendo il path base dell'applicazione.
     * @return string L'URI della richiesta pulita.
     */
    public function getUri(): string
    {
        // Ottiene l'URI completa della richiesta, escludendo i parametri della query
        $uri = strtok($_SERVER['REQUEST_URI'], '?');

        // Ottiene il nome dello script corrente (es. /voci-api/public/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'];

        // Estrae il percorso base dell'applicazione dal nome dello script (es. /voci-api/public)
        $basePath = dirname($scriptName);

        // Se l'URI inizia con il percorso base, lo rimuove
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Normalizza l'URI: assicura che inizi con una singola slash e rimuove slash finali extra
        $uri = '/' . trim($uri, '/');
        
        // Gestisce il caso in cui l'URI pulita risulti in "//" o sia vuota (per la root)
        return ($uri === '//' || $uri === '') ? '/' : $uri;
    }

    /**
     * Ottiene i dati del corpo della richiesta (per POST, PUT).
     * @return array I dati del corpo della richiesta come array associativo.
     */
    public function getBody(): array
    {
        $body = [];
        if ($this->getMethod() === 'POST' || $this->getMethod() === 'PUT') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $body = $data;
            } else {
                // Fallback per corpi non JSON (es. form-urlencoded)
                $body = $_POST;
            }
        }
        return $body;
    }

    /**
     * Ottiene i parametri della query string (per GET).
     * @return array I parametri della query string come array associativo.
     */
    public function getQueryParams(): array
    {
        return $_GET;
    }
}
