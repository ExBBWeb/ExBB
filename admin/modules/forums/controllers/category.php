<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

use Core\Library\Site\Entity\Category;

class ControllerForumsCategory extends BaseController {
	public function ActionIndex() {
		$this->ActionAdd();
	}
	
	public function ActionAdd() {
		$this->loadLanguage('category');

		$app = $this->app;
		$app->template->page_title = $this->lang->create_category;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($this->lang->categories, $this->app->url->module('forums', 'category'), false);
		$this->app->template->addBreadcrumb($this->lang->create_category, $this->app->url->module('forums', 'category', 'add'), true);

		$answer = array(
			'errors' => array(),
			'message' => $this->lang->add_category_success,
		);
		
		if (isset($this->request->post['process'])) {
			$valid = true;
			try {
				$post = $this->request->post;
				
				if (empty($post['title'])) {
					$answer['errors']['title'] = $this->lang->field_required;
					$valid = false;
				}
				
				if (!$valid) throw new \Exception($this->lang->invalid_form);
				
				$category = new Category();
				$category->title = $post['title'];
				$category->position = (int)$post['position'];
				$category->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('forums'), $this->lang->category, $this->lang->add_category_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
			}
		}
		
		$this->viewAnswer($answer, 'category/form');
	}
	
	public function ActionEdit() {
		$this->loadLanguage('category');

		$app = $this->app;
		
		$id = (int)$this->app->router->getVar('param');

		if (!$id) {
			// Страница редиректа
			$app->redirectPage($app->url->module('forums'), $this->lang->category, $this->lang->category_not_exists);
			$app->stop();
		}
		
		$category = new Category($id);

		if (!$category->exists()) {
			// Страница редиректа
			$app->redirectPage($app->url->module('forums'), $this->lang->category, $this->lang->category_not_exists);
			$app->stop();
		}
	
		$app->template->page_title = $this->lang->edit_category;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($this->lang->categories, $this->app->url->module('forums', 'category'), false);
		$this->app->template->addBreadcrumb($this->lang->edit_category, $this->app->url->module('forums', 'category', 'edit', $id), true);

		$answer = array(
			'errors' => array(),
			'message' => $this->lang->edit_category_success,
		);
		
		if (isset($this->request->post['process'])) {
			$valid = true;
			try {
				$post = $this->request->post;
				
				if (empty($post['title'])) {
					$answer['errors']['title'] = $this->lang->field_required;
					$valid = false;
				}
				
				if (!$valid) throw new \Exception($this->lang->invalid_form);

				$category->title = $post['title'];
				$category->position = (int)$post['position'];
				$category->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('forums'), $this->lang->category, $this->lang->edit_category_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
			}
		}
		
		$this->data['category'] = $category;
		
		$this->viewAnswer($answer, 'category/form');
	}
	
	public function ActionDelete() {
		$this->loadLanguage('category');
		$app = $this->app;
		$category_id = (int)$this->app->router->getVar('param');

		if (!$category_id) {
			// Страница редиректа
			$app->redirectPage($app->url->module('forums'), $this->lang->category, $this->lang->category_not_exists);
			$app->stop();
		}
		
		$category = new Category($category_id);

		if (!$category->exists()) {
			// Страница редиректа
			$app->redirectPage($app->url->module('forums'), $this->lang->category, $this->lang->category_not_exists);
			$app->stop();
		}
		
		
		// Удаление форумов
		$this->db->query('DELETE FROM '.DB_PREFIX.'forums WHERE category_id='.$category->id);
		// Удаление тем		
		$this->db->query('DELETE FROM '.DB_PREFIX.'topics WHERE category_id='.$category->id);
		// Удаление сообщений
		$this->db->query('DELETE FROM '.DB_PREFIX.'posts WHERE category_id='.$category->id);
		
		// Удаление опросов
		$polls = $this->db->getIndexedAll('id', 'SELECT id FROM '.DB_PREFIX.'polls WHERE category_id='.$category_id);
		$keys = array_keys($polls);
		$keys = array_chunk($keys, 25);
		foreach ($keys as $ids_array) {
			$this->db->query('DELETE FROM '.DB_PREFIX.'polls_variants WHERE poll_id IN ('.implode(',', $ids_array).')');
			$this->db->query('DELETE FROM '.DB_PREFIX.'polls_votes WHERE poll_id IN ('.implode(',', $ids_array).')');
		}
		$this->db->query('DELETE FROM '.DB_PREFIX.'polls WHERE category_id='.$category_id);
		
		// Удаление самой категории
		$category->delete();
		$app->redirectPage($app->url->module('forums'), $this->lang->category, $this->lang->delete_category_success);
	}
}
?>