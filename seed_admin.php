<?php
define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_DIR . '/.env');

require_once ROOT_DIR . '/app/Models/Database.php';
require_once ROOT_DIR . '/app/Models/User.php';

use App\Models\User;

$userModel = new User();

$username = 'admin';
$password = 'admin123'; 

if ($userModel->findByUsername($username)) {
    echo "Admin user already exists.\n";
} else {
    if ($userModel->create($username, $password, 'admin')) {
        echo "Admin user created successfully!\n";
    } else {
        echo "Failed to create admin user.\n";
    }
}