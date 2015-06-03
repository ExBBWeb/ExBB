<form action="" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="process" value="1">

	<div class="form-group">
		<label for="image">Изображение</label>
		<input type="file" name="image">
		<?php if ($image->image) : ?>
		<img width="240" src="<?php echo $image_src; ?>">
		<?php endif; ?>
	</div>
	
	<div class="form-group">
		<label for="title">Название</label>
		<input type="text" class="form-control" id="title" value="<?php echo $image->title; ?>" name="title" placeholder="Введите название изображения">
	</div>
	
	<div class="form-group">
		<label for="category_id">Категория</label>
		<select name="category_id" id="category_id" class="form-control">
			<option value="0">Не выбрано</option>
			<?php foreach ($albums as $album) : ?>
				<option value="<?php echo $album['id']; ?>"<?php if ($image->category_id == $album['id']) echo ' selected';?>><?php echo $album['title']; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="alias">Алиас изображения</label>
		<input type="text" class="form-control" id="alias" value="<?php echo $image->alias; ?>" name="alias" placeholder="Алиас изображения">
		<span class="help-block">Вы можете оставить поле пустым, тогда алиас будет сгенерирован автоматически</span>
	</div>

	<div class="form-group">
		<label>Описание изображения</label>
		<textarea name="description" id="description">
                <?php echo $image->description; ?>
		</textarea>
		<script type="text/javascript">
			CKEDITOR.replace('description');
		</script>
	</div>

	<div class="form-group">
		<label for="meta_description">Мета-описание</label>
		<input type="text" class="form-control" id="meta_description" value="<?php echo $article->meta_description; ?>" name="meta_description" placeholder="Мета-описание статьи">
	</div>
	
	<div class="form-group">
		<label for="meta_keywords">Ключевые слова</label>
		<input type="text" class="form-control" id="meta_keywords" value="<?php echo $article->meta_keywords; ?>" name="meta_keywords" placeholder="Ключевые слова статьи">
	</div>
	
	<button type="submit" class="btn btn-success">Сохранить</button>
</form>