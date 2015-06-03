<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

class Controller404Index extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Ошибка 404';
		$app->template->setParam('page_header', 'Ошибка');
		
		//$app->template->addBreadcrumb('Главная', $app->url->get('index', array(), true));
		
		//$this->data['article'] = new Article(array('is_index'=>1));

		$this->view('index');
	}
}
?>