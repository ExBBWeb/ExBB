		<?php if ($edit_tab ==  'user_data' && isset($answer['status']) && !$answer['status']) : ?>
			<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
		<?php endif; ?>		

			<form action="<?php echo $url->module('user', 'profile', 'editdata'); ?>" method="POST" data-ajax-form>
				<input type="hidden" name="process" value="1">
				<div class="form-group<?php if (isset($answer['errors']['email'])) echo ' has-error'; ?>">
					<label for="email" class="control-label"><?php echo $lang->your_email; ?> <i class="fa fa-asterisk"></i></label>
					<input type="text" class="form-control" name="email" value="<?php echo $user->email; ?>" id="email" placeholder="<?php echo $lang->enter_your_email; ?>" data-rule-email="true" data-rule-required="true">
					<?php if (isset($answer['errors']['email'])) echo '<span class="help-block">'.$answer['errors']['email'].'</span>'; ?>
				</div>

				<?php foreach ($fields as $field) : ?>
				<div class="form-group<?php if (isset($answer['errors'][$field['name']])) echo ' has-error'; ?>">
					<label for="<?php echo $field['name']; ?>" class="control-label"><?php echo $field['options']['title'][$language]; ?><?php if ($field['options']['required']) echo ' <i class="fa fa-asterisk"></i>'; ?></label>
					<input type="text" class="form-control" name="<?php echo $field['name']; ?>" value="<?php echo $user->getFieldData($field['id']); ?>" id="<?php echo $field['name']; ?>" placeholder="<?php echo $field['title']; ?>"<?php if ($field['options']['required']) echo ' data-rule-required="true"'; ?>>
					<?php if (isset($answer['errors'][$field['name']])) echo '<span class="help-block">'.$answer['errors'][$field['name']].'</span>'; ?>
				</div>
				<?php endforeach; ?>
				
				<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>

			</form>