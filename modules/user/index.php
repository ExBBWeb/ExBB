<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\User;
use Core\Library\User\Users;

class ControllerUserIndex extends BaseController {
	public function ActionIndex() {
		if (Users::isLogged()) {
			$this->app->redirectServer($this->app->url->module('user', 'profile'));
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

		// Если уже был авторизован, редирект на страницу профиля
		if (Users::isLogged()) {
			$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->you_logged, 'error');
			$app->stop();
		}

		$answer = array(
			'status' => true,
			'errors' => array(),
			'message' => $this->lang->auth_success,
		);
		
		$app->template->addBreadcrumb($this->lang->main_page, $app->url->module('index'), false);
		$app->template->addBreadcrumb($this->lang->auth_title, $app->url->module('user', 'index', 'login'), true);
		
		// Пришли данные
		if (isset($this->request->post['process'])) {
			try {
				if (empty($this->request->post['login'])) throw new \Exception($this->lang->empty_login);
				if (empty($this->request->post['password'])) throw new \Exception($this->lang->empty_pass);
				
				// Провека на существование пользователя с таким логином
				$user = new User(array('login'=>$this->request->post['login']));
				if (!$user->exists() || $user->id == 0) throw new \Exception($this->lang->incorrect_login);
				
				// Проверка на правильность пароля
				$password = Users::cryptPassword($this->request->post['password'], $user->salt);
				if ($user->password != $password) throw new \Exception($this->lang->incorrect_login);
				
				// Авторизуем пользователя
				Users::authorize($user);
				
				// Страница редиректа
				$app->redirectPage($app->url->module('users', 'profile'), $this->lang->auth_title, $this->lang->auth_success);
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

		$app->template->addBreadcrumb($this->lang->main_page, $app->url->module('index'), false);
		$app->template->addBreadcrumb($this->lang->registration_title, $app->url->module('user', 'index', 'register'), true);

		// Если уже был авторизован, редирект на страницу профиля
		if (Users::isLogged()) {
			$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->you_logged, 'error');
			$app->stop();
		}

		$answer = array(
			'status' => true,
			'errors' => array(),
			'message' => $this->lang->register_success,
		);
		
		$fields = Users::getUserFieldsByGroup($this->config->getOption('default_group_id'));
		$this->data['fields'] = $fields;

		$this->data['language'] = $this->app->language->getLanguage();
		
		if (isset($this->request->post['process'])) {
			try {
				foreach ($this->request->post as $var => $value) $this->request->post[$var] = htmlspecialchars($value);
				
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
				
				if (empty($post['email']) && preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $post['email'])) {
					$answer['errors']['email'] = $this->lang->invalid_field;
					$valid = false;
				}

				$user = new User(array('login'=>$post['login']));
				if ($user->exists()) {
					$answer['errors']['login'] = $this->lang->login_exists;
					$valid = false;
					throw new \Exception($this->lang->login_exists);
				}

				$user = new User(array('email'=>$post['email']));
				if ($user->exists()) {
					$answer['errors']['email'] = $this->lang->email_exists;
					$valid = false;
					throw new \Exception($this->lang->email_exists);
				}
				
				if (!$valid) throw new \Exception($this->lang->invalid_form);

				// Создание пользователя и его запись в базу данных
				$user = new User();
				
				$user->autosave = false;
				
				$user->salt = Users::generateSalt();
				$password = Users::cryptPassword($post['password'], $user->salt);
				$user->login = $post['login'];
				$user->password = $password;
				$user->active = 1;
				$user->email = $post['email'];
				$user->group_id = $this->config->getOption('default_group_id');
				//$user->name = $this->request->post['name'];
				//$user->sirname = $this->request->post['sirname'];
				$user->register_date = 'NOW()';
				
				foreach ($fields as $field) {
					$value = $post[$field['name']];
					$user->setFieldData($field['id'], $value);
				}
				
				$user->save();
				
				Users::authorize($user);

				$app->redirectPage($app->url->module('users', 'profile'), $this->lang->registration_title, $this->lang->register_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
				//$answer['errors'][] = $error->getMessage();
			}
		}
		
		$this->viewAnswer($answer, 'register');
	}
	
	public function ActionLogout() {
		//$this->loadLanguage('index');
		$this->app->template->page_title = $this->lang->logout_title;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($this->lang->logout_title, $this->app->url->module('user', 'index', 'logout'), true);
		
		if (!Users::isLogged()) {
			$this->app->redirectPage($this->app->url->module('user', 'index', 'login'), $this->lang->logout_title, $this->lang->you_not_logged, 'error');
			$this->app->stop();
		}
		
		Users::logout();
		$this->app->redirectPage($this->app->url->module('user'), $this->lang->logout_title, $this->lang->logout_success);
		$this->app->stop();
	}
	
	public function ActionRegisterULogin() {
		$app = $this->app;
		
		$app->template->page_title = $this->lang->auth_title;
		$app->template->addBreadcrumb($this->lang->main_page, $app->url->module('index'), false);
		$app->template->addBreadcrumb($this->lang->auth_title, $app->url->module('user', 'index', 'login'), true);

		// Если уже был авторизован, редирект на страницу профиля
		if (Users::isLogged()) {
			$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->you_logged, 'error');
			$app->stop();
		}
		
		$data = file_get_contents('http://ulogin.ru/token.php?token='.$this->request->post['token'].'&host=' .$app->url->getBaseUrl());
		$ulogin = json_decode($data, true);

		foreach ($ulogin as $var => $value) $ulogin[$var] = htmlspecialchars($value);
		
		if (!isset($ulogin['uid'])) {
			$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->ulogin_token_error, 'error');
			$app->stop();
		}

