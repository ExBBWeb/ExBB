<?php
namespace Core\Library\MVC;

use Core\Library\Application\Application;
use Core\Library\View\Template;

/**
 * Класс controller является родителем для любого контроллёра в системе.
 */
class BaseWidget {
	
	protected $path;
	protected $widget;

	protected $app;
	
	protected $lang = array();
	
	/**
	* Инициализирует контроллер
	* @param string $path полный путь к файлу контроллёра
	* @return void
	*/
	public function __construct($path, $widget) {
		$this->path = $path;
		$this->widget = $widget;
		
		$app = Application::getInstance();

		$this->app = $app;
	}
	
	/**
	* Инициализирует действие по-умолчанию
	* @return void
	*/
	public function ActionIndex() {
		
	}

	public function loadLanguage($file, $lang='default') {
		if ($lang == 'default') $lang = LANGUAGE;
		$path = $this->path.'/language/'.$lang.'/'.$file.'.php';
		
		if (!file_exists($path)) return false;
		
		include $path;
		if (isset($lang)) $this->lang = array_merge($this->lang, $lang);
	}
	
	public function view($view) {
		$this->data['lang'] = $this->lang;
		$template = $this->app->template;
		
		$template->setData($this->data);
		
		$path = $this->path.'/views/'.$view.'.php';
		
		$redefine = $template->isRedefineView('widgets', $this->widget, $view);
		if ($redefine) $path = $redefine;

		$template->render($path, false, false);
	}
}
?>