<div class="panel">

<div class="header">
	<h1><?php echo $lang->auth_title; ?></h1>
</div>

<div class="body">

<?php if (!empty($answer['errors'])) : ?>
	<?php foreach ($answer['errors'] as $error) : ?>
	<div class="alert alert-danger"><?php echo $error; ?></div>
	<?php endforeach; ?>
<?php endif; ?>

<form action="" method="POST" data-ajax-form>
	<input type="hidden" name="process" value="1">
	
	<div class="form-group">
		<label for="user_login" class="control-label"><?php echo $lang->your_login; ?></label>
		<input type="text" class="form-control" name="login" value="" id="user_login" placeholder="<?php echo $lang->enter_your_login; ?>" required>
	</div>
	
	<div class="form-group">
		<label for="user_password" class="control-label"><?php echo $lang->your_pass; ?></label>
		<input type="password" class="form-control" name="password" value="" id="user_password" placeholder="<?php echo $lang->enter_your_pass; ?>" required>
	</div>
	
	<button type="submit" class="btn btn-primary"><?php echo $lang->enter_auth; ?></button>
	
	<br><br>
	<div>
		<a href="<?php echo $url->module('user', 'index', 'register'); ?>" class="btn btn-info"><?php echo $lang->registration; ?></a>
		<a href="<?php echo $url->module('user', 'index', 'forgot'); ?>" class="btn btn-danger"><?php echo $lang->forgot_pass; ?></a>
	</div>
	<br>
	<script src="//ulogin.ru/js/ulogin.js"></script>
	<a href="#" id="uLogin" data-ulogin="display=window;fields=first_name,last_name;redirect_uri=<?php echo $url->module('users', 'index', 'RegisterULogin'); ?>"><img src="https://ulogin.ru/img/button.png" width="187" height="30" alt="МультиВход"/></a>

</form>
</div>

</div>