<?php
use Core\Library\Extension\Extend;

$menu = new \StdClass();

$menu->items = array(
	'index' => array(
		'url' => $url->module('user', 'profile', 'index'),
		'icon' => 'user',
		'title' => $lang->menu_profile_view,
		'current' => ($tab == 'index'),
	),
	
	'edit' => array(
		'url' => $url->module('user', 'profile', 'edit'),
		'icon' => 'pencil',
		'title' => $lang->menu_profile_edit,
		'current' => ($tab == 'edit'),
	),
	
	'settings' => array(
		'url' => $url->module('user', 'profile', 'settings'),
		'icon' => 'cogs',
		'title' => $lang->menu_settings,
		'current' => ($tab == 'settings'),
	),
);

Extend::setAction('user_profile_menu_build', $menu);
?>

<div class="panel profile">
	<div class="header">
		<h1><?php echo $lang->profile_title; ?></h1>
	</div>

	<div class="body-no-padding row">
		<div class="col-md-2 no-padding profile-menu">
			<ul class="vertical-menu">
				<li class="head"><a href="#"><?php echo $lang->menu; ?></a></li>
				<?php foreach ($menu->items as $item_name => $item) : ?>
				<li<?php if ($item['current']) echo ' class="current"'; ?>><a href="<?php echo $item['url']; ?>"><i class="fa fa-<?php echo $item['icon']; ?>"></i> <?php echo $item['title']; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="col-md-10 pd-7 profile-body">
			<?php include $tab_content; ?>
		</div>
	</div>

</div>