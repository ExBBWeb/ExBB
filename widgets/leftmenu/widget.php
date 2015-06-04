<?php
namespace Extension\Widget;

use Core\Library\Extension\BaseWidget;

class WidgetLeftMenu extends BaseWidget {
	public function ActionIndex() {
		print_r($this->loadLanguage('index'));
		$this->view('menu');
	}
}
?>