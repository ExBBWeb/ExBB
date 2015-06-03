<?php
namespace Extension\Plugin;

use Core\Library\Extension\BasePlugin;

class PluginRuntime extends BasePlugin {
	public function run() {
		//die;
		//$this->setHandler('after_core_init', array($this, 'AfterCoreInit'));
		//$this->loadLanguage('lang');
	}
	
	/**public function AfterCoreInit($data) {
		$app = $data['app'];
		$app->router->addRule('articles/{article}.html', array(
			'module' => 'index',
			'controller' => 'article',
			'action' => 'index',
			'alias' => '{article}',
		));
		
		$app->router->addRule('index.html', array(
			'module' => 'index',
			'controller' => 'index',
			'action' => 'index',
		));
		
		$app->router->addRule('categories/{category}.html', array(
			'module' => 'index',
			'controller' => 'category',
			'action' => 'index',
			'alias' => '{category}',
		));
		
		$app->router->addRule('categories/{category}-page{page}.html', array(
			'module' => 'index',
			'controller' => 'category',
			'action' => 'index',
			'alias' => '{category}',
			'page' => '{page}',
		));
		
		$app->url->set('index', 'index.html');
		
		$app->url->set('article', 'articles/{article}.html');
		$app->url->set('category', 'categories/{category}.html');
		
		
		$app->router->addRule('gallery/index.html', array(
			'module' => 'gallery',
			'controller' => 'index',
			'action' => 'index',
		));
		$app->router->addRule('gallery/albums/{album}.html', array(
			'module' => 'gallery',
			'controller' => 'album',
			'action' => 'index',
			'alias' => '{album}'
		));
		$app->router->addRule('gallery/images/{image}.html', array(
			'module' => 'gallery',
			'controller' => 'image',
			'action' => 'index',
			'alias' => '{image}'
		));

		$app->url->set('gallery', 'gallery/index.html');
		$app->url->set('galleryalbum', 'gallery/albums/{album}.html');
		$app->url->set('galleryimage', 'gallery/images/{image}.html');
		
		$app->url->set('category-page', 'categories/{category}-{page}.html');
	}*/
}

//BasePlugin::addPluginObject('runtime', new PluginRuntime(dirname(__FILE__)));
?>