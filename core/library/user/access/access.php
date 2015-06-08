<?php
namespace Core\Library\User\Access;

use Core\Library\DB\DB;
use Core\Library\Application\Application;

class Access {
	protected $db = null;
	
	protected $user_id = 0;
	protected $group_id = 1;
	
	public function __construct($group_id=null, $user_id=null) {
		$this->db = DB::getInstance();
		
		if (is_null($group_id)) $this->group_id = (int)Application::getInstance()->user->group_id;
		if (is_null($user_id)) $this->user_id = (int)Application::getInstance()->user->id;
	}
	
	public function getDefaultForumAccess($access_name) {
		$access = $this->db->getRow('SELECT access_value FROM '.DB_PREFIX.'groups_forum_access WHERE group_id='.$this->group_id.' AND forum_id=0 AND access_name="'.$access_name.'"');
		return (isset($access['access_value'])) ? $access['access_value'] : false;
	}
}
?>