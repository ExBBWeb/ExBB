<?php
namespace Core\Library\Application;

// Базы данных
use Core\Library\DB\DB;

// Система плагинов
use Core\Library\Extension\Extend;

// Маршрутизатор
use Core\Library\Route\Router;
use Core\Library\Route\Url;

// Системные классы
use Core\Library\Application\Config;
use Core\Classes\Request;

// Шаблон
use Core\Library\View\Template;

// Языки
use Core\Library\Extension\Language;

use Core\Library\Site\Entity\User;

class Application {
    protected static $_instance;
	public $db = null;
	public $router = null;
	public $config = null;
	public $request = null;
	public $template = null;
	public $url = null;
	public $user = null;
	public $language = null;
	
	public $section = null;
	
    private function __construct(){
		
    }
	
	public function __destruct() {
		//echo memory_get_peak_usage()/1024;
	}
	
    private function __clone() {
    
	}

	public function run($section='site') {
		$this->section = $section;
		$this->startInitialize($section);
		$this->endInitialize($section);
	}
	
	public function startInitialize($section='') {
		include ROOT.'/config.php';
	
		$dbDriver = 'Core\Library\DB\Drivers\\'.$config['dbDriver'].'\Driver';
	
		$driver = new $dbDriver(array(
			'host' => $config['dbHost'],
			'user' => $config['dbUser'],
			'password' => $config['dbPass'],
			'db_name' => $config['dbName'],
			'db_prefix' => $config['dbPrefix'],
		));

		$driver->connect();

		$this->db = DB::getInstance();
		$this->db->setDriver($driver);

		Extend::loadPlugins($section);
		Extend::setAction('before_core_init');
	}
	
	public function endInitialize($section='') {
		$this->config = new Config();
		
		$this->config->setOption('redirect_type', 'client', true);
		
		define('LANGUAGE', $this->config->getOption($section.'_language'));
		define('BASE_URL', $this->config->getOption('url'));
		define('TEMPLATE', $this->config->getOption($section.'_template'));
		
		$this->language = Language::getInstance();
		$this->language->setLanguage(LANGUAGE);

		include_once ROOT.'/core/functions/language.php';
		
		$this->template = Template::getInstance();
		$this->template->setUrl($this->config->getOption('url'));
		$this->template->setTemplate($this->config->getOption($section.'_template'));

		$this->template->title = $this->config->getOption($section.'_title');
		
		session_start();
		$this->request = new Request();
		
		if (isset($this->request->session['user']['id'])) {
			$this->user = new User($this->request->session['user']['id']);
		}
		
		$this->router = new Router();
		$this->url = new Url($this->config->getOption('url'));
		
		$url = (isset($_GET['a'])) ? $_GET['a'] : 'index/index';
		$url = trim($url, '/');		
		
		$this->router->setUrl($url);
		
		Extend::setAction('after_core_init', array('app'=>$this));

		$this->router->parse();

		$this->router->route();
	}
	
	public function getDB() {
		return $this->db;
	}
	
	public function getRouter() {
		return $this->router;
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function destroy() {
		
	}
	
	public function kill($status='') {
		die;
	}
	
    /**
     * Функция для получения экземпляра класса Application
     *
     * @return Application
     */
    public static function getInstance() {
        // проверяем актуальность экземпляра
        if (null === self::$_instance) {
            // создаем новый экземпляр
            self::$_instance = new self();
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }
	
	public function redirect($url, $options=array()) {
		if (!isset($options['type'])) $options['type'] = $this->config->getOption('redirect_type');
		if (!isset($options['return'])) $options['return'] = 'auto';
		
		if ($options['return'] == 'auto' && $this->request->getAnswerType() != 'page' || $options['return'] === true) {
			return $url;
		}

		if ($options['type'] == 'client') {
			$options['link'] = $url;
			$this->template->setData($options);
			$this->template->render(false, 'redirect');
			$this->kill();
		}
		elseif ($options['type'] == 'server') {
			header('Location: '.$url, true);
		}
	}
	
	public function redirectPage($url, $title='Переадресация', $message='Сейчас вы будете переадресованы', $status='info', $delay=3000, $return='auto') {
		$this->redirect($url, array(
			'title' => $title,
			'message' => $message,
			'delay' => $delay,
			'status' => $status,
			'return' => $return,
		));
	}
}
?>