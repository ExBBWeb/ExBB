<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Site\Entity\Widget;

use Core\Library\Site\Entity\FeedbackMessage;

class ControllerFeedbackIndex extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Сообщения';
		$app->template->setParam('page_header', 'Сообщения');
		
		$this->data['categories'] = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'feedback_categories');
		$this->data['messages'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'feedback ORDER BY date_added DESC');

		$this->view('index');
	}
	
	public function ActionView() {
		$app = $this->app;
		
		$id = $app->router->getVar('param');
		if (!$id) $app->redirectPage($app->url->module('feedback'), 'Ошибка!', 'Не указан ID сообщения!');
		
		$object = new FeedbackMessage($id);
		if (!$object->exists()) $app->redirectPage($app->url->module('feedback'), 'Ошибка!', 'Сообщения не существует!');
		
		$this->data['object'] = $object;
		$object->readed = true;
		$object->save();
		
		$this->view('message');
	}
	
	public function ActionEdit() {
		$id = $this->app->router->getVar('param');
		
		$object = new FeedbackMessage($id);
		if (!$object->exists()) $this->app->redirectPage($this->app->url->module('feedback'), 'Ошибка!', 'Сообщения не существует!');
		
		$categories = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'feedback_categories');

		if (isset($this->request->post['process'])) {
			$object->title = $this->request->post['title'];
			$object->user_name = $this->request->post['name'];
			$object->user_mail = $this->request->post['mail'];
			$object->category_id = $this->request->post['category_id'];
			$object->text = $this->request->post['text'];
			
			$object->save();
			
			$this->app->redirectPage($this->app->url->module('feedback'), 'Редактирование', 'Сообщение сохранено!');
		}
		
		$this->data['object'] = $object;
		$this->data['categories'] = $categories;
		
		$this->view('form');
	}
	
	public function ActionDelete() {
		$app = $this->app;
		
		$id = $app->router->getVar('param');
		if (!$id) $app->redirectPage($app->url->module('feedback'), 'Ошибка!', 'Не указан ID сообщения!');
		
		$object = new FeedbackMessage($id);
		if (!$object->exists()) $app->redirectPage($app->url->module('feedback'), 'Ошибка!', 'Сообщения не существует!');
		
		$object->delete();
		
		$app->redirectPage($app->url->module('feedback'), 'Удаление сообщения', 'Сообщение успешно удалено!');
	}
}
?>