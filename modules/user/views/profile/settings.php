<div class="panel">

<div class="header">
	<h1><?php echo $lang->profile_settings_title; ?></h1>
</div>

<div class="body">

<?php if (isset($answer['status']) && !$answer['status']) : ?>
	<div class="alert alert-danger"><?php echo $answer['message']; ?></div>
<?php endif; ?>		

<form action="<?php echo $url->module('user', 'profile', 'settings'); ?>" method="POST" data-najax-form>
	<input type="hidden" name="process" value="1">

	<?php if ($allow_select_timezone) : ?>
	<div class="form-group<?php if (isset($answer['errors']['timezone'])) echo ' has-error'; ?>">
		<label for="timezone" class="control-label"><?php echo $lang->select_timezone; ?></label>
		<select class="form-control" name="timezone" value="" id="timezone" data-rule-required="true">
			<?php echo $time_zone_helper->getTimeZoneSelect($user->timezone); ?>
		</select>
		<?php if (isset($answer['errors']['timezone'])) echo '<span class="help-block">'.$answer['errors']['timezone'].'</span>'; ?>
	</div>
	<?php endif; ?>
			
	<?php if ($allow_select_template) : ?>
	<div class="form-group<?php if (isset($answer['errors']['template'])) echo ' has-error'; ?>">
		<label for="template" class="control-label"><?php echo $lang->select_template; ?></label>
		<select class="form-control" name="template" value="" id="template" data-rule-required="true">
			<?php foreach ($templates as $template) : ?>
			<option value="<?php echo $template['id']; ?>"<?php if ($user->template == $template['id']) echo ' selected'; ?>><?php echo $template['title']; ?></option>
			<?php endforeach; ?>
		</select>
		<?php if (isset($answer['errors']['template'])) echo '<span class="help-block">'.$answer['errors']['template'].'</span>'; ?>
	</div>
	<?php endif; ?>
			
	<?php if ($allow_select_language) : ?>
	<div class="form-group<?php if (isset($answer['errors']['language'])) echo ' has-error'; ?>">
		<label for="language" class="control-label"><?php echo $lang->select_language; ?></label>
		<select class="form-control" name="language" value="" id="language" data-rule-required="true">
			<?php foreach ($languages as $language) : ?>
			<option value="<?php echo $language['id']; ?>"<?php if ($user->language == $language['id']) echo ' selected'; ?>><?php echo $language['title']; ?></option>
			<?php endforeach; ?>
		</select>
		<?php if (isset($answer['errors']['language'])) echo '<span class="help-block">'.$answer['errors']['language'].'</span>'; ?>
	</div>
	<?php endif; ?>
			
	<button type="submit" class="btn btn-primary"><?php echo $lang->save; ?></button>
</form>

</div>
</div>