<?php
/**
 * index.php
 * Khadomeh Application Front Controller
 * 
 * Entry point for all incoming requests.
 * Initializes the environment and delegates routing logic to the Router.
 */

// Include the application bootstrapper
require_once __DIR__ . '/includes/init.php';

use App\Core\Router;

// Initialize the Routing Engine
$router = new Router();

// Dispatch the incoming HTTP request
$router->dispatch();
