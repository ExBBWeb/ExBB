<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\User;
use Core\Library\User\Users;

use Core\Classes\Image;

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
		
		$this->data['tab'] = 'index';
		$this->data['tab_content'] = $this->getTab('index');
		$this->view('profile');
	}

	public function ActionEdit($answer=false) {
		$app = $this->app;
		$app->template->addBreadcrumb($this->lang->profile_page, $app->url->module('user', 'profile', 'index'), false);
		$app->template->addBreadcrumb($this->lang->profile_edit, $app->url->module('user', 'profile', 'edit'), true);
		$app->template->page_title = $this->lang->profile_page;
		
		$this->data['tab'] = 'edit';
		
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
		$this->data['edit_tab_avatar'] = $this->getTab('edit_tab_avatar');
		$this->data['edit_tab_signature'] = $this->getTab('edit_tab_signature');
		
		$avatars = scandir(BASE.'/uploads/avatars/default/');
		unset($avatars[0], $avatars[1]);
		$this->data['avatars'] = $avatars;
		
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
	
	public function ActionEditAvatar() {
		$this->data['edit_tab'] = 'avatar';
		$answer = array(
			//'status' => true,
			'errors' => array(),
			'message' => $this->lang->upload_avatar_success,
		);
		
		if (isset($this->request->post['process'])) {
			try {
				$post = $this->request->post;
				$valid = true;

				$avatar_dir = ROOT.'/uploads/avatars/';
				
				$user = $this->app->user;
				
				if (!empty($_FILES['avatar']['name'])) {
					$avatar = $_FILES['avatar'];

					$imageinfo = getimagesize($avatar['tmp_name']);

					$types = mb_strtolower($this->app->config->getOption('user_avatar_allowed_types'));
					$parts = explode('.', $avatar['name']);
					$file_type = mb_strtolower(end($parts));
					
					// Проверка на достустимое расширение файла
					if ($types != '*') {
						$allowed_types = explode(',', str_replace(' ','', $types));
						
						if (!in_array($file_type, $allowed_types)) {
							$answer['errors']['avatar'] = $this->lang->invalid_image_format;
							throw new \Exception($this->lang->invalid_form);
						}
					}
					
					// Проверка на достустимый MIME-тип файла
					if ($this->app->config->getOption('user_avatar_enable_check_mime') == 1) {
						$types = mb_strtolower($this->app->config->getOption('user_avatar_allowed_mime_types'));
						
						if ($types != '*') {
							$allowed_mime_types = explode(',', str_replace(' ','', $types));

							if (!in_array($imageinfo['mime'], $allowed_mime_types)) {
								$answer['errors']['avatar'] = $this->lang->invalid_image_format;
								throw new \Exception($this->lang->invalid_form);
							}
						}
					}

					$max_width = $this->app->config->getOption('user_avatar_max_width');
					$max_height = $this->app->config->getOption('user_avatar_max_height');
					$max_kb_size = $this->app->config->getOption('user_avatar_max_size');

					if ($imageinfo[0] > $max_width || $imageinfo[1] > $max_height) {
						$answer['errors']['avatar'] = $this->lang->invalid_image_size;
						throw new \Exception($this->lang->invalid_form);
					}
					
					$size = filesize($avatar['tmp_name'])/1024;
					
					if ($size > $max_kb_size) {
						$answer['errors']['avatar'] = $this->lang->invalid_image_file_size;
						throw new \Exception($this->lang->invalid_form);
					}

					$type = str_replace('image/', '', $imageinfo['mime']);
					$unicname = uniqid().'.'.$type;

					$path = $avatar_dir.$unicname;

					if(!is_uploaded_file($avatar['tmp_name'])) {
						$answer['errors']['avatar'] = $this->lang->avatar_upload_error;
						throw new \Exception($this->lang->invalid_form);
					}
				
					if (!move_uploaded_file($avatar['tmp_name'], $path)) {
						$answer['errors']['avatar'] = $this->lang->avatar_upload_error;
						throw new \Exception($this->lang->invalid_form);
					}

					if (file_exists(ROOT.'/uploads/avatars/'.$user->avatar) && !stristr($user->avatar, 'default') && $user->avatar != 'uploads/avatars/'.$this->app->config->getOption('default_user_avatar')) {
						unlink(ROOT.'/uploads/avatars/'.$user->avatar);
					}
					
					$resize_x = $this->app->config->getOption('user_avatar_resize_x');
					$resize_y = $this->app->config->getOption('user_avatar_resize_y');
					
					$image = new Image(ROOT.'/uploads/avatars/'.$unicname);
					$image->resize($resize_x, $resize_y, Image::RESIZE_USE_WIDTH);
					$image->crop($resize_x, $resize_y);
					
					$image->save();
					
					$user->avatar = $unicname;
				}
				else {
					if (empty($post['default_avatar'])) {
						$answer['errors']['default_avatar'] = $this->lang->field_required;
						throw new \Exception($this->lang->invalid_form);
					}
					
					if (file_exists(ROOT.'/uploads/avatars/'.$user->avatar) && !stristr($user->avatar, 'default') && $user->avatar != 'uploads/avatars/'.$this->app->config->getOption('default_user_avatar')) {
						unlink(ROOT.'/uploads/avatars/'.$user->avatar);
					}
					
					$user->avatar = 'default/'.$post['default_avatar'];
				}

				if (!$valid) throw new \Exception($this->lang->invalid_form);

				$user->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('user', 'profile', 'edit'), $this->lang->profile_title, $this->lang->upload_avatar_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
				//$answer['errors'][] = $error->getMessage();
			}
		}
		
		$this->ActionEdit($answer);
	}
	
	public function ActionEditSignature() {
		$this->data['edit_tab'] = 'signature';
		$answer = array(
			//'status' => true,
			'errors' => array(),
			'message' => $this->lang->edit_signature_success,
		);
		
		if (isset($this->request->post['process'])) {
			try {
				$post = $this->request->post;
				$valid = true;

				if (mb_strlen($post['signature']) > $this->app->config->getOption('user_signature_max_length')) {
					$answer['errors']['signature'] = $this->lang->long_signature;
					$valid = false;
				}
				
				$strings = count(explode("\n", $post['signature']));
				if ($strings > $this->app->config->getOption('user_signature_max_strings')) {
					$answer['errors']['signature'] = $this->lang->many_signature_strings;
					$valid = false;
				}

				if (!$valid) throw new \Exception($this->lang->invalid_form);

				$user = $this->app->user;
				
				$user->signature = $post['signature'];

				$user->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('user', 'profile', 'edit'), $this->lang->profile_title, $this->lang->edit_signature_success);
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
		
		$app->user->autosave = false;
		
		if (!Users::isLogged()) {
			$app->redirectPage($app->url->module('user', 'profile'), $this->lang->auth_title, $this->lang->you_logged, 'error');
			$app->stop();
		}
		
		$this->user = $this->app->user;
		$this->data['user'] = $this->user;
		
		$app->template->addBreadcrumb($this->lang->main_page, $app->url->module('index'), false);
	}

	
	protected function getTab($tab) {
		return $this->getViewPath('profile/'.$tab);
	}
}
?>