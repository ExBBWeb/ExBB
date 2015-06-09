<?php
/*
Файл compile.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение классов, используемых при компиляции кавайного парсера в брутальный для построения откомпилированного класса, для распределения имён свойств, методов и переменных, а также для накопления кода с последующим его выводом.
*/


/*
Класс FuraxBBParserCompiler описывает компилятор кавайного парсера в брутальный - он накапливает функции и данные брутального парсера и затем собирает их в готовый код класса.
*/
class FuraxBBParserCompiler
{
	public function __construct($FuraxBB, $Name) //$Name - имя класса
	{
		$this->name = $Name;
		$this->furaxBB = $FuraxBB;
	}

	private function addAlignedRunner() //Создание исполнителя и функции проверки сущности тега, содержащего единственный параметр - горизонтальное выравнивание
	{
		$runner = $this->addDoubleTagRunner('alignedRunner');
		if (!$runner) //Исполнитель уже создан
			return;

		$runner->addCode("return \"<\$runnerParameters align=\\\"\$parameters\\\">\$contents</\$runnerParameters>\";"); //Единственный параметр исполнителя - имя тега

		$checker = $this->addMethodOnce('isAlignment'); //Функция проверки того, является ли параметр корректным именем типа выравнивания содержимого
		$checker->addParameter('parameter');
		$alignments = FuraxBBParserCompiler :: serialize($this->furaxBB->getAlignments());
		$checker->addCode("return in_array(\$parameter, $alignments);");
	}

	private function addCommonRunCode($ParametersSetVariable) //Добавление кода функции run(), запускающего выполнение действий по очереди
	{
		$this->members['run']->addCode("return self::runSubActions(\$Text, 0, $ParametersSetVariable, \$ExtraParameters);");
	}

	public function addDoubleTagRunner($Name) //Добавление исполнителя, форматирующего результат обработки двойного тега
	{
		$runner = $this->addMethodOnce($Name);
		if (!$runner) //Исполнитель уже создан
			return NULL;

		$runner->addParameter('parameters'); //Параметр или параметры тега
		$runner->addParameter('contents'); //Содержимое тега, уже прошедшее необходимую обработку
		$runner->addParameter('extraData'); //Дополнительные параметры, переданные функции FuraxBrutalBB::run()
		$runner->addParameter('runnerParameters'); //Параметры исполнителя применительно к конкретному тегу

		return $runner;
	}

	public function addMethod($Name) //Добавление метода (статического, закрытого, с именем $Name) к классу парсера
	{
		return $this->members[$Name] = new FuraxBBParserMethod($Name, false);
	}

	public function addMethodOnce($Name) //Добавление метода только в том случае, если член с таким именем ещё не существует
	{
		if ($this->getMember($Name)) //Член существует
			return NULL;
		else
			return $this->addMethod($Name);
	}

	private function addParameteredDoubleTagRunner() //Создание исполнителя для двойного тега с одним параметром, который непосредственно вставляется в код
	{
		$runner = $this->addDoubleTagRunner('parameteredDoubleTagRunner');
		if (!$runner)
			return;

		$code = <<< CODE_END
return "\$runnerParameters[0]\$parameters\$runnerParameters[1]\$contents\$runnerParameters[2]";
CODE_END;
		$runner->addCode($code);
	}

	private function addParametersSetSelector() //Добавление в тело функции run() переменной, в которой оказывается набор параметров в соответствии с переданным ей именем набора параметров; возвращает имя соответствующей переменной
	{
		$run = $this->members['run'];

		//Если набор параметров с таким именем не найден, используется набор 'default'
		$run->addCode('$parametersSet = (isSet(self::$ParametersSets[$ParametersSet]) ? self::$ParametersSets[$ParametersSet] : self::$ParametersSets[\'default\']);'."\r\n\t\t");
	}

	public function addProperty($Name, $Value = '') //Добавление свойства (статического, закрытого, с именем $Name и значением $Value) в класс; если значение переменной представлено строкой, считается, что оно уже сериализовано
	{
		$this->members[$Name] = new FuraxBBParserProperty($Name, $Value);
	}

