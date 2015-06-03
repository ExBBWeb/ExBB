<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\ArticleCategory;
use Core\Library\Site\Entity\Article;

class ControllerIndexArticle extends BaseController {
	public function ActionIndex() {
		$app = $this->app;
		$alias = $app->router->getVar('alias');

		$article = new Article(array('alias'=>$alias));

		if (!$article->exists()) $app->redirectPage($app->url->get('index'), 'Ошибка', 'Такой статьи не существует!', 'error');
		
		$category = new ArticleCategory($article->category_id);

		$app->template->title = $article->title;
		$app->template->setParam('page_header', $article->title);
		
		$app->template->addBreadcrumb('Главная', $app->url->get('index'));
		$app->template->addBreadcrumb($category->title, $app->url->get('category', array('category'=>$category->alias)));
		$app->template->addBreadcrumb($article->title, false, true);

		$article->views++;
		$article->save();
		
		$this->data['article'] = $article;
		$this->data['category'] = $category;
		
		$this->view('article/view');
	}
}
?>