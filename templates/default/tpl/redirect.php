<?php
use Core\Library\Application\Application;
use Core\Library\User\Users;

$app = Application::getInstance();
$template = $app->template; 
?>

<?php include $template->path('blocks/header.php', true); ?>
<?php include $template->path('blocks/top.php', true); ?>

			<div id="content" class="row">
				<div class="col-md-2">
					<div class="left_block">
						<?php $template->widget('left'); ?>
					</div>
				</div>
				<div class="col-md-8 content">
					<h1>Переадресация</h1>
					
					<div class="redirect-panel">
					<div class="panel-title"><strong><?php echo $title; ?></strong></div>
					<div class="panel-content"><?php echo $message; ?></div>
					<div class="panel-footer"><a href="<?php echo $link; ?>">Перейти</a></div>
					</div>

					<div style="text-align: center;"><img src="<?php $template->url('images/redirect_'.$status.'.jpg'); ?>"></div>
				</div>
				
				<div class="col-md-2">
					<div class="right_block">
						<h3>Последние статьи</h3>
						
						<h3>Популярные статьи</h3>
						
						<h3>Комментарии</h3>
					</div>
				</div>
			</div>

<?php include $template->path('blocks/footer.php', true); ?>