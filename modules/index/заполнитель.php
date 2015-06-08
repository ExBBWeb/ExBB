<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Extension\Extend;

use Core\Library\Site\Entity\Forum;
use Core\Library\Site\Entity\Topic;

use Core\Library\User\Access\Access;

class ControllerIndexIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');
		$lang = $this->getLanguage();

		$this->app->template->title .= ' - '.$lang->main_page;
		$this->app->template->addBreadcrumb($lang->main_page, $this->app->url->module('index'), true);
		
		
		for ($i=0;$i<100;$i++) {
			$forum = new Forum();
			$forum->autosave = false;
			$forum->category_id = rand(1,4);
			$forum->title = 'Форум '.$i;
			$forum->topics = 1;
			$forum->posts = 1;
			$forum->position = 1;
			$forum->save();
			
			$fid = $forum->id;
			
			for ($j=0;$j<100;$j++) {
				$topic = new Topic();
				$topic->category_id = $forum->category_id;
				$topic->forum_id = $fid;
				$topic->author_id = 1;
				$topic->author_login = 'Admin';
				$topic->title = 'Тема '.$j;
				$topic->description = '';
				$topic->created_date = 'NOW()';
				$topic->posts = 100;
				$topic->is_important = 0;
				$topic->is_fixed = 0;
			}
		}
		
		$result = $this->db->query('SELECT * FROM '.DB_PREFIX.'topics');
		while ($row = $this->db->fetchAssoc($result)) {
			for ($i=0;$i<100;$i++) {
				$post = new Post();
				$post->autosave = false;
				$post->category_id = $row['category_id'];
				$post->forum_id = $row['forum_id'];
				$post->topic_id = $row['id'];
				$post->author_id = $row['author_id'];
				$post->author_login = $row['author_login'];
				$post->text = str_repeat('Привет! ', 100);
				$post->created_date = 'NOW()';
				$post->save();
				
			}
		}
		
		die;
		
		$data = new \StdClass();
		
		$data->categories = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'categories ORDER BY position ASC');
		$data->forums = array();
		
		$access = new Access();
		$forum_access = $access->getDefaultForumAccess('read');
		
		$mt = microtime(true);
		
		$result = $this->db->query('SELECT f.id,f.category_id, f.title,f.posts,f.topics,
		p.id,p.created_date,p.author_id,p.author_login,p.topic_id,
		ac.access_value

		FROM '.DB_PREFIX.'forums f
		LEFT JOIN '.DB_PREFIX.'posts p 
		ON (p.forum_id=f.id AND created_date =
			(SELECT MAX(created_date) FROM '.DB_PREFIX.'posts p2 WHERE p2.forum_id=f.id)
		)
		LEFT JOIN '.DB_PREFIX.'groups_forum_access ac ON (ac.group_id='.(int)$this->app->user->group_id.' AND ac.forum_id=f.id AND ac.access_name="read")
		
		ORDER BY f.position ASC
		');
		
		$this->data['forums'] = array();
		while ($row = $this->db->fetchAssoc($result)) {
			if ((empty($row['access_value']) && !$forum_access) || $row['access_value'] == 0) continue;
			$data->forums[$row['category_id']][$row['id']] = $row;
		}

		$t = microtime(true)-$mt;
		//echo round($t, 5);
		
		Extend::setAction('index_page_prepare_data', $data);
		
		$this->data['data'] = $data;
		
		$this->view('index');
	}
}
?>