		<?php if ($edit_tab ==  'signature' && isset($answer['status']) && !$answer['status']) : ?>
			<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
		<?php endif; ?>		

	<form action="<?php echo $url->module('user', 'profile', 'editsignature'); ?>" method="POST" data-ajax-form>
				<input type="hidden" name="process" value="1">
				<div class="form-group<?php if (isset($answer['errors']['signature'])) echo ' has-error'; ?>">
					<label for="signature" class="control-label"><?php echo $lang->your_signature; ?> <i class="fa fa-asterisk"></i></label>
					<textarea class="form-control" name="signature" id="signature" placeholder="<?php echo $lang->enter_your_signature; ?>"><?php echo $user->signature; ?></textarea>
					<?php if (isset($answer['errors']['signature'])) echo '<span class="help-block">'.$answer['errors']['signature'].'</span>'; ?>
				</div>

				<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>

	</form>