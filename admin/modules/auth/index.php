<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\User\Users;
use Core\Library\Site\Entity\User;
use Core\Library\User\Access\Access;

class ControllerAuthIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');

		$this->app->template->title = $this->lang->auth_admin_enter;

		if (Users::isLogged()) {
			$this->app->redirectPage($this->app->url->module('auth'), $this->lang->auth_admin_enter, $this->lang->you_logged, 'error');
			$this->app->stop();
		}
		
		if (isset($this->request->post['login']) && isset($this->request->post['password'])) {
			// Получение пользователя по его логину
			$user = new User(
				array(
					'login' => $this->db->escape($this->request->post['login']),
				)
			);

			if (!$user->exists()) {
				$this->app->redirectPage($this->app->url->module('auth'), $this->lang->auth_login_error, $this->lang->auth_login_error_desc, 'error');
				$this->app->stop();
			}
			
			if (Users::cryptPassword($this->request->post['password'], $user->salt) != $user->password) {
				$this->app->redirectPage($this->app->url->module('auth'), $this->lang->auth_login_error, $this->lang->auth_login_error_desc, 'error');
				$this->app->stop();
			}
			
			$access = new Access($user->group_id);
			$access_value = (bool)$access->getEntityAccess('auth', 'admin');

			if (!$access_value) {
				$this->app->redirectPage($this->app->url->module('auth'), $this->lang->auth_login_error, $this->lang->auth_not_access, 'error');
				$this->app->stop();
			}

			Users::authorize($user);
			
			$this->app->redirectPage($this->app->url->module('index'), $this->lang->auth_admin_enter, $this->lang->auth_login_success, 'success');
			$this->app->stop();
		}

		$this->view('index', 'auth');
	}
	
	public function ActionLogout() {
		$this->loadLanguage('index');
		if (!Users::isLogged()) {
			$this->app->redirectPage(ROOT_URL, $this->lang->logout_title, $this->lang->you_not_logged, 'error');
			$this->app->stop();
		}
		
		Users::logout();
		$this->app->redirectPage(ROOT_URL, $this->lang->logout_title, $this->lang->logout_success);
		$this->app->stop();
	}
}
?>