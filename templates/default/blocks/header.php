<?php
$language = $app->language->getLanguage();
$js_lang = 'templates/'.TEMPLATE.'/language/'.$language.'/js/site.js';
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $template->title.' - '.$template->page_title; ?></title>		
		<link href='http://fonts.googleapis.com/css?family=Arimo:700,400,400italic,700italic&subset=cyrillic' rel='stylesheet' type='text/css'>
		
		<link rel="stylesheet" href="<?php $template->url('css/font-awesome.min.css'); ?>" type="text/css">
		<link rel="stylesheet" href="<?php $template->url('css/bootstrap.min.css'); ?>" type="text/css">
		<link rel="stylesheet" href="<?php $template->url('css/bootstrap-theme.min.css'); ?>" type="text/css">
		<link rel="stylesheet" href="<?php $template->url('css/style.css'); ?>" type="text/css">

		<script type="text/javascript" src="<?php $template->url('js/jquery-1.11.2.min.js'); ?>"></script>
		<script type="text/javascript" src="<?php $template->url('js/jquery.form.min.js'); ?>"></script>
		<script type="text/javascript" src="<?php $template->url('js/jquery.validate.min.js'); ?>"></script>
		<script type="text/javascript" src="<?php $template->url('js/jquery.lighttabs.js'); ?>"></script>
		<script type="text/javascript" src="<?php $template->url('js/site.js'); ?>"></script>
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<?php $template->head(); ?>
		
		<script type="text/javascript">
		$(document).ready(function() {
			Site.setUrl("<?php echo BASE_URL; ?>");
			Site.setLanguage("<?php echo $language; ?>", "<?php echo $js_lang; ?>");
		});
		</script>
		
	</head>
	
	<body>