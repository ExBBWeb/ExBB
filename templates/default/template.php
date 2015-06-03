<?php
use Core\Library\Application\Application;
use Core\Library\User\Users;

$app = Application::getInstance();
$template = $app->template; 
?>

<?php include $template->path('blocks/header.php', true); ?>
<?php include $template->path('blocks/top.php', true); ?>

		<div class="container">
			<div id="content" class="row">
				<div class="col-md-12 content">
				<?php if ($template->checkParam('page_header')) ?>
				<h1><?php $template->param('page_header'); ?></h1>
				
				<?php echo $content; ?>
				</div>
			</div>
		</div>
			
<?php include $template->path('blocks/footer.php', true); ?>