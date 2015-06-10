<?php
namespace Extension\Plugin;

use Core\Library\Extension\BasePlugin;
use Core\Library\Application\Application;
use Core\Library\User\Access\Access;

class PluginRuntime extends BasePlugin {
	public function run() {
		$this->setHandler('after_core_init', array($this, 'afterCoreInit'));
		$this->setHandler('router_parsed_url', array($this, 'routerParsedUrl'));
		//$this->loadLanguage('lang');
	}
	
	public function afterCoreInit() {
		$app = Application::getInstance();
		$current = $app->url->getBaseUrl();

		$app->url->setBaseUrl($current.'/admin');
		$app->template->setUrl($current.'/admin');

		$app->router->setUrl(str_replace('admin/', '', $app->router->getUrl()));
	}
	
	public function routerParsedUrl() {
		$app = Application::getInstance();
		$access = new Access();
		$access_value = (bool)$access->getEntityAccess('auth', 'admin');
		if ($app->user->id == 0) {
			$app->router->setVar('module', 'auth');
			$app->router->setVar('controller', 'index');
			$app->router->setVar('action', 'index');
		}
		elseif (!$access_value) {
			$app->redirectServer(ROOT_URL, false);
			$app->stop();
		}
	}
}

BasePlugin::addPluginObject('runtime', new PluginRuntime(dirname(__FILE__)));
?>