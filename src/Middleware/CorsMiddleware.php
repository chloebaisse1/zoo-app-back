<?php
namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CorsMiddleware
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Si la requête est une requête OPTIONS (preflight), renvoyer une réponse 200 directement
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Access-Control-Allow-Origin', '*'); // Autoriser toutes les origines pour le test
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, x-auth-token');
            $response->headers->set('Access-Control-Max-Age', '3600'); // Optionnel, durée de validité du preflight
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // Appliquer les en-têtes CORS à toutes les réponses
        $response->headers->set('Access-Control-Allow-Origin', '*'); // Change '*' par 'http://127.0.0.1:3307' en production si nécessaire
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, x-auth-token');

        // Vérifier si la méthode est OPTIONS pour éviter d'interférer avec d'autres requêtes
        if ($request->getMethod() === 'OPTIONS') {
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, x-auth-token');
        }
    }
}