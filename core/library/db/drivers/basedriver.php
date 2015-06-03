<?php
namespace Core\Library\DB\Drivers;

/**
* Дополнительные возможности, которые должен реализовать драйвер:
- Защита данных в SQL запросах, Placeholders
- Замена {PREFIX} на префикс таблиц базы данных
*/

abstract class BaseDriver {
	protected $config;
	
	abstract public function __Construct($config);
	
	abstract public function connect();
	
	abstract public function getRow($query);
	abstract public function getAll($query);
	abstract public function getIndexedAll($field, $query);
	
	abstract public function getNumRows($result=false);
	abstract public function getInsertId();
	
	abstract public function query($query);	
}
?>