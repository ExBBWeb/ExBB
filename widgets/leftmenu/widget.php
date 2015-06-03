<?php
namespace Extension\Widget;

use Core\Library\MVC\BaseWidget;
use Core\Library\User\Users;

class WidgetLeftMenu extends BaseWidget {
	public function ActionIndex() {
		$categories = $this->app->db->getAll('SELECT id,parent_id, title,alias FROM '.DB_PREFIX.'article_category');
		$categories_tree = array();
		
		foreach ($categories as $category) {
			$categories_tree[$category['parent_id']][] = $category;
		}

		$this->data['categories'] = $categories_tree;
		$this->data['logged'] = Users::isLogged();
		
		$this->view('menu');
	}
}
?>