<?php
namespace Core\Library\Extension;

use Core\Library\Extension\Extend;
use Core\Library\Application\Application;

class BasePlugin {
	protected $plugin;
	
/**
* Загруженная локализация
* @access protected
* @var object
*/
	protected $lang;
	protected $path;
	
	public function __construct($path) {
		$this->path = $path;
	}
	
	public function setHandler($action, $handler, $alias=false) {
		Extend::setHandler($action, $handler, $alias);
	}
	
/**
* Загружает языковой файл для шаблона
*
* @param string $file Файл локализации
* @return void
*/
	public function loadLanguage($file) {
		$app =  Application::getInstance();

		$lang = $app->language->getLanguage();
		$path = BASE.'/plugins/'.$this->plugin.'/language/'.$lang.'/'.$file.'.php';

		$redefine = $app->language->isRedefineLanguage('plugins', $this->plugin, $file);
		if ($redefine) $path = $redefine;

		return $app->language->load($this->lang, $path);
	}
	
	
	public function view($view) {
		$this->data['lang'] = $this->lang;
		$template = Application::getInstance()->template;
		
		$template->setData($this->data);
		
		$path = $this->path.'/views/'.$view.'.php';
		
		$redefine = $template->isRedefineView('plugins', $this->plugin, $view);
		if ($redefine) $path = $redefine;

		$template->render($path, false, false);
	}
	
/**
* Возвращает загруженную локализацию

* @return object
*/
	public function getLanguage() {
		return $this->lang;
	}
	
	public static function addPluginObject($alias, $object) {
		Extend::registerPluginObject($alias, $object);
	}
}
?>