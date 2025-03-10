<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Controllers\PostController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

// Home route
$app->get('/', function ($request, $response) {
    return $this->get('view')->render($response, 'home.twig');
})->setName('home');

// Auth routes
$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->get('/register', [AuthController::class, 'registerPage'])->setName('auth.register');
    $group->post('/register', [AuthController::class, 'register']);
    $group->get('/login', [AuthController::class, 'loginPage'])->setName('auth.login');
    $group->post('/login', [AuthController::class, 'login']);
    $group->get('/logout', [AuthController::class, 'logout'])->setName('auth.logout');
});

// Authenticated routes
$app->group('', function (RouteCollectorProxy $group) {
    // Posts
    $group->get('/posts', [PostController::class, 'index'])->setName('posts.index');
    $group->post('/posts', [PostController::class, 'create'])->setName('posts.create');
    $group->post('/posts/{id}/like', [PostController::class, 'like'])->setName('posts.like');
    $group->post('/posts/{id}/unlike', [PostController::class, 'unlike'])->setName('posts.unlike');
    
    // User profile
    $group->get('/profile', [UserController::class, 'profile'])->setName('user.profile');
    $group->get('/users/{username}', [UserController::class, 'show'])->setName('user.show');
    $group->post('/users/{username}/follow', [UserController::class, 'follow'])->setName('user.follow');
    $group->post('/users/{username}/unfollow', [UserController::class, 'unfollow'])->setName('user.unfollow');
})->add(new AuthMiddleware());
