<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\Article;

class ControllerContentArticle extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Статьи';
		$app->template->setParam('page_header', 'Все статьи');

		$app->template->addBreadcrumb('Контент', $app->url->module('content'));
		$app->template->addBreadcrumb('Статьи', false, true);
		
		
		$this->data['articles'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'article');
		
		$this->view('article/index');
	}
	
	public function ActionAdd() {
		$app = $this->app;

		$app->template->title = 'Создание статьи';
		$app->template->setParam('page_header', 'Создание статьи');

		$app->template->addBreadcrumb('Контент', $app->url->module('content'));
		$app->template->addBreadcrumb('Статьи', $app->url->module('content', 'article'));
		$app->template->addBreadcrumb('Создание статьи', false, true);
		
		$app->template->addJavaScript($app->url->getBaseUrl().'/media/ckeditor/ckeditor.js');
		
		$article = new Article();
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
			$data = array(
				'title' => $this->request->post['title'],
				'category_id' => $this->request->post['category_id'],
				'alias' => $alias,
				'description' => $this->request->post['description'],
				'content' => $this->request->post['content'],
				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],
				
				'date_added' => 'NOW()',
				'date_modifed' => 'NOW()',
				
				'views' => 0,
				'comments' => 0,
			);
			
			$article->setData($data);
			$article->save();
			
			$app->redirectPage($app->url->module('content', 'article'), 'Создание статьи', 'Статья была успешно создана!');
		}
		
		$this->data['article'] = $article;
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'article_category');
		
		$this->view('article/form');
	}
	
	public function ActionEdit() {
		$app = $this->app;
		
		$article_id = $app->router->getVar('param');
		if (!$article_id) $app->redirectPage($app->url->module('content', 'article'), 'Ошибка!', 'Не указан ID статьи!');
		
		$article = new Article($article_id);
		if (!$article->exists()) $app->redirectPage($app->url->module('content', 'article'), 'Ошибка!', 'Такой статьи не существует!');
		
		$app->template->title = 'Редактирование категории';
		$app->template->setParam('page_header', 'Редактирование категории');

		$app->template->addBreadcrumb('Контент', $app->url->module('content'));
		$app->template->addBreadcrumb('Статьи', $app->url->module('content', 'article'));
		$app->template->addBreadcrumb('Редактирование', false, true);
		
		$app->template->addJavaScript($app->url->getBaseUrl().'/media/ckeditor/ckeditor.js');
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
			$data = array(
				'title' => $this->request->post['title'],
				'category_id' => $this->request->post['category_id'],
				'alias' => $alias,
				'description' => $this->request->post['description'],
				
				'content' => $this->request->post['content'],
				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],

				'date_modifed' => 'NOW()',
				
				'views' => 0,
				'comments' => 0,
			);
			
			$article->setData($data);
			$article->save();
			
			$app->redirectPage($app->url->module('content', 'article'), 'Редактирование статьи', 'Статьия была успешно отредактирована!');
		}
		
		$this->data['article'] = $article;
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'article_category');
		
		$this->view('article/form');
	}
	
	public function ActionDelete() {
		$app = $this->app;
		
		$article_id = $app->router->getVar('param');
		if (!$article_id) $app->redirectPage($app->url->module('content', 'article'), 'Ошибка!', 'Не указан ID статьи!');
		
		$article = new Article($article_id);
		if (!$article->exists()) $app->redirectPage($app->url->module('content', 'article'), 'Ошибка!', 'Такой статьи не существует!');
		
		$article->delete();
		
		$app->redirectPage($app->url->module('content', 'article'), 'Удаление статьи', 'Статья была успешно удалена!');
	}
}
?>