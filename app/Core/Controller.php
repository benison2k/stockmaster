<?php
namespace App\Core;

class Controller {

    /**
     * Render a view template and pass data to it.
     * 
     * @param string $view View file path relative to app/Views (e.g., 'inventory/index')
     * @param array $data Associative array of data to pass into the view
     */
    public function view($view, $data = []) {
        // Extract variables from array keys into local scope
        extract($data);

        $viewFile = ROOT_DIR . '/app/Views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View file standard path not found: " . $viewFile);
        }
    }

    /**
     * Instantiate and return a model object dynamically.
     * 
     * @param string $model Model class name (e.g., 'Product')
     * @return object
     */
    public function model($model) {
        $modelClass = "\\App\\Models\\" . $model;
        
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        
        die("Model class not found: " . $modelClass);
    }
}