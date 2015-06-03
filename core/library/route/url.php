<?php
namespace Core\Library\Route;

use Core\Library\Extension\Extend;
/**
 *  Класс для маршрутизации
 *
 * @name Url
 * @author Николай Пауков
 */
class Url {
	protected $rules = array(
		'rule' => "rules/{param}.html",
	);
	
	protected $base = '';
	
	public function __construct($base) {
		$this->base = $base;
	}
	
	public function setBaseUrl($base) {
		$this->base = $base;
	}
	
	public function getBaseUrl() {
		return $this->base;
	}
	
	public function set($alias, $rule) {
		$this->rules[$alias] = $rule;
	}
	
	public function get($alias, $data = array(), $return = true) {
		if (!isset($this->rules[$alias])) return false;
		
		$url = $this->rules[$alias];
		
		foreach ($data as $name => $value) {
			$url = str_replace('{'.$name.'}', $value, $url);
		}
		
		$url = $this->base.'/'.$url;
		
		if ($return) return $url;
		echo $url;
	}
	
	public function view($alias, $data=array()) {
		$this->get($alias, $data, false);
	}
	
	public function module($module='index', $controller='index', $action='index', $param=false, $get=false) {
		$url = $this->base.'/'.$module.'/'.$controller.'/'.$action;
		if ($param) $url .= '/'.$param;
		
		if ($get) $url .= '&'.http_build_query($get);
		
		return $url;
	}
	
	public function translit($string) {
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => '',  'ы' => 'y',   'ъ' => '',
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
			
			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		);

		$string = strtr($string, $converter);
		// в нижний регистр
		$string = strtolower($string);
		// заменям все ненужное нам на "-"
		$string = preg_replace('~[^-a-z0-9_]+~u', '-', $string);
		// удаляем начальные и конечные '-'
		$string = trim($string, "-");
		return $string;
	}
}
?>