<div class="module">
<div class="head">
	<?php echo $lang->create_category; ?>
</div> 

	<div class="module-body">

<?php if (isset($answer['status']) && !$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>		

<form action="" method="POST" data-najax-form>
	<input type="hidden" name="process" value="1">

	<div class="form-group<?php if (isset($answer['errors']['title'])) echo ' has-error'; ?>">
		<label for="title" class="control-label"><?php echo $lang->title; ?></label>
		<input type="text" class="form-control" name="title" value="<?php if (isset($category)) echo $category->title; ?>" id="title" placeholder="<?php echo $lang->enter_title; ?>" data-rule-required="true">
		<?php if (isset($answer['errors']['title'])) echo '<span class="help-block">'.$answer['errors']['title'].'</span>'; ?>
	</div>

	<div class="form-group<?php if (isset($answer['errors']['position'])) echo ' has-error'; ?>">
		<label for="position" class="control-label"><?php echo $lang->position; ?></label>
		<input type="text" class="form-control" name="position" value="<?php if (isset($category)) echo $category->position; ?>" id="position" placeholder="<?php echo $lang->enter_position; ?>" data-rule-required="true">
		<?php if (isset($answer['errors']['position'])) echo '<span class="help-block">'.$answer['errors']['position'].'</span>'; ?>
	</div>
	
	<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>
</form>

	</div>
</div>