	public function addPropertyOnce($Name, $Value = '') //Добавление свойства только в том случае, если член с таким именем ещё не был создан
	{
		if ($this->getMember($Name)) //Член существует
			return NULL;
		else
			return $this->addProperty($Name, $Value);
	}

	public function addPublicMethod($Name) //Добавление общедоступного метода с именем $Name в класс парсера
	{
		return $this->members[$Name] = new FuraxBBParserMethod($Name, true);
	}

	public function addSimpleDoubleTagRunner() //Добавление исполнителя двойного тега, не имеющего параметров, обработка которого сводится к добавлению тегов в начало и конец содержимого
	{
		$runner = $this->addDoubleTagRunner('simpleDoubleTagRunner');
		if (!$runner) //Исполнитель уже существует
			return;

		//Параметры исполнителя должны быть представлены массивом, содержащим два элемента: код перед содержимым тега и код после содержимого тега, например: array('<b>', '</b>')
		$runner->addCode("return \$runnerParameters[0].\$contents.\$runnerParameters[1];");
	}

	public function addSingleTagRunner($Name) //Создание исполнителя единичного тега
	{
		$runner = $this->addMethodOnce($Name);
		if (!$runner)
			return NULL;

		$runner->addParameter('parameters'); //Параметр или параметры тега
		$runner->addParameter('extraData'); //Дополнительные данные, переданные функции run()
		$runner->addParameter('runnerParameters'); //Параметры исполнителя

		return $runner;
	}

	public function compileAlignedTag($TagName) //Создание массива, служащего результатом метода compile для сущности двойного тега с одним параметром, задающим стиль выравнивания. Параметр $Method должен представлять указатель на метод, являющийся точкой входа в обработку соответствующего тега. Параметр $TagName - это имя тега, генерируемого исполнителем.
	{
		$this->addAlignedRunner();
		return new FuraxBBDoubleTagRunnerData('alignedRunner', $TagName, "self::isAlignment(\$tags[\$tag][3])");
	}

	public function compileParameteredDoubleTag($RegularExpression, $Prefix, $Posfix) //Создание массива, служащего результатом метода compile для сущности двойного тега с одним параметром, работа которого сводится к добавлению кода $Prefix перед содержимым тега и кода $Posfix после содержимого тега, причём в $Prefix присутствует одна подстрока '$parameter', заменяемая значением единственного параметра тега, а значение этого параметра должно удовлетворять регулярному выражению $RegularExpression. Параметр $Method должен представлять указатель на метод, являющийся точкой входа в обработку соответствующего тега.
	{
		$this->addParameteredDoubleTagRunner(); //Используется исполнитель 'parameteredDoubleTagRunner'
		$prefix = explode('$parameter', $Prefix, 2);
		return new FuraxBBDoubleTagRunnerData('parameteredDoubleTagRunner', array($prefix[0], $prefix[1], $Posfix), $this->compileParameterMatching($RegularExpression));
	}

	public function compileParameterMatching($RegularExpression) //Возвращает код проверки соответствия параметра тега, который разбирает точка входа $Method, регулярному выражению $RegularExpression
	{
		$regexp = self::serialize($RegularExpression);
		return "preg_match($regexp, \$tags[\$tag][3])";
	}

	public function compileSimpleDoubleTagRunner($TagName) //Создание массива, служащего результатом метода compile для сущности двойного тега без параметров и HTML-параметров. Параметр $Method должен представлять указатель на метод, являющийся точкой входа в обработку соответствующего тега. Параметр $TagName - это имя тега, генерируемого исполнителем.
	{
		$this->addSimpleDoubleTagRunner(); //Используется исполнитель 'simpleDoubleTagRunner'
		return new FuraxBBDoubleTagRunnerData('simpleDoubleTagRunner', array("<$TagName>", "</$TagName>"));
	}

