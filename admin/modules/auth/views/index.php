<div class="login_form">
	<form id="login" method="POST" action="">
		<h1><?php echo $lang->auth_form_title; ?></h1>

		<div class="input-block">
			<input type="text" name="login" value="" placeholder="<?php echo $lang->auth_admin_login; ?>">
		</div>
		
		<div class="input-block">
			<input type="password" name="password" value="" placeholder="<?php echo $lang->auth_admin_password; ?>">
		</div>
		
		<div class="button-block">
				<button type="submit"><?php echo $lang->auth_admin_enter_button; ?></button>
		</div>
	</form>
</div>