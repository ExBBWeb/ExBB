<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\User;
use Core\Library\User\Users;

class ControllerUserIndex extends BaseController {
	public function ActionIndex() {
		if (Users::isLogged()) {
			$this->ActionProfile();
		}
		else {
			$this->ActionLogin();
		}
	}
	
	public function initialize() {
		$this->loadLanguage('index');
		$this->data['lang'] = $this->getLanguage();
	}
	
	public function ActionLogin() {
		$app = $this->app;
		$app->template->page_title = $this->lang->auth_title;
		$app->template->setParam('page_header', $this->lang->auth_title);
		
		// Если уже был авторизован, редирект на страницу профиля
		if (Users::isLogged()) $app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->you_logged, 'error');

		$answer = array(
			'status' => true,
			'errors' => array(),
			'message' => $this->lang->auth_success,
		);

		$app->template->addBreadcrumb($this->lang->main_page, $app->url->get('index', array(), true));

		// Пришли данные
		if (isset($this->request->post['process'])) {
			try {
				if (empty($this->request->post['login'])) throw new \Exception($this->lang->empty_login);
				if (empty($this->request->post['password'])) throw new \Exception($this->lang->empty_pass);
				
				// Провека на существование пользователя с таким логином
				$user = new User(array('login'=>$this->request->post['login']));
				if (!$user->exists()) throw new \Exception($this->lang->incorrect_login);
				
				// Проверка на правильность пароля
				$password = Users::cryptPassword($this->request->post['password'], $user->salt);
				if ($user->password != $password) throw new \Exception($this->lang->incorrect_login);
				
				// Авторизуем пользователя
				Users::authorize($user);
				
				// Страница редиректа
				$answer['redirect'] = $app->redirectPage($app->url->module('users'), $this->lang->auth_title, $this->lang->auth_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
				$answer['errors'][] = $error->getMessage();
			}
		}
		
		// Вывод представления с ответом
		$this->viewAnswer($answer, 'login');
	}
	
	public function ActionRegister() {
		$app = $this->app;
		$app->template->page_title = $this->lang->registration_title;
		$app->template->setParam('page_header', $this->lang->registration_title);
		
		if (Users::isLogged()) $app->redirectPage($app->url->module('users'), $this->lang->auth_title, $this->lang->you_logged, 'error');

		$answer = array(
			'status' => true,
			'errors' => array(),
			'message' => $this->lang->register_success,
		);
		
		$fields = Users::getUserFieldsByGroup($this->config->getOption('default_group_id'));
		$this->data['fields'] = $fields;

		$app->template->addBreadcrumb($this->lang->main_page, $app->url->get('index', array(), true));

		if (isset($this->request->post['process'])) {
			try {
				$post = $this->request->post;
				$valid = true;
				
				foreach ($fields as $field) {
					if ($field['options']['required'] && empty($post[$field['name']])) {
						$answer['errors'][$field['name']] = $this->lang->field_required;
						$valid = false;
					}
				}
				
				if (!isset($post['login']) || $post['login'] == '') {
					$answer['errors']['login'] = $this->lang->field_required;
					$valid = false;
				}

				if (!isset($post['password']) || $post['password'] == '') {
					$answer['errors']['login'] = $this->lang->field_required;
					$valid = false;
				}
				
				if (mb_strlen($post['login']) > 25) {
					$answer['errors']['login'] = $this->lang->login_long;
					$valid = false;
				}
				
				if (mb_strlen($post['login']) < 5) {
					$answer['errors']['login'] = $this->lang->login_short;
					$valid = false;
				}

				if (mb_strlen($post['password']) < 6) {
					$answer['errors']['password'] = $this->lang->pass_short;
					$valid = false;
				}
				
				if (empty($post['email'])) {
					$answer['errors']['email'] = $this->lang->invalid_field;
					$valid = false;
				}
				
				$user = new User(array('login'=>$this->request->post['login']));
				if ($user->exists()) {
					$answer['errors']['login'] = $this->lang->login_exists;
					$valid = false;
					throw new \Exception('Пользователь с таким логином уже зарегистрирован!');
				}

				if (!$valid) throw new \Exception($this->lang->invalid_form);


				
				$user = new User();
				
				$user->salt = Users::generateSalt();
				$password = Users::cryptPassword($this->request->post['password'], $user->salt);
				$user->login = $this->request->post['login'];
				$user->password = $password;
				$user->active = 1;
				$user->email = $this->request->post['email'];
				$user->group_id = $this->config->getOption('default_group_id');
				//$user->name = $this->request->post['name'];
				//$user->sirname = $this->request->post['sirname'];
				$user->register_date = 'NOW()';
				$user->save();
				
				Users::authorize($user);

				$answer['redirect'] = $app->redirectPage($app->url->module('users'), 'Регистрация', 'Вы успешно зарегистрировались! Теперь вы можете воспользоваться всеми возможностями сайта!');
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
				$answer['errors'][] = $error->getMessage();
			}
		}
		
		$this->viewAnswer($answer, 'register');
	}
	
	public function ActionProfile() {
		$app = $this->app;
		if (!Users::isLogged()) $app->redirectPage($app->url->module('users'), 'Профиль', 'Сначало нужно войти!', 'error');
		
		$user = $app->user;
		
		$this->data['user'] = $user;
		$this->view('profile');
	}
	
	public function ActionLogout() {
		Users::logout();
		$this->app->redirectPage($this->app->url->module('user'), 'Профиль', 'Вы покинули сайт!');
	}
	
	public function ActionRegisterULogin() {
		$app = $this->app;
		
		if (Users::isLogged()) $app->redirectPage($app->url->module('users'), 'Профиль', 'Вы уже входили на сайт!', 'error');
		
		$data = file_get_contents('http://ulogin.ru/token.php?token='.$this->request->post['token'].'&host=' .$app->url->getBaseUrl());
		$ulogin = json_decode($data, true);
		
		if (!isset($ulogin['uid'])) $app->redirectPage($app->url->module('users'), 'Профиль', 'Ошибка входа!', 'error');
		
		$login = 'ulogin_'.$ulogin['network'].'_'.$ulogin['uid'];
		
		// Провека, входил ли пользователь раньше
		$user = new User(array('login'=>$login));

		if ($user->exists()) {
			Users::authorize($user);
			$app->redirectPage($app->url->module('users'), 'Вход', 'Вы успешно вошли!');
		}
		
		// Если пользователь входит впервые
		$user = new User();

		$user->salt = Users::generateSalt();
		$password = Users::cryptPassword(md5(time()), $user->salt);
		$user->password = $password;
		
		$user->login = $login;
		$user->name = $ulogin['first_name'];
		$user->sirname = $ulogin['last_name'];

		$user->date_register = 'NOW()';
		$user->save();
				
		Users::authorize($user);
				
		$app->redirectPage($app->url->module('users'), 'Вход', 'Вы успешно вошли!');
	}
}
?>