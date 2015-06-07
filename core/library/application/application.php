<?php
namespace Core\Library\Application;

define('EXBB_VERSION', '0.1.0');
define('EXFRAMEWORK_VERSIONs', '0.1.0');

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

		include_once ROOT.'/core/functions/extend.php';
		
		Extend::loadPlugins($section);
		Extend::setAction('before_core_init');
	}
	
	public function endInitialize($section='') {
		$this->config = new Config();
		
		$this->config->setOption('redirect_type', 'client', true);

		session_start();
		$this->request = new Request();
		
		if (isset($this->request->session['user']['id'])) {
			$this->user = new User($this->request->session['user']['id']);
		}
		else {
			$this->user = new User(0);
		}

		$this->user->autosave = false;
		
		// Получение включенного шаблона
		if ($section == 'site' && $this->user->template) {
			$template = $this->db->getRow('SELECT name FROM '.DB_PREFIX.'extensions WHERE id='.(int)$this->user->template);
		}
		
		if (empty($template['name']) || !is_dir(BASE.'/templates/'.$template['name'])) {
			$template = $this->db->getRow('SELECT name FROM '.DB_PREFIX.'extensions WHERE section="'.$section.'" AND type='.Extend::EXT_TEMPLATE.' AND selected=1');
		}
		
		// Получение включенного языка
		if ($section == 'site' && $this->user->language) {
			$language = $this->db->getRow('SELECT name FROM '.DB_PREFIX.'extensions WHERE id='.(int)$this->user->language);
		}
		
		if (empty($language['name']) || !is_dir(BASE.'/languages/'.$language['name'])) {
			$language = $this->db->getRow('SELECT name FROM '.DB_PREFIX.'extensions WHERE section="'.$section.'" AND type='.Extend::EXT_LANGUAGE.' AND selected=1');
		}

		if (!empty($this->user->timezone)) {
			date_default_timezone_set($this->user->timezone);
		}
		else {
			date_default_timezone_set('Europe/Moscow');
		}

		$this->db->query("SET `time_zone`='".date('P')."'");
		
		//define('LANGUAGE', $this->config->getOption($section.'_language'));
		define('BASE_URL', $this->config->getOption('url'));
		define('TEMPLATE', $template['name']);
		
		$this->language = Language::getInstance();
		$this->language->setLanguage($language['name']);

		$this->template = Template::getInstance();
		$this->template->setUrl($this->config->getOption('url'));
		$this->template->setTemplate($template['name']);

		$this->template->title = $this->config->getOption($section.'_title');

		$this->router = new Router();
		$this->url = new Url($this->config->getOption('url'));
		
		$url = (isset($_GET['a'])) ? $_GET['a'] : 'index/index';
		$url = trim($url, '/');		
		
		$this->router->setUrl($url);
		
		Extend::setAction('after_core_init');

		$this->router->parse();

		$this->router->route();
	}

	
	public function destroy() {
		
	}
	
    /**
     * Завершает работу приложения
     * @param string $status Статус
	 *
     * @return void
     */
	public function stop($status='') {
		exit;
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
			$this->stop();
		}
		elseif ($options['type'] == 'server') {
			header('Location: '.$url, true);
		}
	}

	public function redirectServer($url, $return=false) {
		$this->redirect($url, array(
			'type' => 'server',
			'return' => $return,
		));
	}

    /**
     * Производит переадресацию на указанный URL, используя страницу переадресации
     *
     * @param string $url URL для переадресации
     * @param string $title Заголовок, отображаемый при переадресации
     * @param string $message Сообщение при переадресации
     * @param string $status Тип переадресации (error - с ошибкой, success - с успехом, info - информационный)
     * @param int $delay Задержка перед переадресацией (в милисекундах)
     * @param string $return Тип возврата функции (true - просто возвращает url, fasle - выполняет переадресацию, auto - при AJAX запросе становится true)
     */
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