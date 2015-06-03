<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

class ControllerGalleryIndex extends BaseController {
	public function ActionIndex() {
		$app = Application::getInstance();
		$app->template->title = 'Галерея';
		$app->template->setParam('page_header', 'Галерея');

		$app->template->addBreadcrumb('Галерея', false, true);

		$this->view('index/index');
	}
}
?>