	private function convertParametersSet($States) //Принимает массив состояний алгоритмов со "старыми" индексами и возвращает массив состояний алгоритмов с "новыми" индексами
	{
		$parametersSet = array();
		foreach ($this->newToOld as $algorithm) //Состояния алгоритмов, не входящих в сборку, в массив не включаются
			$parametersSet[] = $States[$algorithm];
		return $parametersSet;
	}

	public function countAlgorithms($Enabled, $OrderedActions, $Algorithms, $TagsParserIndex) //Подсчёт алгоритмов, включаемых в сборку, и их перенумерация
	{
		//"Старыми" называются номера, которые алгоритмы получили в ходе сборки и настройки кавайного парсера; никаких гарантий касательно их порядка нет. Новые номера присваиваются при сборке брутального парсера; при этом гарантируется, что нумерация действий начинается с нуля и возрастает в порядке очерёдности выполнения действий. Номера сущностей, если они присутствуют в сборке, начинаются со следующего после номера последнего действия числа (минимум - с единицы, поскольку при включении в сборку сущностей гарантируется наличие хотя бы парсера тегов среди включаемых в сборку действий).
		foreach ($OrderedActions as $action) //Сначала присваиваются индексы действиям, причём в том порядке, в котором они выполняются
			if (isSet($Enabled[$algorithm = $action->getIndex()]))
				$this->newToOld[] = $algorithm;
		$this->actionsNumber = count($this->newToOld);

		$this->tagsParserIncluded = isSet($Enabled[$TagsParserIndex]); //Если парсер тегов не включен в сборку (да, можно собрать брутальный парсер, состоящий, к примеру, из одних только экранировщика спецсимволов и расстановщика тегов переноса), точки входа в обработку тегов и информация о самих тегах не включаются в сборку
		if ($this->tagsParserIncluded)
			foreach ($Algorithms as $index => $algorithm)
				if (is_a($algorithm, 'FuraxBBEntity') && isSet($Enabled[$index]))
					$this->newToOld[] = $index;

		$this->algorithmsNumber = count($this->newToOld);
		$this->oldToNew = array_flip($this->newToOld); //Обратный переход от старых номеров к новым
		ksort($this->oldToNew); //Массив сортируется, в основном, для порядку; влиять на что-то эта сортировка, в общем-то, не должна
	}

	private function createBasicRun() //Создание метода run и его параметров
	{
		$run = $this->addPublicMethod('run');
		$run->addParameter('Text'); //Разбираемый текст
		$run->addParameter('ExtraParameters', false, 'NULL'); //Дополнительные параметры парсера
		if ($this->parametersSets > 1 && $this->actionsNumber) //Имя набора параметров требуется только в том случае, если в сборку входит не менее двух наборов параметров и не менее одного действия
			$run->addParameter('ParametersSet', false, "'default'");
	}

	public function createModifyStates() //Создание функции, корректирующей состояния алгоритмов модификаторами
	{
		$modifyStates = $this->addMethod('modifyStates');
		$oldStates = $modifyStates->addParameter('oldStates'); //Состояния до модификации
		$newStates = $modifyStates->addParameter('newStates'); //Состояния, прописанные в модификаторе

		$code = <<< CODE_END
foreach (\$newStates as \$algorithm => \$state)
			\$oldStates[\$algorithm] = \$state*(bool)\$oldStates[\$algorithm];
		return \$oldStates;
CODE_END;
		$modifyStates->addCode($code);
	}