		$ulogin_id = 'ulogin_'.$ulogin['network'].'_'.$ulogin['uid'];

		// Провека, входил ли пользователь раньше
		$user = new User(array('ulogin_id'=>$ulogin_id));

		if ($user->exists()) {
			Users::authorize($user);
			$app->redirectPage($app->url->module('users'), $this->lang->auth_title, $this->lang->auth_success);
		}
		
		$login = $this->generateNickname($ulogin['first_name'], $ulogin['last_name'], $ulogin['nickname']);

		// Если пользователь входит впервые
		$user = new User();

		$user->login = $login;
		if (!empty($ulogin['email'])) $user->email = $ulogin['email'];
		
		$user->salt = Users::generateSalt();
		$password = Users::cryptPassword(md5(time()), $user->salt);
		$user->password = $password;
		$user->ulogin_id = $ulogin_id;
		$user->group_id = $this->config->getOption('default_group_id');
		
		$fieldsByName = array();
		$fields = Users::getUserFieldsByGroup($this->config->getOption('default_group_id'));
		foreach ($fields as $id => $data) {
			$fieldsByName[$data['name']] = $id;
		}
		
		//$user->autosave = false;
		
		// Произвольные поля профиля
		if (!empty($ulogin['first_name']) && isset($fieldsByName['name'])) {
			$user->setFieldData($fieldsByName['name'], $ulogin['first_name']);
		}
		
		if (!empty($ulogin['last_name']) && isset($fieldsByName['surname'])) {
			$user->setFieldData($fieldsByName['surname'], $ulogin['last_name']);
		}
		
		if (!empty($ulogin['bdate']) && isset($fieldsByName['birthdate'])) {
			$user->setFieldData($fieldsByName['birthdate'], $ulogin['bdate']);
		}
		
		if (!empty($ulogin['sex']) && isset($fieldsByName['sex'])) {
			$user->setFieldData($fieldsByName['sex'], $ulogin['sex']);
		}
		
		if (!empty($ulogin['phone']) && isset($fieldsByName['phone'])) {
			$user->setFieldData($fieldsByName['phone'], $ulogin['phone']);
		}
		
		if (!empty($ulogin['city']) && isset($fieldsByName['city'])) {
			$user->setFieldData($fieldsByName['city'], $ulogin['city']);
		}
		
		if (!empty($ulogin['country']) && isset($fieldsByName['country'])) {
			$user->setFieldData($fieldsByName['country'], $ulogin['country']);
		}

		$user->register_date = 'NOW()';
		$user->active = 1;
		$user->save();
				
		Users::authorize($user);
				
		$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->auth_success);
	}

	/**
	* @param string $first_name
	* @param string $last_name
	* @param string $nickname
	* @param string $bdate (string in format: dd.mm.yyyy)
	* @param array $delimiters
	* @return string
	*/
	public function generateNickname($first_name, $last_name="", $nickname="", $bdate="", $delimiters=array('.', '_')) {
		$delim = array_shift($delimiters);

		$first_name = $this->app->url->translit($first_name);
		$first_name_s = substr($first_name, 0, 1);

		$variants = array();
		if (!empty($nickname))
			$variants[] = $nickname;
		$variants[] = $first_name;
		if (!empty($last_name)) {
			$last_name = $this->app->url->translit($last_name);
			$variants[] = $first_name.$delim.$last_name;
			$variants[] = $last_name.$delim.$first_name;
			$variants[] = $first_name_s.$delim.$last_name;
			$variants[] = $first_name_s.$last_name;
			$variants[] = $last_name.$delim.$first_name_s;
			$variants[] = $last_name.$first_name_s;
		}
		if (!empty($bdate)) {
			$date = explode('.', $bdate);
			$variants[] = $first_name.$date[2];
			$variants[] = $first_name.$delim.$date[2];
			$variants[] = $first_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$date[2];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$date[2];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$date[2];
			$variants[] = $first_name_s.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$date[2];
			$variants[] = $last_name.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$delim.$date[0].$date[1];
		}
		$i=0;

		$exist = true;
		while (true) {
			if ($exist = Users::userExistsByLogin($variants[$i])) {
				foreach ($delimiters as $del) {
					$replaced = str_replace($delim, $del, $variants[$i]);
					if($replaced !== $variants[$i]){
						$variants[$i] = $replaced;
						if(!$exist = Users::userExistsByLogin($variants[$i])){
							break;
						}
					}
				}
			}
			if ($i >= count($variants)-1 || !$exist)
				break;
			$i++;
		}

		if ($exist) {
			while ($exist) {
				$nickname = $first_name.mt_rand(1, 100000);
				$exist = Users::userExistsByLogin($nickname);
			}
			return $nickname;
		} else
			return $variants[$i];
	}
}
?>