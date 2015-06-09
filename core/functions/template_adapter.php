<?php
use Core\Library\View\Template;
use Core\Library\Extension\Extend;

function exSetAction($action) {
	call_user_func_array(array('Core\Library\Extension\Extend', 'setAction'), func_get_args());
}

function pagination($pages,$url,$current=1) {
	Template::getInstance()->pagination($pages, $url, $current);
}

/**
* Выводит редактор BB кодов
* $options['name'] - атрибут name для текстового поля
* $options['placeholder'] - Placeholder для текстового поля
* $options['class'] - класс поля с редактором
* $options['id'] - ID поля с редактором
*/
function bbcode_editor($options) {
	Extend::setAction('bbcode_editor', $options);
}
?>