	public function createRunMethods($StartStates) //Добавление в парсер функции run() и используемых ею данных
	{
		$this->parametersSets = count($StartStates); //Количество наборов состояний
		$this->createBasicRun(); //Создание функции run() и её параметров, но не кода

		if ($this->algorithmsNumber == 0) //В парсер не входит ни одного действия - run() должна просто вернуть переданный ей текст
			$this->setEmptyRun();
		elseif ($this->algorithmsNumber == 1) //В парсере всего одно действие и ни одной сущности
		{
			if ($this->parametersSets == 1) //Набор параметров также один; ясно, run() должна просто вернуть результат выполнения единственного действия над текстом
				$this->setSingleActionRun();
			else
			{
				$parametersSets = array();
				foreach ($StartStates as $set => $States) //Единственное, что входит в набор параметров - используется или нет единственное действие
					$parametersSets[$set] = ($States[$this->newToOld[0]] == FuraxBB_Enabled);

				if (array_search(false, $parametersSets) === false) //Во всех наборах параметров единственное действие включено - при обращении к run оно должно быть выполнено в любом случае; обратной ситуации - выключенного во всех наборах параметров единственного действия - быть не может, поскольку тогда действие не было бы включено в сборку
					$this->setSingleActionRun();
				else //run() должна либо выполнить одно действие, либо вернуть исходный текст
					$this->setParameteredSingleActionRun($parametersSets);
			}
		}
		elseif ($this->parametersSets == 1) //Набор параметров всего один; run() выполняет действия в цикле, однако всегда с одним и тем же набором параметров
			return $this->setSingleParametersSetRun($StartStates);
		else //Набор параметров выбирается при обращении к парсеру; все действия выполняются по очереди
		{
			$parametersSets = array();
			foreach ($StartStates as $set => $States)
				$parametersSets[$set] = $this->convertParametersSet($States);
			return $this->setFullRun($parametersSets);
		}
	}

	public function createRunSubActions() //Создание функции, по очереди выполняющей все действия
	{
		$runSubActions = $this->addMethod('runSubActions');

		$runSubActions->addParameter('text');
		$runSubActions->addParameter('index');
		$runSubActions->addParameter('states');
		$runSubActions->addParameter('extraData');

		$enabled = FuraxBB_Enabled;

		$code = <<< CODE_END
\$running = true;
		while (\$running && \$index < $this->actionsNumber) {
			if (\$states[\$index] == $enabled) {
				\$methodName = "action\$index";
				\$text=self::\$methodName(\$text, \$running, \$states, \$extraData);
			}
			++\$index;
		}
		return \$text;
CODE_END;
		$runSubActions->addCode($code);
	}

	public function fillActions($Algorithms) //Компиляция всех действий
	{
		for ($actionIndex = 0; $actionIndex < $this->actionsNumber; ++$actionIndex)
		{
			$action = $Algorithms[$this->newToOld[$actionIndex]]; //Объект действия

			$actionMethod = new FuraxBBParserAction($action, $this); //Функция - точка входа в действие
			$this->members[$actionMethod->getName()] = $actionMethod;

			$action->compile($actionMethod, $this, $actionIndex); //Компиляция действия, заполнение кода точки входа
		}
	}

	public function fillEntities($Entities) //Компиляция всех сущностей
	{
		for ($entityIndex = $this->actionsNumber; $entityIndex < $this->algorithmsNumber; ++$entityIndex)
			if (! is_a($entity = $Entities[$this->newToOld[$entityIndex]], 'FuraxBBEndTagEntity')) //Сущности конечных тегов не компилируются, поскольку с конечных тегов никогда не начинается разбор - соответствующие точки входа не создаются
			{
				$method = new FuraxBBParserEntity($entity, $this); //Функция - точка входа
				$this->members[$method->getName()] = $method;

				$entity->compile($method, $this); //Заполнение кода точки входа
			}
	}

	public function getMember($Name) //Доступ к члену класса брутального парсера по имени
	{
		if (!isSet($this->members[$Name])) //Член не создан
			return NULL;
		else
			return $this->members[$Name];
	}

	public function getName() //Имя класса
	{
		return $this->name;
	}

	public function getNewIndex($OldIndex) //Преобразование старого номера алгоритма к новому
	{
		return $this->oldToNew[$OldIndex];
	}

	public function getNewToOld() //Таблица преобразований старых номеров алгоритмов к новым
	{
		return $this->newToOld;
	}

