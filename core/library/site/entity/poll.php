<?php
namespace Core\Library\Site\Entity;

use Core\Library\DB\DB;

class Poll extends BaseEntity {
	protected $variants = array();
	protected $updated_variants = array();
	protected $deleted_variants = array();
	protected $added_variants = array();
	
	public function __construct($entity_id='no') {
		parent::__construct($entity_id, 'polls');
		
		$db = DB::getInstance();
		
		if ($this->exists()) {
			$result = $db->query('SELECT id,variant FROM '.DB_PREFIX.'polls_variants WHERE user_id='.$this->data['id']);
			
			while ($row = $db->fetchAssoc($result)) {
				$this->variants[$row['id']] = $row['variant'];
			}
		}
	}
	
	public function setVariant($variant, $id=false) {
		if ($id) {
			$this->variants[$id] = $variant;
			$this->updated_variants[$id] = 1;
		}
		else {
			$this->variants[] = $variant;
			$keys = array_keys($this->variants);
			$this->added_variants[end($keys)] = 1;
		}
	}
		
	public function getVariants() {
		return $this->variants;
	}
	
	public function unsetVariant($id) {
		if (isset($this->variants[$id])) {
			unset($this->variants[$id]);
			$this->deleted_variants[$id] = 1;
		}
	}
	
	public function saveFields() {
		$db = DB::getInstance();

		foreach ($this->deleted_variants as $id => $st) {
			$db->query('DELETE FROM '.DB_PREFIX.'users_fields_data WHERE id='.(int)$id);
		}
		$this->deleted_variants = array();
		
		foreach ($this->added_variants as $id => $st) {
			$variant = $this->variants[$id];
			$db->query('INSERT INTO '.DB_PREFIX.'polls_variants SET poll_id='.$this->data['id'].', variant="'.$db->escape($variant).'"');
		}
		$this->added_variants = array();
		
		foreach ($this->updated_variants as $id => $st) {
			$variant = $this->variants[$id];
			$db->query('UPDATE '.DB_PREFIX.'polls_variants SET variant="'.$db->escape($variant).'" WHERE id='.(int)$id);
		}
		$this->updated_variants = array();
	}
	
	public function save() {
		parent::save();
		$this->saveFields();
	}
}
?>