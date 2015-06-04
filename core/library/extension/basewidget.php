<?php
namespace Core\Library\Extension;

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

/**
* Загружает языковой файл для шаблона
*
* @param string $file Файл локализации
* @return void
*/
	public function loadLanguage($file) {
		$lang = $this->app->language->getLanguage();
		$path = BASE.'/widgets/'.$this->widget.'/language/'.$lang.'/'.$file.'.php';

		$redefine = $this->app->language->isRedefineLanguage('widgets', $this->widget, $file);
		if ($redefine) $path = $redefine;

		return $this->app->language->load($this->lang, $path);
	}
	
/**
* Возвращает загруженную локализацию

* @return object
*/
	public function getLanguage() {
		return $this->lang;
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