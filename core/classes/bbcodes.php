<?php
namespace Core\Classes;

use Core\Classes\BBCodes\Collection;

class BBCodes {
	public function __construct() {
		$this->collection = new Collection();
	}
	
	public function parse($string) {
		foreach ($this->collection as $name => $code) {
			if ($code instanceof BBCodes\RegExpSimpleCode) {
				$string = preg_replace($code->getRegExp(), $code->getReplacement(), $string);
			}
			elseif ($code instanceof BBCodes\ReplaceSimpleCode) {
				// В функцию передаётся адрес строки
				$code->replace($string);
			}
		}
		
		return $string;
	}
}

?>