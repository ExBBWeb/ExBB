<?php
/**
* TODO: сделать поддержку неограниченной вложенности категорий,
* сейчас поддерживается масимально - 2 уровня
* Нужно добавить рекурсивную функцию, сейчас не добавил, потому что не хочу
* определять функцию в файле преставления
*/
?>
<div class="block_item">
<h3>Меню</h3>

<ul class="left_menu">
	<?php foreach ($categories[0] as $category) : ?>
	<li><a href="<?php $url->view('category', array('category'=>$category['alias'])); ?>"><?php echo $category['title']; ?></a>
	
	<?php if (isset($categories[$category['id']])) : ?>
		<ul style="padding:0px; margin: 0px 0px 0px 5px;">
		<?php foreach ($categories[$category['id']] as $subcategory) : ?>
			<li><a href="<?php $url->view('category', array('category'=>$subcategory['alias'])); ?>"><?php echo $subcategory['title']; ?></a></lI>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	
	</li>
	<?php endforeach; ?>
	
	<li><a href="<?php $url->view('gallery'); ?>">Фотоальбомы</a></li>
</ul>

<h3>Возможности</h3>

<ul class="left_menu">
	<li><a href="<?php echo $url->module('feedback'); ?>">Задать вопрос</a></li>
	<?php if ($logged) : ?>
		<li><a href="<?php echo $url->module('users', 'index', 'logout'); ?>">Выход</a></li>
	<?php else : ?>
		<li><a href="<?php echo $url->module('users', 'index', 'login'); ?>">Вход</a></li>
	<?php endif; ?>
	<li><a href="<?php echo $url->module('relations', 'chat'); ?>">Чат</a></li>
</ul>
</div>