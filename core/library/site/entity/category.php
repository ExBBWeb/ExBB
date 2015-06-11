<?php
namespace Core\Library\Site\Entity;

class Category extends BaseEntity {
	public function __construct($entity_id='no') {
		parent::__construct($entity_id, 'categories');
	}
}
?>