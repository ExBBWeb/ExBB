<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Extension\Extend;

use Core\Library\User\Access\Access;

use Core\Library\Site\Entity\Forum;
use Core\Library\Site\Entity\Topic;
use Core\Library\Site\Entity\Post;

class ControllerTopicAdd extends BaseController {
	protected $is_poll = false;
	
	public function ActionIndex() {
		$this->loadLanguage('add');
		$app = $this->app;

		$forum_id = (int)$this->app->router->getVar('param');

		if (!$forum_id) {
			// Страница редиректа
			$app->redirectPage($app->url->module('index'), $this->lang->add_topic_title, $this->lang->forum_not_exists);
			$app->stop();
		}
		
		//$forum = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'forums WHERE id='.$forum_id);
		$forum = new Forum($forum_id);
		
		if (!isset($forum->id)) {
			// Страница редиректа
			$app->redirectPage($app->url->module('users'), $this->lang->add_topic_title, $this->lang->forum_not_exists);
			$app->stop();
		}
		
		$access = new Access();
		$add_topic_access = (bool)$access->getForumAccess($forum_id, 'add_topic');
		
		if (!$add_topic_access) {
			// Страница редиректа
			$app->redirectPage($app->url->module('users'), $this->lang->add_topic_title, $this->lang->forum_not_exists);
			$app->stop();
		}
		
		$category = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'categories WHERE id='.$forum->category_id);
		
		$this->app->template->page_title = $forum->title;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($category->title, $this->app->url->module('index', 'index', 'index', $category->id), false);
		$this->app->template->addBreadcrumb($forum->title, $this->app->url->module('forum', 'index', 'index', $forum->id), false);
		$this->app->template->addBreadcrumb($this->lang->add_topic_title, $this->app->url->module('topic', 'add', 'index', $forum->id), true);

		
		$data = new \StdClass();

		
		
		$answer = array(
			'errors' => array(),
			'message' => $this->lang->add_topic_success,
		);
		
		$topic = new Topic();
		
		$data->category = $category;
		$data->forum = $forum;
		$data->topic = $topic;

		if (isset($this->request->post['process'])) {
			$this->save($data, $answer);
		}
		
		$data->is_poll = $this->is_poll;
		if ($this->is_poll) {
			$data->add_poll_form = $this->getViewPath('add/poll');
		}
		
		Extend::setAction('topic_add_prepare_data', $data);
		
		$this->data['data'] = $data;
		
		$this->viewAnswer($answer, 'form');
	}
	
	public function ActionPoll() {
		$this->is_poll = true;
		$this->ActionIndex();
	}
	
	protected function save($data, &$answer) {
		try {
			$post = $this->request->post;
			$valid = true;
			
			if (empty($post['title'])) {
				$answer['errors']['title'] = $this->lang->field_required;
				$valid = false;
			}
			
			$post_min_length = $this->app->config->getOption('post_min_length');
			
			if (empty($post['post']) || mb_strlen($post['post']) < $post_min_length) {
				$answer['errors']['post'] = $this->lang->short_post;
				$valid = false;
			}
			
			if (!$valid) throw new \Exception($this->lang->invalid_form);
			
			// Создаём тему
			$topic = $data->topic;
			$topic->category_id = $data->forum->category_id;
			$topic->forum_id = $data->forum->id;
			$topic->author_id = $this->app->user->id;
			$topic->author_login = $this->app->user->login;
			$topic->title = htmlentities($post['title']);
			$topic->description = htmlentities($post['description']);
			$topic->keywords = htmlentities($post['keywords']);
			$topic->created_date = 'NOW()';
			$topic->updated_date = 'NOW()';
			$topic->posts = 1;
			$topic->views = 0;
			$topic->is_important = 0;
			$topic->is_fixed = 0;
			
			$topic->save();
			$topic_id = $topic->id;
			
			// Создаём сообщение
			$message = new Post();
			$message->category_id = $data->forum->category_id;
			$message->forum_id = $data->forum->id;
			$message->topic_id = $topic_id;
			$message->author_id = $this->app->user->id;
			$message->author_login = $this->app->user->login;
			$message->text = $post['post'];
			$message->created_date = 'NOW()';
			$message->modified_date = 'NOW()';
			$message->save();
			
			$post_id = $message->id;
			
			// Обновляем информацию о последнем сообщении в теме
			$topic->updated_post_id = $post_id;
			$topic->save();

			// Обновляем информацию в форуме
			$forum = $data->forum;
			$forum->topics++;
			$forum->posts++;
			$forum->updated_topic_id = $topic_id;
			$forum->updated_post_id = $post_id;
			$forum->save();
			
			// Обновляем информацию в подфоруме (если он существует)
			if ($forum->parent_id) {
				$parent = new Forum($forum->parent_id);
				if ($parent->exists()) {
					$parent->updated_topic_id = $topic_id;
					$parent->updated_post_id = $post_id;
					$parent->save();
				}
			}
			
			// Обновляем количество сообщений у пользователя
			$this->app->user->posts++;
			$this->app->user->save();
			
			$new_topic_url = $this->app->url->module('topic', 'index', 'index', $topic_id);
			
			$answer['status'] = true;
			$this->app->redirectPage($new_topic_url, sprintf($this->lang->add_topic_success, $new_topic_url));
			$answer['message'] = sprintf($this->lang->add_topic_success, $new_topic_url);
		}
		catch (\Exception $error) {
			$answer['status'] = false;
			$answer['message'] = $error->getMessage();
		}
	}
}
?>