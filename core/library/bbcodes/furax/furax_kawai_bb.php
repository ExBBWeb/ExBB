<?php
/*
Файл furax_kawai_bb.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение класса кавайного парсера.
*/


/*
Класс FuraxKawaiBB описывает кавайный парсер, который представляет собой программный интерфейс для задания параметров парсера bb-тегов, а также стенд для тестирования работы парсера и сборки брутального парсера с фиксированными настройками.
*/
class FuraxKawaiBB
{
	/*
	Аргументы конструктора:
		$SmilesDefaultPath - путь к смайлам, входящим в группу 'default', с которого начинается src картинок-смайлов (слэш в конце пути к каталогу обязателен; путь может также включать приставку, например: "/images/smile_", если смайлы имеют имена smile_1.gif, smile_2.gif и так далее);
		$SpacedSmiles - должны ли смайлы отбиваться пробелами с обеих сторон (рекомендуется иметь хотя бы один параметр из $SpacedSmiles и $HTMLSafe истинным, поскольку иначе возможны ошибки разбора смайлов, приводящие к формированию неверного HTML-кода, коими страдает большая часть форумных движков);
		$HTMLSafe - нужно ли выполнять действие HTMLSafe (переводить в состояние FuraxBB_Enabled), защищающее HTML-теги, HTML-сущности и URLы от выделения в них смайлов (и других подобных операций, если они осуществляются после действия HTMLSafe). Замедляет работу парсера, но гарантирует, что HTML-код не будет испорчен в результате неправильных замен (например, в выражении "&quot;)" присутствует смайл ";)", а в адресе "http://furax.narod.ru/" - смайл ":/", которые могут быть заменены, если отключены HTMLSafe и обязательность отбивания смайлов пробелами;
		$Caseless - независимость имён bb-тегов и имён их параметров (но не значений параметров) от регистра;
		$CreateDefaultEntities - нужно ли добавлять в парсер включенный в него по умолчанию набор тегов (или же все теги должны будут быть созданы вручную).
	*/
	public function __construct($SmilesDefaultPath = '', $SpacedSmiles = true, $HTMLSafe = false, $Caseless = true, $CreateDefaultEntities = true)
	{
		$this->addParametersSet('default'); //Набор параметров по умолчанию

		$this->createDefaultActions($SmilesDefaultPath, $SpacedSmiles, $HTMLSafe, $Caseless); //Создание действий, включенных в сборку парсера
		if ($CreateDefaultEntities)
		{
			$this->createDefaultEntities(); //Создание тегов, включенных в сборку
		}
	}

	public function addAction($Action, $NextAction = FALSE) //Регистрация действия в парсере; эта функция НЕ должна вызываться пользователем парсера - она вызывается автоматически из конструктора класса FuraxBBAction.
	{
		$this->actions[$Action->getID()] = $Action;

		if ($NextAction) //В $NextAction загоняется индекс действия в массиве очерёдности действий
		{
			if (is_string($NextAction))
				$NextAction = $this->actions[$NextAction];
			$NextAction = array_search($NextAction, $this->orderedActions);
		}

		if (is_numeric($NextAction)) //Действие добавляется в определённое место массива упорядоченных действий
		{
			$orderedActions = array_slice($this->orderedActions, 0, $NextAction);
			$orderedActions[] = $Action;
			$this->orderedActions = array_merge($orderedActions, array_slice($this->orderedActions, $NextAction));
		}
		else //Действие добавляется в конец массива упорядоченных действий
			$this->orderedActions[] = $Action;
	}

	public function addAlgorithm($Algorithm, $State) //Регистрация алгоритма в парсере; эта функция НЕ должна вызываться пользователем парсера - она автоматически вызывается из конструктора класса FuraxBBAlgorithm.
	{
		$this->states[$Algorithm->getIndex()] = $State;

		if (isSet($this->algorithms[$Algorithm->getIndex()])) //Модификаторы алгоритмов, более не принадлежащих парсеру (переопределённых), удаляются
			foreach ($this->algorithms[$Algorithm->getIndex()]->getModifiers() as $modifier)
				unSet($this->modifiers[array_search($modifier, $this->modifiers)]);

		$this->algorithms[$Algorithm->getIndex()] = $Algorithm;
	}

