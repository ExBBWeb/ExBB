<?php
namespace Extension\Plugin;

use Core\Library\Extension\BasePlugin;
use Core\Library\Application\Application;

class PluginRuntime extends BasePlugin {
	public function run() {
		$this->setHandler('after_core_init', array($this, 'afterCoreInit'));
		$this->setHandler('router_parsed_url', array($this, 'routerParsedUrl'));
		//$this->loadLanguage('lang');
	}
	
	public function afterCoreInit($data) {
		$app = $data['app'];
		$current = $app->url->getBaseUrl();

		$app->url->setBaseUrl($current.'/admin');
		$app->template->setUrl($current.'/admin');

		$app->router->setUrl(str_replace('admin/', '', $app->router->getUrl()));
		$app->template->addBreadcrumb('Админ-панель', $app->url->module('index'));
	}
	
	public function routerParsedUrl() {
		$app = Application::getInstance();
		
		if (!isset($app->request->session['is_admin']) || !$app->request->session['is_admin']) {
			$app->router->setVar('module', 'auth');
			$app->router->setVar('controller', 'index');
			$app->router->setVar('action', 'index');
		}
	}
}

BasePlugin::addPluginObject('runtime', new PluginRuntime(dirname(__FILE__)));
?>