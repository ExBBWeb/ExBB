<?php
namespace Core\Forum\Entity\BaseEntity;

class BaseEntity {
	protected $data = null;
	protected $updated = null;
	protected $is_new = true;
	
	protected $table = null;
	
	public function __construct($entity_id=false) {
		if ($entity_id) $is_new = false;
	}
}
?>