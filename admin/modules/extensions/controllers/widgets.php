<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Site\Entity\Widget;

class ControllerExtensionsWidgets extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Виджеты';
		$app->template->setParam('page_header', 'Виджеты');
		
		$this->data['widgets'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'widgets');

		$this->view('widgets/index');
	}
	
	public function ActionAdd() {
		$object = new Widget();
		
		$widgets = scandir(ROOT.'/widgets/');
		unset($widgets[0], $widgets[1]);
		
		if (isset($this->request->post['process'])) {
			$object->title = $this->request->post['title'];
			$object->widget = $this->request->post['widget'];
			$object->position = $this->request->post['position'];
			$object->status = $this->request->post['status'];
			$object->priority = $this->request->post['priority'];
			
			$object->save();
			
			$this->app->redirectPage($this->app->url->module('extensions', 'widgets'), 'Создание', 'Виджет создан!');
		}
		
		$this->data['object'] = $object;
		$this->data['widgets'] = $widgets;
		
		$this->view('widgets/form');
	}
	
	public function ActionEdit() {
		$id = $this->app->router->getVar('param');
		
		$object = new Widget($id);
		if (!$object->exists()) $this->app->redirectPage($this->app->url->module('extensions', 'widgets'), 'Ошибка!', 'Виджета не существует!');
		
		$widgets = scandir(ROOT.'/widgets/');
		unset($widgets[0], $widgets[1]);
		
		if (isset($this->request->post['process'])) {
			$object->title = $this->request->post['title'];
			$object->widget = $this->request->post['widget'];
			$object->position = $this->request->post['position'];
			$object->status = $this->request->post['status'];
			$object->priority = $this->request->post['priority'];
			
			$object->save();
			
			$this->app->redirectPage($this->app->url->module('extensions', 'widgets'), 'Редактирование', 'Виджет сохранён!');
		}
		
		$this->data['object'] = $object;
		$this->data['widgets'] = $widgets;
		
		$this->view('widgets/form');
	}
	
	public function ActionChangeState() {
		$id = $this->app->router->getVar('param');
		
		$object = new Widget($id);
		if (!$object->exists()) $this->app->redirectPage($this->app->url->module('extensions', 'widgets'), 'Ошибка!', 'Виджета не существует!');
		
		// Переключение состояния в противоположное.
		$object->status = !$object->status;
		$object->save();
		
		$this->app->redirectPage($this->app->url->module('extensions', 'widgets'), 'Виджет!', 'Состояние виджета изменено!');
	}
	
	public function ActionDelete() {
		$app = $this->app;
		
		$id = $app->router->getVar('param');
		if (!$id) $app->redirectPage($app->url->module('extensions', 'widgets'), 'Ошибка!', 'Не указан ID виджета!');
		
		$object = new Widget($id);
		if (!$object->exists()) $app->redirectPage($app->url->module('extensions', 'widgets'), 'Ошибка!', 'Виджета не существует!');
		
		$object->delete();
		
		$app->redirectPage($app->url->module('extensions', 'widgets'), 'Удаление виджета', 'Виджет успешно удален!');
	}
}
?>