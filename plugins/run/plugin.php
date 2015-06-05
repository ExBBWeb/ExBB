<?php
namespace Extension\Plugin;

use Core\Library\Extension\BasePlugin;

class PluginRuntime extends BasePlugin {
	// Для корректного подключения языков здесь должно находиться название папки плагина
	protected $plugin = 'run';
	
	public function run() {
		// В функции run плагина нельзя загружать язык, но это можно сделать при наступлении события AfterCoreInit
		$this->setHandler('user_profile_menu_build', array($this, 'menu'));
	}
	
	public function menu($menu) {

	}
}

// Раскомментировать эту строку, когда нужно включить плагин
BasePlugin::addPluginObject('run', new PluginRuntime(dirname(__FILE__)));
?>