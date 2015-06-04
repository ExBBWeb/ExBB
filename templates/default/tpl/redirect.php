<?php
use Core\Library\Application\Application;
use Core\Library\User\Users;

$app = Application::getInstance();
$template = $app->template; 

//$template->addBreadCrumb($);
if ($status == 'error') $status = 'danger';
?>

<?php include $template->path('blocks/header.php', true); ?>
<?php include $template->path('blocks/top.php', true); ?>

			<div class="row">
				<div class="col-md-12 content">
					<div class="panel">
						<div class="header"><h1>Переадресация</h1></div>
						<div class="body">
							<div class="alert alert-<?php echo $status; ?>"><strong><?php echo $title; ?></strong><br><?php echo $message; ?></div>
							<div><a href="<?php echo $link; ?>">Перейти</a></div>
						</div>
					</div>
				</div>
			</div>

<?php include $template->path('blocks/footer.php', true); ?>