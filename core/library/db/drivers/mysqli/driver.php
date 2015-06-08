<?php
namespace Core\Library\DB\Drivers\MySQLi;

use Core\Library\DB\Drivers\BaseDriver;
/**
* Дополнительные возможности, которые должен реализовать драйвер:
- Защита данных в SQL запросах, Placeholders
- Замена {PREFIX} на префикс таблиц базы данных
*/

class Driver extends BaseDriver {
	protected $config = array(
		'host' => '',
		'user' => '',
		'password' => '',
		'db_name' => '',
		'db_prefix' => '',
	);
	
	protected $link;
	protected $lastQuery;
	
	public $queries = 0;
	
	protected $functions = array(
		'NOW()' => 1,
	);
	
	public function __Construct($config) {
		$this->config = array_merge($this->config, $config);
	}

	public function connect() {
		$this->link = mysqli_connect($this->config['host'], $this->config['user'], $this->config['password']) or $this->error('Ошибка подключения к серверу баз данных');
		
		mysqli_select_db($this->link, $this->config['db_name']) or $this->error('Ошибка выбора базы данных');
		mysqli_set_charset ($this->link , 'UTF-8');
	}
	
	public function getRow($query) {
		return $this->fetchAssoc($this->query($query));
	}
	
	public function getObject($query) {
		return $this->fetchObject($this->query($query));
	}
	
	public function getAll($query) {
		$result = $this->query($query);
		$array = array();
		
		while ($row = $this->fetchAssoc($result)) {
			$array[] = $row;
		}
		
		return $array;
	}

	public function getIndexedAll($field, $query) {
		$result = $this->query($query);
		$array = array();
		
		while ($row = $this->fetchAssoc($result)) {
			$array[$row[$field]] = $row;
		}
		
		return $array;
	}
	
	public function getNumRows($result=false) {
		if (!$result) return $this->numRows($this->lastQuery['result']);
		return $this->numRows($result);
	}
	
	public function numRows($result) {
		return mysqli_num_rows($result);
	}
	
	public function fetchArray($result) {
		return mysqli_fetch_array($result);
	}
	
	public function fetchRow($result) {
		return mysqli_fetch_row($result);
	}
	
	public function fetchAssoc($result) {
		return mysqli_fetch_assoc($result);
	}
	
	public function fetchObject($result) {
		return mysqli_fetch_object($result);
	}
	
	public function getInsertId() {
		return mysqli_insert_id($this->link);
	}
	
	public function query($query) {
		$this->queries++;

		//$query = str_replace('{PREFIX}', $this->config['db_prefix'], $query);
		$result = mysqli_query($this->link, $query) or $this->error('Ошибка выполнения SQL запроса');
		
		$this->lastQuery = array(
			'query' => $query,
			'result' => $result,
		);
		
		return $result;
	}
	
	public function error($text) {
		throw new \Exception($text.': '.mysqli_error($this->link));
	}
	
	public function escape($value) {
		return mysqli_real_escape_string($this->link, $value);
	}
	
	public function parse($query, $placeholders) {
		$query = str_replace('{PREFIX}', $this->config['db_prefix'], $query);
		$parts = preg_split('~(\?[siu])~u',$query,null,PREG_SPLIT_DELIM_CAPTURE);
		$query = array_shift($parts);

		$i = 1;
		
		foreach ($parts as $part) {
			switch ($part) {
				case '?i':
					$query .= intval($placeholders[$i]);
					$i++;
				break;
				
				case '?u':
					$temp = array();
					
					foreach ($placeholders[$i] as $name => $value) {
						if (!isset($this->functions[$value])) {
							$temp[] = '`'.$name.'`="'.mysqli_real_escape_string($this->link, $value).'"';
						}
						else {
							$temp[] = '`'.$name.'`='.mysqli_real_escape_string($this->link, $value).'';
						}
					}
					
					$query .= implode(',', $temp);
					
					$i++;
				break;
				
				case '?s':
					$query .= '"'.mysqli_real_escape_string($this->link, $placeholders[$i]).'"';
					$i++;
				break;
				
				default:
					$query .= $part;
				break;
			}
		}

		return $query;
	}
	
	public function getConfig($option_name=false) {
		if (!$option_name) return $this->config;
		
		if (isset($this->config[$option_name])) return $this->config[$option_name];
		return false;
	}
}
?>