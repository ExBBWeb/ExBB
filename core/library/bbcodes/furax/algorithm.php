<?php
/*
Файл algorithm.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение базового класса FuraxBBAlgorithm, описывающего алгоритм в составе парсера. Алгоритмы-действия определены в файлах actions.php и smiles.php; алгоритмы-сущности определены в файлах entities.php.
*/


/*
Класс FuraxBBAlgorithm описывает алгоритм в составе парсера. Он осуществляет регистрацию алгоритма в кавайном парсере, а также предоставляет дочерним классам сервисы добавления в группы, создания модификаторов и доступа к кавайному парсеру-владельцу.
*/
class FuraxBBAlgorithm
{
	/*
	Параметры конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$ID - идентификатор (строковый, уникальный);
		$State - состояние по умолчанию (FuraxBB_Forbidden, FuraxBB_Disabled либо FuraxBB_Enabled).
	*/
	protected function __construct($FuraxBB, $ID, $State)
	{
		$this->furaxBB = $FuraxBB;

		$this->id = $ID;
		$this->index = $FuraxBB->generateIndex($ID); //Индекс выдаётся парсером

		$FuraxBB->addAlgorithm($this, $State); //Регистрация алгоритма в парсере
	}

	protected function addToGroups($Group1) //Добавление алгоритма в группы; названия групп передаются этому методу в качестве отдельных параметров.
	{
		$parameters = func_get_args();
		$this->furaxBB->addToGroups($this, $parameters);
	}

	protected function createModifier() //Создание модификатора контекста, используемого данным алгоритмом
	{
		return $this->modifiers[] = new FuraxBBContextModifier($this->furaxBB); //Модификаторы сохраняются в алгоритме, что используется в дальнейшем при компиляции кавайного парсера в брутальный
	}

	public function enableSubalgorithms(&$States) //Метод, применяемый при компиляции кавайного парсера в брутальный для определения того, какие алгоритмы должны быть включены в сборку. Он принимает ссылку на массив состояний алгоритмов, и возвращает массив состояний тех алгоритмов, которые находятся в состоянии FuraxBB_Disabled и могут быть переведены в состояние FuraxBB_Enabled модификаторами, принадлежащими текущему алгоритму; при этом их состояния в массиве-аргументе этого метода также переводятся в FuraxBB_Enabled. Формат возвращаемого массива - "индекс => FuraxBB_Enabled".
	{
		$enabled = array(); //Список алгоритмов, переводимых модификаторами текущего алгоритма из состояния FuraxBB_Disabled в FuraxBB_Enabled

		foreach ($this->modifiers as $modifier) //Учитываются все модификаторы
			foreach ($modifier->getRules() as $algorithm => $state) //К моменту вызова модификаторы уже откомпилированны
				if ($state == FuraxBB_Enabled && $States[$algorithm] == FuraxBB_Disabled)
					$enabled[$algorithm] = $States[$algorithm] = FuraxBB_Enabled;

		return $enabled;
	}

	protected function getFuraxBB()
	{
		return $this->furaxBB;
	}

	public function getID()
	{
		return $this->id;
	}

	public function getIndex()
	{
		return $this->index;
	}

	public function getModifiers()
	{
		return $this->modifiers;
	}


	private $furaxBB; //Указатель на кавайный парсер

	private $id; //Идентификатор алгоритма
	private $index; //Индекс алгоритма в парсере

	private $modifiers = array(); //Все модификаторы алгоритма
}


?>