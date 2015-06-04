<div class="panel">

<div class="header">
	<h1><?php echo $lang->registration_title; ?></h1>
</div>

<div class="body">

<p class="alert alert-info no-close"><i class="fa fa-asterisk"></i> - <?php echo $lang->field_is_required; ?></p>

<?php if (!$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>

<form action="" method="POST" data-ajax-form>
	<input type="hidden" name="process" value="1">

	<div class="form-group<?php if (isset($answer['errors']['login'])) echo ' has-error'; ?>">
		<label for="user_login" class="control-label"><?php echo $lang->your_login; ?> <i class="fa fa-asterisk"></i></label>
		<input type="text" class="form-control" name="login" value="" id="user_login" placeholder="<?php echo $lang->enter_your_login; ?>" data-rule-minlength="4" data-rule-required="true">
		<?php if (isset($answer['errors']['login'])) echo '<span class="help-block">'.$answer['errors']['login'].'</span>'; ?>
	</div>
	
	<div class="form-group<?php if (isset($answer['errors']['password'])) echo ' has-error'; ?>">
		<label for="user_password" class="control-label"><?php echo $lang->your_pass; ?> <i class="fa fa-asterisk"></i></label>
		<input type="password" class="form-control" name="password" value="" id="user_password" placeholder="<?php echo $lang->enter_your_pass; ?>" data-rule-minlength="6" data-rule-required="true">
		<?php if (isset($answer['errors']['password'])) echo '<span class="help-block">'.$answer['errors']['password'].'</span>'; ?>
	</div>

	<div class="form-group<?php if (isset($answer['errors']['email'])) echo ' has-error'; ?>">
		<label for="email" class="control-label">Ваш E-mail <i class="fa fa-asterisk"></i></label>
		<input type="text" class="form-control" name="email" value="" id="email" placeholder="Введите ваш E-mail" data-rule-email="true" data-rule-required="true">
		<?php if (isset($answer['errors']['email'])) echo '<span class="help-block">'.$answer['errors']['email'].'</span>'; ?>
	</div>

	<?php foreach ($fields as $field) : ?>
	<div class="form-group<?php if (isset($answer['errors'][$field['name']])) echo ' has-error'; ?>">
		<label for="<?php echo $field['name']; ?>" class="control-label"><?php echo $field['options']['title'][$language]; ?><?php if ($field['options']['required']) echo ' <i class="fa fa-asterisk"></i>'; ?></label>
		<input type="text" class="form-control" name="<?php echo $field['name']; ?>" value="" id="<?php echo $field['name']; ?>" placeholder="<?php echo $field['title']; ?>"<?php if ($field['options']['required']) echo ' data-rule-required="true"'; ?>>
		<?php if (isset($answer['errors'][$field['name']])) echo '<span class="help-block">'.$answer['errors'][$field['name']].'</span>'; ?>
	</div>
	<?php endforeach; ?>
	
	<button type="submit" class="btn btn-primary"><?php echo $lang->registration; ?></button>
	
	<br><br>
	<div>
		<a href="<?php echo $url->module('user', 'index', 'login'); ?>" class="btn btn-success"><?php echo $lang->authentication; ?></a>
		<a href="<?php echo $url->module('user', 'index', 'forgot'); ?>" class="btn btn-danger"><?php echo $lang->forgot_pass; ?></a>
	</div>
	
</form>
</div>

</div>