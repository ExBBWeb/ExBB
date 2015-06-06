<div class="row">
	<div class="col-md-2 profile-avatar">
		<p><strong><?php echo $user->login; ?></strong></p>
		<p><img src="<?php echo $baseurl.'/uploads/avatars/'.$user->avatar; ?>" class="avatar-image" alt="<?php echo $user->login; ?>"></p>
	</div>
	<div class="col-md-10 profile-info">
		<h3>Личная информация</h3>
		<p><strong>Логин:</strong> <?php echo $user->login; ?></p>
		<p><strong>E-mail:</strong> <?php echo $user->email; ?></p>
		<br>
		<?php foreach ($fields as $field) : ?>
		<p><strong><?php echo $field['options']['title'][$language]; ?>:</strong> <?php echo $user->getFieldData($field['id']); ?></p>
		<?php endforeach; ?>
		
		<h3>Статистика</h3>
		<p><strong>Тем:</strong> <?php echo $user->topics; ?></p>
		<p><strong>Сообщений:</strong> <?php echo $user->posts; ?></p>
		<p><strong>Дата регистрации:</strong> <?php echo $user->register_date; ?></p>
		<p><strong>Дата последнего посещения:</strong> <?php echo $user->last_login_date; ?></p>
	</div>
</div>