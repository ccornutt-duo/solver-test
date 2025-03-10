<?php

use DI\Container;
use DI\Bridge\Slim\Bridge;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Create Container
$container = new Container();

// Set view renderer
$container->set('view', function() {
    return Twig::create(__DIR__ . '/../src/Views', ['cache' => false]);
});

// Create App
$app = Bridge::create($container);

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Register routes
require __DIR__ . '/../config/routes.php';

// Run app
$app->run();
