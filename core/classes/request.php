<?php
namespace Core\Classes;

/**
* Класс для обращения к переменным запроса и глобальным переменным, таким
* как $_POST, $_GET, $_SESSION и $_COOKIE
*
* @package Core
* @subpackage Classes
*/
class Request {
/**
* Данные $_POST
* @access public
* @var array
*/
	public $post = null;
	
/**
* Данные $_GET
* @access public
* @var array
*/
	public $get = null;
	
/**
* Данные $_SESSION
* @access public
* @var array
*/
	public $session = null;
	
/**
* Данные $_COOKIE
* @access public
* @var array
*/
	public $cookie = null;
	
/**
* Данные $_REQUEST
* @access public
* @var array
*/
	public $request = null;
	
	protected $answerTypes = array(
		'json', 'html', 'page'
	);
 /**
  * Создаёт экземпляр класса, входных параметров не нужно
  */
	public function __Construct() {
		$this->post = (!empty($_POST)) ? $_POST : array();
		$this->request = (!empty($_REQUEST)) ? $_REQUEST : array();
		$this->get = (!empty($_GET)) ? $_GET : array();
		$this->cookie = (!empty($_COOKIE)) ? $_COOKIE : array();
		$this->session = (!empty($_SESSION)) ? $_SESSION : array();
	}
	
	public function __Destruct() {
		$_SESSION = $this->session;
	}
	
/**
 * Проверяет, используется ли AJAX для запроса
 *
 * @return bool
*/
	public function isAjax() {
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}
	
/**
* Возвращает требуемый тип ответа (JSON, HTML блок, целая страница)
* 
* @return string
*/
	public function getAnswerType() {
		return (!empty($this->request['answerType']) && in_array($this->request['answerType'], $this->answerTypes)) ? $this->request['answerType'] : 'page';
	}
	
/**
* Добавляет новый тип ответа в список разрешённых
*
* @param string $ype Тип
* @return void
*/
	public function addAnswerType($type) {
		$this->answerTypes[] = $type;
	}
}
?>