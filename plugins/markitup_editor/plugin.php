<?php
namespace Extension\Plugin;

use Core\Library\Extension\BasePlugin;
use Core\Library\Application\Application;

class PluginMartikupEditor extends BasePlugin {
	// Для корректного подключения языков здесь должно находиться название папки плагина
	protected $plugin = 'markitup_editor';
	
	public function run() {
		// В функции run плагина нельзя загружать язык, но это можно сделать при наступлении события AfterCoreInit
		$this->setHandler('bbcode_editor', array($this, 'editor'));
	}
	
	public function editor($options) {
		$this->data['attr_id'] = ' id="'.$options['id'].'"';
		$this->data['id'] = $options['id'];
		$this->data['attr_name'] = ' name="'.$options['name'].'"';
		$this->data['name'] = $options['name'];
		
		$this->data['content'] =  (!empty($options['content'])) ? $options['content'] : '';
		
		$this->data['attr_class'] = (!empty($options['class'])) ? ' class="'.$options['class'].'"' : '';
		$this->data['attr_placeholder'] = (!empty($options['placeholder'])) ? ' placeholder="'.$options['placeholder'].'"' : '';
		
		$template = Application::getInstance()->template;
		$template->addJavaScript(ROOT_URL.'/plugins/'.$this->plugin.'/markitup/jquery.markitup.js');
		$template->addJavaScript(ROOT_URL.'/plugins/'.$this->plugin.'/markitup/sets/bbcode/set.js');
		
		$template->addStyleSheet(ROOT_URL.'/plugins/'.$this->plugin.'/markitup/skins/markitup/style.css');
		$template->addStyleSheet(ROOT_URL.'/plugins/'.$this->plugin.'/markitup/sets/bbcode/style.css');
		
		$this->view('editor');
	}
}

// Раскомментировать эту строку, когда нужно включить плагин
BasePlugin::addPluginObject('markitup_editor', new PluginMartikupEditor(dirname(__FILE__)));
?>