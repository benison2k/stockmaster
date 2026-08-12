<?php
session_start();

// In a real app, use Composer's autoloader here
require_once '../app/Core/Security.php';
require_once '../app/Core/Router.php';

// Route the request
$router = new Router();
$url = $_GET['url'] ?? '';

// Basic routing logic
switch ($url) {
    case '':
        require '../app/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->landing();
        break;
    case 'login':
        require '../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
    // Add routes for dashboard and inventory...
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}