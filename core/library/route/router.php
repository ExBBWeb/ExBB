<?php
namespace Core\Library\Route;

use Core\Library\Extension\Extend;
/**
 *  Класс для маршрутизации
 *
 * @name DB
 * @author Николай Пауков
 */
class Router {
	protected $url;

	protected $rules = array(
		'module' => array(
			'pattern' => '(?P<module>\w+)',
			'vars' => array(
				'module' => '{module}',
				'controller' => 'index',
				'action' => 'index',
			),
		),
		'module_controller' => array(
			'pattern' => '(?P<module>\w+)\/(?P<controller>\w+)',
			'vars' => array(
				'module' => '{module}',
				'controller' => '{controller}',
				'action' => 'index',
			),
		),
		'module_controller_action' => array(
			'pattern' => '(?P<module>\w+)\/(?P<controller>\w+)\/(?P<action>\w+)',
			'vars' => array(
				'module' => '{module}',
				'controller' => '{controller}',
				'action' => '{action}',
			),
		),
		'module_controller_action_param' => array(
			'pattern' => '(?P<module>\w+)\/(?P<controller>\w+)\/(?P<action>\w+)\/(?P<param>\w+)',
			'vars' => array(
				'module' => '{module}',
				'controller' => '{controller}',
				'action' => '{action}',
				'param' => '{param}',
			),
		),
	);

	protected $vars;
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
    public function parse() {
		$vars = array();
	
		$route = $this->url;

		foreach ($this->rules as $rule) {
			$pattern = $rule['pattern'];
			if (preg_match('/^'.$pattern.'$/', $route, $matches)) {
				foreach ($rule['vars'] as $var => $val) {
					if (preg_match('/^{(?P<var>(.*?)\w+)}+$/', $val, $res)) {
						$vars[$var] = $matches[$res['var']];
					}
					else {
						$vars[$var] = $val;
					}
				}

				break;
			}
		}

		$this->vars = $vars;

		if (empty($this->vars['module'])) $this->vars['module'] = 'index';
		if (empty($this->vars['controller'])) $this->vars['controller'] = 'index';
		if (empty($this->vars['action'])) $this->vars['action'] = 'index';

		Extend::setAction('router_parsed_url', array('router'=>$this));
    }
	
	public function route() {
		if ($this->vars['controller'] == 'index') {
			$path = BASE.'/modules/'.$this->vars['module'].'/index.php';
		}
		else {
			$path = BASE.'/modules/'.$this->vars['module'].'/controllers/'.$this->vars['controller'].'.php';
		}
		
		$event_name = $this->vars['module'].'_'.$this->vars['controller'].'_'.$this->vars['action'];
		if (Extend::isHandlersExist('define_action_'.$event_name)) {
			Extend::setAction('define_action_'.$event_name);
		}
		else {		
			if (!file_exists($path)) $this->error('404 Not Found', 404);

			include_once $path;
			$class_name = '\Extension\Module\Controller'.$this->vars['module'].$this->vars['controller'];

			if (!class_exists($class_name, false)) $this->error('404 Not Found', 404);

			$controller = new $class_name(BASE.'/modules/'.$this->vars['module'], $this->vars['module']);
			$action = 'Action'.$this->vars['action'];
			
			if (!method_exists($controller, $action)) $this->error('404 Not Found', 404);
			
			Extend::setAction('before_run_action_'.$event_name, array('Controller'=>$controller));
			$controller->$action();
			Extend::setAction('after_run_action_'.$event_name, array('Controller'=>$controller));
		}
	}
	
	public function error($code='404 Not Found', $url='404') {
		header('HTTP/1.1 '.$code);
		header('Status: '.$code);
		header('Location: '.BASE_URL.'/index.php?a='.$url);
		exit();
	}
	
	public function getVars() {
		return $this->vars;
	}
	
	public function getVar($var) {
		return (isset($this->vars[$var])) ? $this->vars[$var] : false;
	}
	
	public function setVar($var, $value) {
		$this->vars[$var] = $value;
	}

	public function addRule($pattern, $data, $alias=false) {
		$pattern = $this->createPattern($pattern);
	
		$rule = array(
			'pattern' => $pattern,
			'vars' => $data,
		);
		
		if ($alias) {
			$this->rules[$alias] = $rule;
		}
		else {
			array_unshift($this->rules, $rule);
		}
	}
	
	public function createPattern($pattern) {
		$pattern = str_replace('/', '\/', $pattern);
		$pattern = str_replace(array('{', '}'), array('(?P<', '>(.*?)\w+)'), $pattern);
		
		return $pattern;
	}
	
	public function getRuleByAlias($alias) {
		if (isset($this->rules[$alias])) return $this->rules[$alias];
		return false;
	}

	public function getRuleByPattern($pattern) {
		$pattern = $this->createPattern($pattern);
		
		foreach ($this->rules as $alias => $rule) {
			if ($rule['pattern'] == $pattern) return $rule; 
		}
		
		return false;
	}
}
?>