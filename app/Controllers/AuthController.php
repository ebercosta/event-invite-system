<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use PDO;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        include __DIR__ . '/../../views/auth/login.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            Auth::login($user);
            header('Location: /dashboard');
            exit;
        }

        $error = 'E-mail ou senha inválidos';
        include __DIR__ . '/../../views/auth/login.php';
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        include __DIR__ . '/../../views/auth/register.php';
    }

    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        if (strlen($name) < 2) $errors[] = 'Nome inválido';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido';
        if (strlen($password) < 6) $errors[] = 'Senha deve ter no mínimo 6 caracteres';
        if ($password !== $passwordConfirm) $errors[] = 'Senhas não conferem';

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'E-mail já cadastrado';

        if ($errors) {
            include __DIR__ . '/../../views/auth/register.php';
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);

        $user = [
            'id' => (int)$db->lastInsertId(),
            'name' => $name,
            'email' => $email,
        ];

        Auth::login($user);
        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
