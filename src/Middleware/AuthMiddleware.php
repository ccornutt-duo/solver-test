<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        if (!isset($_SESSION['user'])) {
            $response = new Response();
            return $response
                ->withHeader('Location', $routeParser->urlFor('auth.login'))
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}
