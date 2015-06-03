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

// �������� ������
Extensions::setExtensionStatus($plugin->id, Extensions::ENABLED);
Extensions::enable($plugin->id);
Extensions::disable($plugin->id);

// ��������� ���������� ���������
Extensions::install($plugin->id);

// ��������� ���������� ��������
Extensions::uninstall($plugin->id);

$plugin->autosave = false;
$plugin->author = 'WebMaster!';
$plugin->create();
$plugin->update();

$users = ExBB::get('user', false, 15);
?>