<form action="" method="POST">
	<input type="hidden" name="process" value="1">

	<div class="form-group">
		<label for="title">Название</label>
		<input type="text" class="form-control" id="title" value="<?php echo $object->title; ?>" name="title" placeholder="Введите название">
	</div>
	
	<div class="form-group">
		<label for="widget">Виджет</label>
		<select name="widget" id="widget" class="form-control">
			<?php foreach ($widgets as $widget) : ?>
				<option value="<?php echo $widget; ?>"<?php if ($object->widget == $widget) echo ' selected';?>><?php echo $widget; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="position">Позиция</label>
		<input type="text" class="form-control" id="position" value="<?php echo $object->position; ?>" name="position" placeholder="Позиция">
	</div>
	
	<div class="form-group">
		<label for="priority">Порядок</label>
		<input type="text" class="form-control" id="priority" value="<?php echo $object->priority; ?>" name="priority" placeholder="Приоритет в сортировке">
	</div>
	
	<div class="form-group">
		<label for="status">Состояние</label>
		<select name="status" id="status" class="form-control">
			<option value="1"<?php if ($object->status == 1) echo ' selected'; ?>>Включен</option>
			<option value="0"<?php if ($object->status == 0) echo ' selected'; ?>>Выключен</option>
		</select>
	</div>

	<button type="submit" class="btn btn-success">Сохранить</button>
</form>