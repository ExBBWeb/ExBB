<?php
/*
Файл context.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение класса контекста парсинга, в котором определены текущие состояния алгоритмов парсера и правила их формирования, а также дополнительные данные, переданные пользователем методу run().
*/


/*
Класс FuraxBBParsingContext описывает контекст разбора исходной строки - набор текущих состояний алгоритмов и правила, по которым эти состояния задаются.
*/
class FuraxBBParsingContext
{
	/*
	Конструктор может вызываться в двух вариантах:
		* в первом варианте он принимает указатель на объект кавайного парсера, массив состояний алгоритмов по умолчанию, указатель на объет набора параметров и дополнительные данные, переданные пользователем в метод run();
		* во втором варианте он принимает указатели на объекты уже существующего контекста и на модификатор контекста, в соответствии с которым должны быть изменены состояния алгоритмов из первого аргумента.
	*/
	public function __construct($FuraxBB_Context, $States_Modifier, $ParametersSet_ = NULL, $ExtraParameters_ = NULL)
	{
		if ($ParametersSet_) //Создание полностью нового контекста
			$this->constructContext($FuraxBB_Context, $States_Modifier, $ParametersSet_, $ExtraParameters_);
		else //Модификация существующего контекста
			$this->cloneContext($FuraxBB_Context, $States_Modifier);
	}

	private function calculateState($State, $ModifierState) //Вычисление нового состояния алгоритма по старому состоянию и состоянию, указанному в модификаторе
	{
		return $ModifierState * (bool)$State; //Модификатор не может вывести алгоритм из состояния FuraxBB_Forbidden
	}

	private function cloneContext($Context, $Modifier)
	{
		$this->furaxBB = $Context->furaxBB;
		$this->states = $Context->states;
		$this->extraParameters = $Context->extraParameters;

		foreach ($Modifier->getRules() as $algorithm => $state) //Метод FuraxKawaiBB::run() всегда компилирует все модификаторы перед обращением к контекстам, так что $Modifier->rules заданы корректно
			$this->states[$algorithm] = $this->calculateState($this->states[$algorithm], $state);
	}

	private function constructContext($FuraxBB, $States, $ParametersSet, $ExtraParameters)
	{
		$this->furaxBB = $FuraxBB;

		$groups = $this->furaxBB->getGroupsArray();
		foreach ($ParametersSet->getGroups() as $group => $state) //Сначала применяются групповые модификаторы
			if (isSet($groups[$group]))
				foreach ($groups[$group] as $algorithm => $meanless)
					$States[$algorithm] = $state; //Состояния из наборов параметров применяются непосредственно, при этом "старые" (по умолчанию) состояния алгоритмов не учитываются

		$this->states = $ParametersSet->getAlgorithms() + $States; //Индивидуальные модификаторы применяются после групповых
		$this->extraParameters = $ExtraParameters;
	}

	public function getExtraParameters()
	{
		return $this->extraParameters;
	}

	public function getFuraxBB()
	{
		return $this->furaxBB;
	}

	public function getState($Index)
	{
		return $this->states[$Index];
	}

	public function getStates()
	{
		return $this->states;
	}


	private $furaxBB; //Указатель на объект кавайного парсера, к которому относится текущий контекст
	private $states; //Набор состояний алгоритмов
	private $extraParameters; //Дополнительные данные, переданные методу run() кавайного парсера
}


?>