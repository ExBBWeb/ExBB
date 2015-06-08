<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;

class Controller404Index extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');
		$this->app->template->page_title = $this->lang->not_found_error_title;
		$this->app->template->addBreadcrumb($this->lang->main_page, $this->app->url->module('index'), false);
		$this->app->template->addBreadcrumb($this->lang->not_found_error_title, $this->app->url->module('404'), true);
		
		$this->view('index');
	}
}
?>