	public function addContextModifier($Modifier) //Регистрация модификатора контекста в парсере; эта функция НЕ должна вызываться пользователем парсера - она автоматически вызывается из конструктора класса FuraxBBContextModifier.
	{
		$this->modifiers[] = $Modifier;
	}

	public function addEntity($Entity) //Регистрация сущности в парсере; эта функция НЕ должна вызываться пользователем парсера - она автоматически вызывается из конструктора класса FuraxBBEntity.
	{
		$this->actions['tagsParser']->addEntity($Entity);
	}

	public function addGroup($Name) //Создание группы тегов с именем $Name
	{
		if (!isSet($this->groups[$Name]))
			$this->groups[$Name] = array();
	}

	public function addParametersSet($Name) //Создание набора параметров с именем $Name; возвращает созданный набор параметров
	{
		if (isSet($this->parametersSets[$Name]))
			return $this->parametersSets[$Name];
		else
			return $this->parametersSets[$Name] = new FuraxBBParsingParametersSet($this);
	}

	/*
	Добавление смайла в парсер. Аргументы:
		$Code - код смайла (например, ';)');
		$FileName - имя файла смайла в директории, в которой находятся смайлы из набора, к которому он относится;
		$Alt - замещающий текст смайла (если не указан, используется замещающий текст набора);
		$Width - ширина смайла (если не указана, производится попытка прочитать файл смайла и получить его ширину, а при неудаче используется заданное для всего набора значение);
		$Height - высота смайла (обрабатывается аналогично ширине);
		$SmilesSet - набор смайлов, к которому относится добавляемый смайл (по умолчанию - 'default').
	*/
	public function addSmile($Code, $FileName, $Alt = '', $Width = 0, $Height = 0, $SmilesSet = 'default')
	{
		$this->actions['smilesParser']->setSmile($Code, $FileName, $Alt, $Width, $Height, $SmilesSet);
	}

	public function addSmileHTML($Code, $HTML) //Добавление напрямую HTML-кода смайла, в который не производятся никакие подстановки
	{
		$this->actions['smilesParser']->setSmileHTML($Code, $HTML);
	}

	public function addToGroups($Algorithm, $Group1) //Добавление алгоритма в группы; созданные пользователем алгоритмы должны использовать НЕ этот метод, а методы FuraxBBAlgorithm::addToGroups() (для действий) и FuraxBBEntity::addToGroups() (для сущностей), которые и вызывают данный метод.
	{
		$arguments = func_num_args();

		for ($argument = 1; $argument < $arguments; ++$argument)
		{
			$parameter = func_get_arg($argument);
			if (is_string($parameter)) //Строковый аргумент - имя группы
				@ $this->groups[$parameter][$Algorithm->getIndex()] = true; //Если группа не существует, этот оператор создаёт её
			else //Массив - набор имён групп
				foreach ($parameter as $group)
					@ $this->groups[$group][$Algorithm->getIndex()] = true; //Если группа не существует, этот оператор создаёт её
		}
	}

	private function assignActionsIndexes() //Присваивание действиям инлексов из массива очерёдности; выполняется перед входом в цикл поочерёдного выполнения действий; используется для того, чтобы действия могли вызывать следующие после них, зная, с какого индекса следует начать
	{
		$actions = count($this->orderedActions);
		for ($action = 0; $action < $actions; ++$action)
			$this->orderedActions[$action]->setActionIndex($action);
	}

