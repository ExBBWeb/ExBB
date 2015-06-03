<?php
class Extensions {
	const MODULE = 1;
	const LANGUAGE = 2;
	const TEMPLATE = 3;
	
	const PLUGIN = 4;
	const WIDGET = 5;
	
	const ENABLED = 1;
	const DISABLED = 0;
}

// Включить плагин
Extensions::setExtensionStatus($plugin->id, Extensions::ENABLED);
Extensions::enable($plugin->id);
Extensions::disable($plugin->id);

// Запустить контроллер установки
Extensions::install($plugin->id);

// Запустить контроллер удаления
Extensions::uninstall($plugin->id);

$plugin->autosave = false;
$plugin->author = 'WebMaster!';
$plugin->create();
$plugin->update();

$users = ExBB::get('user', false, 15);
?>