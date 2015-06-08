<?php
use Core\Library\View\Template;

function exSetAction($action) {
	call_user_func_array(array('Core\Library\Extension\Extend', 'setAction'), func_get_args());
}

function pagination($pages,$url,$current=1) {
	Template::getInstance()->pagination($pages, $url, $current);
}
?>