	private function calculateStartStates() //Вычисление состояний всех алгоритмов в каждом наборе параметров (потому что наборы параметров фиксируют лишь отклонения состояний параметров от состояний по умолчанию
	{
		$startStates = array();
		foreach ($this->parametersSets as $name => $set)
		{
			$context = new FuraxBBParsingContext($this, $this->states, $set); //Алгоритм вычисления состояний параметров по набору параметров заложен в классе контекста, поэтому создаётся временный контекст
			$startStates[$name] = $context->getStates();
		}
		return $startStates;
	}

	public function compile($ClassName = 'FuraxBrutalBB', $FileName = '') //Компиляция кавайного парсера в брутальный; $ClassName - имя класса брутального парсера, $FileName - файл, в который сохраняется код (если не задан, сохранения кода не происходит)
	{
		$compiler = new FuraxBBParserCompiler($this, $ClassName); //Компилятор - объект, накапливающий данные и код

		$this->listAlgorithms($compiler); //Составляется список алгоритмов, входящих в сборку, им присваиваются индексы
		$compiler->fillActions($this->algorithms); //Компилируются все входящие в сборку действия

		$code = $compiler->toString(); //Генерация кода класса
		if ($FileName) //Вывод файл
			return file_put_contents($FileName, "<?php\r\n$code\r\n?>");
		else //Прямой возврат
			return $code;
	}

	private function compileContextModifiers() //Компиляция (вычисление) всех модификаторов контекстов, принадлежащих алгоритмам парсера
	{
		foreach ($this->modifiers as $modifier)
			$modifier->compile();
	}

	public function convertCase($Text) //Приведение имён тегов и имён параметров тегов к нижнему регистру, если задана независимость этих имён от регистра
	{
		return $this->actions['tagsParser']->convertCase($Text);
	}

	private function createDefaultActions($SmilesDefaultPath, $SpacedSmiles, $HTMLSafe, $Caseless) //Создание действий, входящих в сборку кавайного парсера
	{
		new FuraxBBTagsParser($this, $Caseless);
		new FuraxBBTextStopper($this);
		new FuraxBBSimpleFunctionAction($this, 'specialChars', 'htmlSpecialChars');
		new FuraxBBProcessingStopper($this);
		new FuraxBBBreaksInserter($this);
		new FuraxBBLinksParser($this);
		new FuraxBBHTMLSafe($this, FuraxBB_Enabled * $HTMLSafe);
		new FuraxBBSmilesParser($this, $SpacedSmiles, $SmilesDefaultPath);
	}

