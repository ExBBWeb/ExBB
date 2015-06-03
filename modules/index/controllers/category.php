<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\ArticleCategory;

class ControllerIndexCategory extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$alias = $app->router->getVar('alias');

		$category = new ArticleCategory(array('alias'=>$alias));

		if (!$category->exists()) $app->redirectPage($app->url->get('index'), 'Ошибка', 'Такой категории не существует!', 'error');
		
		$app->template->title = $category->title;
		//$app->template->setParam('page_header', $category->title);
		
		$app->template->addBreadcrumb('Главная', $app->url->get('index'));
		$app->template->addBreadcrumb($category->title, false, true);
		
		$query = $this->db->parse('SELECT id,category_id,title,alias,date_added,description FROM '.DB_PREFIX.'article WHERE category_id=?i', $category->id);
		$this->data['articles'] = $this->db->getAll($query);
		
		$this->view('category/view');
	}
}
?>