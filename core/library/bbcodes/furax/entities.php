<?php
/*
Файл entities.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение базового класса сущности и его дочерних классов, описывающих базовые типы сущностей тегов.
*/


/*
Класс FuraxBBEntity описывает сущность - набор правил и алгоритмов разбора конкретного bb-тега в коде. Он производен от FuraxBBAlgorithm, поскольку все сущности являются алгоритмами. Он абстрактен, поскольку способ разбора тега и алгоритм компиляции сущности в состав брутального парсера должны быть определены дочерними классами.
*/
abstract class FuraxBBEntity extends FuraxBBAlgorithm
{
	protected function __construct($FuraxBB, $Name, $Type, $State) //Аргумент $Name - это имя тега (в нижнем регистре, если постулирована независимость имён тегов от регистра); аргумент $Type - это его тип (FuraxBB_NoParameters, FuraxBB_SingleParameter, FuraxBB_ListedParameters или FuraxBB_EndTag)
	{
		parent :: __construct($FuraxBB, $FuraxBB->makeID($Name, $Type), $State);

		$this->name = $Name;
		$this->type = $Type;

		$this->getFuraxBB()->addEntity($this); //Регистрация сущности в парсере

		if ($Type != FuraxBB_EndTag) //Все теги, кроме закрывающих, включаются в одну из двух групп:
			if ($State == FuraxBB_Disabled) //Те, которые разрешены только внутри других (td, tr, *) - в группу normallyDisabled
				$this->addToGroups('normallyDisabled');
			else //Остальные - в группу normallyUndisabled, что позволяет легко отключать и подключать их в нужных местах (внутри table запрещены любые теги, кроме tr, а внутри td все отключенные теги вновь включаются)
				$this->addToGroups('normallyUndisabled');
	}

	public function addToGroups($Group1) //Добавление в набор групп (имена групп должны передаваться как отдельные аргументы)
	{
		$arguments = func_get_args();
		call_user_func_array(array($this, 'FuraxBBAlgorithm::addToGroups'), $arguments); //Родительский метод добавляет тег в группы, определённые на уровне парсера

		foreach ($arguments as $group) //Сущность, в отличие от действия, "помнит", в какие группы она входит
			$this->groups[$group] = true;
	}

	abstract function compile($Method, $Compiler); //Компиляция сущности в состав брутального парсера; $Method - это точка входа в обработку сущности; метод должен быть определён в дочернем классе

	public function getName() //Возвращает имя сущности
	{
		return $this->name;
	}

	public function getType() //Возвращает тип сущности
	{
		return $this->type;
	}

	public function isEndTag($EndTags) //Является ли тег текущей сущности конечным (упомянут ли он в $EndTag лично или как член группы)
	{
		if (isSet($EndTags[$this->getIndex()])) //Тег входит в массив $EndTags
			return $EndTags[$this->getIndex()];

		foreach ($this->groups as $group => $meanless)
			if (isSet($EndTags[$group])) //Тег входит в группу, входящую в $EndTags
				return $EndTags[$group];

		return FuraxBB_NotEndTag; //Тег не входит в список конечных для текущей группы
	}

	abstract function parse($Tag, $ParsingCycle); //Разбор тега соответствующей сущности; $Tag - объект типа FuraxBBTag, гарантированно относящийся к этой сущности; метод должен быть определён в дочернем классе. Возвращённое булево значение означает, успешно ли произведён разбор тега (при успешном разборе тег должен сам позаботиться о дополнении кода и сдвижке указателей в цикле разбора).


	private $name; //Имя тега сущности
	private $type; //Тип тега сущности

	private $groups = array(); //Группы, в которые входит сущность
}