	private function createDefaultEntities() //Создание сущностей, входящих в сборку кавайного парсера
	{
		//Косметические теги без параметров
		new FuraxBBSimpleCosmeticEntity($this, 'b', '<b>', '</b>');
		new FuraxBBSimpleCosmeticEntity($this, 'i', '<i>', '</i>');
		new FuraxBBSimpleCosmeticEntity($this, 'u', '<u>', '</u>');
		new FuraxBBSimpleCosmeticEntity($this, 'highlight', '<font style="background: yellow;">', '</font>');
		new FuraxBBSimpleCosmeticEntity($this, 'sub', '<sub>', '</sub>', true);
		new FuraxBBSimpleCosmeticEntity($this, 'sup', '<sup>', '</sup>', true);
		new FuraxBBSimpleCosmeticEntity($this, 's', '<strike>', '</strike>', false, array('strike' => FuraxBB_NoParameters));
		new FuraxBBSimpleCosmeticEntity($this, 'strike', '<strike>', '</strike>', false, array('s' => FuraxBB_NoParameters));

		//Косметические теги с одним параметром
		new FuraxBBParameteredCosmeticEntity($this, 'size', '<font style="font-size: $parameterpt;">', '</font>', '/^\d{1,3}$/u');
		new FuraxBBParameteredCosmeticEntity($this, 'color', '<font style="color: $parameter;">', '</font>', '/^(?:(?:rgb\(\d{1,3}\,\d{1,3}\,\d{1,3}\))|(?:#[0-9a-f]{3,6})|(?:[a-z]+))$/u');
		new FuraxBBParameteredCosmeticEntity($this, 'font', '<font style="font-family: &quot;$parameter&quot;;">', '</font>', '/^[a-z\-0-9]+(?: [a-z\-0-9]+)*$/u');

		//Теги выравнивания
		new FuraxBBAlignmentEntity($this, 'l', 'left');
		new FuraxBBAlignmentEntity($this, 'r', 'right');
		new FuraxBBAlignmentEntity($this, 'c', 'center');
		new FuraxBBAlignmentEntity($this, 'j', 'justify');
		new FuraxBBAlignmentEntity($this, 'left', 'left');
		new FuraxBBAlignmentEntity($this, 'right', 'right');
		new FuraxBBAlignmentEntity($this, 'center', 'center');
		new FuraxBBAlignmentEntity($this, 'justify', 'justify');

		//Ссылки типа [url]адрес_ссылки[/url]
		new FuraxBBSimpleLinkEntity($this, 'url', $this->getLinkOnlyRegularExpression(), '', true);
		new FuraxBBSimpleLinkEntity($this, 'email', $this->getEmailOnlyRegularExpression(), 'mailto:', false);

		//Ссылки типа [url=адрес_ссылки]содержимое_ссылки[/url]
		new FuraxBBEmbeddableLinkEntity($this, 'url', $this->getLinkOnlyRegularExpression(), '', true);
		new FuraxBBEmbeddableLinkEntity($this, 'email', $this->getEmailOnlyRegularExpression(), 'mailto:', false);

		//Списки
		new FuraxBBListWithoutParametersEntity($this);
		new FuraxBBParameteredListEntity($this);
		new FuraxBBListItemEntity($this);

		//Таблицы
		new FuraxBBTableWithoutParametersEntity($this);
		new FuraxBBTableWithParameterEntity($this);
		new FuraxBBTableRowWithoutParametersEntity($this);
		new FuraxBBParameteredTableRowEntity($this);
		new FuraxBBTableCellWithoutParametersEntity($this, 'td');
		new FuraxBBParameteredTableCellEntity($this, 'td');
		new FuraxBBTableCellWithoutParametersEntity($this, 'th');
		new FuraxBBParameteredTableCellEntity($this, 'th');

		//Модификаторы выполняемых действий (отключение смайлов, отключение всякой обработки)
		new FuraxBBActionsListModifierEntity($this, 'nosmiles', FuraxBB_Enabled, true, array('smilesParser' => FuraxBB_Forbidden));
		new FuraxBBActionsListModifierEntity($this, 'html', FuraxBB_Forbidden, false, array('specialChars' => FuraxBB_Forbidden, 'processingStopper' => FuraxBB_Enabled));
		new FuraxBBActionsListModifierEntity($this, 'nobb', FuraxBB_Enabled, false, array());

		//Горизонтальная черта
		new FuraxBBSingleTagWithoutParametersEntity($this, 'hr', FuraxBB_Enabled, '<hr>');

		//Логические блоки без параметров (цитаты, спойлеры, код и т. д.)
		new FuraxBBSemanticBlockWithoutParametersEntity($this, 'code', FuraxBB_Enabled, false, '<div><strong>Код:</strong><pre style="border: 1px solid #808080;">', '</pre></div>', array('processingStopper' => FuraxBB_Enabled));
		new FuraxBBSemanticBlockWithoutParametersEntity($this, 'quote', FuraxBB_Enabled, true, '<div><strong>Цитата:</strong><div style="border: 1px solid #808080;">', '</div></div>');
		new FuraxBBSemanticBlockWithoutParametersEntity($this, 'q', FuraxBB_Enabled, true, '<div><strong>Цитата:</strong><div style="border: 1px solid #808080;">', '</div></div>');
		new FuraxBBSemanticBlockWithoutParametersEntity($this, 'spoiler', FuraxBB_Enabled, true, "<div style=\"border: 1px solid #808080;\"><a href=\"javascript:void(0);\" onclick=\"if (this.nextSibling.style.display=='none') { this.nextSibling.style.display='block'; this.firstChild.nodeValue='Спойлер (спрятать содержимое)'; } else { this.nextSibling.style.display='none'; this.firstChild.nodeValue='Спойлер (показать содержимое)'; }\">Спойлер (показать содержимое)</a><div style=\"display: none;\">", '</div></div>');
		new FuraxBBSemanticBlockWithoutParametersEntity($this, 'off', FuraxBB_Enabled, true, '<div style="border: 1px solid #808080;"><strong>Оффтопик:</strong><br>', '</div>');
		new FuraxBBSemanticBlockWithoutParametersEntity($this, 'edit', FuraxBB_Enabled, false, '<textarea style="width: 100%;" cols="100" rows="10">', '</textarea>', array('processingStopper' => FuraxBB_Enabled));

		//Логические блоки с параметрами
		new FuraxBBParameteredSemanticBlockEntity($this, 'code', FuraxBB_Enabled, false, '/^[\w\s\+]+$/', '<div><strong>Код ($parameter):</strong><pre style="border: 1px solid #808080;">', '</pre></div>', array('processingStopper' => FuraxBB_Enabled));
		new FuraxBBParameteredSemanticBlockEntity($this, 'quote', FuraxBB_Enabled, true, '//', '<div><strong>$parameter пишет:</strong><div style="border: 1px solid #808080;">', '</div></div>');
		new FuraxBBParameteredSemanticBlockEntity($this, 'q', FuraxBB_Enabled, true, '//', '<div><strong>$parameter пишет:</strong><div style="border: 1px solid #808080;">', '</div></div>');

		//Картинки и т. п.
		new FuraxBBMediaWithoutParametersEntity($this, 'img', $this->getLinkOnlyRegularExpression(), '<img src="$contents" alt="Изображение">');
		new FuraxBBMediaSingleParameterEntity($this, 'img', $this->getLinkOnlyRegularExpression(), '<img src="$parameter" alt="Изображение">');
		new FuraxBBMediaListedParametersEntity($this, 'img', '<img src="$src" width="$width" height="$height" alt="$alt">', array('src' => $this->getLinkOnlyRegularExpression(), 'width' => '/^\d+$/', 'height' => '/^\d+$/', 'alt' => "/^[^'\"]*$/"));
	}

