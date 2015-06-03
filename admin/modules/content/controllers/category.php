<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\ArticleCategory;

class ControllerContentCategory extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Категории';
		$app->template->setParam('page_header', 'Все категории');

		$app->template->addBreadcrumb('Контент', $app->url->module('content'));
		$app->template->addBreadcrumb('Категории', false, true);
		
		
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'article_category');
		
		$this->view('category/index');
	}
	
	public function ActionAdd() {
		$app = $this->app;

		$app->template->title = 'Создание категории';
		$app->template->setParam('page_header', 'Создание категории');

		$app->template->addBreadcrumb('Контент', $app->url->module('content'));
		$app->template->addBreadcrumb('Категории', $app->url->module('content', 'category'));
		$app->template->addBreadcrumb('Создание категории', false, true);
		
		$category = new ArticleCategory();
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
			$data = array(
				'title' => $this->request->post['title'],
				'parent_id' => $this->request->post['parent_id'],
				'alias' => $alias,
				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],
			);
			
			$category->setData($data);
			$category->save();
			
			$app->redirectPage($app->url->module('content', 'category'), 'Создание категории', 'Категория была успешно создана!');
		}
		
		$this->data['category'] = $category;
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'article_category');
		
		$this->view('category/form');
	}
	
	public function ActionEdit() {
		$app = $this->app;
		
		$category_id = $app->router->getVar('param');
		if (!$category_id) $app->redirectPage($app->url->module('content', 'category'), 'Ошибка!', 'Не указан ID категории!');
		
		$category = new ArticleCategory($category_id);
		if (!$category->exists()) $app->redirectPage($app->url->module('content', 'category'), 'Ошибка!', 'Такой категории не существует!');
		
		$app->template->title = 'Редактирование категории';
		$app->template->setParam('page_header', 'Редактирование категории');

		$app->template->addBreadcrumb('Контент', $app->url->module('content'));
		$app->template->addBreadcrumb('Категории', $app->url->module('content', 'category'));
		$app->template->addBreadcrumb('Редактирование', false, true);
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
			$data = array(
				'title' => $this->request->post['title'],
				'parent_id' => $this->request->post['parent_id'],
				'alias' => $alias,
				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],
			);
			
			$category->setData($data);
			$category->save();
			
			$app->redirectPage($app->url->module('content', 'category'), 'Редактирование категории', 'Категория была успешно отредактирована!');
		}
		
		$this->data['category'] = $category;
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'article_category');
		
		$this->view('category/form');
	}
	
	public function ActionDelete() {
		$app = $this->app;
		
		$category_id = $app->router->getVar('param');
		if (!$category_id) $app->redirectPage($app->url->module('content', 'category'), 'Ошибка!', 'Не указан ID категории!');
		
		$category = new ArticleCategory($category_id);
		if (!$category->exists()) $app->redirectPage($app->url->module('content', 'category'), 'Ошибка!', 'Такой категории не существует!');
		
		$category->delete();
		
		$app->redirectPage($app->url->module('content', 'category'), 'Удаление категории', 'Категория была успешно удалена!');
	}
}
?>