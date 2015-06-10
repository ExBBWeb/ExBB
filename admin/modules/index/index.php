<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

class ControllerIndexIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');

		$app = $this->app;
		//$app->template->page_title = $home;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), true);
		
		$this->data['board_start_date'] = $app->config->getOption('board_start_date');
		
		$this->data['topics'] = array_shift($this->db->getRow('SELECT COUNT(*) as count FROM '.DB_PREFIX.'topics'));
		$this->data['posts'] = array_shift($this->db->getRow('SELECT COUNT(*) as count FROM '.DB_PREFIX.'posts'));
		$this->data['users'] = array_shift($this->db->getRow('SELECT COUNT(*) as count FROM '.DB_PREFIX.'users'));
		
		$this->view('index');
	}
}
?>