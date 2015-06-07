<?php
namespace Core\Classes\Image\Drivers;

use Core\Classes\Image\BaseDriver;

class GD implements BaseDriver {
	protected $path;
	protected $image = null;

	public function __construct($path=false) {
		if ($path) $this->load($path);
	}
	
	public function load($file) {
		if (!is_file($file)) throw new \Exception('Ошибка загрузки изображения!');
		$this->path = $file;

        //Получаем информацию о файле
        list($width, $height, $image_type) = getimagesize($file);
 
        //Создаем изображение из файла
        switch ($image_type) {
			case 1:
				$this->image = imagecreatefromgif($file);
			break;
			case 2:
				$this->image = imagecreatefromjpeg($file);
			break;
			case 3:
				$this->image = imagecreatefrompng($file);
			break;
            
			default:
				return false;
			break;
		}

	}
	
	public function save($file=false) {
		if ($file) imagejpeg($this->image, $file);
		else imagejpeg($this->image, $this->path);
	}
	
	public function resize($width, $height=false, $type=BaseDriver::RESIZE_USE_WIDTH) {
		if (!$this->image) return false;
		

		$x = ImageSX($this->image);
		$y = ImageSY($this->image);

		$k = $x/$y;
		
		if ($type == BaseDriver::RESIZE_USE_WIDTH) {
			$height = $width/$k;
		}
		elseif ($type == BaseDriver::RESIZE_USE_HEIGHT) {
			$width = $height*$k;
		}
		elseif ($type == BaseDriver::RESIZE_STRICT) {
			
		}
		
		$image_new = imagecreatetruecolor($width,$height);
		imagecopyresampled($image_new, $this->image, 0, 0, 0, 0, $width, $height, $x, $y);
		$this->image = $image_new;
	}
	
	public function crop($width, $height, $offset_x=0, $offset_y=0) {
		if (!$this->image) return false;

		$x = ImageSX($this->image);
		$y = ImageSY($this->image);

		if ($width > $x) $width = $x;
		if ($height > $y) $height = $y;
		
		$temp = imagecreatetruecolor($width, $height);
		imagecopyresampled($temp, $this->image, 0, 0, $offset_x, $offset_y, $width, $height, $width, $height);
		$this->image = $temp;
	}
	
	public function show() {
		header('Content-type: image/jpeg');
		// Выводим изображение
		imagejpeg($this->image);

		// Освобождаем память
		//imagedestroy($this->image);
	}
	
	public function __destruct() {
		imagedestroy($this->image);
	}
	
	public function isAvailable() {
		return (extension_loaded('gd') || extension_loaded('gd2'));
	}
}
?>