<?php
use Core\Library\Application\Application;
$app = Application::getInstance();

$template = $app->template; 
$url = $app->url;

if ($template->page_title) $template->title .= ' - '.$template->page_title;

$template->loadLanguage('template');
$lang = $template->getLanguage();

$module = $app->router->getVar('module');
$controller = $app->router->getVar('controller');
$action = $app->router->getVar('action');
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
                        <a href="<?php echo $url->module('auth', 'index', 'logout'); ?>" id="logout">
                        <?php echo $lang->logout; ?>
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
                                <li<?php if ($module == 'index') echo ' id="current"'; ?>><a href="<?php echo $url->module('index'); ?>"><i class="fa fa-home"></i> <?php echo $lang->dashboard; ?></a></li>
                                <li<?php if ($module == 'forums') echo ' id="current"'; ?>><a href="<?php echo $url->module('forums'); ?>"><i class="fa fa-paw"></i> <?php echo $lang->forums; ?></a></li>
                                <li<?php if ($module == 'users') echo ' id="current"'; ?>><a href="<?php echo $url->module('users'); ?>"><i class="fa fa-users"></i> <?php echo $lang->users; ?></a></li>
                                <li<?php if ($module == 'extensions') echo ' id="current"'; ?>><a href="<?php echo $url->module('extensions'); ?>"><i class="fa fa-plug"></i> <?php echo $lang->extensions; ?></a></li>
                                <li<?php if ($module == 'settings') echo ' id="current"'; ?>><a href="<?php echo $url->module('settings'); ?>"><i class="fa fa-cogs"></i> <?php echo $lang->settings; ?></a></li>
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
                            <li><a href="<?php echo $url->module('forums', 'add'); ?>"><?php echo $lang->create_forum; ?></a></li>
                            <li><a href="<?php echo $url->module('users', 'add'); ?>"><?php echo $lang->create_user; ?></a></li>
                            <li><a href="<?php echo $url->module('stats'); ?>"><?php echo $lang->stats; ?></a></li>
                            <li><a href="<?php echo $url->module('extensions', 'install'); ?>"><?php echo $lang->install_extension; ?></a></li>
                            <li><a href="<?php echo $url->module('groups'); ?>"><?php echo $lang->groups; ?></a></li>
                        </ul>
                        
                    </div><!-- End. .grid_12-->
                </div><!-- End. .container_12 -->
                <div style="clear: both;"></div>
            </div> <!-- End #subnav -->
        </div> <!-- End #header -->
		
		<div class="container_12 clearfix">
		
		<?php $template->breadcrumbs(); ?>

		<?php echo $content; ?>
		
		</div>
		
        <!-- Footer -->
        <div id="footer">
        	<div class="container_12">
            	<div class="grid_12">
                	<!-- You can change the copyright line for your own -->
                	<p>ExBB &copy; <?php echo date('Y'); ?>. <a href="http://exbb.pw/ " title="ExBB Team Forum">ExBB Team</a></p>
					<p><?php echo $lang->exec_time; ?> <?php echo round(microtime(true)-MT, 3); ?><?php echo $lang->exec_second; ?> <?php echo $lang->exec_memory; ?> <?php echo round(memory_get_peak_usage()/1024, 2) ?><?php echo $lang->exec_kb; ?></p>
        		</div>
            </div>
            <div style="clear:both;"></div>
        </div> <!-- End #footer -->
	</body>
</html>