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
		if (!$app->language) throw new \Exception('LanguageManager is not loaded!');

		$lang = $app->language->getLanguage();
		$path = BASE.'/plugins/'.$this->plugin.'/language/'.$lang.'/'.$file.'.php';

		$redefine = $app->language->isRedefineLanguage('plugins', $this->plugin, $file);
		if ($redefine) $path = $redefine;

		return $app->language->load($this->lang, $path);
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