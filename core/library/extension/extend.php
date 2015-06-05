<?php
namespace Core\Library\Extension;

use Core\Library\DB\DB;
use Extension\Plugin;

class Extend {
	protected static $handlers = array();
	protected static $plugins = array();
	
	const EXT_UNKNOWN = 0;
	const EXT_MODULE = 1;
	const EXT_PLUGIN = 2;
	const EXT_TEMPLATE = 3;
	const EXT_LANGUAGE = 4;
	const EXT_WIDGET = 5;
	
	public static function setHandler($action, $handler, $alias=false) {
		if (!$alias) self::$handlers[$action][] = $handler;
		else self::$handlers[$action][$alias] = $handler;
	}
	
	public static function unsetHanlder($action, $alias) {
		if (isset(self::$handlers[$action][$alias])) unset(self::$handlers[$action][$alias]);
	}
	
	public static function unsetActionHandlers($action) {
		self::$handlers[$action] = array();
	}
	
	public static function unsetAllHandlers() {
		self::$handlers = array();
	}
	
	public static function setAction($action) {
		if (!isset(self::$handlers[$action])) return false;
		
		$args = array_slice(func_get_args(), 1);

		foreach (self::$handlers[$action] as $handler) {
			call_user_func_array($handler, $args);
		}
		
		return true;
	}

	public static function isHandlersExist($action) {
		return (isset(self::$handlers[$action]));
	}

	public static function loadPlugins($section) {
		$plugin_dir = BASE.'/plugins';
		
		$db = DB::getInstance();
		$result = $db->query('SELECT name FROM '.DB_PREFIX.'extensions WHERE section="'.$section.'" AND type='.Extend::EXT_PLUGIN.' AND enabled=1 ORDER BY priority');
		
		while ($plugin = $db->fetchAssoc($result)) {
			$path = $plugin['name'];
			$plugin_file = $plugin_dir.'/'.$path;

			if (is_dir($plugin_dir.'/'.$path)) {
				$plugin_file = $plugin_dir.'/'.$path.'/plugin.php';
				if (file_exists($plugin_file)) include $plugin_file;
			}
			elseif (is_file($plugin_file.'.php')) {
				include $plugin_file.'.php';
			}
		}
	}
	
	public static function registerPluginObject($alias, $object) {
		self::$plugins[$alias] = $object;
		self::$plugins[$alias]->run();
	}
}
?>