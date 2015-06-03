<?php if (isset($errors)) : ?>
	<?php foreach ($errors as $error) : ?>
	<div class="message-box-error"><?php echo $error; ?></div>
	<?php endforeach; ?>
<?php endif; ?>

<h1>Письмо администратору</h1>

<form action="" method="POST">
	<input type="hidden" name="process" value="1">
	
	<?php if (!$logged) : ?>
	
	<div class="form-group">
		<label for="user_name">Имя пользователя</label>
		<input type="text" class="form-control" name="name" value="<?php echo $object->user_name; ?>" id="user_name" placeholder="Имя пользователя">
	</div>

	<?php else : ?>

	<?php endif; ?>
	
	<div class="form-group">
		<label for="user_mail">Адрес E-mail</label>
		<input type="text" class="form-control" name="mail" value="<?php echo $object->user_mail; ?>" id="user_mail" placeholder="Адрес электронной почты">
	</div>
	
	<div class="form-group">
		<label for="feedback_title">Название сообщения</label>
		<input type="text" class="form-control" name="title" value="<?php echo $object->title; ?>" id="feedback_title" placeholder="Тема сообщения">
	</div>
	
	<div class="form-group">
		<label for="feedback_category">Категория вопроса?</label>
		<select class="form-control" name="category_id" id="feedback_category">
			<?php foreach ($categories as $category) : ?>
				<option value="<?php echo $category['id']; ?>"<?php if ($object->category_id == $category['id']) echo ' selected';?>><?php echo $category['title']; ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="form-group">
		<label for="feedback_text">Текст сообщения</label>
		<textarea class="form-control" name="text" value="" id="feedback_text" placeholder="Текст сообщения"><?php echo $object->text; ?></textarea>
	</div>
	
	<button type="submit" class="btn btn-primary">Сохранить</button>
</form>
