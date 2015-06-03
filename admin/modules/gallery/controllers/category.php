<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\GalleryAlbum;

class ControllerGalleryCategory extends BaseController {
	protected $base;
	protected $base_url;
	
	public function __Construct($path, $module) {
		parent::__Construct($path, $module);
		
		$this->base_url = BASE_URL.'/media/gallery';
		$this->base = ROOT.'/media/gallery';
	}
	
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Категории';
		$app->template->setParam('page_header', 'Все категории');

		$app->template->addBreadcrumb('Галерея', $app->url->module('gallery'));
		$app->template->addBreadcrumb('Категории', false, true);
		
		
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'gallery_albums');
		
		$this->view('category/index');
	}
	
	public function ActionAdd() {
		$app = $this->app;

		$app->template->title = 'Создание категории';
		$app->template->setParam('page_header', 'Создание категории');

		$app->template->addBreadcrumb('Галерея', $app->url->module('gallery'));
		$app->template->addBreadcrumb('Категории', $app->url->module('gallery', 'category'));
		$app->template->addBreadcrumb('Создание категории', false, true);
		
		$category = new GalleryAlbum();
		
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
				'image' => $this->upload(),
				'images' => 0,
			);
			
			$category->setData($data);
			$category->save();
			
			$app->redirectPage($app->url->module('gallery', 'category'), 'Создание категории', 'Категория была успешно создана!');
		}
		
		$this->data['category'] = $category;
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'gallery_albums');
		
		$this->view('category/form');
	}
	
	public function ActionEdit() {
		$app = $this->app;
		
		$category_id = $app->router->getVar('param');
		if (!$category_id) $app->redirectPage($app->url->module('gallery', 'category'), 'Ошибка!', 'Не указан ID категории!');
		
		$category = new GalleryAlbum($category_id);
		if (!$category->exists()) $app->redirectPage($app->url->module('gallery', 'category'), 'Ошибка!', 'Такой категории не существует!');
		
		$app->template->title = 'Редактирование категории';
		$app->template->setParam('page_header', 'Редактирование категории');

		$app->template->addBreadcrumb('Галерея', $app->url->module('gallery'));
		$app->template->addBreadcrumb('Категории', $app->url->module('gallery', 'category'));
		$app->template->addBreadcrumb('Редактирование', false, true);
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
			$path = $this->upload($this->base.'/'.$category->image);
			
			if (!$path) $path = $category->image;

			$data = array(
				'title' => $this->request->post['title'],
				'parent_id' => $this->request->post['parent_id'],
				'alias' => $alias,
				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],
				
				'image' => $path,
				
				'images' => 0,
			);
			
			$category->setData($data);
			$category->save();
			
			$app->redirectPage($app->url->module('gallery', 'category'), 'Редактирование категории', 'Категория была успешно отредактирована!');
		}
		
		$this->data['category'] = $category;
		$this->data['categories'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'gallery_albums');

		$this->data['image_src'] = $this->base_url.'/'.$category->image;
		
		$this->view('category/form');
	}
	
	public function ActionDelete() {
		$app = $this->app;
		
		$category_id = $app->router->getVar('param');
		if (!$category_id) $app->redirectPage($app->url->module('gallery', 'category'), 'Ошибка!', 'Не указан ID категории!');
		
		$category = new GalleryAlbum($category_id);
		if (!$category->exists()) $app->redirectPage($app->url->module('gallery', 'category'), 'Ошибка!', 'Такой категории не существует!');
		
		if (file_exists($this->base.'/'.$category->image)) unlink($this->base.'/'.$category->image);
		
		$category->delete();
		
		$app->redirectPage($app->url->module('gallery', 'category'), 'Удаление категории', 'Категория была успешно удалена!');
	}
	
	protected function upload($remove_path=false) {	
		if (isset($_FILES['image']) && isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
			$fileName = $_FILES['image']['name'];
				
			$extension = substr(strrchr($fileName, '.'), 1);
					
			$folder = date('Y/m/d');
			
			$img_folder = $this->base.'/'.$folder;
		
			if (!is_dir($img_folder)) mkdir($img_folder, 0755, true);
				
			$unicname = uniqid();
			$img_path = $img_folder.'/'.$unicname.'.'.$extension;

			move_uploaded_file($_FILES['image']['tmp_name'], $img_path);

			if (class_exists('\Imagick', false)) {
				$imagem = new \Imagick($img_path);
				$imagem->thumbnailImage(640, 0);
				$imagem->writeImage();
			}
			
			if ($remove_path && file_exists($remove_path) && is_file($remove_path)) unlink($remove_path);
			
			return $folder.'/'.$unicname.'.'.$extension;
		}
		else return false;
	}
}
?>