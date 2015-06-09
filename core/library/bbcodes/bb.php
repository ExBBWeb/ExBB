<?php
namespace Core\Library\BBCodes;

use Core\Library\Application\Application;

class BB {
	protected $driver;
	
	public function __construct($driver_name=false) {
		if (!$driver_name) $driver_name = Application::getInstance()->config->getOption('core_bbcode_driver');
		
		$driver_name = 'Core\Library\BBCodes\\'.$driver_name.'\Driver';
		
		$this->driver = new $driver_name();
	}
	
	public function getDriver() {
		return $this->driver;
	}
	
	public function parse($string) {
		return $this->driver->parse($string);
	}
}
?>