<?php
namespace Core\Classes\Image;

interface BaseDriver {
	const RESIZE_STRICT = 1;
	const RESIZE_USE_WIDTH = 2;
	const RESIZE_USE_HEIGHT = 3;
	//const RESIZE_AUTO = 4;

	public function load($path);
	public function save($path);
	
    public function resize($width, $height, $type);
	public function crop($width, $height, $offset_x, $offset_y);
	
	public function isAvailable();
	public function show();
}
?>