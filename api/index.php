<?php
 header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
 require '../vendor/autoload.php';

 $app = new \Slim\App;

// Include route files
require './users.php';

$app->run();
?>