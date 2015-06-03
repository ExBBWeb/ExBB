<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Site\Entity\User;
use Core\Library\User\Users;

class ControllerAuthIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');

		$this->app->template->title = l('auth_admin_enter');
		$this->app->template->setParam('page_header', 'Главная');
		
		if (isset($this->request->post['login']) && isset($this->request->post['password'])) {
			// Получение пользователя по его логину
			$user = new User(
				array(
					'login' => $this->request->post['login'],
				)
			);

			if (!$user->exists()) $this->app->redirectPage($this->app->url->module('auth'), l('auth_login_error'), l('auth_login_error_desc'), 'error');

			if (Users::cryptPassword($this->request->post['password'], $user->salt) != $user->password) $this->app->redirectPage($this->app->url->module('auth'), 'Ошибка входа', 'Вы ввели неверные данные!', 'error');
			
			// СДЕЛАТЬ! Проверка права доступа для группы!!!
			
			$this->request->session['is_admin'] = true;
			$this->request->session['user_id'] = $user->id;
			$this->app->redirectPage($this->app->url->module('index'), l('auth_admin_enter'), l('auth_login_success'), 'success');
		}

		$this->view('index', 'auth');
	}
}
?>