	public function createEndTag($Name) //Создание закрывающего тега с определённым именем. Эта функция НЕ должна вызываться пользователем; её вызов производится автоматически конструктором класса FuraxBBDoubleTagEntity.
	{
		return $this->actions['tagsParser']->createEndTag($Name);
	}

	private function enableSubalgorithms(&$States, $Algorithms) //Составление списка алгоритмов, которые находятся в состоянии FuraxBB_Disabled в массиве $States, но которые могут быть переведены в состояние FuraxBB_Enabled модификаторами, принадлежащими алгоритмам из массива $Algorithms, находящимся в состоянии FuraxBB_Enabled.
	{
		$enabled = array();
		foreach ($Algorithms as $algorithm => $state)
			if ($state == FuraxBB_Enabled)
				$enabled += $this->algorithms[$algorithm]->enableSubalgorithms($States);
		return $enabled;
	}

	public function generateIndex($ID) //Генерация номера алгоритма по его идентификатору
	{
		if (isSet($this->indexes[$ID])) //Индекс уже существует
			return $this->indexes[$ID];
		else //Генерация нового индекса
			return $this->indexes[$ID] = count($this->indexes)+1;
	}

	public function generateTagIndex($Name, $Type) //Генерация номера алгоритма по имени и типу тега
	{
		return $this->generateIndex($this->makeID($Name, $Type));
	}

	public function getAlignments() //Возвращает массив допустимых значений выравнивания
	{
		return $this->alignments;
	}

	public function getEmailOnlyRegularExpression() //Возвращает регулярное выражение, проверяющее, что сопоставляемая ему строка является адресом электронной почты
	{
		return '/^' . $this->getEmailRegularExpressionContents() . '$/ui';
	}