	public function getNonEndEntityIndex($OldIndex) //Преобразование старого индекса к новому в том случае, если сущность с таким индексом не является сущностью закрывающего тега
	{
		if (isSet($this->oldToNew[$OldIndex]) && $this->furaxBB->isNonEndEntity($OldIndex))
			return $this->oldToNew[$OldIndex];
		else
			return NULL;
	}

	public function getOldIndex($NewIndex) //Преобразование нового индекса к старому
	{
		return $this->newToOld[$NewIndex];
	}

	public function getOldToNew() //Таблица преобразований старых индексов к новым
	{
		return $this->oldToNew;
	}

	private static function isSuccessiveArray($Data) //Возвращает true, если индексы массива являются последовательными целыми числами, начиная с нуля, и false в противном случае
	{
		$successiveKey = 0;
		foreach ($Data as $key => $value)
			if ($key !== $successiveKey++)
				return false;
		return true;
	}

	public static function serialize($Data) //Сериализация данных через var_export; отличается тем, что записывает массивы в одну строку и опускает индексы у тех массивов, у которых они являются последовательными целыми числами, начиная с нуля
	{
		if (is_array($Data))
		{
			$result = array(); //Массив пар "ключ => значение", уже переведённых в строки

			if (self::isSuccessiveArray($Data))
				foreach ($Data as $element)
					$result[] = self::serialize($element);
			else
				foreach ($Data as $key => $element)
					$result[] = self::serialize($key) . '=>' . self::serialize($element);

			return 'array(' . implode(', ', $result) . ')';
		}
		else
			return var_export($Data, true);
	}

	private function setEmptyRun() //Задание кода функции run(), просто возвращающего переданный ему текст
	{
		$this->members['run']->addCode('return $Text;');
	}

	private function setFullRun($ParametersSet) //Создание кода функции run(), циклически вызывающего действия в соответствии с выбранным набором параметров
	{
		$this->addProperty('ParametersSets', $ParametersSet); //Сохранение набора параметров в классе
		$this->createRunSubActions(); //Создание функции поочерёдного вызова дейтсвий
		$this->addParametersSetSelector(); //Создание кода выбора набора параметров
		$this->addCommonRunCode('$parametersSet'); //Создание кода вызова функции, поочерёдно выполняющей действия
	}

	private function setParameteredSingleActionRun($ParametersSets) //Создание кода функции run(), выполняющего единственное действие или возвращающего текст без изменений, в зависимости от набора параметров
	{
		$this->addProperty('ParametersSets', $ParametersSets); //Сохранение набора параметров в классе
		$this->addParametersSetSelector($parametersSets); //Создание кода выбора набора параметров
		$this->members['run']->addCode('return ($parametersSet ? self::a0($Text, true, array(), $ExtraParameters) : $Text);'); //Код функции run()
	}

	private function setSingleActionRun() //Создание кода функции run(), выполняющего единственное действие
	{
		$this->members['run']->addCode('return $this->a0($Text, true, array(), $ExtraParameters);');
	}

	private function setSingleParametersSetRun($StartStates) //Создание кода функции run(), выполняющего поочерёдно все действия из набора, но всегда с единственным набором параметров
	{
		$this->addProperty('ParametersSet', $this->convertParametersSet($StartStates['default']));
		$this->createRunSubActions();
		$this->addCommonRunCode('self::$ParametersSet');
	}

	public function toString() //Генерация кода класса брутального парсера
	{
		$code = "class $this->name\r\n{\r\n";
		foreach ($this->members as $member) //Код членов класса
			$code .= $member->toString();
		$code .= "}\r\n";

		return $code;
	}

	private $name; //Имя класса
	private $furaxBB; //Указатель на кавайный парсер

	private $members = array(); //Члены класса брутального парсера

	private $newToOld = array(); //Массов соответствий старых номеров алгоритмов новым номерам
	private $oldToNew; //Массов соответствий новых номеров алгоритмов старым номерам

	private $actionsNumber; //Число действий в парсере
	private $algorithmsNumber; //Число алгоритмов в парсере

