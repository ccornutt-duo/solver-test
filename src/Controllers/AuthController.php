<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Illuminate\Database\Capsule\Manager as DB;

class AuthController
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function registerPage(Request $request, Response $response)
    {
        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        
        // Validate input
        $errors = [];
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (User::where('username', $data['username'])->exists()) {
            $errors['username'] = 'Username already exists';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (User::where('email', $data['email'])->exists()) {
            $errors['email'] = 'Email already exists';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            return $this->view->render($response, 'auth/register.twig', [
                'errors' => $errors,
                'old' => $data
            ]);
        }
        
        // Create user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        
        // Log the user in
        $_SESSION['user'] = $user->id;
        
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function loginPage(Request $request, Response $response)
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $user = User::where('username', $data['username'])->first();
        
        $errors = [];
        
        if (!$user || !password_verify($data['password'], $user->password)) {
            $errors['auth'] = 'Invalid username or password';
            return $this->view->render($response, 'auth/login.twig', [
                'errors' => $errors,
                'old' => $data
            ]);
        }
        
        $_SESSION['user'] = $user->id;
        
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function logout(Request $request, Response $response)
    {
        unset($_SESSION['user']);
        session_destroy();
        
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
}