	public function getEmailRegularExpression() //Возвращает регулярное выражение, используемое для поиска адресов электронной почты в строке
	{
		return '/' . $this->getEmailRegularExpressionContents() . '/ui';
	}

	private function getEmailRegularExpressionContents() //Возвращает тело регулярного выражения адреса электронной почты
	{
		return '[a-z0-9\-_\.]+@(?:[a-z0-9\-_]+\.)+[a-z]{2,5}';
	}

	public function getEntities() //Возвращает массив зарегистрированных в парсере сущностей
	{
		return $this->actions['tagsParser']->getEntities();
	}

	public function getGroupsArray() //Возвращает набор массивов групп
	{
		return $this->groups;
	}

	public function getIndex($ID) //Возвращает номер алгоритма, соответствующий переданному идентификатору, или NULL, если алгоритм с таким идентификатором не известен парсеру
	{
		if (isSet($this->indexes[$ID])) //Алгоритм существует
			return $this->indexes[$ID];
		else
			return NULL;
	}

	public function getLinkOnlyRegularExpression() //Возвращает регулярное выражение, проверяющее, что сопоставляемая ему строка является URLом
	{
		return '/^' . $this->getLinkRegularExpressionContents() . '$/ui';
	}

	public function getLinkRegularExpression() //Возвращает регулярное выражение, используемое для поиска URLов в строке
	{
		return '/' . $this->getLinkRegularExpressionContents() . '/ui';
	}

	private function getLinkRegularExpressionContents() //Возвращает тело регулярного выражения URLа
	{
		return '(?:(?:https?)|(?:ftp))\:\/\/(?:(?:(?:[\w\-_]+\.)+[a-z]{2,4})|(?:\d{1,3}(?:\.\d{1,3}){3}))(?:\/[^\s\\;"'."'".']*)*';
	}

	public function getListedParameterRegularExpression() //Возвращает регулярное выражение, используемое для разбора параметров тега
	{
		return "/\\s+($this->parameterNameExpression)\\=($this->parameterValueExpression)(?=\\s|\$)/u";
	}

	public function getTagIndex($Name, $Type) //Возвращает индекс тега по его имени и типу, или NULL, если такой тег не зарегистрирован в парсере
	{
		return $this->getIndex($this->makeID($Name, $Type));
	}

	public function getTagRegularExpression() //Возвращает регулярное выражение, используемое для поиска тегов
	{
		return "/\\[(?:(?:($this->tagNameExpression)(?:(?:[\\=\\:]($this->tagParameterExpression))?|((?:\\s+$this->parameterNameExpression\\=$this->parameterValueExpression)*)))|(?:\\/($this->tagNameExpression)))\\]/u";
	}

	public function isAlignment($Parameter) //Проверяет, является ли $Parameter допустимым значением параметра выравнивания
	{
		return in_array($Parameter, $this->alignments);
	}

	public function isNonEndEntity($Index) //Возвращает true, если алгоритм с номером $Index не является сущностью закрывающего тега
	{
		return !is_a($this->algorithms[$Index], 'FuraxBBEndTagEntity');
	}

	private function listAlgorithms($Compiler) //Составляет списки входящих в сборку брутального парсера алгоритмов, и на основе этих списков заполняет служебные массивы и создаёт код запуска брутального парсера
	{
		$this->compileContextModifiers(); //Явное вычисление содержимого всех модификаторов контекста, зарегистрированных в парсере
		$startStates = $this->calculateStartStates(); //Явное вычисление содержимого всех наборов параметров в парсере
		$enabledAlgorithms = $this->listEnabledAlgorithms($startStates); //Составление списка алгоритмов, которые следует включить в сборку (неиспользуемые алгоритмы в сборку не включаются)

		$Compiler->countAlgorithms($enabledAlgorithms, $this->orderedActions, $this->algorithms, $this->actions['tagsParser']->getIndex()); //Подсчёт входящих в сборку алгоритмов
		$Compiler->createRunMethods($startStates); //Создание кода запуска брутального парсера
		$Compiler->createModifyStates(); //Создание кода модификации состояний алгоритмов в брутальном парсере
	}

