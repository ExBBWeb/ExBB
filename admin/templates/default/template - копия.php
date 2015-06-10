<?php
use Core\Library\Application\Application;
$app = Application::getInstance();

$template = $app->template; 
$url = $app->url;

if ($template->page_title) $template->title .= ' - '.$template->page_title;

$template->loadLanguage('template');
$lang = $template->getLanguage();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $template->title; ?></title>
		
		<script type="text/javascript" src="<?php $template->url('js/jquery-1.11.2.min.js'); ?>"></script>

		<link rel="stylesheet" href="<?php $template->url('css/reset.css'); ?>">
		<link rel="stylesheet" href="<?php $template->url('css/grid.css'); ?>">
		<link rel="stylesheet" href="<?php $template->url('css/styles.css'); ?>">
		<link rel="stylesheet" href="<?php $template->url('css/font-awesome.min.css'); ?>">
		<link rel="stylesheet" href="<?php $template->url('css/theme-blue.css'); ?>">

		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		
		<?php $template->head(); ?>
	</head>
	
	<body>

    	<!-- Header -->
        <div id="header">
            <!-- Header. Status part -->
            <div id="header-status">
                <div class="container_12">
                    <div class="grid_8">
					&nbsp;
                    </div>
                    <div class="grid_4">
                        <a href="" id="logout">
                        Logout
                        </a>
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div> <!-- End #header-status -->
            
            <!-- Header. Main part -->
            <div id="header-main">
                <div class="container_12">
                    <div class="grid_12">
                        <div id="logo">
                            <ul id="nav">
                                <li id="current"><a href="">Dashboard</a></li>
                                <li><a href="">Articles</a></li>
                                <li><a href="">Files</a></li>
                                <li><a href="">Profile</a></li>
                                <li><a href="">Settings</a></li>
                            </ul>
                        </div><!-- End. #Logo -->
                    </div><!-- End. .grid_12-->
                    <div style="clear: both;"></div>
                </div><!-- End. .container_12 -->
            </div> <!-- End #header-main -->
            <div style="clear: both;"></div>
            <!-- Sub navigation -->
            <div id="subnav">
                <div class="container_12">
                    <div class="grid_12">
                        <ul>
                            <li><a href="#">link 1</a></li>
                            <li><a href="#">link 2</a></li>
                            <li><a href="#">link 3</a></li>
                            <li><a href="#">link 4</a></li>
                            <li><a href="#">link 5</a></li>
                        </ul>
                        
                    </div><!-- End. .grid_12-->
                </div><!-- End. .container_12 -->
                <div style="clear: both;"></div>
            </div> <!-- End #subnav -->
        </div> <!-- End #header -->
	
    <div class="container">
		<nav class="navbar navbar-default">
		  <div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
			  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#admin-top-navbar">
				<span class="sr-only">Навигация</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>
			  <a class="navbar-brand" href="#">ExBB</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="admin-top-navbar">
			  <ul class="nav navbar-nav">
				<li><a href="<?php echo $url->module('index'); ?>">Главная</a></li>
				<li><a href="<?php echo $url->module('index'); ?>">Пользователи</a></li>
				<li class="dropdown">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
				  Контент
					<?php if ($content_badge != 0) : ?>
					<span class="badge"><?php echo $content_badge; ?></span>
					<?php endif; ?>
				  <span class="caret"></span></a>
				  <ul class="dropdown-menu" role="menu">
					<li><a href="<?php echo $url->module('content', 'category'); ?>">Категории</a></li>
					<li><a href="<?php echo $url->module('content', 'article'); ?>">Статьи</a></li>
					<li class="divider"></li>
					<li><a href="<?php echo $url->module('content', 'news'); ?>">Новости сайта</a></li>
					<li><a href="<?php echo $url->module('feedback'); ?>">Письма пользователей
					<?php if ($messages != 0) : ?>
					<span class="badge"><?php echo $messages; ?></span>
					<?php endif; ?>
					</a></li>
				  </ul>
				</li>
				<li class="dropdown">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Дополнения <span class="caret"></span></a>
				  <ul class="dropdown-menu" role="menu">
					<li><a href="<?php echo $url->module('files'); ?>">Файловый менеджер</a></li>
					<li><a href="<?php echo $url->module('content', 'article'); ?>">Статьи</a></li>
					<li class="divider"></li>
					<li><a href="<?php echo $url->module('extensions', 'modules'); ?>">Модули</a></li>
					<li><a href="<?php echo $url->module('extensions', 'plugins'); ?>">Плагины</a></li>
					<li><a href="<?php echo $url->module('extensions', 'widgets'); ?>">Виджеты</a></li>
					<li><a href="<?php echo $url->module('extensions', 'templates'); ?>">Шаблоны</a></li>
					<li><a href="<?php echo $url->module('extensions', 'languages'); ?>">Языки</a></li>
					<li class="divider"></li>
					<li><a href="<?php echo $url->module('extensions', 'install'); ?>">Установить дополнение</a></li>
					<li><a href="<?php echo $url->module('extensions', 'index'); ?>">Дополнения</a></li>
				  </ul>
				</li>
				<!--li><a href="<?php echo $url->module('files'); ?>">Файловый менеджер</a></li-->
				<li><a href="<?php echo $url->module('gallery'); ?>">Альбомы</a></li>
			  </ul>
			  <!--form class="navbar-form navbar-left" role="search">
				<div class="form-group">
				  <input type="text" class="form-control" placeholder="Search">
				</div>
				<button type="submit" class="btn btn-default">Submit</button>
			  </form-->
			  <ul class="nav navbar-nav navbar-right">
				<li><a href="/">Сайт</a></li>
				<li class="dropdown">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-user"></i> <?php echo $app->user->login; ?> <span class="caret"></span></a>
				  <ul class="dropdown-menu" role="menu">
					<li><a href="#">Выход</a></li>
					<li class="divider"></li>
					<li><a href="#">Выход</a></li>
				  </ul>
				</li>
			  </ul>
			</div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		
		<?php $template->breadcrumbs(); ?>
		
		<?php if ($template->checkParam) ?>
		<h1 class="page-header"><?php $template->param('page_header'); ?></h1>

		<?php echo $content; ?>

		<div class="row hor-space"></div>
		
		<div class="panel panel-info text-center">
			<div class="panel-body bg-info">
				Время выполнения: <?php echo round(microtime(true)-MT, 3); ?>сек. Макс. ОЗУ: <?php echo round(memory_get_peak_usage()/1024, 2) ?>кб.
			</div>
		</div>
	</div>
	</body>
</html>