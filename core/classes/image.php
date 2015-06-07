<?php
namespace Core\Classes;

use Core\Library\Application\Application;

class Image {
	const RESIZE_STRICT = 1;
	const RESIZE_USE_WIDTH = 2;
	const RESIZE_USE_HEIGHT = 3;
	//const RESIZE_AUTO = 4;
	
	protected $driver;
	protected $path;
	
	public function __construct($path, $driver=false) {
		$this->path = $path;
		
		$this->loadDriver($driver);
	}
	
	public function loadDriver($driver_type=false) {
		if (!$driver_type) $driver_type = Application::getInstance()->config->getOption('core_image_driver');
		$driver_class = 'Core\Classes\Image\Drivers\\'.$driver_type;
		$this->driver = new $driver_class($this->path);
	}
	
	public function resize($width, $height=false, $type=Image::RESIZE_USE_WIDTH) {
		$this->driver->resize($width, $height, $type);
	}
	
	public function crop($width, $height, $offset_x=0, $offset_y=0) {
		$this->driver->crop($width, $height, $offset_x, $offset_y);
	}
	
	public function show() {
		$this->driver->show();
	}

	public function save($file=false) {
		$this->driver->save($file);
	}
}
?>