	private function listEnabledAlgorithms($StartStates) //Составление списка алгоритмов, которые в ходе выполнения могут перейти в состояние FuraxBB_Enabled (остальные нет смысла включать в сборку)
	{
		$enabledAlgorithms = array();
		foreach ($StartStates as $states) //Учитываются все наборы параметров
		{
			for ($extra = $this->enableSubalgorithms($states, $states); $extra; $extra = $this->enableSubalgorithms($states, $extra)); //Учёт тех алгоритмов, которые находятся в состоянии FuraxBB_Disabled в наборе параметров, но могут быть переведены в состояние FuraxBB_Enabled модификаторами, принадлежащими тем алгоритмам, которые сами находятся в состоянии FuraxBB_Enabled (за произвольное колчиество таких итераций)
			foreach ($states as $algorithm => $state) //В сборку включаются все алгоритмы, переходящие в состояние FuraxBB_Enabled в соответствии с правилами хотя бы одного набора параметров
				if ($state == FuraxBB_Enabled)
					$enabledAlgorithms[$algorithm] = true;
		}

		return $enabledAlgorithms;
	}

	public function makeID($Name, $Type) //Переход от имени и типа тега к его идентификатору
	{
		return $Type . $this->convertCase($Name);
	}

	/*
	Запуск разбора строки кавайным парсером. Аргументы:
		$Text - разбираемая строка;
		$ExtraParameters - дополнительные параметры, которые могут использоваться пользовательскими алгоритмами;
		$ParametersSet - имя набора параметров, в соответствии с которым следует обработать входную строку.
	*/
	public function run($Text, $ExtraParameters = NULL, $ParametersSet = 'default')
	{
		$this->assignActionsIndexes(); //Присваивание действием их номеров в очереди выполнения
		$this->compileContextModifiers(); //Модификаторы приводятся в надлежащий вид

		$context = new FuraxBBParsingContext($this, $this->states, $this->addParametersSet($ParametersSet), $ExtraParameters); //Создание контекста, согласно которому нужно произвести разбор входной строки
		return $this->runSubActions($Text, 0, $context); //Непосредственно разбор входной строки
	}

	public function runSubActions($Text, $Index, $Context) //Разбор строки $Text в соответствии с контекстом $Context действиями парсера, начиная с действия № $Index в очереди. Данная функция НЕ предназначена для вызова пользователем; для вызова действий, следующих после текущего, в контексте выполнения пользовательского действия, используйте метод FuraxBBAction::runSubActions().
	{
		$running = true; //Флаг выполнения
		$actions = count($this->orderedActions);


		while ($running && ($Index < $actions)) //Действия выполняются по очереди
		{
			$actionObject = $this->orderedActions[$Index];
			if ($Context->getState($actionObject->getIndex()) == FuraxBB_Enabled) //Выполняются лишь действия, находящиеся в состоянии FuraxBB_Enabled
				$Text = $actionObject->run($Text, $running, $Context);
			++$Index;
		}

		return $Text;
	}

	public function setBreaksInserterParameters($NewLineCharacter, $BreakTag) //Установка параметров замены переводов строки тегами: символа новой строки ('\n', '\r\n' и так далее) и тега перевода строки ('<br>', '<br />' и т. д.)
	{
		$this->actions['breaksInserter']->setParameters($NewLineCharacter, $BreakTag);
	}

	public function setLinksTemplates($LinkTemplate, $EmailTemplate) //Установка шаблонов выделения URLов и адресов электронной почты в сообщении; подстановка для адреса - '\0'.
	{
		$this->actions['linksParser']->setTemplates($LinkTemplate, $EmailTemplate);
	}

