<?php
use Core\Library\User\Users;
?>
<div class="container-fluid">
	
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
		<li class="current"><a href="<?php echo $url->module('index'); ?>"><i class="fa fa-home"></i> Главная</a></li>
		<li><a href="<?php echo $url->module('search'); ?>"><i class="fa fa-search"></i> Поиск</a></li>
		<li><a href="<?php echo $url->module('users'); ?>"><i class="fa fa-users"></i> Пользователи</a></li> 
		<li><a href="<?php echo $url->module('help'); ?>"><i class="fa fa-support"></i> Помощь</a></li>
		<li><a href="<?php echo $url->module('help'); ?>"><i class="fa fa-wechat"></i> Чат</a></li>

		<li class="right"><a href="<?php echo $url->module('user', 'index', 'register'); ?>"><i class="fa fa-sign-in"></i> Регистрация</a></li> 
		<li class="right"><a href="<?php echo $url->module('user', 'index', 'login'); ?>"><i class="fa fa-user"></i> Вход</a></li>
	</ul>
	
	<?php $template->breadcrumbs(); ?>
