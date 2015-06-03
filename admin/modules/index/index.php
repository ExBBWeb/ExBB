<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

class ControllerIndexIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');

		$app = Application::getInstance();
		$app->template->title = 'Привет!';
		$app->template->setParam('page_header', 'Главная');

		$this->view('index');
	}
}
?>