<?php
use Core\Library\Extension\Language;

function l($lang) {
	return Language::getInstance()->getString($lang);
}

function _l($lang) {
	echo Language::getInstance()->getString($lang);
}
?>