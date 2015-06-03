<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\Article;

class ControllerIndexIndex extends BaseController {
	public function ActionIndex() {
		$this->loadLanguage('index');
		$lang = $this->getLanguage();

		$this->app->template->title .= ' - '.$lang->page_title;
		$this->app->template->addBreadcrumb($lang->page_title, $this->app->url->module('index'));
		$this->app->template->addBreadcrumb($lang->page_title, $this->app->url->module('index'));
		$this->app->template->addBreadcrumb($lang->page_title, $this->app->url->module('index'));
		$this->app->template->addBreadcrumb($lang->page_title, $this->app->url->module('index'));
		
		$this->view('index');
	}
}
?>