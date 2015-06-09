<?php
/*
Файл actions.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение базового класса FuraxBBAction, описывающего действие в составе парсера, а также все его производные классы, описывающие включенные в пакет действия, кроме класса FuraxBBSmilesParser, осуществляющего замену смайлов; последний вынесен в файл smiles.php вместе со вспомогательными классами.
*/


/*
Класс FuraxBBAction описывает действие в составе парсера. Он абстрактен, поскольку методы, отвечающие за выполнение и компиляцию действия, должны быть определены в дочернем классе. Он производен от FuraxBBAlgorithm, поскольку действие является алгоритмом.
*/
abstract class FuraxBBAction extends FuraxBBAlgorithm
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$ID - идентификатор действия (строковый, уникальный);
		$State - состояние по умолчанию (FuraxBB_Forbidden, FuraxBB_Disabled либо FuraxBB_Enabled);
		$NextAction - указатель на действие либо идентификатор действия, перед которым должно выполняться данное; действие добавляется в конец очереди, если этот параметр опущен.
	*/
	protected function __construct($FuraxBB, $ID, $State, $NextAction = FALSE)
	{
		parent :: __construct($FuraxBB, $ID, $State);
		$this->getFuraxBB()->addAction($this, $NextAction);
	}

	/*
	Метод компиляции действия в состав брутального парсера; должен быть определён в дочернем классе. Аргументы:
		$Action - функция, в которую требуется занести алгоритм действия;
		$Compiler - компилятор, в состав которого входит этот метод;
		$Index - индекс действия в компиляторе.
	*/
	abstract public function compile($Action, $Compiler, $Index);

	/*
	Метод запуска действия; должен быть определён в дочернем классе. Аргументы:
		$Text - текст, над которым производится действие;
		$ProcessFollowingActions - установка этого флага в состояние false отменяет выполнение последующих действий;
		$Context - контекст, согласно которому выполняются действия.
	*/
	abstract public function run($Text, &$ProcessFollowingActions, $Context);

	public function runSubActions($Text, $Context) //Вызов действий, находящихся в списке действий после текущего, применительно к тексту $Text по правилам из контекста $Context.
	{
		return $this->getFuraxBB()->runSubActions($Text, $this->actionIndex + 1, $Context);
	}

	public function setActionIndex($Index) //Присваивание действию индекса; всегда производится до вызова действия.
	{
		$this->actionIndex = $Index;
	}


	private $actionIndex; //Номер действия в очереди выполняемых в данный момент действий.
}


/*
Класс FuraxBBTagsParser описывает действие разбора bb-тегов. Собственно говоря, это основное действие парсера.
*/
class FuraxBBTagsParser extends FuraxBBAction
{
	public function __construct($FuraxBB, $Caseless) //Второй аргумент конструктора - нечувствительность имён тегов и параметров тегов к регистру
	{
		parent :: __construct($FuraxBB, 'tagsParser', FuraxBB_Enabled);

		$this->caseless = $Caseless;
	}

	public function addEntity($Entity) //Добавление сущности в парсер.
	{
		$this->entities[$Entity->getIndex()] = $Entity;
	}

	public function compile($Action, $Compiler, $Index) //Компиляция парсера тегов в состав брутального парсера.
	{
		$this->fillEntitiesIndexes($Compiler);
		$this->createProcess($Compiler, $Index);
		$this->createParseCycle($Compiler);
		$this->compileRun($Action, $Compiler, $Index);

		$Compiler->fillEntities($this->entities);
	}

	public function compileConvertCase($VariableName) //Компиляция функции приведения регистра.
	{
		return ($this->caseless ? "strToLower(\$$VariableName)" : "\$$VariableName");
	}

