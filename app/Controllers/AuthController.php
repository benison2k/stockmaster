<?php
namespace App\Controllers;

use App\Core\Security;
use App\Models\User;

class AuthController {

    public function showLogin(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        require_once ROOT_DIR . '/app/Views/auth/login.php';
    }

    public function login(): void {
        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
            require_once ROOT_DIR . '/app/Views/auth/login.php';
            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $error = "Invalid username or password.";
        require_once ROOT_DIR . '/app/Views/auth/login.php';
    }

    public function logout(): void {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}