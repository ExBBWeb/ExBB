<div class="hor-space"></div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th>#</th>
			<th>Название</th>
			<th>Пользователь</th>
			<th>E-mail</th>
			<th>Категория</th>
			<th>Дата</th>
			<th>Опции</th>
		</tr>
	</thead>
	
	<?php foreach ($messages as $message) : ?>
		<tr<?php if (!$message['readed']) echo ' class="bg-info"'; ?>>
			<td><?php echo $message['id']; ?></td>
			<td><a href="<?php echo $url->module('feedback', 'index', 'view', $message['id']); ?>"><?php echo $message['title']; ?></a></td>
			<td><?php echo $message['user_name']; ?></td>
			<td><?php echo $message['user_mail']; ?></td>
			<td><?php echo $categories[$message['category_id']]['title']; ?></td>
			<td><?php echo $message['date_added']; ?></td>
			<td>
				<a class="btn btn-info" href="<?php echo $url->module('feedback', 'index', 'view', $message['id']); ?>">Смотреть</a>
				<a class="btn btn-primary" href="<?php echo $url->module('feedback', 'index', 'edit', $message['id']); ?>">Редактировать</a>
				<a class="btn btn-danger" href="<?php echo $url->module('feedback', 'index', 'delete', $message['id']); ?>">Удалить</a>
			</td>
		</tr>
	<?php endforeach; ?>
</table>