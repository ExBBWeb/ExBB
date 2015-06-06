<?php if ($edit_tab ==  'secret' && isset($answer['status']) && !$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>		

<form action="<?php echo $url->module('user', 'profile', 'changepassword'); ?>" method="POST" data-ajax-form>
	<input type="hidden" name="process" value="1">
	<div class="form-group<?php if (isset($answer['errors']['password'])) echo ' has-error'; ?>">
		<label for="password" class="control-label"><?php echo $lang->your_pass; ?> <i class="fa fa-asterisk"></i></label>
		<input type="password" class="form-control" name="password" value="" id="password" placeholder="<?php echo $lang->enter_your_pass; ?>" data-rule-minlength="6" data-rule-required="true">
		<?php if (isset($answer['errors']['password'])) echo '<span class="help-block">'.$answer['errors']['password'].'</span>'; ?>
	</div>

	<div class="form-group<?php if (isset($answer['errors']['confirm'])) echo ' has-error'; ?>">
		<label for="confirm" class="control-label"><?php echo $lang->your_confirm_pass; ?> <i class="fa fa-asterisk"></i></label>
		<input type="password" class="form-control" name="confirm" value="" id="confirm" placeholder="<?php echo $lang->enter_your_confirm_pass; ?>" data-rule-minlength="6" data-rule-equalto="#password" data-rule-required="true">
		<?php if (isset($answer['errors']['confirm'])) echo '<span class="help-block">'.$answer['errors']['confirm'].'</span>'; ?>
	</div>
				
	<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>
</form>
