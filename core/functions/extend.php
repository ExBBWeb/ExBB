<?php
function exSetAction($action) {
	call_user_func_array(array('Core\Library\Extension\Extend', 'setAction'), func_get_args());
}
?>