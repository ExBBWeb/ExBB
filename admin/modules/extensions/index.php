<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

class ControllerExtensionsIndex extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Управление дополнениями';
		$app->template->setParam('page_header', 'Главная');
		
		$this->view('index');
	}
}
?>