<?php
namespace Core\Library\MVC;

use Core\Library\Application\Application;
use Core\Library\View\Template;

/**
 * Класс controller является родителем для любого контроллёра в системе.
 */
class BaseController {
	
	protected $path;
	protected $module;
	protected $db;
	protected $config;
	protected $request;
	
	protected $app;
	
	protected $lang;
	
	//protected $lang = array();
	
	/**
	* Инициализирует контроллер
	* @param string $path полный путь к файлу контроллёра
	* @return void
	*/
	public function __construct($path, $module) {
		$this->path = $path;
		$this->module = $module;
		
		$app = Application::getInstance();

		$this->app = $app;
		
		$this->db = $app->db;
		$this->config = $app->config;
		$this->request = $app->request;
		
		$this->app->language->loadCommon($this->lang, 'common');
		
		$this->initialize();
	}
	
	/**
	* Инициализация контроллёра
	*/
	public function initialize() {
		
	}
	
	/**
	* Инициализирует действие по-умолчанию
	* @return void
	*/
	public function ActionIndex() {
		
	}
	
	/**
	* Получает и возвращает объект модели
	* @param string $model название модели
	* @return object
	*/
	public function loadModel($model) {
		$path = $this->path.'/models/'.$model.'.php';

		if (!file_exists($path)) return false;
		
		include $path;
		$class = 'Extension\Module\Model\Model'.$model;

		if (!class_exists($class, false)) return false;
		$model = new $class;
		
		return new $model;
	}
	
	/**
	* Подключает сущность
	*/
	public function loadEntity($entity) {
		$path = $this->path.'/entity/'.$entity.'.php';
		
		if (!file_exists($path)) return false;
		
		include $path;
		$class = '\Core\Library\Site\Entity\''.$entity;
		return $class;
	}
	
	public function loadLanguage($file) {
		$lang = LANGUAGE;
		$path = $this->path.'/language/'.$lang.'/'.$file.'.php';
		
		$redefine = $this->app->language->isRedefineLanguage('modules', $this->module, $file);
		if ($redefine) $path = $redefine;

		$this->app->language->load($this->lang, $path);
	}
	
	public function getLanguage() {
		return $this->lang;
	}
	
	public function view($view, $template_file='template') {
		$this->data['lang'] = $this->lang;
		$template = $this->app->template;
		
		$template->setData($this->data);
		
		$path = $this->path.'/views/'.$view.'.php';
		
		$redefine = $template->isRedefineView('modules', $this->module, $view);
		if ($redefine) $path = $redefine;

		$template->render($path, $template_file);
	}
	
	public function viewAnswer($answer, $view='', $template_file='template', $answerType=false) {
		if (!$answerType) $answerType = $this->request->getAnswerType();

		if ($answerType == 'json') {
			echo json_encode($answer);
			return true;
		}
		
		$enable_template = ($answerType == 'page') ? true : false;
		
		$this->data['lang'] = $this->lang;
		$template = $this->app->template;
		
		$this->data['answer'] = $answer;
		$template->setData($this->data);
		
		$path = $this->path.'/views/'.$view.'.php';
		
		$redefine = $template->isRedefineView('modules', $this->module, $view);
		if ($redefine) $path = $redefine;

		$template->render($path, $template_file, $enable_template);
	}
}
?>