	private function compileRun($Action, $Compiler, $Index) //Компиляция функции запуска действия, вызываемой непосредственно из очереди действий.
	{
		$regexp = FuraxBBParserCompiler::serialize($this->getFuraxBB()->getTagRegularExpression()); //Регулярное выражение bb-тега
		$parameterRegex = FuraxBBParserCompiler::serialize($this->getFuraxBB()->getListedParameterRegularExpression()); //Регулярное выражение параметра bb-тега (одного из параметров в списке)

		$lowerCaseMatches1 = $this->compileConvertCase("parametersMatches[\$parameter][1]"); //Выражение приведения к нужному регистру имени открывающего тега
		$lowerCaseMatch40 = $this->compileConvertCase("match[4][0]"); //Выражение приведения к нужному регистру имени закрывающего тега

		$code = <<< CODE_END
if (! preg_match_all($regexp, \$text, \$matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) return \$text;
		\$tags = array();
		foreach (\$matches as \$match) {
			if (strlen(@\$match[1][0])) {
				\$name = \$match[1][0];
				if (strlen(@\$match[2][0])) {
					if (\$index = @self::\$singleParameterTags[\$name]) \$tags[] = array(\$index, \$match[0][1], \$match[0][1]+strlen(\$match[0][0]), \$match[2][0]);
				}
				elseif (@\$match[3][0]) {
					if (\$index = @self::\$listedParametersTags[\$name]) {
						\$parameters = array();
						\$parametersNumber = preg_match_all($parameterRegex, \$match[3][0], \$parametersMatches, PREG_SET_ORDER);
						for (\$parameter = 0; \$parameter < \$parametersNumber; ++\$parameter) \$parameters[$lowerCaseMatches1] = \$parametersMatches[\$parameter][2];
						\$tags[] = array(\$index, \$match[0][1], \$match[0][1]+strlen(\$match[0][0]), \$parameters);
					}
				}
				elseif (\$index = @self::\$noParametersTags[\$name]) \$tags[] = array(\$index, \$match[0][1], \$match[0][1]+strlen(\$match[0][0]), NULL);
			} else {
				\$name = $lowerCaseMatch40;
				if (\$index = @self::\$endTags[\$name]) \$tags[] = array(\$index, \$match[0][1], \$match[0][1]+strlen(\$match[0][0]), NULL);
			}
		}
		if (\$tags) {
			\$running = false;
			\$tag=0;
			\$processedTo=0;
			return self::parseCycle(\$text, \$states, \$extraData, \$tags, \$tag, \$processedTo, array(), true, true);
		}
		else return \$text;
CODE_END;
		$Action->addCode($code); //Код функции добавляется в функцию
	}

	public function convertCase($Text) //Приведение имени тега или имени параметра тега к нужному регистру
	{
		return ($this->caseless ? strtolower($Text) : $Text); //Если задана независимость от регистра, все имена приводятся к нижнему регистру; в противном случае имена оставляются без изменений.
	}

	public function createEndTag($Name) //Создание закрывающего тега [/$Name]
	{
		$index = $this->getFuraxBB()->generateTagIndex($Name, FuraxBB_EndTag);
		if (!isSet($this->entities[$index])) //Создаём тег только в том случае, если тег ещё не существует
			new FuraxBBEndTagEntity($this->getFuraxBB(), $Name);
		return $index;
	}

	private function createParseCycle($Compiler) //Компиляция функции цикла разбора тегов
	{
		$parseCycle = $Compiler->addMethod('parseCycle'); //Фукнция цикла разбора тегов

		$parseCycle->addParameter('text'); //Разбираемый текст
		$parseCycle->addParameter('states'); //Состояния алгоритмов в цикле разбора
		$parseCycle->addParameter('extraData'); //Дополнительные пользовательские данные
		$parseCycle->addParameter('tags'); //Теги, содержащиеся в исходной строке
		$parseCycle->addParameter('tag', true); //Текущий разбираемый тег
		$parseCycle->addParameter('processedTo', true); //Место в исходной строке, до которого произведён разбор
		$parseCycle->addParameter('endTags'); //Индексы и типы разрешённых закрывающих тегов
		$parseCycle->addParameter('lineEndAllowed'); //Может ли этот цикл закончиться концом входной строки
		$parseCycle->addParameter('parseTags'); //Производится ли в этом цикле разбор открывающих тегов

		//Константы типов закрывающих тегов
		$localEndTag = FuraxBB_LocalEndTag;
		$unembeddableEndTag = FuraxBB_UnembeddableEndTag;
		$bubblingEndTag = FuraxBB_BubblingEndTag;
		$throwableEndTag = FuraxBB_ThrowableEndTag;
		$enabled = FuraxBB_Enabled;

		$code = <<< CODE_END
\$result = '';
		while (\$tag < count(\$tags)) {
			switch (@\$endTags[\$tags[\$tag][0]]) {
				case $localEndTag: case $unembeddableEndTag:
					\$result .= self::process(\$text, \$processedTo, \$tags[\$tag][1], \$states, \$extraData);
					\$processedTo = \$tags[\$tag][2];
					++\$tag;
					return \$result;
				case $bubblingEndTag:
					\$result .= self::process(\$text, \$processedTo, \$tags[\$tag][1], \$states, \$extraData);
					\$processedTo = \$tags[\$tag][1];
					return \$result;
				case $throwableEndTag:
					return NULL;
			}
			\$success = NULL;
			if (\$parseTags && \$states[\$tags[\$tag][0]] == $enabled) {
				\$method = 'entity'.\$tags[\$tag][0];
				\$success = self::\$method(\$text, \$states, \$extraData, \$tags, \$tag, \$processedTo, \$endTags);
			}
			if (\$success === NULL) ++\$tag;
			else \$result .= \$success;
		}
		if (\$lineEndAllowed) {
			\$result .= self::process(\$text, \$processedTo, strlen(\$text), \$states, \$extraData);
			\$processedTo = strlen(\$text);
			return \$result;
		} else return NULL;
CODE_END;
		$parseCycle->addCode($code);
	}

	private function createProcess($Compiler, $Index) //Создание функции разбора текста между тегами с помощью действий, стоящих в очереди после парсера тегов
	{
		$process = $Compiler->addMethod('process'); //Функция разбора текста

		$process->addParameter('text'); //Разбираемый текст
		$process->addParameter('processFrom'); //Позиция, с которой необходимо начать разбор
		$process->addParameter('processTo'); //Позиция, которой разбор заканчивается
		$process->addParameter('states'); //Состояния алгоритмов при текущем разборе
		$process->addParameter('extraData'); //Дополнительные пользовательские данные

		++$Index; //Номер текущего действия в очереди увеличивается, поскольку новый цикл разбора начинается не с текущего действия, а со следующего
		$process->addCode("return self::runSubActions(substr(\$text, \$processFrom, \$processTo-\$processFrom), $Index, \$states, \$extraData);");
	}

	private function fillEntitiesIndexes($Compiler) //Создание массивов, в которых задаётся соответствие между именами тегов и их индексами в системе
	{
		$tags = array(FuraxBB_NoParameters => array(), FuraxBB_SingleParameter => array(), FuraxBB_ListedParameters => array(), FuraxBB_EndTag => array()); //Массив нужных массивов
		foreach ($Compiler->getNewToOld() as $newIndex => $oldIndex)
			if (isSet($this->entities[$oldIndex])) //Соответствие задаётся только для тегов, включаемых в текущую сборку
				$tags[$this->entities[$oldIndex]->getType()][$this->entities[$oldIndex]->getName()] = $newIndex;

		//Добавление массивов в класс брутального парсера
		$Compiler->addProperty('noParametersTags', $tags[FuraxBB_NoParameters]);
		$Compiler->addProperty('singleParameterTags', $tags[FuraxBB_SingleParameter]);
		$Compiler->addProperty('listedParametersTags', $tags[FuraxBB_ListedParameters]);
		$Compiler->addProperty('endTags', $tags[FuraxBB_EndTag]);
	}

	public function getEntities() //Получение массива известных системе сущностей
	{
		return $this->entities;
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск парсера тегов на исполнение
	{
		$ProcessFollowingActions = false; //В текущем цикле дальнейшие действия не выполняются, поскольку они будут запущены отдельно только для текста, находящегося между тегами, и по правилам, определяемым тегами

		$input = new FuraxBBInputData($Text, $this->entities, $this->getFuraxBB()); //Набор входных данных
		$cycle = new FuraxBBParsingCycle($input, $Context, $this); //Цикл разбора

		return $cycle->parse(); //Осуществляется разбор тегов верхнего уровня (не вложенных в другие теги)
	}


	private $entities; //Сущности, известные парсеру
	private $caseless; //Независимость имён тегов и параметров тегов от регистра
}


/*
Класс FuraxBBTextStopper описывает действие, не пропускающее никакой текст (всегда возвращающее пустую строку). Это действие используется там, где текст не разрешён - например, между тегами [table] и [td].
*/
class FuraxBBTextStopper extends FuraxBBAction
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, 'textStopper', FuraxBB_Disabled); //По умолчанию это действие находится в состоянии FuraxBB_Disabled, и может быть активировано одним из выполненных ранее алгоритмов - например, сущностью table
	}

	public function compile($Action, $Compiler, $Index) //Компиляция действия в состав брутального парсера
	{
		//Алгоритм полностью аналогичен алгоритму из run()
		$Action->addCode("\$running = false;\r\n\t\treturn '';");
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		$ProcessFollowingActions = false; //Поскольку результат - пустая строка - известен заранее, необходимости в запуске последующих действий нет
		return '';
	}
}