/*
Класс FuraxBBEndTagEntity описывает сущность закрывающего тега (например, [/code]). Его специфика определяется тем, что разбор никогда не начинается с закрывающего тега. Он производен от класса сущности.
*/
final class FuraxBBEndTagEntity extends FuraxBBEntity
{
	public function __construct($FuraxBB, $Name)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_EndTag, FuraxBB_Disabled); //Сущность закрывающего тега переходит в состояние FuraxBB_Enabled только внутри своего открывающего тега (и то только для того, чтобы быть включённой вместе с ним в сборку брутального парсера); в остальных случаях разбирать её нет необходимости
	}

	public function compile($Method, $Compiler) //На самом деле, этот метод - затычка, поскольку сущности конечных тегов не компилируются в состав брутального парсера и не имеют в нём точек входа; этот метод не вызывается никогда, и определён лишь для того, чтобы класс не был абстрактным и допускал создание экземпляров
	{
		$Method->addCode('return NULL;');
	}

	public function parse($Tag, $ParsingCycle) //На самом деле, этот метод также является затычкой и никогда не вызывается: вне открывающих тегов закрывающие всё равно запрещены, и разбор тега не производится, а внутри открывающего тега, где закрывающий переходит в состояние FuraxBB_Enabled, проверка тега на то, является ли он закрывающим, производится ранее попытки произвести разбор тега
	{
		return false;
	}
}


/*
Класс FuraxBBSingleTagEntity описывает сущность единичного - не имеющего закрывающего - тега (например, [hr], [img=foto.jpg] или [img src=foto.jpg alt=Моя фотография]). Он по понятным причинам производен от класса сущности FuraxBBEntity. Он абстрактен, так как способы разбора и компиляции сущностей конкретных единичных тегов определяются в дочерних классах.
*/
abstract class FuraxBBSingleTagEntity extends FuraxBBEntity
{
	protected function __construct($FuraxBB, $Name, $Type, $State)
	{
		parent :: __construct($FuraxBB, $Name, $Type, $State);
	}

	public function compile($Method, $Compiler) //Заполнение кода функции - точки входа в разбор тега
	{
		$this->compileSingleTagParser($Compiler);

		$runnerData = $this->compileSingleTag($Compiler);
		$runnerName = FuraxBBParserCompiler::serialize($runnerData->getName()); //Имя исполнителя - функции, разбирающей конкретный тег
		$runnerArguments = FuraxBBParserCompiler::serialize($runnerData->getParameters()); //Параметры исполнителя

		//Точка входа всего лишь вызывает общую для всех сущностей единичного тега функцию разбора, передавая ей конкретные имя и параметры единичного тега
		$Method->addCode("return self::parseSingleTag(\$text, \$tags, \$tag, \$processedTo, \$states, \$extraData, $runnerName, $runnerArguments);");
	}

	abstract protected function compileSingleTag($Compiler); //Метод, производящий компиляцию конкретного единичного тега. Он должен вернуть объект FuraxBBSingleTagRunnerData.

	private function compileSingleTagParser($Compiler)
	{
		$parse = $Compiler->addMethodOnce('parseSingleTag'); //Создание общей для всех сущностей единичного тега функции разбора тегов
		if (!$parse) //Функция разбора уже создана
			return;

		//Аргументы функции разбора
		$parse->addParameter('text'); //Исходный текст
		$parse->addParameter('tags'); //Теги, содержащиеся в исходном тексте
		$parse->addParameter('tag', true); //Номер тега в наборе, который нужно разобрать
		$parse->addParameter('processedTo', true); //Позиция в исходной строке, до которой произведён разбор
		$parse->addParameter('states'); //Состояния алгоритмов
		$parse->addParameter('extraData'); //Дополнительные данные, переданные функции run()
		$parse->addParameter('parserName'); //Имя исполнителя конкретного тега
		$parse->addParameter('parserArguments'); //Параметры исполнителя конкретного тега

		//Общая для всех сущностей единичных тегов функция разбора вызывает конкретный исполнитель. Если этот исполнитель возвращает NULL, функция разбора также возвращает NULL; в противном случае возвращается ещё не обработанная до момента вызова исполнителя подстрока перед текущем тегом, пропущенная через оставшиеся действия, с присоединённым к ней результатом разбора конкретного тега, а переданные ей указатели положений в исходной строке и в наборе тегов устанавливаются в положение после текущего тега.
		$code = <<< CODE_END
\$result = self::\$parserName(\$tags[\$tag][3], \$extraData, \$parserArguments);
		if (\$result === NULL) return NULL;
		else {
			\$result = self::process(\$text, \$processedTo, \$tags[\$tag][1], \$states, \$extraData) . \$result;
			\$processedTo = \$tags[\$tag][2];
			++\$tag;
			return \$result;
		}
CODE_END;
		$parse->addCode($code);
	}

