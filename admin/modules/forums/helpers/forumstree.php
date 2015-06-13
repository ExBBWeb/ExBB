<?php
namespace Extension\Module\Helpers;

use Core\Library\DB\DB;

class ForumsTree {
	public function getTree($selected=0) {
		
	}
	
	public function getListForSelectParent($selected=false, $current=false) {
		$db = DB::getInstance();
		$content = '';
		$result = $db->query('SELECT id,title FROM '.DB_PREFIX.'forums WHERE parent_id=0 AND id != '.(int)$current);
		while ($row = $db->fetchAssoc($result)) {
			$content .= '<option value="'.$row['id'].'"'.(($row['id'] == $selected) ? ' selected' : '').'>'.$row['title'].'</option>';
		}
		
		return $content;
	}
	
	public function getListForSelectCategory($selected=false) {
		$db = DB::getInstance();
		$content = '';
		$result = $db->query('SELECT id,title FROM '.DB_PREFIX.'categories');
		while ($row = $db->fetchAssoc($result)) {
			$content .= '<option value="'.$row['id'].'"'.(($row['id'] == $selected) ? ' selected' : '').'>'.$row['title'].'</option>';
		}
		
		return $content;
	}
}
?>