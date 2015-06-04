<?php
namespace Core\Library\DB;

/**
 *  Класс для работы с базой данных
 *
 * @name DB
 * @author Николай ѕауков
 */
class DB {
    /**
     * Экземпляр класса
     *
     * @var DB
     */
    protected static $_instance;
	protected $driver;
	
    private function __construct(){
		
    }
	
	public function __destruct() {

	}

	public function setDriver($driver) {
		$this->driver = $driver;

		define('DB_PREFIX', $this->driver->getConfig('db_prefix'));
	}
	
	public function getDriver() {
		return $this->driver;
	}
	
	public function getConfig($option_name=false) {
		return $this->driver->getConfig($option_name);
	}

	public function query($query) {
		return $this->driver->query($query);
	}
	
	public function getRow($query) {
		return $this->driver->getRow($query);
	}
	
	public function getAll($query) {
		return $this->driver->getAll($query);
	}
	
	public function getIndexedAll($field, $query) {
		return $this->driver->getIndexedAll($field, $query);
	}
	
	public function fetchAssoc($result) {
		return $this->driver->fetchAssoc($result);
	}
	
	public function fetchRow($result) {
		return $this->driver->fetchRow($result);
	}
	
	public function fetchArray($result) {
		return $this->driver->fetchArray($result);
	}
	
	public function getNumRows($result) {
		return $this->driver->getNumRows($result);
	}
	
	public function insertId() {
		return $this->driver->getInsertId();
	}
	
	public function escape($string) {
		return $this->driver->escape($string);
	}
	
	public function parse($query) {
		$args = func_get_args();
		unset($args[0]);
		$placeholders = $args;
		
		return $this->driver->parse($query, $placeholders);
	}
	
    private function __clone(){
    }

    /**
     * Функция для получения экземпляра класса DB
     *
     * @return DB
     */
    public static function getInstance() {
        // проверяем актуальность экземпляра
        if (null === self::$_instance) {
            // создаем новый экземпляр
            self::$_instance = new self();
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }
}
?>