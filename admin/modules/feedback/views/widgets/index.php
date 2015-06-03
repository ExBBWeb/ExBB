<div class="text-right">
	<a class="btn btn-success" href="<?php echo $url->module('extensions', 'widgets', 'add'); ?>">Создать</a>
</div>

<div class="hor-space"></div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th>#</th>
			<th>Название</th>
			<th>Позиция</th>
			<th>Виджет</th>
			<th>Статус</th>
			<th>Порядок</th>
			<th>Опции</th>
		</tr>
	</thead>
	
	<?php foreach ($widgets as $widget) : ?>
		<tr>
			<td><?php echo $widget['id']; ?></td>
			<td><?php echo $widget['title']; ?></td>
			<td><?php echo $widget['position']; ?></td>
			<td><?php echo $widget['widget']; ?></td>
			<td><?php echo $widget['status']; ?></td>
			<td><?php echo $widget['priority']; ?></td>
			<td>
				<a class="btn btn-primary" href="<?php echo $url->module('extensions', 'widgets', 'edit', $widget['id']); ?>">Редактировать</a>
			
				<?php if ($widget['status'] == 1) : ?>
					<a class="btn btn-warning" href="<?php echo $url->module('extensions', 'widgets', 'changestate', $widget['id']); ?>">Выключить</a>
				<?php elseif ($widget['status'] == 0) : ?>
					<a class="btn btn-success" href="<?php echo $url->module('extensions', 'widgets', 'changestate', $widget['id']); ?>">Включить</a>
				<?php endif; ?>

				<a class="btn btn-danger" href="<?php echo $url->module('extensions', 'widgets', 'delete', $widget['id']); ?>">Удалить</a>
			</td>
		</tr>
	<?php endforeach; ?>
</table>