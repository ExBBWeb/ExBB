<?php
namespace Core\Library\BBCodes;

interface BaseDriver {
	public function parse($string);
	public function addBBCode($code);
	public function addSmile($smile, $path, $title='');
}
?>