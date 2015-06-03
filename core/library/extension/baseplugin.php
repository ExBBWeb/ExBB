<?php
namespace Core\Library\Extension;

use Core\Library\Extension\Extend;

class BasePlugin {
	public function setHandler($action, $handler, $alias=false) {
		Extend::setHandler($action, $handler, $alias);
	}
	
	public static function addPluginObject($alias, $object) {
		Extend::registerPluginObject($alias, $object);
	}
}
?>