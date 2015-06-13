<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

use Core\Library\Site\Entity\Category;
use Core\Library\Site\Entity\Forum;

class ControllerForumsForum extends BaseController {
	public function ActionIndex() {
		$this->ActionAdd();
	}
	
	public function ActionAdd() {
		$this->loadLanguage('forum');

		$app = $this->app;
		$app->template->page_title = $this->lang->create_forum;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($this->lang->forums, $this->app->url->module('forums'), false);
		$this->app->template->addBreadcrumb($this->lang->create_forum, $this->app->url->module('forums', 'forum', 'add'), true);

		$answer = array(
			'errors' => array(),
			'message' => $this->lang->add_forum_success,
		);
		
		$helper = $this->loadHelper('forumstree');

		$this->data['tree_helper'] = $helper;
		
		if (isset($this->request->post['process'])) {
			$valid = true;
			try {
				$post = $this->request->post;
				
				if (empty($post['title'])) {
					$answer['errors']['title'] = $this->lang->field_required;
					$valid = false;
				}
				
				$category = new Category($post['category_id']);
				if (!$category->exists()) {
					$answer['errors']['category_id'] = $this->lang->error_select_category;
					$valid = false;
				}
				else {
					if ($post['parent_id'] > 0) {
						$parent = new Forum($post['parent_id']);
						if (!$parent->exists()) {
							$answer['errors']['parent_id'] = $this->lang->error_select_parent;
							$valid = false;
						}
						
						if ($parent->category_id != $category->id) {
							$answer['errors']['parent_id'] = $this->lang->category_error_select_parent;
							$valid = false;
						}
					}
				}
				
				if (!$valid) throw new \Exception($this->lang->invalid_form);
				
				$forum = new Forum();
				$forum->title = $post['title'];
				$forum->position = (int)$post['position'];
				$forum->category_id = (int)$post['category_id'];
				$forum->parent_id = (int)$post['parent_id'];
				$forum->posts = 0;
				$forum->topics = 0;
				$forum->updated_topic_id = 0;
				$forum->updated_post_id = 0;
				$forum->status_icon = $post['status_icon'];
				
				$forum->save();
				
				$answer['status'] = true;
				$this->app->redirectPage($this->app->url->module('forums'), $this->lang->forum, $this->lang->add_forum_success);
			}
			catch (\Exception $error) {
				$answer['status'] = false;
				$answer['message'] = $error->getMessage();
			}
		}
		
		if (isset($this->request->get['category'])) $this->data['category_id'] = (int)$this->request->get['category'];
		if (isset($this->request->get['parent'])) $this->data['parent_id'] = (int)$this->request->get['parent'];
		
		$this->viewAnswer($answer, 'forum/form');
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