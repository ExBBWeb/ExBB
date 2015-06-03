<form action="" method="POST">
	<input type="hidden" name="process" value="1">

	<div class="form-group">
		<label for="title">Название</label>
		<input type="text" class="form-control" id="title" value="<?php echo $article->title; ?>" name="title" placeholder="Введите название статьи">
	</div>
	
	<div class="form-group">
		<label for="category_id">Категория</label>
		<select name="category_id" id="category_id" class="form-control">
			<option value="0">Не выбрано</option>
			<?php foreach ($categories as $cat) : ?>
				<option value="<?php echo $cat['id']; ?>"<?php if ($article->category_id == $cat['id']) echo ' selected';?>><?php echo $cat['title']; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="alias">Алиас статьи</label>
		<input type="text" class="form-control" id="alias" value="<?php echo $article->alias; ?>" name="alias" placeholder="Алиас статьи">
		<span class="help-block">Вы можете оставить поле пустым, тогда алиас будет сгенерирован автоматически</span>
	</div>

	<div class="form-group">
		<label>Текст статьи</label>
		<textarea name="content" id="content">
                <?php echo $article->content; ?>
		</textarea>
		<script type="text/javascript">
			CKEDITOR.replace('content');
		</script>
	</div>
	
	<div class="form-group">
		<label>Краткое описание статьи</label>
		<textarea name="description" id="description">
                <?php echo $article->description; ?>
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