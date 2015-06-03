<?php
use Core\Library\Application\Application;
$template = Application::getInstance()->template; 
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $template->title; ?></title>
		
		<script type="text/javascript" src="<?php $template->url('js/jquery-1.11.2.min.js'); ?>"></script>

		<link rel="stylesheet" href="<?php $template->url('bootstrap/css/bootstrap.min.css'); ?>">
		<link rel="stylesheet" href="<?php $template->url('bootstrap/css/bootstrap-theme.css'); ?>">
		<script src="<?php $template->url('bootstrap/js/bootstrap.min.js'); ?>"></script>
		
		<link rel="stylesheet" href="<?php $template->url('css/admin.css'); ?>">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		
		<?php $template->head(); ?>
	</head>
	
	<body>
		<div class="container">
		
		<div class="hor-space"></div>
		
		<div class="panel panel-<?php echo $status; ?> panel-redirect">
			<div class="panel-heading"><?php echo $title; ?></div>
			<div class="panel-body"><?php echo $message; ?></div>
			<div class="panel-footer"><a href="<?php echo $link; ?>">Перейти</a></div>
		</div>

		<div class="panel panel-info text-center">
			<div class="panel-body bg-info">
				Время выполнения: <?php echo round(microtime(true)-MT, 3); ?>сек. Макс. ОЗУ: <?php echo round(memory_get_peak_usage()/1024, 2) ?>кб.
			</div>
		</div>
		</div>
	</body>
</html>