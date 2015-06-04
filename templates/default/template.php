<?php
use Core\Library\Application\Application;
use Core\Library\User\Users;

$app = Application::getInstance();
$template = $app->template; 

$template->loadLanguage('template');
$lang = $template->getLanguage();
?>

<?php include $template->path('blocks/header.php', true); ?>
<?php include $template->path('blocks/top.php', true); ?>

			<div class="row">
				<div class="col-md-12 content">
				<?php echo $content; ?>
				</div>
			</div>

<?php include $template->path('blocks/footer.php', true); ?>