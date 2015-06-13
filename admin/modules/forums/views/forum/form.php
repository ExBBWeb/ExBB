<div class="module">
<div class="head">
	<?php echo $lang->create_forum; ?>
</div> 

	<div class="module-body">

<?php if (isset($answer['status']) && !$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>		

<form action="" method="POST" data-najax-form>
	<input type="hidden" name="process" value="1">

	<div class="form-group<?php if (isset($answer['errors']['title'])) echo ' has-error'; ?>">
		<label for="title" class="control-label"><?php echo $lang->title; ?></label>
		<input type="text" class="form-control" name="title" value="<?php if (isset($forum)) echo $forum->title; ?>" id="title" placeholder="<?php echo $lang->enter_title; ?>" data-rule-required="true">
		<?php if (isset($answer['errors']['title'])) echo '<span class="help-block">'.$answer['errors']['title'].'</span>'; ?>
	</div>

	<div class="form-group<?php if (isset($answer['errors']['category_id'])) echo ' has-error'; ?>">
		<label for="category_id" class="control-label"><?php echo $lang->category_forum; ?></label>
		<select class="form-control" name="category_id" value="<?php if (isset($forum)) echo $forum->category_id; ?>" id="category_id"<?php if (isset($forum)) echo ' disabled'; ?>>
		<?php
		$category = isset($forum) ? $forum->category_id : false;
		if (isset($category_id)) $category = $category_id;
		echo $tree_helper->getListForSelectCategory($category);
		?>
		</select>
		<?php if (isset($answer['errors']['category_id'])) echo '<span class="help-block">'.$answer['errors']['category_id'].'</span>'; ?>
	</div>
	
	<div class="form-group<?php if (isset($answer['errors']['parent_id'])) echo ' has-error'; ?>">
		<label for="parent_id" class="control-label"><?php echo $lang->parent_forum; ?></label>
		<select class="form-control" name="parent_id" value="<?php if (isset($forum)) echo $forum->parent_id; ?>" id="parent_id">
			<option value="0"><?php echo $lang->no_parent; ?></option>
			<?php
			$parent = isset($forum) ? $forum->parent_id : false;
			if (isset($parent_id)) $parent = $parent_id;
			$current = (isset($forum)) ? $forum->id : false;
			echo $tree_helper->getListForSelectParent($parent, $current); 
			?>
		</select>
		<?php if (isset($answer['errors']['parent_id'])) echo '<span class="help-block">'.$answer['errors']['parent_id'].'</span>'; ?>
	</div>
	
	<div class="form-group<?php if (isset($answer['errors']['position'])) echo ' has-error'; ?>">
		<label for="position" class="control-label"><?php echo $lang->position; ?></label>
		<input type="text" class="form-control" name="position" value="<?php if (isset($forum)) echo $forum->position; ?>" id="position" placeholder="<?php echo $lang->enter_position; ?>" data-rule-required="true">
		<?php if (isset($answer['errors']['position'])) echo '<span class="help-block">'.$answer['errors']['position'].'</span>'; ?>
	</div>
	
	<div class="form-group<?php if (isset($answer['errors']['status_icon'])) echo ' has-error'; ?>">
		<label for="status_icon" class="control-label"><?php echo $lang->status_icon; ?></label>
		<input type="text" class="form-control" name="status_icon" value="<?php if (isset($forum)) echo $forum->status_icon; ?>" id="status_icon" placeholder="<?php echo $lang->enter_status_icon; ?>" data-rule-required="true">
		<?php if (isset($answer['errors']['status_icon'])) echo '<span class="help-block">'.$answer['errors']['status_icon'].'</span>'; ?>
	</div>
	
	<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>
</form>

	</div>
</div>