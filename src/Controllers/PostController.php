<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PostController
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response)
    {
        $currentUser = User::find($_SESSION['user']);
        $followingIds = $currentUser->following()->pluck('id')->push($currentUser->id)->toArray();
        
        $posts = Post::whereIn('user_id', $followingIds)
            ->orderBy('created_at', 'desc')
            ->with(['user', 'likes'])
            ->get();
            
        return $this->view->render($response, 'posts/index.twig', [
            'posts' => $posts,
            'currentUser' => $currentUser
        ]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $userId = $_SESSION['user'];
        
        // Validate
        $errors = [];
        if (empty($data['content'])) {
            $errors['content'] = 'Post content is required';
        } elseif (strlen($data['content']) > 280) {
            $errors['content'] = 'Post cannot exceed 280 characters';
        }
        
        if (!empty($errors)) {
            return $this->view->render($response, 'posts/index.twig', [
                'errors' => $errors,
                'old' => $data
            ]);
        }
        
        // Create post
        Post::create([
            'user_id' => $userId,
            'content' => $data['content']
        ]);
        
        return $response
            ->withHeader('Location', '/posts')
            ->withStatus(302);
    }

    public function like(Request $request, Response $response, $args)
    {
        $postId = $args['id'];
        $userId = $_SESSION['user'];
        
        // Check if post exists
        $post = Post::find($postId);
        if (!$post) {
            return $response->withStatus(404);
        }
        
        // Check if already liked
        $like = Like::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();
            
        if (!$like) {
            Like::create([
                'user_id' => $userId,
                'post_id' => $postId
            ]);
        }
        
        return $response
            ->withHeader('Location', '/posts')
            ->withStatus(302);
    }

    public function unlike(Request $request, Response $response, $args)
    {
        $postId = $args['id'];
        $userId = $_SESSION['user'];
        
        // Find and delete like
        Like::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
            
        return $response
            ->withHeader('Location', '/posts')
            ->withStatus(302);
    }
}
