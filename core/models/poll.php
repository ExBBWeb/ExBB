<?php
namespace Core\Models;

use Core\Library\Site\Entity\Topic;
use Core\Library\Site\Entity\Poll as PollEntity;

class Poll {
	public function add($topic, $title, $variants, $is_private=false, $is_checkbox=false) {
		if (!is_object($topic)) $topic = new Topic($topic);
		
		$poll = new PollEntity();
		$poll->category_id = $topic->category_id;
		$poll->forum_id = $topic->forum_id;
		$poll->topic_id = $topic->id;
		$poll->title = $title;
		$poll->is_private = $is_private;
		$poll->is_checkbox = $is_checkbox;
		foreach ($variants as $variant) {
			$poll->setVariant($variant);
		}
		
		$poll->save();
		return $poll->id;
	}
}
?>