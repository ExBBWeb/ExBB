<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

class ControllerForumsIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');

		$app = $this->app;
		$app->template->page_title = $this->lang->forums;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($this->lang->forums, $this->app->url->module('forums'), true);

		$data = new \StdClass();
		
		$data->forums = array();
		
		$result = $this->db->query('SELECT * FROM '.DB_PREFIX.'forums ORDER BY position ASC');
		while ($row = $this->db->fetchAssoc($result)) {
			$data->forums[$row['category_id']][$row['parent_id']][$row['id']] = $row;
		}

		$data->categories = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'categories');
		
		$this->data['data'] = $data;
		
		$this->view('index');
	}
}
?>