	private $tagsParserIncluded; //Включен ли в сборку парсер тегов
	private $parametersSets; //Число наборов параметров в сборке
}


/*
Класс FuraxBBParserMember описывает член класса брутального парсера - (статическую) переменную или функцию. Он абстрактен, поскольку способ генерации кода члена должен быть определён в дочернем классе.
*/
abstract class FuraxBBParserMember
{
	public function __construct($Name)
	{
		$this->name = $Name;
	}

	public function getName() //Доступ к короткому имени члена, которое войдёт в сборку
	{
		return $this->name;
	}

	abstract public function toString(); //Генерация кода члена


	protected $name;
}

/*
Класс FuraxBBParserProperty описывает (статическое) свойство класса брутального парсера. Объект этого класса создаётся классом FuraxBBParserCompiler и добавляется в массив members, а затем используется для генерации кода.
*/
class FuraxBBParserProperty extends FuraxBBParserMember
{
	public function __construct($Name, $Value)
	{
		parent :: __construct($Name);
		$this->setValue($Value);
	}

	public function getValue() //Доступ к сериализованному значению свойства
	{
		return $this->value;
	}

	public function setValue($Value) //Установка значения свойства
	{
		if (is_string($Value)) //Если значение свойства задаётся строкой, предполагается, что эта строка представляет собой сериализованное значение
			$this->value = $Value;
		else //Иначе сериализация выполняется явно
			$this->value = FuraxBBParserCompiler::serialize($Value);
	}

	public function toString() //Генерация кода
	{
		$code = "	static private \$$this->name";
		if (strlen($this->value)) //Если значение свойства задано
			$code .= "=$this->value";
		$code .= ";\r\n";

		return $code;
	}


	private $value; //Сериализованное значение переменной
}


/*
Класс FuraxBBParserMethod описывает (статический) метод класса брутального парсера. Объект этого класса создаётся классом FuraxBBParserCompiler и затем используется для генерации кода. Его базовый класс FuraxBBNamesDistributor отвечает за именование локальных переменных и параметров функции.
*/
class FuraxBBParserMethod extends FuraxBBParserMember
{
	public function __construct($Name, $IsPublic) //Первый аргумент - булев - указывает, является ли метод общедоступным
	{
		parent :: __construct($Name);
		$this->isPublic = $IsPublic;
	}

	public function addCode($Code) //Добавление кода в функцию
	{
		$this->code .= $Code;
	}

	public function addParameter($Name, $IsLink = false, $Value = '') //Добавление параметра в функцию (флаг $IsLink указывает, принимается ли параметр по ссылке, а $Value представляет значение по умолчанию)
	{
		$this->parameters[$Name] = new FuraxBBParserMethodParameter($Name, $IsLink, $Value);
	}

	public function setVariable($Name, $Value) //Установка значения новой переменной (выделяется имя для новой переменной, а в код добавляется оператор присваивания ей значения)
	{
		$this->code .= "\$$name=";

		if (is_string($Value)) //Если $Value представлено строкой, эта строка воспринимается как уже сериализованное значение
			$this->code .= $Value;
		else //Иначе сериализация производится явно
			$this->code .= FuraxBBParserCompiler::serialize($Value);

		$this->code .= ";\r\n\t\t";
	}

	public function toString() //Генерация кода метода
	{
		$code = '	static ' . ($this->isPublic ? 'public' : 'private') . " function $this->name(";

		$parameters = array(); //Массив объявлений параметров метода, представленных в виде строк
		foreach ($this->parameters as $parameter)
			$parameters[] = $parameter->toString();
		$code .= implode(', ', $parameters) . ")\r\n\t{\r\n\t\t$this->code\r\n\t}\r\n";

		return $code;
	}


	private $code = ''; //Код метода
	private $parameters = array(); //Параметры метода
	private $isPublic; //Является ли метод общедоступным
}


