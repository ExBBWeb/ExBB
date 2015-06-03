<?php
namespace Core\Library\Application;

use Core\Library\DB\DB;

class Config {
	protected $options = array();
	protected $updated = array();

	//protected $local = array();
	
	public function __construct() {
		$result = DB::getInstance()->getAll('SELECT * FROM '.DB_PREFIX.'config WHERE autoload=1');
		
		foreach ($result as $row) {
			$this->options[$row['option_name']] = $row['option_value'];
		}
	}
	
	public function setOption($option_name, $option_value, $local=false) {
		$this->options[$option_name] = $option_value;
		if (!$local) $this->updated[$option_name] = $option_value;
		//if ($local) $this->local[$option_name] = $option_value;
	}
	
	public function getOption($option_name, $local=false) {
		if (isset($this->options[$option_name])) return $this->options[$option_name];
		
		if ($local) return false;
		
		$row = DB::getInstance()->getRow('SELECT option_value FROM '.DB_PREFIX.'config WHERE option_name="'.$option_name.'"');
		if (isset($row['option_value'])) {
			$this->options[$option_name] = $row['option_value'];
			return $row['option_value'];
		}
		
		return false;
	}
	
	public function __destruct() {
		foreach ($this->updated as $option_name => $option_value) {
			DB::getInstance()->query('INSERT INTO '.DB_PREFIX.'config SET option_name="'.$option_name.'", option_value="'.$option_value.'"
			ON DUPLICATE KEY UPDATE option_value = "'.$option_value.'"');
		}
	}
}
?>