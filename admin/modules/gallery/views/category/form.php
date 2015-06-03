<form action="" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="process" value="1">

	<div class="form-group">
		<label for="image">Изображение</label>
		<input type="file" name="image">
		<?php if ($category->image) : ?>
		<img width="240" src="<?php echo $image_src; ?>">
		<?php endif; ?>
	</div>
	
	<div class="form-group">
		<label for="title">Название категории</label>
		<input type="text" class="form-control" id="title" value="<?php echo $category->title; ?>" name="title" placeholder="Введите название категории">
	</div>
	
	<div class="form-group">
		<label for="parent_id">Родительская категория</label>
		<select name="parent_id" id="parent_id" class="form-control">
			<option value="0">Не выбрано</option>
			<?php foreach ($categories as $cat) : ?>
				<?php if ($cat['id'] == $category->id) continue; ?>
			
				<option value="<?php echo $cat['id']; ?>"<?php if ($category->parent_id == $cat['id']) echo ' selected';?>><?php echo $cat['title']; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="alias">Алиас категории</label>
		<input type="text" class="form-control" id="alias" value="<?php echo $category->alias; ?>" name="alias" placeholder="Алиас категории">
		<span class="help-block">Вы можете оставить поле пустым, тогда алиас будет сгенерирован автоматически</span>
	</div>

	<div class="form-group">
		<label for="meta_description">Мета-описание</label>
		<input type="text" class="form-control" id="meta_description" value="<?php echo $category->meta_description; ?>" name="meta_description" placeholder="Мета-описание категории">
	</div>
	
	<div class="form-group">
		<label for="meta_keywords">Ключевые слова</label>
		<input type="text" class="form-control" id="meta_keywords" value="<?php echo $category->meta_keywords; ?>" name="meta_keywords" placeholder="Ключевые слова категории">
	</div>
	
	<button type="submit" class="btn btn-success">Сохранить</button>
</form>