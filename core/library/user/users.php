<?php
namespace Core\Library\User;

use Core\Library\Application\Application;

class Users {
	public static function cryptPassword($password, $salt) {
		// Шифруем пароль с применением данной соли
		return crypt($password, $salt);
	}
	
	public static function generateSalt() {
		// Генерируем соль
		$salt = '$2a$10$'.substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(),mt_rand()))), 0, 22) . '$';
		return $salt;
	}
	
	public static function isLogged() {
		$app = Application::getInstance();
		return (isset($app->request->session['user']['logged']));
	}
	
	public static function authorize($user) {
		$app = Application::getInstance();
		$app->request->session['user']['logged'] = true;
		$app->request->session['user']['id'] = $user->id;
		$app->request->session['user']['login'] = $user->login;
		//$app->request->session['user']['name'] = $user->name;
		//$app->request->session['user']['sirname'] = $user->sirname;
		
		$user->last_login_date = 'NOW()';
	}
	
	public static function logout() {
		unset(Application::getInstance()->request->session['user']);
	}
	
	public static function getUserFieldsByGroup($group_id) {
		$db = Application::getInstance()->db;
		$fields = array();
		$result = $db->query('SELECT * FROM '.DB_PREFIX.'users_fields WHERE group_id='.(int)$group_id.' OR group_id=0 ORDER BY priority');
		
		while ($row = $db->fetchAssoc($result)) {
			$row['options'] = unserialize($row['options']);
			$fields[$row['id']] = $row;
		}
		
		
		if (count($fields) >= 1) {
			$keys = array_keys($fields);
			$result = $db->query('SELECT * FROM '.DB_PREFIX.'users_fields_values WHERE field_id IN ('.implode(',', $keys).')');
			while ($row = $db->fetchAssoc($result)) {
				$fields[$row['field_id']]['values'][$row['id']] = $row;
			}
		}
		
		return $fields;
	}
}
?>