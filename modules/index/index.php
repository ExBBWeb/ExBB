<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Extension\Extend;

use Core\Library\Site\Entity\Forum;
use Core\Library\Site\Entity\Topic;
use Core\Library\Site\Entity\Post;

use Core\Library\User\Access\Access;

class ControllerIndexIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');
		$lang = $this->getLanguage();

		$this->app->template->title .= ' - '.$lang->main_page;
		$this->app->template->addBreadcrumb($lang->main_page, $this->app->url->module('index'), true);
		
		$data = new \StdClass();
		
		$data->categories = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'categories ORDER BY position ASC');
		$data->forums = array();

		$access = new Access();
		$forum_access = (bool)$access->getDefaultForumAccess('read');

		$category_id = $this->app->router->getVar('param');
		if ($category_id) {
			$where_category = ' WHERE f.category_id='.(int)$category_id;
		}
		else $where_category = '';
		
		$result = $this->db->query('SELECT f.id,f.category_id, f.title,f.posts,f.topics, f.updated_topic_id, f.updated_post_id, f.status_icon,
		p.created_date as update_date,p.author_id,p.author_login,
		t.title as topic_title,
		ac.access_value

		FROM '.DB_PREFIX.'forums f
		LEFT JOIN '.DB_PREFIX.'posts p  ON (p.id=f.updated_post_id)
		LEFT JOIN '.DB_PREFIX.'topics t  ON (t.id=f.updated_topic_id)
		LEFT JOIN '.DB_PREFIX.'groups_forum_access ac ON (ac.group_id='.(int)$this->app->user->group_id.' AND ac.forum_id=f.id AND ac.access_name="read")
		
		'.$where_category.'
		ORDER BY f.position ASC
		');

		$this->data['forums'] = array();
		
		$default_icons = $this->app->config->getOption('forum_default_icons');

		$icons_path = ROOT_URL.'/media/images/forum_icons';
		
		while ($row = $this->db->fetchAssoc($result)) {
			if (is_null($row['access_value'])) {
				if (!$forum_access) continue;
			}
			else  {
				if ($row['access_value'] == false) continue;
			}

			$row['readed'] = true;
			
			if (empty($row['status_icon'])) $row['status_icon'] = $default_icons;
			
			if ($row['readed']) {
				$row['icon'] = $icons_path.'/'.$row['status_icon'].'_read.png';
			}
			else{
				$row['icon'] = $icons_path.'/'.$row['status_icon'].'_unread.png';
			}
			
			$data->forums[$row['category_id']][$row['id']] = $row;
		}

		Extend::setAction('index_page_prepare_data', $data);
		
		$this->data['data'] = $data;
		
		$this->view('index');
	}
}
?>