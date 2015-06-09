<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Extension\Extend;
use Core\Library\BBCodes\BB;
use Core\Library\User\Access\Access;

class ControllerTopicIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');
		$app = $this->app;

		$topic_id = (int)$this->app->router->getVar('param');

		if (!$topic_id) {
			// Страница редиректа
			$app->redirectPage($app->url->module('index'), $this->lang->topic, $this->lang->topic_not_exists);
			$app->stop();
		}
		
		$topic = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'topics WHERE id='.$topic_id);

		if (!isset($topic->id)) {
			// Страница редиректа
			$app->redirectPage($app->url->module('users'), $this->lang->topic, $this->lang->topic_not_exists);
			$app->stop();
		}

		$forum = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'forums WHERE id='.$topic->forum_id);
		$forum_id = $forum->id;

		$access = new Access();
		$read_access = (bool)$access->getForumAccess($forum_id, 'read');
		
		if (!$read_access) {
			// Страница редиректа
			$app->redirectPage($app->url->module('users'), $this->lang->topic, $this->lang->topic_not_exists);
			$app->stop();
		}
		
		$category = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'categories WHERE id='.$forum->category_id);
		
		$this->app->template->page_title = $topic->title;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($category->title, $this->app->url->module('index', 'index', 'index', $category->id), false);
		$this->app->template->addBreadcrumb($forum->title, $this->app->url->module('forum', 'index', 'index', $forum->id), false);
		$this->app->template->addBreadcrumb($topic->title, $this->app->url->module('topic', 'index', 'index', $topic->id), true);
		
		$this->db->query('UPDATE '.DB_PREFIX.'topics SET views=views+1 WHERE id='.$topic_id);
		
		$data = new \StdClass();

		$data->category = $category;
		$data->forum = $forum;
		$data->topic = $topic;
		$data->posts = array();
		$data->authors = array();
		
		$posts_on_page = (int)$this->app->config->getOption('posts_on_page');
		
		$count = $this->db->getRow('SELECT COUNT(*) as count FROM '.DB_PREFIX.'posts WHERE topic_id='.$topic_id);
		$count = $count['count'];
		$pages = ceil($count/$posts_on_page);
	
		$page = (isset($this->request->get['page'])) ? (int)$this->request->get['page'] : 1;
		$start = $posts_on_page*($page-1);

		$data->pages = $pages;
		$data->on_page = $posts_on_page;
		$data->count = $count;
		$data->page = $page;

		$default_avatar = $app->config->getOption('default_user_avatar');
		$avatars_path = ROOT_URL.'/uploads/avatars';
		
		
		// ID авторов сообщений
		$uids = array();
		
		$bb = new BB();
		
		$result = $this->db->query('SELECT * FROM '.DB_PREFIX.'posts WHERE topic_id='.$topic_id.'
		ORDER BY created_date DESC LIMIT '.$start.','.$posts_on_page);
		while ($row = $this->db->fetchAssoc($result)) {
			$row['text'] = $bb->parse($row['text']);
			$data->posts[$row['id']] = $row;
			$uids[] = $row['author_id'];
		}
		
		if (count($uids) >= 1) {
			$result = $this->db->query('SELECT * FROM '.DB_PREFIX.'users WHERE id IN ('.implode(',', $uids).')');
			while ($row = $this->db->fetchAssoc($result)) {
				if (empty($row['avatar'])) $row['avatar'] = $default_avatar;
				$row['avatar'] = $avatars_path.'/'.$row['avatar'];
				$data->authors[$row['id']] = $row;
			}
		}

		$data->add_topic_access = (bool)$access->getForumAccess($forum_id, 'add_topic');
		$data->add_poll_access = (bool)$access->getForumAccess($forum_id, 'add_poll');
		$data->add_post_access = (bool)$access->getForumAccess($forum_id, 'add_post');
		$data->moderation_access = (bool)$access->getForumAccess($forum_id, 'moderation');

		Extend::setAction('topic_page_prepare_data', $data);
		
		$this->data['data'] = $data;
		
		$this->view('index');
	}
}
?>