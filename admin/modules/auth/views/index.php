<?php echo l('hello'); ?>
<div class="login_form">
	<form id="login" method="POST" action="">
		<h1><?php _l('auth_form_title'); ?></h1>

		<div class="input-block">
			<input type="text" name="login" value="" placeholder="<?php _l('auth_admin_login'); ?>">
		</div>
		
		<div class="input-block">
			<input type="password" name="password" value="" placeholder="<?php _l('auth_admin_password'); ?>">
		</div>
		
		<div class="button-block">
				<button type="submit"><?php _l('auth_admin_enter_button'); ?></button>
		</div>
	</form>
</div>