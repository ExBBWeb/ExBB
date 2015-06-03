<?php
namespace Core\Library\Site\Entity;

class FeedbackMessage extends BaseEntity {
	public function __construct($entity_id='no') {
		parent::__construct($entity_id, 'feedback');
	}
}
?>