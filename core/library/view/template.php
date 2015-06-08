<?phpnamespace Core\Library\View;use Core\Library\Application\Application;use Core\Library\Extension\Extend;/*** Класс для взаимодействия с шаблоном и управления отображаемым HTML* @package Core* @subpackage View*/class Template {/*** Хранится добавленный в <head></head> код * @access protected* @var array*/	protected $head = array();	/*** Данные, переданные из контроллера в шаблон * @access protected* @var array*/	protected $data = array();	/*** Параметры, переданные в шаблон * @access protected* @var array*/	protected $params = array();	/*** Хлебные крошки * @access protected* @var array*/	protected $breadcrumbs = array();	/*** Название форума* @access public* @var string*/	public $title;	/*** Заголовок страницы* @access public* @var string*/	public $page_title;		/*** URL, по которому установлен движок * @access protected* @var string*/	protected $url;	/*** Включенный шаблон (не вид!)* @access protected* @var string*/	protected $template;	/*** Singleton object* @access protected* @var object*/	protected static $_instance;	/*** Подключённые виджеты* @access protected* @var array*/	protected $widgets = array();	/*** Загруженная локализация* @access protected* @var object*/	protected $lang;	 /**  * Создаёт экземпляр класса, входных параметров не нужно  */    private function __construct(){		$app = Application::getInstance();		$db = $app->db;		$result = $db->query('SELECT id,name,position FROM '.DB_PREFIX.'extensions WHERE section="'.$app->section.'" AND type='.Extend::EXT_WIDGET.' AND enabled=1 ORDER BY priority');		while ($widget = $db->fetchAssoc($result)) $this->widgets[$widget['position']][$widget['id']] = $widget;		$app->language->loadCommon($this->lang, 'common');	}	/*** Добавляет CSS таблицу стилей в <head></head> шаблона** @param string $url URL таблицы стилей* @return void*/	public function addStyleSheet($url) {		$this->head[] = '<link rel="stylesheet" href="'.$url.'" type="text/css">';	}/*** Добавляет JS скрипт в <head></head> шаблона** @param string $url URL скрипта* @return void*/	public function addJavaScript($url) {		$this->head[] = '<script type="text/javascript" src="'.$url.'"></script>';	}/*** Устанавливает базовый URL, по которому установлен форум** @param string $url URL* @return void*/	public function setUrl($url) {		$this->url = $url;	}	/*** Добавляет новую странцу в "хлебные крошки"** @param string $title Название страницы* @param string $url URL страницы* @param bool $active Флаг активности* @return void*/	public function addBreadcrumb($title, $url = false, $current=false) {		$this->breadcrumbs[] = array(			'title' => $title,			'url' => $url,			'current' => $current,		);	}	/*** Выводит "хлебные крошки"** @return void*/	public function breadcrumbs() {		if (file_exists(BASE.'/templates/'.$this->template.'/core/breadcrumbs.php')) {			$breadcrumbs = $this->breadcrumbs;			$lang = $this->lang;			include BASE.'/templates/'.$this->template.'/core/breadcrumbs.php';		}	}	/*** Выводит постраничную навигацию** @return void*/	public function pagination($pages,$url,$current=1) {		if (file_exists(BASE.'/templates/'.$this->template.'/core/pagination.php')) {			$lang = $this->lang;			include BASE.'/templates/'.$this->template.'/core/pagination.php';		}	}	/*** Устанавливает включенный шаблон** @param string $template Название страницы* @return void*/	public function setTemplate($template) {		$this->template = $template;		$this->loadLanguage('common');	}	/*** Возвращает или выводит путь к файлу относительно шаблона** @param string $file Путь к файлу* @param bool $return Флаг возврата (true - возвращает значение, false - выводит)* @return mixed*/	public function path($file, $return=true) {		$path = BASE.'/templates/'.$this->template.'/'.$file;		if ($return) return $path;				echo $path;	}	/*** Возвращает или выводит URL файла относительно шаблона** @param string $file URL файла* @param bool $return Флаг возврата (true - возвращает значение, false - выводит)* @return mixed*/	public function url($file, $return=false) {		$path = $this->url.'/templates/'.$this->template.'/'.$file;		if ($return) return $path;				echo $path;	}/*** Добавляет произвольный код в <head></head> шаблона** @param string $code Добавляемый код* @return void*/	public function addHeadCode($code) {		$this->head[] = $code;	}/*** Выводит весь код, добавленный в <head></head> шаблона** @param string $return Флаг возврата (true - возвращает значение, false - выводит)* @return void*/	public function head($return=false) {		$head = '';		foreach ($this->head as $code) $head .= $code."\n";				if ($return) return $head;		echo $head;	}/*** Передаёт массив данных в шаблон** @param array $data Данные* @return void*/	public function setData($data) {		$this->data = array_merge($this->data, $data);	}/*** Проверяет, переопределён ли файл представления** @param string $section Тип (модуль, плагин и т.д..)* @param string $component Название папки с представлением* @param string $view Файл представления без .php* @return void*/	public function isRedefineView($section, $component, $view) {				$path = BASE.'/templates/'.$this->template.'/'.$section.'/'.$component.'/'.$view.'.php';				return (file_exists($path)) ? $path : false;	}/*** Отображает шаблон с представлением** @param string $path Полный путь к файлу шаблона* @param string $template_file Название подключаемого файла внутри шаблона* @param string $enable_template Флаг активности шаблона* (true - шаблон подключается, false - представление выводится без шаблона)** @return void*/	public function render($path=false, $template_file='template', $enable_template=true, $return_template=false) {		foreach ($this->data as $name => $value) ${$name} = $value;		$content = '';				$application =  Application::getInstance();		$url = $application->url;		$config = $application->config;				$baseurl = $url->getBaseUrl();				if ($path) {			ob_start();			if (file_exists($path)) include $path;			$content = ob_get_clean();		}		if ($enable_template) {			if ($return_template) ob_start();			if ($template_file == 'template') {				include BASE.'/templates/'.$this->template.'/'.$template_file.'.php';			}			else {				include BASE.'/templates/'.$this->template.'/tpl/'.$template_file.'.php';			}			if ($return_template) return ob_get_clean();		}		else {						if ($return_template) return $content;			echo $content;		}	}	/*** Возвращает Singleton объект* @return object*/    public static function getInstance() {        // проверяем актуальность экземпляра        if (null === self::$_instance) {            // создаем новый экземпляр           self::$_instance = new self();        }        // возвращаем созданный или существующий экземпляр        return self::$_instance;    }	/*** Получает или выводить переданный шаблону параметр** @param string $param Название параметра* @param string $return Флаг возврата (true - возвращает значение, false - выводит)** @return mixed*/	public function param($param, $return=false) {		if (!isset($this->params[$param])) return false;				if ($return) return $this->params[$param];				echo $this->params[$param];	}	/*** Проверяет на существование переданный шаблону параметр** @param string $param Название параметра* @return bool*/	public function checkParam($param) {		return isset($this->params[$param]);	}	/*** Передаёт шаблону параметр** @param string $name Название параметра* @param mixed $name Значение параметра* @return void*/	public function setParam($name, $value) {		$this->params[$name] = $value;	}	/*** Выводит виджеты в одной из позиций** @param string $position Название позиции* @return void*/	public function widget($position) {		if (!isset($this->widgets[$position])) return false;		$widgets = $this->widgets[$position];				foreach ($widgets as $widget) {			$path = BASE.'/widgets/'.$widget['name'].'/widget.php';			if (!file_exists($path)) continue;						$class_name = '\Extension\Widget\Widget'.$widget['name'];			if (!class_exists($class_name, false)) include $path;			if (!class_exists($class_name, false)) continue;			$widget = new $class_name(dirname($path), $widget['name']);			$widget->ActionIndex();		}	}	/*** Считает виджеты в позиции** @param string $position Название позиции* @return int*/	public function countWidgets($position) {		if (!isset($this->widgets[$position])) return 0;		return count($this->widgets[$position]);	}    private function __clone() {    	}	/*** Загружает языковой файл для шаблона** @param string $file Файл локализации* @return void*/	public function loadLanguage($file) {		$app =  Application::getInstance();		$lang = $app->language->getLanguage();		$path = BASE.'/templates/'.$this->template.'/language/'.$lang.'/'.$file.'.php';		$redefine = $app->language->isRedefineLanguage('templates', $this->template, $file);		if ($redefine) $path = $redefine;		return $app->language->load($this->lang, $path);	}	/*** Возвращает загруженную локализацию* @return object*/	public function getLanguage() {		return $this->lang;	}}?>