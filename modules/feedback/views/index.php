<?php if (isset($errors)) : ?>
	<?php foreach ($errors as $error) : ?>
	<div class="message-box-error"><?php echo $error; ?></div>
	<?php endforeach; ?>
<?php endif; ?>

<h1>Письмо администратору</h1>

<div>
	<p>На этой странице вы можете написать сообщение администратору сайта.</p>
	<p>Администратор ответит на сообщение в течение одного-двух дней.</p>
</div>

<form action="" method="POST">
	<input type="hidden" name="process" value="1">
	
	<?php if (!$logged) : ?>
	
	<div class="form-group">
		<label for="user_name">Ваше имя</label>
		<input type="text" class="form-control" name="name" value="" id="user_name" placeholder="Ваше имя">
	</div>

	<?php else : ?>

	<?php endif; ?>
	
	<div class="form-group">
		<label for="user_mail">Ваш E-mail</label>
		<input type="text" class="form-control" name="mail" value="" id="user_mail" placeholder="Ваш адрес электронной почты">
		<span class="help-block">Заполнять не обязательно, но лучше указать реальный адрес, чтобы администратор смог ответить на ваше сообщение.</span>
	</div>
	
	<div class="form-group">
		<label for="feedback_title">Название сообщения</label>
		<input type="text" class="form-control" name="title" value="" id="feedback_title" placeholder="Здесь введите тему сообщения">
	</div>
	
	<div class="form-group">
		<label for="feedback_category">О чём ваш вопрос?</label>
		<select class="form-control" name="category_id" id="feedback_category">
			<?php foreach ($categories as $category) : ?>
				<option value="<?php echo $category['id']; ?>"><?php echo $category['title']; ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="form-group">
		<label for="feedback_text">Текст сообщения</label>
		<textarea class="form-control" name="text" value="" id="feedback_text" placeholder="Текст сообщения"></textarea>
		<span class="help-block">Пожалуйста, опишите интересующий вас вопрос как можно точнее и укажите, как можно с вами связаться (для ответа на вопрос). Можно указать адрес страницы ВКонтаке, адрес E-mail и т. д..</span>
	</div>
	
	<button type="submit" class="btn btn-primary">Обратиться</button>
</form>
