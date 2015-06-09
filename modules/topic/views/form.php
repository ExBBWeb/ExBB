<div class="panel">

	<div class="header">
		<h1><?php echo $lang->add_topic_title; ?></h1>
	</div>

	<div class="body">
<?php if (isset($answer['status']) && !$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>

<form action="" method="POST" data-ajax-form>
	<input type="hidden" name="process" value="1">

	<div class="form-group<?php if (isset($answer['errors']['title'])) echo ' has-error'; ?>">
		<label for="title" class="control-label"><?php echo $lang->title; ?> <i class="fa fa-asterisk"></i></label>
		<input type="text" class="form-control" name="title" value="" id="title" placeholder="<?php echo $lang->enter_title; ?>" data-rule-required="true">
		<?php if (isset($answer['errors']['title'])) echo '<span class="help-block">'.$answer['errors']['title'].'</span>'; ?>
	</div>
	
	<div class="form-group<?php if (isset($answer['errors']['description'])) echo ' has-error'; ?>">
		<label for="description" class="control-label"><?php echo $lang->description; ?></label>
		<input type="text" class="form-control" name="description" value="" id="description" placeholder="<?php echo $lang->enter_description; ?>">
		<?php if (isset($answer['errors']['description'])) echo '<span class="help-block">'.$answer['errors']['description'].'</span>'; ?>
	</div>

	<div class="form-group<?php if (isset($answer['errors']['keywords'])) echo ' has-error'; ?>">
		<label for="keywords" class="control-label"><?php echo $lang->keywords; ?></label>
		<input type="text" class="form-control" name="keywords" value="" id="keywords" placeholder="<?php echo $lang->enter_keywords; ?>">
		<?php if (isset($answer['errors']['keywords'])) echo '<span class="help-block">'.$answer['errors']['keywords'].'</span>'; ?>
	</div>
	
	<div class="form-group<?php if (isset($answer['errors']['post'])) echo ' has-error'; ?>">
		<label for="post" class="control-label"><?php echo $lang->post; ?></label>
		<?php bbcode_editor(array('name'=>'post', 'id'=>'post', 'placeholder'=>$lang->enter_post)); ?>
		<?php if (isset($answer['errors']['post'])) echo '<span class="help-block">'.$answer['errors']['post'].'</span>'; ?>
	</div>
	
	<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>
</form>
	</div>
</div>