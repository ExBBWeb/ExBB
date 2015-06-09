<?php
namespace Core\Library\Site\Entity;

use Core\Library\Application\Application;
use Core\Library\DB\DB;


class User extends BaseEntity {
	protected $fieldsData = array();
	protected $fieldsDataUpdated = array();
	protected $fieldsDataAdded = array();
	protected $fieldsDataDeleted = array();
	
	public function __construct($entity_id='no') {
		parent::__construct($entity_id, 'users');
		
		$db = DB::getInstance();
		
		if ($this->exists()) {
			$result = $db->query('SELECT * FROM '.DB_PREFIX.'users_fields_data WHERE user_id='.$this->data['id']);
			
			while ($row = $db->fetchAssoc($result)) {
				$this->fieldsData[$row['field_id']][$row['id']] = $row['value'];
			}
		}
	}
	
	public function setFieldData($field_id, $value, $index=null) {
		if (!isset($this->fieldsData[$field_id])) $index = 0;
		
		if ($index === null) {
			$keys = array_keys($this->fieldsData[$field_id]);
			$index = array_shift($keys);
		}
		
		if (isset($this->fieldsData[$field_id][$index]) && !$this->is_new) {
			$this->fieldsDataUpdated[$field_id][$index] = 1;
		}
		else {
			$this->fieldsDataAdded[$field_id][$index] = 1;
		}
		
		$this->fieldsData[$field_id][$index] = $value;
	}
	
/**
* Удаляет значение произвольного поля для профиля пользователя
*
* @param int $field_id ID поля в базе данных
* @param int $index Индекс удаляемой записи
* @return void
*/
	public function unsetFieldData($field_id, $index=null) {
		if (isset($this->fieldsData[$field_id])) {
			if ($index === null) {
				if (isset($this->fieldsDataAdded[$field_id])) unset($this->fieldsDataAdded[$field_id]);
				if (isset($this->fieldsDataUpdated[$field_id])) unset($this->fieldsDataUpdated[$field_id]);
				
				foreach ($this->fieldsData[$field_id] as $key => $value) {
					$this->fieldsDataDeleted[$field_id][$key] = 1;
				}
				
				unset($this->fieldsData[$field_id]);
				return true;
			}
			
			unset($this->fieldsData[$field_id][$index]);
			
			if (isset($this->fieldsDataAdded[$field_id][$index])) unset($this->fieldsDataAdded[$field_id][$index]);
			if (isset($this->fieldsDataUpdated[$field_id][$index])) unset($this->fieldsDataUpdated[$field_id][$index]);
			
			$this->fieldsDataDeleted[$field_id][$index] = 1;
		}
	}
	
	public function getFieldData($field_id, $index=null, $array=false) {
		if (!isset($this->fieldsData[$field_id])) $index = 0;

		if ($index === null) {
			$keys = array_keys($this->fieldsData[$field_id]);
			$index = array_shift($keys);
		}
		
		if (isset($this->fieldsData[$field_id][$index])) {
			if ($array) return $this->fieldsData[$field_id];
			else return $this->fieldsData[$field_id][$index];
		}
		
		return false;
	}
	
	public function saveFields() {
		$db = DB::getInstance();

		foreach ($this->fieldsDataDeleted as $id => $values) {
			foreach ($values as $key => $value) {
				echo $key;
				$db->query('DELETE FROM '.DB_PREFIX.'users_fields_data WHERE id='.(int)$key);
			}
		}
		$this->fieldsDataDeleted = array();
		
		foreach ($this->fieldsDataAdded as $id => $values) {
			foreach ($values as $key => $value) {
				$value = $this->fieldsData[$id][$key];
				$db->query('INSERT INTO '.DB_PREFIX.'users_fields_data SET user_id='.$this->data['id'].', field_id='.(int)$id.', value="'.$db->escape($value).'"');
			}
		}
		$this->fieldsDataAdded = array();
		
		foreach ($this->fieldsDataUpdated as $id => $values) {
			foreach ($values as $key => $value) {
				$value = $this->fieldsData[$id][$key];
				$db->query('UPDATE '.DB_PREFIX.'users_fields_data SET value="'.$db->escape($value).'" WHERE id='.(int)$key);
			}
		}
		$this->fieldsDataUpdated = array();
	}
	
	public function save() {
		parent::save();
		$this->saveFields();
	}
	
	public function getAvatar() {
		if (empty($this->data['avatar'])) return Application::getInstance()->config->getOption('default_user_avatar');
		
		return $this->data['avatar'];
	}

}
?>