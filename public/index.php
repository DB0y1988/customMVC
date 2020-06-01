<?php

// This is where it all begins

require '../vendor/autoload.php';

/**
 *  Error and Exception handling
 */

set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');
error_reporting(E_ALL);
ini_set('session.cookie_lifetime', '864000'); // Ten days in seconds

session_start();

$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);

// Creates the routing table array -> Creates regular expression array keys
$router->add('{controller}/{action}');

$router->add('login', ['controller' => 'Login', 'action' => 'new']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);

// Admin edit routes
$router->add('pages/{id:\d+}/edit-page', ['controller' => 'Pages', 'action' => 'editPage']);
$router->add('recipes/{id:\d+}/edit', ['controller' => 'Recipes', 'action' => 'edit']);
$router->add('home/{id:\d+}/edit-slide', ['controller' => 'Home', 'action' => 'editSlide']);
$router->add('recipes/{id:\d+}/edit-category', ['controller' => 'Recipes', 'action' => 'editRecipeCategory']);
$router->add('support/{id:\d+}/view-ticket', ['controller' => 'Support', 'action' => 'viewTicket']);
$router->add('support/{id:\d+}/delete-ticket', ['controller' => 'Support', 'action' => 'deleteTicket']);
$router->add('support/{id:\d+}/update-ticket', ['controller' => 'Support', 'action' => 'updateTicket']);
$router->add('pages/{id:\d+}/delete-page', ['controller' => 'Pages', 'action' => 'deletePage']);
$router->add('recipes/{id:\d+}/delete', ['controller' => 'Recipes', 'action' => 'delete']);

$router->add('{controller}/{id:\d+}/{action}/[a-z]*\w*[a-z]', ['controller' => 'Blog', 'action' => 'single']);
$router->add('{controller}/{slug:[a-z]*\w*[a-z]+}', ['controller' => 'Pages', 'action' => 'page']);
$router->add('{controller}/{slug:[a-z]*\w*[a-z]*\w*}', ['controller' => 'Pages', 'action' => 'page']);
$router->add('{controller}/{category:[a-z]+}/{action}');

$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);

// Query string is anything after the main domain (/products/about)
$router->dispatch($_SERVER['QUERY_STRING']);
