<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

class ControllerContentIndex extends BaseController {
	public function ActionIndex() {
		$app = Application::getInstance();
		$app->template->title = 'Контент';
		$app->template->setParam('page_header', 'Контент');

		$app->template->addBreadcrumb('Статьи', false, true);

		$this->view('index/index');
	}
}
?>