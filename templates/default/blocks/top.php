<?php
use Core\Library\User\Users;
$module = $app->router->getVar('module');
$controller = $app->router->getVar('controller');
$action = $app->router->getVar('action');
?>
<div class="container">
	
	<div id="header" class="row">	
		<div class="col-md-12">
			<div class="logo"><img src="<?php $template->url('images/logo.png'); ?>"></div>
		</div>
		<!--div class="col-md-6">
			<h2><?php echo $app->config->getOption('site_title'); ?></h2>
			<p class="description"><?php echo $app->config->getOption('site_description'); ?></p>
		</div-->
	</div>
	
	<ul class="top-menu clearfix">
		<li<?php if ($module == 'index') echo ' class="current"'; ?>><a href="<?php echo $url->module('index'); ?>"><i class="fa fa-home"></i> Главная</a></li>
		<li<?php if ($module == 'search') echo ' class="current"'; ?>><a href="<?php echo $url->module('search'); ?>"><i class="fa fa-search"></i> Поиск</a></li>
		<li<?php if ($module == 'users') echo ' class="current"'; ?>><a href="<?php echo $url->module('users'); ?>"><i class="fa fa-users"></i> Пользователи</a></li> 
		<l<?php if ($module == 'help') echo ' class="current"'; ?>i><a href="<?php echo $url->module('help'); ?>"><i class="fa fa-support"></i> Помощь</a></li>
		<li<?php if ($module == 'chat') echo ' class="current"'; ?>><a href="<?php echo $url->module('chat'); ?>"><i class="fa fa-wechat"></i> Чат</a></li>

		<?php if (!Users::isLogged()) : ?>
		<li class="<?php if ($module == 'user' && $action == 'register') echo 'current '; ?>right"><a href="<?php echo $url->module('user', 'index', 'register'); ?>"><i class="fa fa-sign-in"></i> Регистрация</a></li> 
		<li class="<?php if ($module == 'user' && $action == 'login') echo 'current '; ?>right"><a href="<?php echo $url->module('user', 'index', 'login'); ?>"><i class="fa fa-user"></i> Вход</a></li>
		<?php else : ?>
		<li class="<?php if ($module == 'user' && $action == 'logout') echo 'current '; ?>right"><a href="<?php echo $url->module('user', 'index', 'logout'); ?>"><i class="fa fa-log-out"></i> Выход</a></li>
		<li class="<?php if ($module == 'user' && $controller == 'profile') echo 'current '; ?>right"><a href="<?php echo $url->module('user', 'profile'); ?>"><i class="fa fa-user"></i> Профиль</a></li> 
		<?php endif; ?>
	</ul>
	
	<div id="content" class="container-fluid">
	
	<?php $template->breadcrumbs(); ?>
