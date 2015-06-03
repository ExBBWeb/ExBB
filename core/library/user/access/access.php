<?php
namespace Core\Library\User\Access;

use Core\Library\DB\DB;

class Access {
	protected static $group_id = null;
	protected static $access = array();
	
	public static function setGroupId($group_id) {
		self::$group_id = $group_id;
	}
	
	public static function getGroupId() {
		return self::$group_id;
	}

	public static function check($access_name, $entity_name=false, $entity_id=false) {
		return self::checkGroupAccess(self::$group_id, $entity_name, $entity_id, $access_name);
	}
	
	public static function setAccess($group_id, $access_name, $access_value, $entity_name=0, $entity_id=0) {
		$query = DB::getInstance()->parse('SELECT id FROM {PREFIX}access WHERE group_id=?i AND entity_name=?s AND entity_id=?s AND access_name=?s',
		$group_id, $entity_name, $entity_id, $access_name);
		$access = DB::getInstance()->getRow($query);
			
		if (isset($access['id'])) {
			$query = DB::getInstance()->parse('UPDATE {PREFIX}access SET access_value=?i WHERE id=?i', $access_value, $access['id']);
			DB::getInstance()->query($query);
		}
		else {
			$query = DB::getInstance()->parse('INSERT INTO {PREFIX}access SET group_id=?i, entity_name=?s, entity_id=?i, access_name=?s, access_value=?i',
			$group_id, $entity_name, $entity_id, $access_name, $access_value);
			DB::getInstance()->query($query);
		}
	}
	
	public static function checkGroupAccess($group_id, $access_name, $entity_name=0, $entity_id=0) {
		if (isset(self::$access[$group_id]) && isset(self::$access[$group_id][$entity_name]) && isset(self::$access[$group_id][$entity_name][$entity_id])) {
			if (isset(self::$access[$group_id][$entity_name][$entity_id][$access_name])) return self::$access[$group_id][$entity_name][$entity_id][$access_name];
		}
		else {
			$query = DB::getInstance()->parse('SELECT access_value FROM {PREFIX}access WHERE group_id=?i AND entity_name=?s AND entity_id=?s AND access_name=?s',
			$group_id, $entity_name, $entity_id, $access_name);
			$access = DB::getInstance()->getRow($query);
			
			$result = true;
			if (!isset($access['access_value'])) $result = false;
			else $result = (bool)$access['access_value'];
			
			 self::$access[$group_id][$entity_name][$entity_id][$access_name] = $access['access_value'];
			
			return $result;
		}
	}
}
?>