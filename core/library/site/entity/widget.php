<?php
namespace Core\Library\Site\Entity;

class Widget extends BaseEntity {
	public function __construct($entity_id='no') {
		parent::__construct($entity_id, 'widgets');
	}
}
?>