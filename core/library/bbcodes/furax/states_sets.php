<?php
/*
Файл states_sets.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определения классов набора параметров и модификатора контекста. Несмотря на различие выполняемых функций, поведение этих классов сходно, поэтому они имеют общего предка - класс набора состояний.
*/


/*
Класс FuraxBBStatesSet описывает набор состояний алгоритмов. Этот класс используется исключительно как базовый для классов набора параметров и модификатора контекста, и содержит общий для них код.
*/
class FuraxBBStatesSet
{
	protected function __construct($FuraxBB)
	{
		$this->furaxBB = $FuraxBB;
	}

	public function getAlgorithms()
	{
		return $this->algorithms;
	}

	public function getGroups()
	{
		return $this->groups;
	}

	public function toggleAction($ID, $State) //Изменение состояния действия с идентификатором $ID на $State
	{
		$this->toggleAlgorithm($this->furaxBB->getIndex($ID), $State);
	}

	public function toggleActions($ActionsToToggle) //Изменение состояния группы действий (массив $ActionsToToggle имеет структуру "идентификатор действия => состояние")
	{
		foreach ($ActionsToToggle as $action => $state)
			$this->toggleAction($action, $state);
	}

	public function toggleAlgorithm($Index, $State) //Изменение состояния алгоритма с номером $Index на $State
	{
		$this->algorithms[$Index] = $State;
	}

	public function toggleGroup($Name, $State) //Изменение состояния группы алгоритмов с именем $Name на $State
	{
		$this->groups[$Name] = $State;
	}

	public function toggleTag($Name, $Type, $State) //Изменение состояния сущности тега с именем $Name и типом $Type на $State
	{
		$Index = $this->furaxBB->generateTagIndex($Name, $Type);
		$this->toggleAlgorithm($Index, $State);
	}

	private $algorithms = array(); //Индивидуальные состояния алгоритмов
	private $groups = array(); //Состояния групп алгоритмов

	protected $furaxBB; //Указатель на объект кавайного парсера, которому принадлежит этот набор состояний
}


/*
Класс FuraxBBParsingParametersSet описывает набор параметров разбора. Весь его имеющий значение код содержится в базовом классе - классе набора состояний алгоритмов.
*/
class FuraxBBParsingParametersSet extends FuraxBBStatesSet
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB);
	}
}


/*
Класс FuraxBBContextModifier описывает модификатор контекста - набор правил, по которым состояния, содержищиеся в родительском контексте, пересчитываются в состояния дочернего контекста. Большая часть его кода содержится в базовом классе - классе набора состояний алгоритмов.
*/
class FuraxBBContextModifier extends FuraxBBStatesSet
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB);
		$FuraxBB->addContextModifier($this); //Регистрация модификатора контекста
	}

	public function compile() //Вычисление всех определённых в данном модификаторе состояний алгоритмов (поскольку часть состояний задана для групп алгоритмов, а состав групп может меняться, имеется необходимость в этом методе)
	{
		$states = array();
		$groups = $this->furaxBB->getGroupsArray();

		foreach ($this->getGroups() as $group => $state) //Сначала применяются правила для групп
			if (isSet($groups[$group]))
				foreach ($groups[$group] as $algorithm => $meanless)
					$states[$algorithm] = $state;

		$this->rules = $this->getAlgorithms() + $states; //После групповых правил применяются индивидуальные
	}

	public function compileModifierData($Compiler) //Подготовка данных модификатора к включению в состав брутального парсера; метод compile() гарантированно вызывается раньше данного
	{
		$rules = array();
		foreach ($this->rules as $old => $state)
			if (($new = $Compiler->getNonEndEntityIndex($old)) !== NULL) //В состав брутального парсера включается только информация о тех алгоритмах, которые входят в сборку; состояния сущностей закрывающих тегов ни на что не влияют
				$rules[$new] = $state;
		return FuraxBBParserCompiler::serialize($rules);
	}

	public function getRules()
	{
		return $this->rules;
	}


	private $rules; //Общедоступные скомпилированные правила, содержащиеся в модификаторе
}


?>