	public function setSmilesParameters($Path, $Alt = '', $Directory = NULL, $Width = 0, $Height = 0, $Code = NULL) //Установка параметров смайлов, входящих в набор 'default'; аргументы - те же, что у setSmilesSet().
	{
		$this->setSmilesSet('default', $Path, $Alt, $Directory, $Width, $Height, $Code); 
	}

	/*
	Установка параметров смайлов, входящих в произвольный набор. Аргументы:
		$SmilesSet - имя набора смайлов;
		$Path - путь к смайлам, вставляемый в src картинки смайла перед именем файла; слэш после имени директории обязателен; может также включать префикс имён файлов, общий для всех файлов в группе;
		$Alt - замещающий текст;
		$Directory - имя директории на сервере, в которой находятся смайлы (для проверки их размеров); слэш в после имени директории обязателен; может включать префикс имён файлов;
		$Width - ширина смайлов (при условии, что она одинакова у всех смайлов в наборе);
		$Height - высота смайлов (аналогично);
		$Code - HTML-код смайла; не меняется, если опущен; в нём осуществляются следующие подстановки:
			$code - код смайла (например, ';)');
			$safeCode - код смайла, пропущенный через htmlSpecialChars и гарантированно не содержащий "опасных" символов;
			$src - путь к файлу смайла;
			$alt - замещающий текст;
			$width - ширина смайла;
			$height - высота смайла.
	*/
	public function setSmilesSet($SmilesSet, $Path, $Alt = '', $Directory = NULL, $Width = 0, $Height = 0, $Code = NULL)
	{
		$this->actions['smilesParser']->setSmilesSet($SmilesSet, $Path, $Alt, $Directory, $Width, $Height, $Code);
	}

	public function toggleAction($ID, $State, $ParametersSetName = 'default') //Изменение состояния действия с идентификатором $ID на $State в наборе параметров $ParametersSetName
	{
		if (isSet($this->actions[$ID]))
			$this->addParametersSet($ParametersSetName)->toggleAlgorithm($this->actions[$ID]->getIndex(), $State);
	}

	private function toggleAlgorithm($Index, $State, $ParametersSetName = 'default') //Изменение состояния алгритма с номером $Index на $State в наборе параметров $ParametersSetName; не должен вызываться пользователем
	{
		$this->addParametersSet($ParametersSetName)->toggleAlgorithm($Index, $State);
	}

	public function toggleGroup($GroupName, $State, $ParametersSetName = 'default') //Изменение состояния алгоритмов, входящих в группу $GroupName, на $State в наборе параметров $ParametersSetName
	{
		$this->addParametersSet($ParametersSetName)->toggleGroup($GroupName, $State);
	}

	public function toggleTag($Name, $Type, $State, $ParametersSetName = 'default') //Изменение состояния сущности тега с именем $Name и типом $Type на $State в наборе параметров $ParametersSetName
	{
		$this->addParametersSet($ParametersSetName)->toggleTag($Name, $Type, $State);
	}


	private $indexes = array(); //Соответствие номеров алгоритмов их идентификаторам
	private $states = array(); //Состояния алгоритмов по умолчанию

	private $tagNameExpression = '[a-zA-Z0-9\-\*]+'; ///Регулярное выражение имени тега
	private $tagParameterExpression = '[^\[\]]*'; //Регулярное выражение значения (единственного) параметра тега
	private $parameterNameExpression = '[a-zA-Z0-9\-]+'; //Регулярное выражение имени параметра тега
	private $parameterValueExpression = '[^\[\]\=]*'; //Регулярное выражение значения проименованного параметра тега

	private $parametersSets = array(); //Наборы параметров
	private $modifiers = array(); //Модификаторы, принадлежащие алгоритмам парсера

	private $actions = array(); //Действия парсера по идентификаторам
	private $orderedActions = array(); //Действия парсера по номерам в очереди выполнения

	private $groups = array(); //Группы алгоритмов парсера
	private $algorithms = array(); //Алгоритмы парсера

	private $alignments = array('left', 'right', 'center', 'justify'); //Допустимые значения параметров выравнивания
}


?>