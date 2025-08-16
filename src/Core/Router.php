<?php
// src/Core/Router.php

namespace VociApi\Core;

class Router
{
    protected array $routes = [];
    protected Request $request;
    protected Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Registra una rotta GET.
     * @param string $path Il percorso URI.
     * @param mixed $callback La funzione o il controller/metodo da eseguire.
     */
    public function get(string $path, $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Registra una rotta POST.
     * @param string $path Il percorso URI.
     * @param mixed $callback La funzione o il controller/metodo da eseguire.
     */
    public function post(string $path, $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Registra una rotta PUT.
     * @param string $path Il percorso URI.
     * @param mixed $callback La funzione o il controller/metodo da eseguire.
     */
    public function put(string $path, $callback): void
    {
        $this->routes['PUT'][$path] = $callback;
    }

    /**
     * Registra una rotta DELETE.
     * @param string $path Il percorso URI.
     * @param mixed $callback La funzione o il controller/metodo da eseguire.
     */
    public function delete(string $path, $callback): void
    {
        $this->routes['DELETE'][$path] = $callback;
    }

    /**
     * Risolve la richiesta in entrata.
     */
    public function resolve(): void
    {
        $method = $this->request->getMethod();
        $uri = $this->request->getUri();

        $callback = false;
        $params = [];

        foreach ($this->routes[$method] ?? [] as $routePath => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(\d+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $callback = $handler;
                array_shift($matches);
                $params = $matches;
                break;
            }
        }

        if ($callback === false) {
            $this->response->error("Endpoint non trovato o metodo non consentito.", 404);
        }

        if (is_array($callback)) {
            $controllerClass = $callback[0];
            $methodName = $callback[1];

            if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
                $controller = new $controllerClass($this->request, $this->response);
                call_user_func_array([$controller, $methodName], $params);
            } else {
                $this->response->error("Errore interno del server: controller o metodo non valido.", 500);
            }
        } else if (is_callable($callback)) {
            call_user_func_array($callback, $params);
        } else {
            $this->response->error("Errore interno del server: callback non gestibile.", 500);
        }
    }
}
