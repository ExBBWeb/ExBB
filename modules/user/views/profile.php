<h1 class="page-header">Кабинет</h1>

<?php if (!stristr($user->login, 'ulogin')) : ?>
<div class="row">
	<div class="col-md-2"><strong>Логин:</strong></div>
	<div class="col-md-10"><?php echo $user->login; ?></div>
</div>
<?php endif; ?>

<div class="row">
	<div class="col-md-2"><strong>Имя:</strong></div>
	<div class="col-md-10"><?php echo $user->name; ?></div>
</div>

<div class="row">
	<div class="col-md-2"><strong>Фамилия:</strong></div>
	<div class="col-md-10"><?php echo $user->sirname; ?></div>
</div>

<!--div class="row">
	<div class="col-md-2"><strong>E-mail:</strong></div>
	<div class="col-md-10"><?php echo $user->mail; ?></div>
</div-->

<div><a href="<?php echo $url->module('users', 'index', 'logout'); ?>" class="btn btn-warning">Покинуть сайт</a></div>