/*
Класс FuraxBBSimpleFunctionAction описывает действие, осуществляемое над тексом путём вызова одноаргументной функции - например, htmlSpecialChars().
*/
class FuraxBBSimpleFunctionAction extends FuraxBBAction
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на кавайный парсер;
		$ID - идентификатор действия (строковый, уникальный);
		$Function - имя вызываемой функции;
		$NextAction - действие, перед которым нужно выполнить данное (если этот аргумент опущен, действие добавляется в конец очереди).
	*/
	public function __construct($FuraxBB, $ID, $Function, $NextAction = NULL)
	{
		parent :: __construct($FuraxBB, $ID, FuraxBB_Enabled, $NextAction);

		$this->function = $Function;
	}

	public function compile($Action, $Compiler, $Index) //Компиляция действия в состав брутального парсера
	{
		$Action->addCode("return $this->function(\$text);");
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		$function = $this->function; //Эта переменная необходима, поскольку выражение $this->function($Text) означало бы вызов метода, а не функции с именем, совпадающем со значением свойства
		return $function($Text);
	}

	private $function; //Имя вызываемой действием функции
}


/*
Класс FuraxBBProcessingStopper описывает действие, прерывающую всякую дальнейшую обработку текста. Например, внутри тега [url] без параметров выделение ссылок и адресов, а также подстановка смайлов осуществляться не должна.
*/
class FuraxBBProcessingStopper extends FuraxBBAction
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, 'processingStopper', FuraxBB_Disabled);
	}

	public function compile($Action, $Compiler, $Index) //Компиляция действия в состав брутального парсера
	{
		//Алгоритм аналогичен алгоритму из run()
		$Action->addCode("\$running = false;\r\n\t\treturn \$text;");
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		$ProcessFollowingActions = false; //Дальнейшие действия не выполняются
		return $Text; //Текст возвращается без изменений
	}
}


