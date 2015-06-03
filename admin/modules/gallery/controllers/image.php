<?php
namespace Extension\Module;

use Core\Library\MVC\BaseController;
use Core\Library\Application\Application;

use Core\Library\Site\Entity\GalleryImage;
use Core\Library\Site\Entity\GalleryAlbum;

class ControllerGalleryImage extends BaseController {
	protected $base;
	protected $base_url;
	
	public function __Construct($path, $module) {
		parent::__Construct($path, $module);
		
		$this->base_url = BASE_URL.'/media/gallery';
		$this->base = ROOT.'/media/gallery';
		
		$this->app->template->addStyleSheet($this->app->url->getBaseUrl().'/media/fileupload/uploadfile.css');
		$this->app->template->addJavaScript($this->app->url->getBaseUrl().'/media/fileupload/jquery.uploadfile.min.js');
	}
	
	public function ActionIndex() {
		$app = $this->app;
		$app->template->title = 'Изображения';
		$app->template->setParam('page_header', 'Изображения');

		$app->template->addBreadcrumb('Галерея', $app->url->module('gallery'));
		$app->template->addBreadcrumb('Изображения', false, true);
		
		
		$this->data['images'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'gallery_images');
		
		$this->data['albums'] = $this->db->getIndexedAll('id', 'SELECT * FROM '.DB_PREFIX.'gallery_albums');
		$this->view('image/index');
	}
	
	public function ActionAdd() {
		$app = $this->app;

		$app->template->title = 'Загрузка изображения';
		$app->template->setParam('page_header', 'Загрузка изображения');

		$app->template->addBreadcrumb('Галерея', $app->url->module('gallery'));
		$app->template->addBreadcrumb('Изображение', $app->url->module('gallery', 'article'));
		$app->template->addBreadcrumb('Создание изобажения', false, true);
		
		$app->template->addJavaScript($app->url->getBaseUrl().'/media/ckeditor/ckeditor.js');
		
		$image = new GalleryImage();
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
			$n = 0;
			$img = new GalleryImage(array('alias'=>$alias));
			while ($img->exists()) {
				$n++;
				$img = new GalleryImage(array('alias'=>$alias.'-'.$n));
			}
			
			$alias .= '-'.$n;

			$data = array(
				'title' => $this->request->post['title'],
				'category_id' => $this->request->post['category_id'],
				'alias' => $alias,
				'description' => $this->request->post['description'],

				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],

				'date_added' => 'NOW()',
				
				'image' => $this->upload(),
				
				'views' => 0,
				'comments' => 0,
			);
			
			$image->setData($data);
			$image->save();
			
			$album = new GalleryAlbum($image->category_id);

			$album->images++;
			$album->save();
			
