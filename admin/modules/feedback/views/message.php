<h1>Сообщение</h1>

<table class="table table-bordered">
	<thead>
		<tr>
			<th style="width: 20%;">Параметр</th>
			<th style="width: 80%;">Значение</th>
		</tr>
	</thead>
	
	<tr>
		<td><strong>ID</strong></td>
		<td><?php echo $object->id; ?></td>
	</tr>
	
	<tr>
		<td><strong>ID пользователя</strong></td>
		<td><?php echo $object->user_id; ?></td>
	</tr>
	
	<tr>
		<td><strong>Имя пользователя</strong></td>
		<td><?php echo $object->user_name; ?></td>
	</tr>
	
	<tr>
		<td><strong>E-mail пользователя</strong></td>
		<td><?php echo $object->user_mail; ?></td>
	</tr>
	
	<tr>
		<td><strong>Название сообщения</strong></td>
		<td><?php echo $object->title; ?></td>
	</tr>
	
	<tr>
		<td><strong>Текст сообщения</strong></td>
		<td><?php echo $object->text; ?></td>
	</tr>
	
	<tr>
		<td><strong>Дата сообщения</strong></td>
		<td><?php echo $object->date_added; ?></td>
	</tr>
</table>