/*
Класс FuraxBBBreaksInserter описывает действие, производящее замену символов перевода строки соответствующими тегами.
*/
class FuraxBBBreaksInserter extends FuraxBBAction
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, 'breaksInserter', FuraxBB_Enabled);
	}

	public function compile($Action, $Compiler, $Index) //Компиляция действия в состав брутального парсера
	{
		$newLineCharacter = FuraxBBParserCompiler::serialize($this->newLineCharacter); //Символ новой строки
		$breakTag = FuraxBBParserCompiler::serialize($this->breakTag); //Тег новой строки

		$Action->addCode("return str_replace($newLineCharacter, $breakTag, \$text);");
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		return str_replace($this->newLineCharacter, $this->breakTag, $Text); //Простая замена
	}

	public function setParameters($NewLineCharacter, $BreakTag) //Изменение параметров действия
	{
		$this->newLineCharacter = $NewLineCharacter;
		$this->breakTag = $BreakTag;
	}


	private $newLineCharacter = "\r\n"; //Символ новой строки
	private $breakTag = "<br>\r\n"; //Тег новой строки
}


/*
Класс FuraxBBLinksParser описывает действие, осуществляющее подсветку ссылок и адресов электронной почты в разбираемом тексте.
*/
class FuraxBBLinksParser extends FuraxBBAction
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, 'linksParser', FuraxBB_Enabled);
	}

	public function compile($Action, $Compiler, $Index) //Компиляция действия в состав брутального парсера
	{
		$link = FuraxBBParserCompiler::serialize($this->getFuraxBB()->getLinkRegularExpression()); //Регулярное выражение URL-адреса
		$linkTemplate = FuraxBBParserCompiler::serialize($this->linkTemplate); //Шаблон, по которому осуществляется замена адреса соответствующим тегом
		$email = FuraxBBParserCompiler::serialize($this->getFuraxBB()->getEmailRegularExpression()); //Регулярное выражение адреса электронной почты
		$emailTemplate = FuraxBBParserCompiler::serialize($this->emailTemplate); //Шаблон, по которому осуществляется замена адреса тегом

		$Action->addCode("return preg_replace($link, $linkTemplate, preg_replace($email, $emailTemplate, \$text));"); //Две последовательных замены
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		$Text = preg_replace($this->getFuraxBB()->getLinkRegularExpression(), $this->linkTemplate, $Text); //Замена URLов гиперссылками
		$Text = preg_replace($this->getFuraxBB()->getEmailRegularExpression(), $this->emailTemplate, $Text); //Замена адресов электронной почты гиперссылками

		return $Text;
	}

	public function setTemplates($LinkTemplate, $EmailTemplate) //Установка шаблонов замены (например, с целью изменения CSS-класса ссылок, добавления картинов a la Wikipeida или прописывания событий мыши для ссылок); изменение регулярных выражений не производится
	{
		$this->linkTemplate = $LinkTemplate;
		$this->emailTemplate = $EmailTemplate;
	}


	private $linkTemplate = '<a href="\0" target="_blank">\0</a>'; //Шаблон замены URLов гиперссылками
	private $emailTemplate = '<a href="mailto:\0">\0</a>'; //Шаблон замены E-mailов гиперссылками
}


