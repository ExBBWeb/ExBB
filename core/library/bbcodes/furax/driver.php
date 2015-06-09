<?php
namespace Core\Library\BBCodes\Furax;

use Core\Library\BBCodes\BaseDriver;

require_once('brutal.php');

class Driver implements BaseDriver {
	public function parse($string) {
		return \FuraxBrutalBB::run($string);
	}
	
	public function addBBCode($code) {
		
	}
	
	public function addSmile($smile, $path, $title='') {
		
	}
}
?>