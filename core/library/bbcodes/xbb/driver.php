<?php
namespace Core\Library\BBCodes\xBB;

use Core\Library\BBCodes\BaseDriver;

include dirname(__FILE__).'/bbcode.lib.php';

class Driver implements BaseDriver {
	public function parse($string) {
		$parser = new \bbcode($string);
		return $parser->get_html();
	}
	
	public function addBBCode($code) {
		
	}
	
	public function addSmile($smile, $path, $title='') {
		
	}
}
?>