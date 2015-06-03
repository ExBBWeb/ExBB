<div class="text-right">
	<a class="btn btn-success" href="<?php echo $url->module('content', 'category', 'add'); ?>">Создать</a>
</div>

<div class="hor-space"></div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th>#</th>
			<th>Категория</th>
			<th>Количество статей</th>
			<th>Опции</th>
		</tr>
	</thead>
	
	<?php foreach ($categories as $category) : ?>
		<tr>
			<td><?php echo $category['id']; ?></td>
			<td><?php echo $category['title']; ?></td>
			<td><?php echo $category['articles']; ?></td>
			<td>
				<a class="btn btn-primary" href="<?php echo $url->module('content', 'category', 'edit', $category['id']); ?>">Редактировать</a>
				<a class="btn btn-danger" href="<?php echo $url->module('content', 'category', 'delete', $category['id']); ?>">Удалить</a>
			</td>
		</tr>
	<?php endforeach; ?>
</table>