	public function parse($Tag, $ParsingCycle)
	{
		$result = $this->parseSingleTag($Tag, $ParsingCycle->getContext()->getExtraParameters()); //Результат разбора конкретного елиничного тега

		if ($result === NULL) //Разбор тега не удался
			return false;
		else //Разбор тега удался
		{
			$ParsingCycle->process($Tag->getPosition(), $result, $Tag->getEnd(), $ParsingCycle->getCurrentTag() + 1); //Дополнение полученного в результате разбора кода, сдвижка указателей позиций в исходной строке и наборе тегов
			return true;
		}
	}


	abstract protected function parseSingleTag($Tag, $ExtraParameters); //Метод разбора конкретного единичного тега. При неудачном разборе он обязан вернуть NULL, при удачном - строку - результат разбора. О сдвижке указателей и дополнении кода цикла разбора он не должен заботиться - этим занимается родительский (FuraxBBSingleTagEntity) класс.
}


/*
Класс FuraxBBDoubleTagEntity описывает сущность двойного тега - тега, состоящего из открывающего и закрывающего тега, т. е. имеющего содержимое, например: [b]...[/b], [color=red]...[/color], [quote author=Username topic=123 message=45]...[/quote], [*]...[/list]. Он по понятным причинам производен от FuraxBBEntity. Он абстрактен, поскольку алгоритмы разбора и компиляции в состав брутального парсера сущностей конкретных двойных тегов должны быть определены в производных классах.
*/
abstract class FuraxBBDoubleTagEntity extends FuraxBBEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - ссылка на объект кавайного парсера;
		$Name - имя тега;
		$Type - тип открывающего тега (FuraxBB_NoParameters, FuraxBB_SingleParameter или FuraxBB_ListedParameters);
		$State - состояние по умолчанию (FuraxBB_Enabled, FuraxBB_Disabled или FuraxBB_Forbidden);
		$ParentEndTagsAllowed - разрешено ли наследование родительских закрывающих тегов типов FuraxBB_LocalEndTag или FuraxBB_BubblingEndTag (закрывающие теги типов FuraxBB_UnembeddableEndTag и FuraxBB_ThrowableEndTag наследуются независимо от этой настройки);
		$LineEndAllowed - разрешено ли тегу не иметь закрывающего и закончиться вместе с входной строкой;
		$EndTag - создать ли закрывающий тег, и если да, то какого типа (FuraxBB_NotEndTag - не создавать; FuraxBB_LocalEndTag - создать закрывающий тег, закрывающий вложенные теги, если они допускают наследование закрывающих тегов; FuraxBB_UnembeddableEndTag - создать закрывающий тег, прерывающий обработку вложенных тегов независимо от того, допускают ли они наследование закрывающих тегов);
		$SubTagsAllowed - разрешён ли внутри этого тега разбор вложенных тегов (например, внутри тега left вложенные теги разрешены, а внутри code - нет).
	*/
	protected function __construct($FuraxBB, $Name, $Type, $State, $ParentEndTagsAllowed, $LineEndAllowed, $EndTag, $SubTagsAllowed)
	{
		parent :: __construct($FuraxBB, $Name, $Type, $State);

		$this->parentEndTagsAllowed = $ParentEndTagsAllowed;
		$this->lineEndAllowed = $LineEndAllowed;

		$this->modifier = $this->createModifier(); //Создание модификатора контекста, согласно которому будет обрабатываться содержимое тега
		$this->subTagsAllowed = $SubTagsAllowed;

		if ($EndTag) //Создание закрывающего тега
			$this->createEndTag($Name, $EndTag);
	}

	protected function addEndGroup($Name, $EndTagType = FuraxBB_BubblingEndTag) //Добавление к тегу группы, теги из которой считаются закрывающими; параметр $EndTagType должен иметь значение FuraxBB_BubblingEndTag (при появлении тега из этой группы внутри текущего разбор текущего тега считается завершённым успешно) или FuraxBB_ThrowableEndTag (разбор тега считается произведённым неверно).
	{
		$this->endTags[$Name] = $EndTagType;
	}

	protected function addEndTag($Name, $Type, $EndTagType = FuraxBB_BubblingEndTag) //Добавление к тегу закрывающего тега; параметр $EndTagType должен иметь значение FuraxBB_BubblingEndTag (при появлении тега из этой группы внутри текущего разбор текущего тега считается завершённым успешно) или FuraxBB_ThrowableEndTag (разбор тега считается произведённым неверно).
	{
		$this->endTags[$this->getFuraxBB()->generateTagIndex($Name, $Type)] = ($EndTagType ? $EndTagType : ($Type == FuraxBB_EndTag ? FuraxBB_LocalEndTag : FuraxBB_BubblingEndTag));
	}

	public function allowedLineEnd() //Разрешено ли тегу закончиться вместе с исходной строкой, т. е. без закрывающего тега
	{
		return $this->lineEndAllowed;
	}

	public function allowedParentEndTags() //Производится ли наследование закрывающих тегов типов FuraxBB_LocalEndTag и FuraxBB_BubblingEndTag
	{
		return $this->parentEndTagsAllowed;
	}

	protected function checkOpeningTag($Tag) //Проверка открывающего тега на допустимость параметров; если такая проверка производится, она должна быть сделана в дочернем классе в этом переопределённом методе. Метод должен возвращать true (параметры допустимы) или false (недопустимы).
	{
		return true;
	}

	public function compile($Method, $Compiler) //Заполнение функции - точки входа в обработку сущности
	{
		$this->compileDoubleTagParser($Compiler);

		$modifierData = $this->modifier->compileModifierData($Compiler); //Данные из модификатора, приведённые к виду массива "новый индекс => состояние" и даже уже сериализованные
		$specifiedEndTags = $this->compileEndTags($Compiler); //Закрывающие теги текущей сущности в уже сериализованном виде
		$parentEndTagsAllowed = (int)$this->parentEndTagsAllowed; //Разрешено ли наследование закрывающих тегов
		$lineEndAllowed = (int)$this->lineEndAllowed; //Разрешено ли тегу закончиться вместе с входной строкой, без закрывающего тега
		$parseTags = (int)$this->subTagsAllowed; //Выполняется ли разбор вложенных тегов

		$runnerData = $this->compileDoubleTag($Compiler, $Method); //Имя исполнителя текущего тега, его параметры и выражение проверки допустимости открывающего тега
		$runnerName = FuraxBBParserCompiler::serialize($runnerData->getName());
		$runnerArguments = FuraxBBParserCompiler::serialize($runnerData->getParameters());
		$checkExpression = $runnerData->getCheck();

		if ($checkExpression) //Если задано выражение проверки правильности открывающего тега, эта проверка добавляется в код точки входа
			$Method->addCode("if (!$checkExpression) return NULL;\r\n\t\t");
		//Точка входа вызывает основную функцию разбора двойных тегов, передавая ей имя исполнителя конкретного тега и его параметры
		$Method->addCode("return self::parseDoubleTag(\$text, \$states, \$extraData, \$tags, \$tag, \$processedTo, \$endTags, $modifierData, $specifiedEndTags, $parentEndTagsAllowed, $lineEndAllowed, $parseTags, $runnerName, $runnerArguments);");
	}

	abstract protected function compileDoubleTag($Compiler); //Метод, непосредственно компилирующий конкретный двойной тег в состав брутального парсера. Должен быть определён в дочернем классе. Должен вернуть объект FuraxBBFuraxBBDoubleTagRunnerData.

	private function compileDoubleTagParser($Compiler)
	{
		$this->createModifyEndTags($Compiler); //Создание функции, пересчитывающей массив закрывающих тегов для вложенных тегов
		$this->createParse($Compiler); //Создание основной функции, разбирающей двойные теги
	}

	private function compileEndTags($Compiler) //Создание списка определённых в сущности закрывающих тегов
	{
		$groups = $this->getFuraxBB()->getGroupsArray(); //Таблица групп и входящих в них тегов
		$oldToNew = $Compiler->getOldToNew(); //Таблица переходов от старых номеров сущностей к новым

		$endTags = array(); //Массив конечных тегов сущности
		foreach ($this->endTags as $group => $type)
			if (is_string($group) && isSet($groups[$group])) //Сначала обрабатываются группы
				foreach ($groups[$group] as $algorithm => $meanless)
					if (isSet($oldToNew[$algorithm])) //Обрабатываются только теги, входящие в сборку
						$endTags[$oldToNew[$algorithm]] = $type;
		foreach ($this->endTags as $algorithm => $type)
			if (is_int($algorithm) && isSet($oldToNew[$algorithm])) //После групп обрабатываются отдельные закрывающие теги
				$endTags[$oldToNew[$algorithm]] = $type;

		return FuraxBBParserCompiler::serialize($endTags); //Массив сразу же сериализуется для добавления в код функции
	}

	protected function createEndTag($Name, $EndTagType)
	{
		$this->endTags[$this->getFuraxBB()->createEndTag($Name)] = $EndTagType;
		$this->modifier->toggleTag($Name, FuraxBB_EndTag, FuraxBB_Enabled);
	}

	private function createModifyEndTags($Compiler) //Создание функции, пересчитывающей массив текущих закрывающих тегов при разборе вложенных тегов
	{
		$modifyEndTags = $Compiler->addMethodOnce('modifyEndTags');
		if (!$modifyEndTags) //Функция уже создана
			return;

		$oldTags = $modifyEndTags->addParameter('oldTags'); //Набор закрывающих тегов, пришедший из вышестоящего цикла разбора
		$newTags = $modifyEndTags->addParameter('newTags'); //Набор тегов, определённых непосредственно в разбираемой сущности
		$parentEndTagsAllowed = $modifyEndTags->addParameter('parentEndTagsAllowed'); //Разрешено ли наследование закрывающих тегов FuraxBB_LocalEndTag и FuraxBB_BubblingEndTag

		//Константы
		$notEnd = FuraxBB_NotEndTag;
		$local = FuraxBB_LocalEndTag;
		$bubbling = FuraxBB_BubblingEndTag;
		$unembeddable = FuraxBB_UnembeddableEndTag;
		$throwable = FuraxBB_ThrowableEndTag;

		$code = <<< CODE_END
\$endTags = array();
		\$changedStates = array($notEnd => $notEnd, $local => \$parentEndTagsAllowed*$bubbling, $bubbling => \$parentEndTagsAllowed*$bubbling, $unembeddable => $throwable, $throwable => $throwable);
		foreach (\$oldTags as \$tag => \$state)
			if (\$changedStates[\$state])
				\$endTags[\$tag] = \$changedStates[\$state];
		return \$newTags + \$endTags;
CODE_END;
		$modifyEndTags->addCode($code);
	}

	private function createParse($Compiler) //Создание основной функции разбора двойных тегов
	{
		$parse = $Compiler->addMethodOnce('parseDoubleTag');
		if (!$parse) //Функция уже создана
			return;

		$text = $parse->addParameter('text'); //Разбираемый текст
		$states = $parse->addParameter('states'); //Состояния алгоритмов
		$extraData = $parse->addParameter('extraData'); //Дополнительные данные, переданные функции run()
		$tags = $parse->addParameter('tags'); //Теги, содержащиеся во входной строке
		$tag = $parse->addParameter('tag', true); //Позиция текущего разбираемого тега в массиве $tags
		$processedTo = $parse->addParameter('processedTo', true); //Позиция в исходной строке, до которой произведён разбор
		$endTags = $parse->addParameter('endTags'); //Массив закрывающих тегов родительского цикла разбора
		$modifierData = $parse->addParameter('modifierData'); //Состояния алгоритмов из модификатора контекста, принадлежащего разбираемой сущности
		$specifiedEndTags = $parse->addParameter('specifiedEndTags'); //Закрывающие теги, принадлежащие разбираемой сущности
		$parentEndTagsAllowed = $parse->addParameter('parentEndTagsAllowed'); //Разрешено ли наследование закрывающих тегов типов FuraxBB_LocalEndTag и FuraxBB_BubblingEndTag
		$lineEndAllowed = $parse->addParameter('lineEndAllowed'); //Разрешено ли текущему тегу закончиться вместе с концом строки, без закрывающего тега
		$parseTags = $parse->addParameter('parseTags'); //Производится ли разбор вложенных тегов
		$runnerName = $parse->addParameter('runnerName'); //Имя исполнителя текущего тега
		$runnerArguments = $parse->addParameter('runnerArguments'); //Параметры исполнителя текущего тега

		$code = <<< CODE_END
\$internalTag = \$tag+1;
		\$internalProcessedTo = \$tags[\$tag][2];
		\$subParsingResult = self::parseCycle(\$text, self::modifyStates(\$states, \$modifierData), \$extraData, \$tags, \$internalTag, \$internalProcessedTo, self::modifyEndTags(\$endTags, \$specifiedEndTags, \$parentEndTagsAllowed), \$lineEndAllowed, \$parseTags);
		if (\$subParsingResult === NULL) return NULL;
		\$result = self::\$runnerName(\$tags[\$tag][3], \$subParsingResult, \$extraData, \$runnerArguments);
		if (\$result === NULL) return NULL;
		\$result = self::process(\$text, \$processedTo, \$tags[\$tag][1], \$states, \$extraData) . \$result;
		\$tag = \$internalTag;
		\$processedTo = \$internalProcessedTo;
		return \$result;
CODE_END;
		$parse->addCode($code);
	}

	public function getEndTags() //Возвращает массив закрывающих тегов данной сущности
	{
		return $this->endTags;
	}

	public function getModifier() //Возвращает модификатор контекста, применяемый данной сущностью
	{
		return $this->modifier;
	}

	public function parse($Tag, $ParsingCycle) //Непосредственный разбор тега
	{
		$extraParameters = $ParsingCycle->getContext()->getExtraParameters(); //Дополнительные данные, переданные функции run()

		if (! $this->checkOpeningTag($Tag, $extraParameters)) //Открывающий тег недопустим
			return false;

		$subCycle = new FuraxBBParsingCycle($ParsingCycle, $this, $Tag); //Вложенный цикл разбора

		$result = $subCycle->parse($this->subTagsAllowed);
		if ($result === NULL) //Не удалось разобрать вложенный цикл
			return false;

		$result = $this->parseDoubleTag($Tag, $result, $extraParameters);
		if ($result === NULL) //Не удалось разобрать тег
			return false;
		else
		{
			$ParsingCycle->process($Tag->getPosition(), $result, $subCycle->getProcessedTo(), $subCycle->getCurrentTag()); //Во внешний цикл добавляется результат разбора цикла вложенного, указатели устанавливаются в соответствии с таковыми из вложенного цикла
			return true;
		}
	}

	abstract protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters); //Функция непосредственного разбора двойного тега. Она принимает объект тега, его содержимое, уже пропущенное через последующие действия, и дополнительные параметры, принятые методом run(). Она должна быть определена в дочернем классе. Она должна возвращать NULL в случае неудачного разбора тега или строку - результат разбора - в случае удачи.


	private $parentEndTagsAllowed; //Разрешено ли наследование закрывающих тегов типов FuraxBB_LocalEndTag и FuraxBB_BubblingEndTag
	private $lineEndAllowed; //Разрешено ли тегу закончиться вместе с входной строкой, без закрывающего тега

	private $endTags = array(); //Закрывающие теги текущей сущности и их типы

	protected $modifier; //Модификатор текущей сущности
	private $subTagsAllowed; //Разрешён ли разбор вложенных тегов
}


?>