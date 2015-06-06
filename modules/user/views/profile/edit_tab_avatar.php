<?php if ($edit_tab ==  'avatar' && isset($answer['status']) && !$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>		

<form action="<?php echo $url->module('user', 'profile', 'editavatar'); ?>" method="POST" enctype="multipart/form-data" data-ajax-form>
	<input type="hidden" name="process" value="1">

	<div class="form-group<?php if (isset($answer['errors']['default_avatar'])) echo ' has-error'; ?>">
		<label for="default_avatar" class="control-label"><?php echo $lang->select_default_avatar; ?></label>
		<select class="form-control" name="default_avatar" value="" id="default_avatar" data-rule-required="true">
			<?php foreach ($avatars as $avatar) : ?>
			<option name="<?php echo $avatar; ?>"><?php echo $avatar; ?></option>
			<?php endforeach; ?>
		</select>
		<?php if (isset($answer['errors']['default_avatar'])) echo '<span class="help-block">'.$answer['errors']['default_avatar'].'</span>'; ?>
	</div>
				
	<div class="form-group<?php if (isset($answer['errors']['avatar'])) echo ' has-error'; ?>">
		<label for="avatar" class="control-label"><?php echo $lang->upload_avatar; ?></label>
		<input type="file" id="avatar" name="avatar">
		<?php if (isset($answer['errors']['avatar'])) echo '<span class="help-block">'.$answer['errors']['avatar'].'</span>'; ?>
	</div>
				
	<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>
</form>
