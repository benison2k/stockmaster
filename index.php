<?php
session_start();

define('ROOT_DIR', __DIR__);

// Dynamically determine base URL path (/stockmaster on XAMPP, empty on Hostinger root)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', $scriptDir);

// Load Environment Variables
require_once ROOT_DIR . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_DIR . '/.env');

// Autoload dependencies or manually require files
if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
    require_once ROOT_DIR . '/vendor/autoload.php';
} else {
    require_once ROOT_DIR . '/app/Core/Security.php';
    require_once ROOT_DIR . '/app/Core/Router.php';
    require_once ROOT_DIR . '/app/Models/Database.php';
    require_once ROOT_DIR . '/app/Models/User.php';
    require_once ROOT_DIR . '/app/Models/Product.php';
    require_once ROOT_DIR . '/app/Controllers/HomeController.php';
    require_once ROOT_DIR . '/app/Controllers/AuthController.php';
    require_once ROOT_DIR . '/app/Controllers/InventoryController.php';
    require_once ROOT_DIR . '/app/Controllers/PosController.php';
    require_once ROOT_DIR . '/app/Controllers/SalesController.php';
    
}

use App\Core\Router;

$router = new Router();

// Define Routes
$router->add('GET', '', 'HomeController', 'landing');
$router->add('GET', 'login', 'AuthController', 'showLogin');
$router->add('POST', 'login', 'AuthController', 'login');
$router->add('GET', 'logout', 'AuthController', 'logout');
$router->add('GET', 'dashboard', 'HomeController', 'dashboard');

// Inventory Routes
$router->add('GET',  'inventory',                 'InventoryController', 'index');
$router->add('POST', 'inventory/add',             'InventoryController', 'add');
$router->add('POST', 'inventory/update',          'InventoryController', 'update');
$router->add('POST', 'inventory/delete',          'InventoryController', 'delete');
$router->add('POST', 'inventory/update-stock', 'InventoryController', 'updateStock');
$router->add('POST', 'inventory/updateStock',  'InventoryController', 'updateStock');

// POS Routes
$router->add('GET',  'pos',          'PosController', 'index');
$router->add('POST', 'pos/checkout', 'PosController', 'checkout');

// Sales Routes
$router->add('GET', 'sales',         'SalesController', 'index');
$router->add('GET', 'sales/details', 'SalesController', 'details');

// Dispatch Request
$url = $_GET['url'] ?? '';
$router->dispatch($url);