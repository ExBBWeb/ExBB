<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\User;
use Core\Library\User\Users;
use Core\Library\Site\Entity\FeedbackMessage;

class ControllerFeedbackIndex extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		
		$logged = Users::isLogged();
		$categories = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'feedback_categories');

		$this->data['logged'] = $logged;
		$this->data['categories'] = $categories;
		
		if (isset($this->request->post['process'])) {
			try {
				if (!$logged) {
					if (!isset($this->request->post['name']) || $this->request->post['name'] == '') throw new \Exception('Вы не ввели имя!');
					
					if (mb_strlen($this->request->post['name']) > 25) throw new \Exception('Вы ввели слишком длинное имя!');
					if (mb_strlen($this->request->post['name']) < 5) throw new \Exception('Вы ввели слишком короткое имя!');

					//if (!isset($this->request->post['mail']) || $this->request->post['mail'] == '') throw new \Exception('Вы не ввели адрес E-mail!');

					$user_id = 0;
					$user_name = $this->request->post['name'];
					//$user_mail = $this->request->post['mail'];
				}
				else {
					$user_id = $app->user->id;
					$user_name = $app->user->name;
					//$user_mail = $app->user->mail;
				}
				
				if (!isset($this->request->post['mail'])) throw new \Exception('Вы не ввели адрес E-mail!');
				
				$user_mail = $this->request->post['mail'];
				
				if (!isset($this->request->post['category_id']) || !isset($categories[$this->request->post['category_id']])) throw new \Exception('Вы указали неправильную категорию!');
				if (!isset($this->request->post['title']) || $this->request->post['title'] == '') throw new \Exception('Вы не ввели название сообщения!');
				if (!isset($this->request->post['text']) || $this->request->post['text'] == '') throw new \Exception('Вы не написали текст сообщения!');

				if (mb_strlen($this->request->post['title']) < 5) throw new \Exception('Вы ввели слишком короткое название сообщения!');
				if (mb_strlen($this->request->post['title']) > 250) throw new \Exception('Вы ввели слишком длинное название сообщения!');
				
				$message = new FeedbackMessage();
				$message->user_id = $user_id;
				$message->user_name = $user_name;
				$message->user_mail = $user_mail;
				
				$message->category_id = $this->request->post['category_id'];
				$message->title = $this->request->post['title'];
				$message->text = $this->request->post['text'];
				$message->date_added = 'NOW()';
				
				$message->save();
				
				$app->redirectPage($app->url->module('index'), 'Письмо', 'Ваше сообщение администратору сайта отправлено! Скоро вы получите ответ!', 'success', 7000);
			}
			catch (\Exception $error) {
				$this->data['errors'][] = $error->getMessage();
			}
		}

		$this->view('index');
	}
}
?>