			$app->redirectPage($app->url->module('gallery', 'image'), 'Создание изображения', 'Изображение успешно загружено!');
		}
		
		$this->data['image'] = $image;
		$this->data['albums'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'gallery_albums');
		
		$this->view('image/form');
	}
	
	public function ActionEdit() {
		$app = $this->app;
		
		$article_id = $app->router->getVar('param');
		if (!$article_id) $app->redirectPage($app->url->module('gallery', 'image'), 'Ошибка!', 'Не указан ID изображения!');
		
		$image = new GalleryImage($article_id);
		if (!$image->exists()) $app->redirectPage($app->url->module('gallery', 'image'), 'Ошибка!', 'Такого изображения не существует!');

		$app->template->title = 'Редактирование изображения';
		$app->template->setParam('page_header', 'Редактирование изображения');

		$app->template->addBreadcrumb('Галерея', $app->url->module('gallery'));
		$app->template->addBreadcrumb('Изображения', $app->url->module('gallery', 'image'));
		$app->template->addBreadcrumb('Редактирование', false, true);

		$app->template->addJavaScript($app->url->getBaseUrl().'/media/ckeditor/ckeditor.js');
		
		if (isset($this->request->post['process'])) {
			$alias = $this->request->post['alias'];
			
			if (empty($this->request->post['alias'])) {
				$alias = $app->url->translit($this->request->post['title']);
			}
			
				
			$n = 0;
			$img = new GalleryImage(array('alias'=>$alias));
			while ($img->exists() && $img->id != $image->id) {
				$n++;
				$img = new GalleryImage(array('alias'=>$alias.'-'.$n));
			}
			
			$alias .= '-'.$n;

			$path = $this->upload($this->base.'/'.$image->image);
			
			if (!$path) $path = $image->image;
			
			$data = array(
				'title' => $this->request->post['title'],
				'category_id' => $this->request->post['category_id'],
				'alias' => $alias,
				'description' => $this->request->post['description'],

				'meta_description' => $this->request->post['meta_description'],
				'meta_keywords' => $this->request->post['meta_keywords'],

				'date_added' => 'NOW()',
				
				'image' => $path,
				
				'views' => 0,
				'comments' => 0,
			);

			$image->setData($data);
			$image->save();
			
			$app->redirectPage($app->url->module('gallery', 'image'), 'Редактирование изображения', 'Изображения было успешно отредактировано!');
		}
		
		$this->data['image'] = $image;
		$this->data['albums'] = $this->db->getAll('SELECT * FROM '.DB_PREFIX.'gallery_albums');

		$this->data['image_src'] = $this->base_url.'/'.$image->image;

		$this->view('image/form');
	}
	
	public function ActionDelete() {
		$app = $this->app;
		
		$image_id = $app->router->getVar('param');
		if (!$image_id) $app->redirectPage($app->url->module('gallery', 'image'), 'Ошибка!', 'Не указан ID изобажения!');
		
		$image = new GalleryImage($image_id);
		if (!$image->exists()) $app->redirectPage($app->url->module('gallery', 'image'), 'Ошибка!', 'Такого изобажения не существует!');
		
		if (file_exists($this->base.'/'.$image->image)) unlink($this->base.'/'.$image->image);
		
		$image->delete();
		
		$app->redirectPage($app->url->module('gallery', 'image'), 'Удаление статьи', 'Изобажения было успешно удалено!');
	}
	
	public function ActionUpload() {
		$fileName = $_FILES['image']['name'];
		
		$extension = substr(strrchr($fileName, '.'), 1);
		
		$folder = date('Y/m/d');
		
		$img_folder = $this->base.'/'.$folder;
	
		if (!is_dir($img_folder)) mkdir($img_folder, 0755, true);
		
		$unicname = uniqid();
		$img_path = $img_folder.'/'.$unicname.'.'.$extension;
		
		//file_put_contents(dirname(__FILE__).'/test.txt', print_r($_FILES, true).$img_path);
		
		if(!is_uploaded_file($_FILES['image']['tmp_name'])) $this->uploadError('Ошибка загрузки файла #0');
		
		if (!move_uploaded_file($_FILES['image']['tmp_name'], $img_path)) $this->uploadError('Ошибка загрузки файла #1');
		
		if (class_exists('\Imagick', false)) {
			$image = new \Imagick($img_path);
			$image->thumbnailImage(640, 0);
			$image->writeImage();
		}
		
		echo json_encode(array('status'=>true, 'message'=>'Файл загружен', 'folder'=>$folder, 'image_path'=>$folder.'/'.$unicname.'.'.$extension));
	}
	
	public function ActionSaveUpload() {
		$image = new GalleryImage();

		$alias = '';
		if (!isset($this->request->post['alias'])) {
			$alias = $this->app->url->translit($this->request->post['title']);
		}
		else {
			$alias = $this->request->post['alias'];
		}
		
		$data = array(
			'title' => $this->request->post['title'],
			'category_id' => $this->request->post['category_id'],
			'alias' => $alias,
			'description' => $this->request->post['description'],

			'meta_description' => $this->request->post['meta_description'],
			'meta_keywords' => $this->request->post['meta_keywords'],

			'date_added' => 'NOW()',
			
			'image' => $this->request->post['image'],
			
			'views' => 0,
			'comments' => 0,
		);
		
		$image->setData($data);
		$image->save();
		
		echo json_encode(array('status'=>1, 'message'=>'Изображение успешно сохранено'));
	}
	
	public function ActionDeleteUpload() {
		if (file_exists($this->base.'/'.$this->request->post['image'])) unlink($this->base.'/'.$this->request->post['image']);
		echo json_encode(array('status'=>1, 'message'=>'Изображение успешно удалено'));
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
			
			if ($remove_path && file_exists($remove_path)) unlink($remove_path);
			
			return $folder.'/'.$unicname.'.'.$extension;
		}
		else return false;
	}
	
	protected function uploadError($message) {
		echo json_encode(array('status'=>false, 'message'=>$message));
		die;
	}
}
?>