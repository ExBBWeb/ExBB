<div class="messages"></div>

<div id="fileuploader">Upload</div>
<div class="hor-space"></div>

<script type="text/javascript">
<?php
$select = '<select name="category_id">';
foreach ($albums as $album) {
	$select .= '<option value="'.$album['id'].'">'.$album['title'].'</option>';
}
$select .= '</select>';
?>
var cats_select = '<?php echo $select; ?>';

$(document).ready(function() {
	$("#fileuploader").uploadFile({
		url:"<?php echo $url->module('gallery', 'image', 'upload'); ?>",
		multiple:false,
		fileName:"image",
		returnType : 'json',
		onSuccess:function(files,data,xhr,pd) {
			if (!data.status) {
				alert(data.message);
				return 0;
			}
			
			$('.images-list tbody').prepend('\
			<tr class="image_save_form">\
				<td>#<input type="hidden" name="image" value="'+data.image_path+'"></td>\
				<td><input type="text" name="title" value=""></td>\
				<td><textarea name="description"></textarea><br>\
				Meta-описание:<br><input type="text" name="meta_description" value="">\
				Meta-ключевые слова:<br><input type="text" name="meta_keywords" value="">\
				\</td>\
				<td>'+cats_select+'</td>\
				<td><img width="110" src="<?php echo BASE_URL.'/media/gallery/'; ?>'+data.image_path+'"></td>\
				<td>0</td>\
				<td>0</td>\
				<td>\
					<a class="btn btn-primary btn-image-save" href="<?php echo $url->module('gallery', 'image', 'saveupload'); ?>">Сохранить</a>\
					<a class="btn btn-danger btn-image-delete" href="<?php echo $url->module('gallery', 'image', 'deleteupload'); ?>">Удалить</a>\
				</td>\
			</tr>\
			');
		},
		onError: function(files,status,errMsg,pd) {
			alert(errMsg);
		},
	});
	
	$('body').on('click', '.btn-image-save', function(e) {
		e.preventDefault();
		tr = $(this).parent().parent();
		
		$.ajax({
			method: "POST",
			url: $(this).attr('href'),
			data: tr.find('input,textarea,select').serialize(),
			dataType: 'json',
			success: function(data) {
				if (data.status)
					$('.messages').append('<div class="alert alert-success">'+data.message+'</div>');
				else
					$('.messages').append('<div class="alert alert-error">'+data.message+'</div>');
			}
		});
	});
	
	$('body').on('click', '.btn-image-delete', function(e) {
		e.preventDefault();
		tr = $(this).parent().parent();
		
		$.ajax({
			method: "POST",
			url: $(this).attr('href'),
			data: tr.find('input,textarea,select').serialize(),
			dataType: 'json',
			success: function(data) {
				if (data.status)
					$('.messages').append('<div class="alert alert-warning">'+data.message+'</div>');
				else
					$('.messages').append('<div class="alert alert-error">'+data.message+'</div>');
				
				tr.remove();
			}
		});
	});
});
</script>

<div class="text-right">
	<a class="btn btn-success" href="<?php echo $url->module('gallery', 'image', 'add'); ?>">Создать</a>
</div>

<div class="hor-space"></div>

<table class="table table-bordered images-list">
	<thead>
		<tr>
			<th>#</th>
			<th>Название</th>
			<th>Описание</th>
			<th>Альбом</th>
			<th>Изображение</th>
			<th>Просмотров</th>
			<th>Комментариев</th>
			<th>Опции</th>
		</tr>
	</thead>
	
	<tbody>
	<?php foreach ($images as $image) : ?>
		<tr>
			<td><?php echo $image['id']; ?></td>
			<td><?php echo $image['title']; ?></td>
			<td><?php echo $image['description']; ?></td>
			<td><?php echo $albums[$image['category_id']]['title']; ?></td>
			<td><img width="110" src="<?php echo BASE_URL.'/media/gallery/'.$image['image']; ?>"></td>
			<td><?php echo $image['views']; ?></td>
			<td><?php echo $image['comments']; ?></td>
			<td>
				<a class="btn btn-primary" href="<?php echo $url->module('gallery', 'image', 'edit', $image['id']); ?>">Редактировать</a>
				<a class="btn btn-danger" href="<?php echo $url->module('gallery', 'image', 'delete', $image['id']); ?>">Удалить</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>