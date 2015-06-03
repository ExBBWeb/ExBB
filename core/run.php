<?php
// autoload function
function __autoload($class) {
  // convert namespace to full file path
  $class = str_replace('\\', '/', strtolower($class)) . '.php';
  require_once(BASE.'/'.$class);
}

ini_set('error_reporting', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use Core\Library\Application\Application;

$app = Application::getInstance();
$app->run();

$app->destroy();
?>