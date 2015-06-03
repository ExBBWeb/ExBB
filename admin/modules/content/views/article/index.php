<div class="text-right">
	<a class="btn btn-success" href="<?php echo $url->module('content', 'article', 'add'); ?>">Создать</a>
</div>

<div class="hor-space"></div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th>#</th>
			<th>Название</th>
			<th>Просмотров</th>
			<th>Комментариев</th>
			<th>Опции</th>
		</tr>
	</thead>
	
	<?php foreach ($articles as $article) : ?>
		<tr>
			<td><?php echo $article['id']; ?></td>
			<td><?php echo $article['title']; ?></td>
			<td><?php echo $article['views']; ?></td>
			<td><?php echo $article['comments']; ?></td>
			<td>
				<a class="btn btn-primary" href="<?php echo $url->module('content', 'article', 'edit', $article['id']); ?>">Редактировать</a>
				<a class="btn btn-danger" href="<?php echo $url->module('content', 'article', 'delete', $article['id']); ?>">Удалить</a>
			</td>
		</tr>
	<?php endforeach; ?>
</table>