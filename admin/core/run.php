<?php
// autoload function
function __autoload($class) {
  // convert namespace to full file path
  $class = str_replace('\\', '/', strtolower($class)) . '.php';
  require_once(ROOT.'/'.$class);
}

use Core\Library\Application\Application;

$app = Application::getInstance();

try {
	$app->run('admin');
}
catch (\Exception $error) {
	echo $error->getMessage();
}

$app->destroy();
?>