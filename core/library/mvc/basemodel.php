<?php
namespace Core\Library\MVC;

use Core\Library\Application\Application;

/**
 * Класс BaseModel является родителем для любой модели в системе.
 */
class BaseModel {
	protected $db;
	protected $config;

	public function __construct() {
		$app = Application::getInstance();

		$this->db = $app->db;
		$this->config = $app->config;
	}
}
?>