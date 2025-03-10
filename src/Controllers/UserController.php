<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserController
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function profile(Request $request, Response $response)
    {
        $user = User::with(['posts' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($_SESSION['user']);
        
        return $this->view->render($response, 'users/profile.twig', [
            'user' => $user,
            'posts' => $user->posts,
            'followersCount' => $user->followers()->count(),
            'followingCount' => $user->following()->count()
        ]);
    }

    public function show(Request $request, Response $response, $args)
    {
        $user = User::where('username', $args['username'])->first();
        
        if (!$user) {
            return $response->withStatus(404);
        }
        
        $currentUser = User::find($_SESSION['user']);
        $isFollowing = $currentUser->following()->where('following_id', $user->id)->exists();
        
        return $this->view->render($response, 'users/show.twig', [
            'user' => $user,
            'posts' => $user->posts()->orderBy('created_at', 'desc')->get(),
            'followersCount' => $user->followers()->count(),
            'followingCount' => $user->following()->count(),
            'isFollowing' => $isFollowing,
            'isSelf' => $currentUser->id === $user->id
        ]);
    }

    public function follow(Request $request, Response $response, $args)
    {
        $userToFollow = User::where('username', $args['username'])->first();
        
        if (!$userToFollow) {
            return $response->withStatus(404);
        }
        
        $currentUser = User::find($_SESSION['user']);
        
        // Don't follow yourself
        if ($currentUser->id === $userToFollow->id) {
            return $response->withStatus(400);
        }
        
        // Check if already following
        if (!$currentUser->following()->where('following_id', $userToFollow->id)->exists()) {
            $currentUser->following()->attach($userToFollow->id);
        }
        
        return $response
            ->withHeader('Location', '/users/' . $userToFollow->username)
            ->withStatus(302);
    }

    public function unfollow(Request $request, Response $response, $args)
    {
        $userToUnfollow = User::where('username', $args['username'])->first();
        
        if (!$userToUnfollow) {
            return $response->withStatus(404);
        }
        
        $currentUser = User::find($_SESSION['user']);
        $currentUser->following()->detach($userToUnfollow->id);
        
        return $response
            ->withHeader('Location', '/users/' . $userToUnfollow->username)
            ->withStatus(302);
    }
}
