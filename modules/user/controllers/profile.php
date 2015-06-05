<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\User;
use Core\Library\User\Users;

class ControllerUserProfile extends BaseController {
	protected $user;

	public function ActionIndex() {
		$app = $this->app;
		$app->template->addBreadcrumb($this->lang->profile_page, $app->url->module('user', 'profile', 'index'), true);
		$app->template->page_title = $this->lang->profile_page;
		
		$this->app->user->autosave = false;

		$this->data['language'] = $this->app->language->getLanguage();
		$this->data['fields'] = Users::getUserFieldsByGroup($this->app->user->group_id);
		$this->data['user'] = $this->app->user;
		
		$this->data['tab_content'] = $this->getTab('index');
		$this->view('profile');
	}
	
	public function ActionEdit($answer=false) {
		$app = $this->app;
		$app->template->addBreadcrumb($this->lang->profile_page, $app->url->module('user', 'profile', 'index'), true);
		$app->template->page_title = $this->lang->profile_page;
		
		if (!$answer) {
			$answer = array(
				//'status' => true,
				'errors' => array(),
				'message' => $this->lang->edit_profile_success,
			);
		}
		
		$this->app->user->autosave = false;

		$this->data['language'] = $this->app->language->getLanguage();
		$this->data['fields'] = Users::getUserFieldsByGroup($this->app->user->group_id);
		$this->data['user'] = $this->app->user;
		
		$this->data['tab_content'] = $this->getTab('edit');

		if (empty($this->data['edit_tab'])) $this->data['edit_tab'] = 'user_data';
		$this->data['edit_tab_content'] = $this->getTab('edit_user_data');
		$this->data['edit_tab_secret'] = $this->getTab('edit_tab_secret');
		
		// Вывод представления с ответом
		$this->viewAnswer($answer, 'profile');
	}
	
	public function ActionEditData() {
		$this->data['edit_tab'] = 'user_data';
		$answer = array(
			//'status' => true,
			'errors' => array(),
			'message' => $this->lang->edit_profile_success,
		);
		
		if (isset($this->request->post['process'])) {
			try {
				$post = $this->request->post;
				$valid = true;
				
				$fields = Users::getUserFieldsByGroup($this->config->getOption('default_group_id'));
				
				foreach ($fields as $field) {
					if ($field['options']['required'] && empty($post[$field['name']])) {
						$answer['errors'][$field['name']] = $this->lang->field_required;
						$valid = false;
					}
				}
				
				if (empty($post['email']) && preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $post['email'])) {
					$answer['errors']['email'] = $this->lang->invalid_field;
					$valid = false;
				}
				
				if ($post['email'] != $this->app->user->email) {
					$user = new User(array('email'=>$post['email']));
					if ($user->exists()) {
						$answer['errors']['email'] = $this->lang->email_exists;
						$valid = false;
						throw new \Exception($this->lang->email_exists);
					}
				}
				
				if (!$valid) throw new \Exception($this->lang->invalid_form);

				$user = $this->app->user;
				
				$user->email = $post['email'];
				foreach ($fields as $field) {
					$value = $post[$field['name']];
					$user->setFieldData($field['id'], $value);
				}
				
				$user->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('user', 'profile', 'edit'), $this->lang->profile_title, $this->lang->edit_profile_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
				//$answer['errors'][] = $error->getMessage();
			}
		}
		
		$this->ActionEdit($answer);
	}
	
	public function ActionChangePassword() {
		$this->data['edit_tab'] = 'secret';
		$answer = array(
			//'status' => true,
			'errors' => array(),
			'message' => $this->lang->change_password_success,
		);
		
		if (isset($this->request->post['process'])) {
			try {
				$post = $this->request->post;
				$valid = true;

				if (empty($post['password'])) {
					$answer['errors']['password'] = $this->lang->field_required;
					$valid = false;
				}

				if (mb_strlen($post['password']) < 6) {
					$answer['errors']['password'] = $this->lang->pass_short;
					$valid = false;
				}
				
				if ($post['password'] != $post['confirm']) {
					$answer['errors']['confirm'] = $this->lang->error_confirm_pass;
					$valid = false;
				}
				
				if (!$valid) throw new \Exception($this->lang->invalid_form);

				$user = $this->app->user;

				$user->salt = Users::generateSalt();
				$password = Users::cryptPassword($post['password'], $user->salt);
				$user->password = $password;
				
				$user->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('user', 'profile', 'edit'), $this->lang->profile_title, $this->lang->change_password_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
				//$answer['errors'][] = $error->getMessage();
			}
		}
		
		$this->ActionEdit($answer);		
	}
	
	public function initialize() {
		$this->loadLanguage('profile');

		$this->data['lang'] = $this->getLanguage();
		
		$app = $this->app;
		
		if (!Users::isLogged()) {
			$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->you_logged, 'error');
			$app->stop();
		}
		
		$this->user = $this->app->user;
		$this->data['user'] = $this->user;
		
		$app->template->addBreadcrumb($this->lang->main_page, $app->url->module('index'), false);
	}

	protected function getTab($tab) {
		$this->data['tab'] = $tab;
		return $this->getViewPath('profile/'.$tab);
	}
}
?>