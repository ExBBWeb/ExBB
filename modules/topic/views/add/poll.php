<div class="form-group<?php if (isset($answer['errors']['poll_title'])) echo ' has-error'; ?>">
	<label for="poll_title" class="control-label"><?php echo $lang->poll_title; ?> <i class="fa fa-asterisk"></i></label>
	<input type="text" class="form-control" name="poll_title" value="" id="poll_title" placeholder="<?php echo $lang->enter_poll_title; ?>" data-rule-required="true">
	<?php if (isset($answer['errors']['poll_title'])) echo '<span class="help-block">'.$answer['errors']['poll_title'].'</span>'; ?>
</div>

<div class="form-group<?php if (isset($answer['errors']['poll_variants'])) echo ' has-error'; ?>">
	<label for="poll_variants" class="control-label"><?php echo $lang->poll_variants; ?> <i class="fa fa-asterisk"></i></label>
	<textarea class="form-control" name="poll_variants" id="poll_variants" placeholder="<?php echo $lang->enter_poll_variants; ?>" data-rule-required="true"></textarea>
	<?php if (isset($answer['errors']['poll_variants'])) echo '<span class="help-block">'.$answer['errors']['poll_variants'].'</span>'; ?>
</div>