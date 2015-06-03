<?php
use Core\Library\Application\Application;
$template = Application::getInstance()->template; 
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $template->title; ?></title>
		
		<script type="text/javascript" src="<?php $template->url('js/jquery-1.11.2.min.js'); ?>"></script>
		
		<link rel="stylesheet" href="<?php $template->url('css/style.css'); ?>" type="text/css">
		<link rel="stylesheet" href="<?php $template->url('css/auth.css'); ?>" type="text/css">
		<?php $template->head(); ?>
		
	</head>
	
	<body>
		<div class="container">
		
		<?php echo $content; ?>

			<div id="footer">
				<p>Седмиховка &copy; 2015</p>
				<p class="runtime-info">Время выполнения: <?php echo round(microtime(true)-MT, 3); ?>сек. Макс. ОЗУ: <?php echo round(memory_get_peak_usage()/1024, 2) ?>кб.</p>
			</div>
		</div>
	</body>
</html>