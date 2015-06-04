<?php
namespace Core\Library\Site\Entity;

use Core\Library\DB\DB;

define('SITE_BASE_ENTITY_DEBUG', false);

class BaseEntity {
	protected $data = array();
	protected $updated = array();
	protected $is_new = true;
	protected $is_saved = true;
	
	protected $table = null;
	
	public $autosave = true;
	
	public function __construct($entity_id='no', $table=null) {
		$this->table = $table;
		
		$db = DB::getInstance();
		
		if (is_array($entity_id)) {
			$this->is_new = false;
			
			$where = array();
			foreach ($entity_id as $name => $value) {
				$where[] = $name.'='.$db->parse('?s', $value);
			}

			$query = $db->parse('SELECT * FROM '.DB_PREFIX.$this->table.' WHERE '.implode(' AND ', $where));

			$this->data =$db->getRow($query);
			$this->is_saved = true;
		}
		elseif (is_numeric($entity_id)) {
			$this->is_new = false;
			$query = DB::getInstance()->parse('SELECT * FROM '.DB_PREFIX.$this->table.' WHERE id=?i', $entity_id);
			$this->data = DB::getInstance()->getRow($query);
			
			$this->is_saved = true;
		}
		else {
			$this->is_saved = false;
		}
	}
	
	public function exists($field='id') {
		return isset($this->data[$field]);
	}
	
	public function __destruct() {
		if (!$this->is_saved && count($this->updated) != 0 && $this->autosave) $this->save();
	}
	
	public function __set($name, $value) {
		$this->updated[$name] = true;

		$this->is_saved = false;
		
		$method = 'set'.$name;
	
		if (method_exists($this, $method)) {
			return $this->$method($name, $value);
		}
		
		$this->data[$name] = $value;
	}

	public function __get($name)  {
		$method = 'get'.$name;
		if (method_exists($this, $method)) return $this->$method($name);
		
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}

		if (!SITE_BASE_ENTITY_DEBUG)
		return false;
	
        $trace = debug_backtrace();
        trigger_error('Неопределенное свойство в __get(): '.$name.' в файле '.$trace[0]['file'].' на строке '.$trace[0]['line'], E_USER_NOTICE);
        return null;
    }
	
	public function isSaved() {
		return $this->isSaved;
	}
	
	public function save() {
		$data = array();
		foreach ($this->updated as $var => $status) {
			$data[$var] = $this->data[$var];
		}

		if (!$this->is_new) {
			$query = DB::getInstance()->parse('UPDATE '.DB_PREFIX.$this->table.' SET ?u WHERE id=?i', $data, $this->data['id']);
		}
		elseif (count($data != 0)) {
			$query = DB::getInstance()->parse('INSERT INTO '.DB_PREFIX.$this->table.' SET ?u', $data);
			$this->is_new = false;
			$set_id = 1;
		}
		
		DB::getInstance()->query($query);
		
		if (isset($set_id)) $this->data['id'] = DB::getInstance()->insertId();
		
		$this->updated = array();
		$this->is_saved = true;
	}
	
	public function delete() {
		if ($this->is_new) return false;
		DB::getInstance()->query(DB::getInstance()->parse('DELETE FROM '.DB_PREFIX.$this->table.' WHERE id=?i', $this->data['id']));
		$this->is_saved = true;
		$this->is_new = true;
	}
	
	public function setData($data) {
		$this->data = array_merge($this->data, $data);
		$this->is_saved = false;
		
		foreach ($data as $name => $value) $this->updated[$name] = 1;
	}
	
	public function getData() {
		return $this->data;
	}
}
?>