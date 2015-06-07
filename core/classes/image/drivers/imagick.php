<?php
namespace Core\Classes\Image\Drivers;

use Core\Classes\Image\BaseDriver;

class Imagick implements BaseDriver {
	protected $path;
	protected $image = null;
	
	public function __construct($path=false) {
		//$this->path = $path;
		if ($path) $this->load($path);
	}
	
	public function load($file) {
		if (!is_file($file)) throw new \Exception('Ошибка загрузки изображения!');
		
		$this->path = $file;
		$this->image = new \Imagick($file);
	}
	
	public function save($file=false) {
		if ($file) $this->image->writeImage($file);
		else $this->image->writeImage($this->path);
	}
	
	public function resize($width, $height=false, $type=BaseDriver::RESIZE_USE_WIDTH) {
		if ($type == BaseDriver::RESIZE_USE_WIDTH) {
			$this->image->thumbnailImage($width, 0);
		}
		elseif ($type == BaseDriver::RESIZE_USE_HEIGHT) {
			$this->image->thumbnailImage(0, $height);
		}
		elseif ($type == BaseDriver::RESIZE_STRICT) {
			$this->image->thumbnailImage($width, $height);
		}
	}
	
	public function crop($width, $height, $offset_x=0, $offset_y=0) {
		$this->image->cropImage($width, $height, $offset_x, $offset_y);
	}
	
	public function show() {
		header('Content-type: image/jpeg');
		echo $this->image;
	}
	
	public function isAvailable() {
		return (class_exists('\Imagick', false));
	}
}
?>