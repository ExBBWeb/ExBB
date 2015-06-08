<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Extension\Extend;

use Core\Library\Site\Entity\Forum;

use Core\Library\User\Access\Access;

class ControllerForumIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');
		$app = $this->app;
		
		$lang = $this->lang;

		$forum_id = (int)$this->app->router->getVar('param');

		if (!$forum_id) {
			// Страница редиректа
			$app->redirectPage($app->url->module('index'), $this->lang->forum, $this->lang->forum_not_exists);
			$app->stop();
		}
		
		$forum = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'forums WHERE id='.$forum_id);
		
		if (!isset($forum->id)) {
			// Страница редиректа
			$app->redirectPage($app->url->module('users'), $this->lang->forum, $this->lang->forum_not_exists);
			$app->stop();
		}
		
		$access = new Access();
		$read_access = (bool)$access->getForumAccess($forum_id, 'read');
		
		if (!$read_access) {
			// Страница редиректа
			$app->redirectPage($app->url->module('users'), $this->lang->forum, $this->lang->forum_not_exists);
			$app->stop();
		}
		
		$category = $this->db->getObject('SELECT * FROM '.DB_PREFIX.'categories WHERE id='.$forum->category_id);
		
		$this->app->template->page_title = $forum->title;
		$this->app->template->addBreadcrumb($lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($category->title, $this->app->url->module('index', 'index', 'index', $category->id), false);
		$this->app->template->addBreadcrumb($forum->title, $this->app->url->module('forum', 'index', 'index', $forum->id), true);
		
		$data = new \StdClass();

		$data->category = $category;
		$data->forum = $forum;
		$data->topics = array();

		$topics_on_page = (int)$this->app->config->getOption('topics_on_page');
		
		$count = $this->db->getRow('SELECT COUNT(*) as count FROM '.DB_PREFIX.'topics WHERE forum_id='.$forum_id);
		$count = $count['count'];
		$pages = ceil($count/$topics_on_page);
	
		$page = (isset($this->request->get['page'])) ? (int)$this->request->get['page'] : 1;
		$start = $topics_on_page*($page-1);

		$data->pages = $pages;
		$data->on_page = $topics_on_page;
		$data->count = $count;
		$data->page = $page;
		
		$default_icons = $this->app->config->getOption('topic_default_icons');
		$icons_path = ROOT_URL.'/media/images/topic_icons';
		
		$result = $this->db->query('SELECT t.id, t.author_id, t.author_login, t.title, t.description, t.posts, t.created_date,
			t.updated_date, t.is_important, t.is_fixed, t.views,
		p.author_id as post_author_id, p.author_login as post_author_login, p.created_date as post_created_date
		FROM '.DB_PREFIX.'topics t
		LEFT JOIN '.DB_PREFIX.'posts p ON (p.id=t.updated_post_id)
		WHERE t.forum_id='.$forum_id.'
		ORDER BY t.updated_date DESC, t.is_fixed DESC LIMIT '.$start.','.$topics_on_page);
		// LIMIT '.$start.','.(int)$topics_on_page
		while ($row = $this->db->fetchAssoc($result)) {
			$row['readed'] = true;

			if ($row['readed']) {
				$row['icon'] = $icons_path.'/'.$default_icons.'_read.png';
			}
			else{
				$row['icon'] = $icons_path.'/'.$default_icons.'_unread.png';
			}
			
			$data->topics[$row['id']] = $row;
		}
		
		$data->add_topic_access = (bool)$access->getForumAccess($forum_id, 'add_topic');
		$data->add_poll_access = (bool)$access->getForumAccess($forum_id, 'add_poll');

		Extend::setAction('forum_page_prepare_data', $data);
		
		$this->data['data'] = $data;
		
		$this->view('index');
	}
}
?>