/*
Класс FuraxBBHTMLSafe описывает действие, защищающее URLы, HTML-теги и HTML-сущности от вмешательства последующих действий - например, от замены их частей на смайлы. Это предотвращает выделение смайла ;) в строке "журналы (например, &quot;Мурзилка&quot;)", выделение смайла :/ в строке "http://furax.narod.ru/furaxkawaibb/" и тому подобные нежелательные эффекты.
*/
class FuraxBBHTMLSafe extends FuraxBBAction
{
	public function __construct($FuraxBB, $State) //Второй аргумент конструктора - состояние, в котором находится это действие по умолчанию
	{
		parent :: __construct($FuraxBB, 'HTMLSafe', $State);
	}

	public function compile($Action, $Compiler, $Index) //Компиляция действия в состав брутального парсера
	{
		$regexp = FuraxBBParserCompiler::serialize($this->unsafeContents); //Регулярное выражение защищаемого содержимого
		++$Index; //Индекс действия увеличивается, поскольку запуск последующих действий начинается не с текущего действия, а со следующего

		$code = <<< CODE_END
\$matches = array();
		if (! preg_match_all($regexp, \$text, \$matches, PREG_OFFSET_CAPTURE)) return \$text;
		\$end = 0;
		\$result = '';
		foreach (\$matches[0] as \$match) {
			list(\$string, \$position) = \$match;
			\$result .= self::runSubActions(substr(\$text, \$end, \$position-\$end), $Index, \$states, \$extraData) . \$string;
			\$end = \$position + strlen(\$string);
		}
		\$result .= self::runSubActions(substr(\$text, \$end), $Index, \$states, \$extraData);
		\$running = false;
		return \$result;
CODE_END;
		$Action->addCode($code);
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		$matches = array();
		if (! preg_match_all($this->unsafeContents, $Text, $matches, PREG_OFFSET_CAPTURE))
			return $Text; //Если в строке нет опасного содержимого - дальнейшие действия выполняются как обычно

		$end = 0; //Позиция в исходной строке, до которой уже произведён разбор
		$result = ''; //Результат разбора

		foreach ($matches[0] as $match) //Все вхождения опасного содержимого обрабатываютя индивидуально
		{
			list($string, $position) = $match; //Данные об опасном содержимом

			$result .= $this->runSubActions(substr($Text, $end, $position-$end), $Context); //Выполняются последующие действия над безопасной подстрокой между двумя опасными подстроками (или между началом строки и опасной подстрокой)
			$result .= $string;
			$end = $position + strlen($string); //Сдвижка позиции конца обработки
		}

		$result .= $this->runSubActions(substr($Text, $end), $Context); //Обработка безопасной строки между 
		$ProcessFollowingActions = false; //Вызов дальнейших действий не нужен, поскольку они уже были вызваны - для безопасного содержимого
		return $result;
	}

	private $unsafeContents = "/(?:<(?:[^>]|(?:'[^']*')|(?:\"[^\"]\")>)|(?:(?:https?)|(?:ftp))\\:\\/\\/\S+)|(?:&(?:(?:#\d+)|\w+);)/u"; //Регулярное выражение опасного содержимого: тега, сущности или URLа.
}


?>