/*
Класс FuraxBBParserMethodParameter описывает параметр (статического) метода класса брутального парсера. Он создаётся классом FuraxBBParserMethod и затем используется для генерации кода.
*/
class FuraxBBParserMethodParameter
{
	public function __construct($Name, $IsLink = false, $Value)
	{
		$this->name = $Name;
		$this->isLink = $IsLink;

		if (is_string($Value)) //Если значение представлено строкой, эта строка воспринимается как результат сериализации значения
			$this->value = $Value;
		else //Иначе значение сериализуется явно
			$this->value = FuraxBBParserCompiler::serialize($Value);
	}

	public function toString()
	{
		$code = '';
		if ($this->isLink) //Параметр принимается по ссылке
			$code .= '&';
		$code .= "\$$this->name";
		if (strlen($this->value)) //Задано значение параметра по умолчанию
			$code .= "=$this->value";

		return $code;
	}


	private $name; //Имя параметра
	private $isLink; //Передаётся ли параметр по ссылке
	private $value; //Значение параметра по умолчанию
}


/*
Класс FuraxBBParserAction описывает (статический) метод брутального парсера, являющийся точкой входа в обработку действия - его сигнатура фиксирована.
*/
class FuraxBBParserAction extends FuraxBBParserMethod
{
	public function __construct($Action, $Compiler)
	{
		parent :: __construct('action'.$Compiler->getNewIndex($Action->getIndex()), false, $Action->getID());

		$this->addParameter('text'); //Обрабатываемый текст
		$this->addParameter('running', true); //Ссылка на флаг продолжения обработки (присваивание ему значения false отменяет выполнение следующих действий из очереди)
		$this->addParameter('states'); //Массив состояний алгоритмов
		$this->addParameter('extraData'); //Дополнительные данные, переданные функции run()
	}
}


/*
Класс FuraxBBParserEntity описывает (статический) метод брутального парсера, являющийся точкой входа в обработку тега - его сигнатура фиксирована.
*/
class FuraxBBParserEntity extends FuraxBBParserMethod
{
	public function __construct($Entity, $Compiler)
	{
		parent :: __construct('entity'.$Compiler->getNewIndex($Entity->getIndex()), false, $Entity->getID());

		$this->addParameter('text'); //Обрабатываемый текст
		$this->addParameter('states'); //Массив состояний алгоритмов
		$this->addParameter('extraData'); //Дополнительные данные, переданные функции run()
		$this->addParameter('tags'); //Массив содержащихся в исходной строке тегов
		$this->addParameter('tag', true); //Ссылка на номер обрабатываемого в данный момент тега
		$this->addParameter('processedTo', true); //Ссылка на позицию, до которой уже произведена обработка строки
		$this->addParameter('endTags'); //Массив тегов, в данный момент считающихся закрывающими
	}
}


/*
Класс FuraxBBSingleTagRunnerData описывает набор данных, получающихся при компиляции сущности конкретного одиночного тега и используемых его точкой входа для вызова исполнителя.
*/
class FuraxBBSingleTagRunnerData
{
	public function __construct($RunnerName, $RunnerParameters = NULL)
	{
		$this->name = $RunnerName; //Имя исполнителя
		$this->parameters = $RunnerParameters; //Параметры исполнителя
	}

	public function getName()
	{
		return $this->name;
	}

	public function getParameters()
	{
		return $this->parameters;
	}


	private $name;
	private $parameters;
}


/*
Класс FuraxBBDoubleTagRunnerData описывает набор данных, получающихся при компиляции сущности конкретного двойного тега и используемых его точкой входа для вызова исполнителя.
*/
class FuraxBBDoubleTagRunnerData
{
	public function __construct($RunnerName, $RunnerParameters = NULL, $CheckExpression = NULL)
	{
		$this->name = $RunnerName; //Имя исполнителя
		$this->parameters = $RunnerParameters; //Параметры исполнителя
		$this->check = $CheckExpression; //Условие, проверяющее допустимость открывающего тега
	}

	public function getCheck()
	{
		return $this->check;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getParameters()
	{
		return $this->parameters;
	}


	private $name;
	private $parameters;
	private $check;
}


?>