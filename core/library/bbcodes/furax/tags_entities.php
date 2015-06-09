<?php
/*
Файл tags_entities.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определения конечных классов сущностей, входящих в сборку кавайного парсера.
*/


/*
Класс FuraxBBSimpleCosmeticEntity описывает простой двойной косметический тег, не имеющий параметров (например, [b]...[/b]).
*/
class FuraxBBSimpleCosmeticEntity extends FuraxBBDoubleTagEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега (например, 'b');
		$OpeningTag - HTML-код открывающего тега (например, '<b>');
		$ClosingTag - HTML-код закрывающего тега (например, '</b>');
		$DisableStructureSubTags - запрещены ли структурные теги (таблицы, списки, выравнивания и т. д.) внутри текущего (например, они запрещены в тегах sub и sup);
		$ForbiddenTags - дополнительные запрещённые внутри текущего теги (массив должен иметь структуру "имя тега => тип тега") (например, тег strike запрещён внутри тега s, поскольку является его псевдонимом).
	*/
	public function __construct($FuraxBB, $Name, $OpeningTag, $ClosingTag, $DisableStructureSubTags = false, $ForbiddenTags = array())
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, FuraxBB_Enabled, false, false, FuraxBB_UnembeddableEndTag, true);

		$this->openingTag = $OpeningTag;
		$this->closingTag = $ClosingTag;

		$this->addToGroups('cosmetic'); //Группа косметических тегов

		$this->modifier->toggleTag($Name, FuraxBB_NoParameters, FuraxBB_Forbidden); //Вложение однотипных косметических тегов запрещено
		if ($DisableStructureSubTags)
			$this->modifier->toggleGroup('structure', FuraxBB_Forbidden);
		foreach ($ForbiddenTags as $name => $type)
			$this->modifier->toggleTag($name, $type, FuraxBB_Forbidden);
	}

	protected function compileDoubleTag($Compiler)
	{
		$Compiler->addSimpleDoubleTagRunner(); //Используется исполнитель 'simpleDoubleTagRunner'
		return new FuraxBBDoubleTagRunnerData('simpleDoubleTagRunner', array($this->openingTag, $this->closingTag)); //Проверка на правильность не требуется
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return $this->openingTag . $ProcessedContents . $this->closingTag;
	}


	private $openingTag; //HTML-код открывающего тега
	private $closingTag; //HTML-код закрывающего тега
}


/*
Класс FuraxBBParameteredCosmeticEntity описывает сущность двойного косметического тега с одним параметром (например, [size=...]...[/size]).
*/
class FuraxBBParameteredCosmeticEntity extends FuraxBBDoubleTagEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$OpeningTag - HTML-код открывающего тега;
		$ClosingTag - HTML-код закрываюшего тега;
		$ParameterRegularExpression - регулярное выражение, которому должен соответствовать параметр тега.
	*/
	public function __construct($FuraxBB, $Name, $OpeningTag, $ClosingTag, $ParameterRegularExpression)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_SingleParameter, FuraxBB_Enabled, true, false, FuraxBB_UnembeddableEndTag, true);

		$this->openingTag = $OpeningTag;
		$this->closingTag = $ClosingTag;
		$this->parameterRegularExpression = $ParameterRegularExpression;

		$this->addToGroups('cosmetic'); //Группа косметических тегов
		$this->modifier->toggleTag($Name, FuraxBB_SingleParameter, FuraxBB_Forbidden); //Однотипные вложенные теги запрещены
	}

	protected function checkOpeningTag($Tag)
	{
		return preg_match($this->parameterRegularExpression, $Tag->getParameter()); //Проверка на соответствие параметра тега шаблону
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileParameteredDoubleTag($this->parameterRegularExpression, $this->openingTag, $this->closingTag);
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		//Проверка на допустимость параметра уже пройдена
		return str_replace('$parameter', $OpeningTag->getParameter(), $this->openingTag) . $ProcessedContents . $this->closingTag;
	}


	private $openingTag; //HTML-код открывающего тега
	private $closingTag; //HTML-код закрывающего тега
	private $parameterRegularExpression; //Регулярное выражение, которому должен соответствовать единственный параметр тега
}

/*
Класс FuraxBBAlignmentEntity описывает сущность двойного тега выравнивания (l, right и т. д.).
*/
class FuraxBBAlignmentEntity extends FuraxBBDoubleTagEntity
{
	public function __construct($FuraxBB, $Name, $Alignment) //$Alignment - тип выравнивания в терминах CSS
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, FuraxBB_Enabled, false, false, FuraxBB_LocalEndTag, true);

		$this->alignment = $Alignment;
		$this->addToGroups('structure', 'alignment'); //Тег входит в группы структурных тегов и тегов выравнивания
		$this->addEndGroup('alignment'); //Встреча следующего тега выравнивания прекращает разбор текущего тега
	}

	protected function compileDoubleTag($Compiler)
	{
		$Compiler->addSimpleDoubleTagRunner(); //Используется исполнитель 'simpleDoubleTagRunner'
		return new FuraxBBDoubleTagRunnerData('simpleDoubleTagRunner', array("<p style=\"text-align: $this->alignment;\">", '</p>')); //Проверка на правильность не требуется
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<p style=\"text-align: $this->alignment;\">$ProcessedContents</p>";
	}


	private $alignment;
}


/*
Класс FuraxBBLinkEntity описывает сущность тегов ссылки и адреса электронной почты (как с параметром, так и без); он является базовым для классов сущности тега ссылки с параметром и без параметра, и лишь предоставляет функции для формирования HTML-кода ссылки своим дочерним классам. Он абстрактен, поскольку выполнение и компиляция сущности в состав брутального парсера должны быть определены дочерними классами.
*/
abstract class FuraxBBLinkEntity extends FuraxBBDoubleTagEntity
{
	protected function __construct($FuraxBB, $Name, $Type, $SubTagsAllowed, $HrefPrefix, $TargetBlank)
	{
		parent :: __construct($FuraxBB, $Name, $Type, FuraxBB_Enabled, false, false, FuraxBB_UnembeddableEndTag, $SubTagsAllowed);

		$this->hrefPrefix = $HrefPrefix;
		$this->targetBlank = ($TargetBlank ? ' target="_blank"' : '');

		$this->addToGroups('links');
		//Вложенные теги ссылок и вложенные структурные теги не запрещаются на этом уровне, потому что для тега ссылки без параметров и так запрещены любые вложенные теги (ибо его содержимое должно представлять собой URL)
	}

	protected function compileMakeLink($Compiler)
	{
		$makeLink = $Compiler->addMethodOnce('makeLink');
		if (!$makeLink) //Функция формирования HTML-кода ссылки уже создана
			return;

		$makeLink->addParameter('href'); //Адрес ссылки
		$makeLink->addParameter('contents'); //Содержимое ссылки
		$makeLink->addParameter('parameters'); //Параметры (префикс, указатель targetа)

		$code = <<< CODE_END
return "<a href=\"\$parameters[0]\$href\"\$parameters[1]>\$contents</a>";
CODE_END;
		$makeLink->addCode($code);
	}

	protected function makeLink($Href, $Text) //Формирование кода ссылки на адрес $Href с текстом $Text
	{
		return "<a href=\"$this->hrefPrefix$Href\"$this->targetBlank>$Text</a>";
	}


	protected $hrefPrefix; //Префикс адреса (например, 'mailto:')
	protected $targetBlank; //Указатель targetа, готовый к вставке в код (равен либо ' target="_blank"', либо '')
}

/*
Класс FuraxBBSimpleLinkEntity описывает сущность тега ссылки без параметров, для которого адрес находится в теле тега, и текст ссылки совпадает с адресом.
*/
class FuraxBBSimpleLinkEntity extends FuraxBBLinkEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$ContentsRegularExpression - регулярное выражение для содержимого тега;
		$HrefPrefix - префикс адреса (например, 'mailto:');
		$TargetBlank - нужно ли открывать ссылку в новом окне (булев флаг).
	*/
	public function __construct($FuraxBB, $Name, $ContentsRegularExpression, $HrefPrefix, $TargetBlank)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, false, $HrefPrefix, $TargetBlank); //Разбор вложенных тегов запрещён

		$this->contentsRegularExpression = $ContentsRegularExpression;
		$this->modifier->toggleAction('processingStopper', FuraxBB_Enabled); //Единственное действие, производимое над содержимым, - экранировка опасных символов HTML
	}

	protected function compileDoubleTag($Compiler)
	{
		$this->compileMakeLink($Compiler);
		$this->compileSimpleLinkRunner($Compiler);
		return new FuraxBBDoubleTagRunnerData('runSimpleLink', array($this->hrefPrefix, $this->targetBlank, $this->contentsRegularExpression)); //Проверка открывающего тега не производится, поскольку проверяемый адрес заключён не в нём, а в теле тега
	}

	private function compileSimpleLinkRunner($Compiler)
	{
		$runner = $Compiler->addDoubleTagRunner('runSimpleLink');
		if (!$runner) //Исполнитель уже создан
			return;

		$code = <<< CODE_END
if (preg_match(\$runnerParameters[2], \$contents)) return self::makeLink(\$contents, \$contents, \$runnerParameters);
		else return NULL;
CODE_END;
		$runner->addCode($code);
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		if (preg_match($this->contentsRegularExpression, $ProcessedContents)) //Проверка допустимости содержимого тега
			return $this->makeLink($ProcessedContents, $ProcessedContents);
		else
			return NULL;
	}


	private $contentsRegularExpression; //Регулярное выражение для содержимого тега
}

/*
Класс FuraxBBEmbeddableLinkEntity описывает сущность тега ссылки, адрес которой указывается как единственный параметр ссылки и которая допускает вложенные теги (например, картинки).
*/
class FuraxBBEmbeddableLinkEntity extends FuraxBBLinkEntity
{
	
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$ParameterRegularExpression - регулярное выражение для параметра тега;
		$HrefPrefix - префикс адреса (например, 'mailto:');
		$TargetBlank - нужно ли открывать ссылку в новом окне (булев флаг).
	*/
	public function __construct($FuraxBB, $Name, $ParameterRegularExpression, $HrefPrefix, $TargetBlank)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_SingleParameter, true, $HrefPrefix, $TargetBlank);

		$this->parameterRegularExpression = $ParameterRegularExpression;

		$this->modifier->toggleGroup('links', FuraxBB_Forbidden); //Вложенные ссылки запрещены
		$this->modifier->toggleGroup('structure', FuraxBB_Forbidden); //Вложенные структурные теги (выравнивание, таблицы, списки и т. д.) запрещены
		$this->modifier->toggleAction('linksParser', FuraxBB_Forbidden); //Выделение адресов ссылками не производится
	}

	protected function checkOpeningTag($Tag)
	{
		return preg_match($this->parameterRegularExpression, htmlSpecialChars($Tag->getParameter())); //Проверка, является ли параметр допустимым адресом ссылки
	}

	protected function compileDoubleTag($Compiler)
	{
		$this->compileMakeLink($Compiler);
		$this->compileEmbeddableLinkRunner($Compiler);

		return new FuraxBBDoubleTagRunnerData('runEmbeddableLink', array($this->hrefPrefix, $this->targetBlank), $Compiler->compileParameterMatching($this->parameterRegularExpression));
	}

	private function compileEmbeddableLinkRunner($Compiler)
	{
		$runner = $Compiler->addDoubleTagRunner('runEmbeddableLink');
		if (!$runner) //Исполнитель уже создан
			return;

		//К этому моменту параметр уже пропущен через htmlSpecialChars() - это делается на этапе проверки правильности параметра
		$code = <<< CODE_END
return self::makeLink(htmlSpecialChars(\$parameters), \$contents, \$runnerParameters);
CODE_END;
		$runner->addCode($code);
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return $this->makeLink(htmlSpecialChars($OpeningTag->getParameter()), $ProcessedContents); //Создание тега ссылки
	}


	private $parameterRegularExpression; //Регулярное выражение, которому должен соответствовать параметр тега ссылки
}


/*
Класс FuraxBBListEntity описывает сущность тега списка. Это родительский класс для тегов списка с параметром и без, которые обрабатываются по-разному; он лишь настраивает общие для них правила модификации контекста при обработке вложенного содержимого.
*/
abstract class FuraxBBListEntity extends FuraxBBDoubleTagEntity
{
	protected function __construct($FuraxBB, $Type)
	{
		parent :: __construct($FuraxBB, 'list', $Type, FuraxBB_Enabled, false, false, FuraxBB_LocalEndTag, true);

		$this->addToGroups('lists', 'structure'); //Группы списков и структурных тегов

		$this->modifier->toggleGroup('normallyUndisabled', FuraxBB_Disabled); //Внутри списка запрещены все теги, кроме [*]
		$this->modifier->toggleTag('*', FuraxBB_NoParameters, FuraxBB_Enabled); //Тег [*] разрешён
		$this->modifier->toggleAction('textStopper', FuraxBB_Enabled); //Текст внутри списка также запрещён
	}
}

/*
Класс FuraxBBListWithoutParametersEntity описывает сущность тега списка без параметров, который размечается как ненумерованный список.
*/
class FuraxBBListWithoutParametersEntity extends FuraxBBListEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, FuraxBB_NoParameters); //Обо всех операциях по созданию и настройке сущности позаботится конструктор родительского класса
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileSimpleDoubleTagRunner('ul');
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<ul>$ProcessedContents</ul>"; //Непронумерованный список
	}
}

/*
Класс FuraxBBParameteredListEntity описывает сущность тега списка с одним параметром, который задаёт тип нумерации списка или тип его маркеров.
*/
class FuraxBBParameteredListEntity extends FuraxBBListEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, FuraxBB_SingleParameter); //Обо всех операциях по созданию сущности заботится конструктор родительского класса
	}

	protected function checkOpeningTag($Tag)
	{
		return in_array($Tag->getParameter(), self :: $olTypes) || in_array($Tag->getParameter(), self :: $ulTypes); //Допустимо ли значение типа списка
	}

	protected function compileDoubleTag($Compiler)
	{
		$this->compileParameteredListRunner($Compiler);

		$types = FuraxBBParserCompiler::serialize(array_merge(self::$olTypes, self::$ulTypes));
		$check = "in_array(\$tags[\$tag][3], $types)"; //Тип списка известен

		return new FuraxBBDoubleTagRunnerData('runParameteredList', NULL, $check);
	}

	private function compileParameteredListRunner($Compiler)
	{
		$runner = $Compiler->addDoubleTagRunner('runParameteredList');
		if (!$runner) //Исполнитель уже создан
			return;

		$olTypes = FuraxBBParserCompiler::serialize(self::$olTypes); //Типы, означающие нумерованный список
		//Поскольку к моменту вызова исполнителя открывающий тег уже гарантированно прошёл проверку на правильность, если параметра тега нет в списке типов нумерованных списков, это означает лишь то, что тип списка относится к типам маркированных списков

$code = <<< CODE_END
if (in_array(\$parameters, $olTypes))
			return "<ol type=\"\$parameters\">\$contents</ol>";
		else
			return "<ul type=\"\$parameters\">\$contents</ul>";
CODE_END;
		$runner->addCode($code);
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		if (in_array($OpeningTag->getParameter(), self :: $olTypes)) //Список нумерован
			return "<ol type=\"{$OpeningTag->getParameter()}\">$ProcessedContents</ol>";
		else //Список маркирован
			return "<ul type=\"{$OpeningTag->getParameter()}\">$ProcessedContents</ul>";
	}

	private static $olTypes = array('A', 'a', 'I', 'i', '1'); //Типы нумерованных списков
	private static $ulTypes = array('disc', 'circle', 'square'); //Типы маркированных списков
}


/*
Класс FuraxBBListItemEntity описывает сущность тега элемента списка.
*/
class FuraxBBListItemEntity extends FuraxBBDoubleTagEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, '*', FuraxBB_NoParameters, FuraxBB_Disabled, true, false, FuraxBB_NotEndTag, true); //Разрешено наследование закрывающих тегов, так что элемент списка закроется вместе со списком

		$this->addToGroups('lists', 'structure'); //Относится к группам списков и структурных тегов
		$this->addEndTag('*', FuraxBB_NoParameters); //Закрывается при начале следующего элемента списка

		$this->modifier->toggleGroup('normallyUndisabled', FuraxBB_Enabled); //Вложенные теги разрешены
		$this->modifier->toggleGroup('structure', FuraxBB_Forbidden); //Запрещены вложенные теги форматирования
		$this->modifier->toggleGroup('lists', FuraxBB_Enabled); //Единственное исключение - вложенные списки разрешены
		$this->modifier->toggleTag('*', FuraxBB_NoParameters, FuraxBB_Disabled); //Вложенные элементы - нет
		$this->modifier->toggleAction('textStopper', FuraxBB_Disabled); //Разрешён текст
		$this->modifier->toggleAction('breaksInserter', FuraxBB_Disabled); //Запрещена расстановка переносов
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileSimpleDoubleTagRunner('li');
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<li>$ProcessedContents</li>";
	}
}


/*
Класс FuraxBBTableEntity описывает сущность тега таблицы. Сущности тегов таблиц с параметром и без параметра описывают его дочерние классы.
*/
abstract class FuraxBBTableEntity extends FuraxBBDoubleTagEntity
{
	protected function __construct($FuraxBB, $Type)
	{
		parent :: __construct($FuraxBB, 'table', $Type, FuraxBB_Enabled, false, false, FuraxBB_LocalEndTag, true);

		$this->addToGroups('tables', 'structure'); //Входит в группы таблиц и структурных тегов

		$this->modifier->toggleGroup('normallyUndisabled', FuraxBB_Disabled); //Внутри таблицы запрещены все теги,
		$this->modifier->toggleGroup('tableRows', FuraxBB_Enabled); //кроме тегов строк таблицы
		$this->modifier->toggleAction('textStopper', FuraxBB_Enabled); //Текст также запрещён
	}
}

/*
Класс FuraxBBTableWithoutParametersEntity описывает сущность тега таблицы без параметров.
*/
class FuraxBBTableWithoutParametersEntity extends FuraxBBTableEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, FuraxBB_NoParameters); //О большинстве вещей заботится конструктор родительского класса
	}

	protected function compileDoubleTag($Compiler) 
	{
		return $Compiler->compileSimpleDoubleTagRunner('table');
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<table>$ProcessedContents</table>";
	}
}

/*
Класс FuraxBBTableWithParameterEntity описывает сущность тега таблицы, имеющего единственный параметр - стиль выравнивания.
*/
class FuraxBBTableWithParameterEntity extends FuraxBBTableEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, FuraxBB_SingleParameter); //О большинстве операций заботится конструктор родительского класса
	}

	protected function checkOpeningTag($Tag)
	{
		return $this->getFuraxBB()->isAlignment($Tag->getParameter()); //Корректно ли задан стиль выравнивания
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileAlignedTag('table'); //Имя тега - 'table'
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<table align=\"{$OpeningTag->getParameter()}\">$ProcessedContents</table>";
	}
}


/*
Класс FuraxBBTableRowEntity описывает сущность тега строки таблицы. Это базовый класс для классов сущностей тегов строк таблиц без параметров и с единственным параметром.
*/
abstract class FuraxBBTableRowEntity extends FuraxBBDoubleTagEntity
{
	protected function __construct($FuraxBB, $Type)
	{
		parent :: __construct($FuraxBB, 'tr', $Type, FuraxBB_Disabled, true, false, FuraxBB_LocalEndTag, true);

		$this->addToGroups('tables', 'tableRows', 'structure'); //Группы таблиц, строк таблиц и структурных тегов

		$this->modifier->toggleGroup('tableRows', FuraxBB_Disabled); //Вложенные строки таблиц запрещены
		$this->modifier->toggleGroup('tableCells', FuraxBB_Enabled); //Вложенные ячейки таблиц разрешены

		$this->addEndGroup('tableRows'); //Тег строки таблицы завершает текущую строку и начинает следующую
	}
}

/*
Класс FuraxBBTableRowWithoutParametersEntity описывает сущность тега строки таблицы без параметров.
*/
class FuraxBBTableRowWithoutParametersEntity extends FuraxBBTableRowEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, FuraxBB_NoParameters); //О задании большинства параметров заботится конструктор родительского класса
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileSimpleDoubleTagRunner('tr');
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<tr>$ProcessedContents</tr>";
	}
}

/*
Класс FuraxBBParameteredTableRowEntity описывает сущность тега строки таблицы с единственным параметром, задающим стиль выравнивания содержимого строки.
*/
class FuraxBBParameteredTableRowEntity extends FuraxBBTableRowEntity
{
	public function __construct($FuraxBB)
	{
		parent :: __construct($FuraxBB, FuraxBB_SingleParameter); //Большинство параметров задаёт конструктор родительского класса.
	}

	protected function checkOpeningTag($Tag)
	{
		return $this->getFuraxBB()->isAlignment($Tag->getParameter()); //Является ли параметр допустимым типом выравнивания содержимого
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileAlignedTag('tr'); //Имя тега - 'tr'
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return "<tr align=\"{$OpeningTag->getParameter()}\">$ProcessedContents</tr>";
	}
}


/*
Кдасс FuraxBBTableCellEntity описывает сущность тега ячейки таблицы и служит базовым для классов сущностей ячеек таблиц без параметров и с единственным параметром.
*/
abstract class FuraxBBTableCellEntity extends FuraxBBDoubleTagEntity
{
	protected function __construct($FuraxBB, $Name, $Type)
	{
		parent :: __construct($FuraxBB, $Name, $Type, FuraxBB_Disabled, true, false, FuraxBB_LocalEndTag, true);

		$this->addToGroups('tables', 'tableCells', 'structure'); //Группы таблиц, ячеек таблиц и структурных тегов
		$this->addEndGroup('tableCells'); //Начало следующей ячейки заканчивает текущую

		$this->modifier->toggleGroup('tableCells', FuraxBB_Disabled); //Вложенные ячейки запрещены
		$this->modifier->toggleGroup('normallyUndisabled', FuraxBB_Enabled); //Разрешены все разрешённые в нормальном состоянии теги
		$this->modifier->toggleAction('textStopper', FuraxBB_Disabled); //Текст разрешён
	}
}

/*
Класс FuraxBBTableCellWithoutParametersEntity описывает сущность тега ячейки таблицы без параметров.
*/
class FuraxBBTableCellWithoutParametersEntity extends FuraxBBTableCellEntity
{
	public function __construct($FuraxBB, $Name)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters); //Большинство параметров задаётся конструктором родительского класса
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileSimpleDoubleTagRunner($this->getName());
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		$name = $this->getName();
		return "<$name>$ProcessedContents</$name>";
	}
}

/*
Класс FuraxBBParameteredTableCellEntity описывает сущность тега ячейки таблицы с единственным параметром - стилем выравнивания содержимого ячейки.
*/
class FuraxBBParameteredTableCellEntity extends FuraxBBTableCellEntity
{
	public function __construct($FuraxBB, $Name)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_SingleParameter); //Большинство параметров задаёт конструктор родительского класса
	}

	protected function checkOpeningTag($Tag)
	{
		return $this->getFuraxBB()->isAlignment($Tag->getParameter()); //Верно ли задан стиль выравнивания в ячейке
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileAlignedTag($this->getName()); //Имя тега - $this->getName() (ибо может быть 'td', а может 'th')
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		$name = $this->getName();
		return "<$name align=\"{$OpeningTag->getParameter()}\">$ProcessedContents</$name>";
	}
}


/*
Класс FuraxBBActionsListModifierEntity описывает сущность тега, обработка которого сводится к модификации состояний набора действий при обработке содержимого тега.
*/
class FuraxBBActionsListModifierEntity extends FuraxBBDoubleTagEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$State - состояние тега по умолчанию;
		$SubTagsAllowed - производится ли разбор вложенных тегов;
		$ActionsToModify - массив действий, состояния которых надо изменить, в формате "идентификатор действия => новое состояние".
	*/
	public function __construct($FuraxBB, $Name, $State, $SubTagsAllowed, $ActionsToModify)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, $State, true, true, FuraxBB_LocalEndTag, $SubTagsAllowed);

		$this->modifier->toggleActions($ActionsToModify); //Изменение состояний действий
	}

	protected function compileDoubleTag($Compiler)
	{
		$this->compileListModifierRunner($Compiler);
		return new FuraxBBDoubleTagRunnerData('runListModifier'); //Проверка правильности открывающего тега не требуется
	}

	private function compileListModifierRunner($Compiler)
	{
		$runner = $Compiler->addDoubleTagRunner('runListModifier');
		if (!$runner) //Исполнитель уже существует
			return;

		$runner->addCode("return \$contents;");
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return $ProcessedContents; //Обо всём заботится модификатор
	}
}


/*
Класс FuraxBBSingleTagWithoutParametersEntity описывает тег, не имеющий ни закрывающего тега, ни параметров, и просто заменяющийся неким фиксированным HTML-кодом.
*/
class FuraxBBSingleTagWithoutParametersEntity extends FuraxBBSingleTagEntity
{
	public function __construct($FuraxBB, $Name, $State, $Code)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, $State);

		$this->code = $Code;
	}

	protected function compileSingleTag($Compiler)
	{
		$this->compileSingleTagRunner($Compiler);
		return new FuraxBBSingleTagRunnerData('runSingleTagWithoutParameters', $this->code); //Код тега - единственный параметр исполнителя
	}

	private function compileSingleTagRunner($Compiler)
	{
		$runner = $Compiler->addSingleTagRunner('runSingleTagWithoutParameters');
		if (! $runner) //Исполнитель уже создан
			return;

		$runner->addCode("return \$runnerParameters;");
	}

	protected function parseSingleTag($Tag, $ExtraParameters)
	{
		return $this->code; //Тег просто заменяется своим кодом
	}


	private $code;
}


/*
Класс FuraxBBSemanticBlockEntity описывает сущность тега смыслового блока. Он является базовым для классов сущностей тегов смысловых блоков с параметром и без.
*/
abstract class FuraxBBSemanticBlockEntity extends FuraxBBDoubleTagEntity
{
	protected function __construct($FuraxBB, $Name, $Type, $State, $SubTagsEnabled, $Prefix, $Posfix, $ActionsToModify)
	{
		parent :: __construct($FuraxBB, $Name, $Type, $State, false, false, FuraxBB_UnembeddableEndTag, $SubTagsEnabled);

		$this->prefix = $Prefix;
		$this->posfix = $Posfix;

		$this->addToGroups('structure', 'semantic'); //Группы структурных и смысловых тегов
		$this->modifier->toggleActions($ActionsToModify); //Правила выполнения действий над содержимым тега могут быть изменены
	}


	protected $prefix; //Открывающий тег
	protected $posfix; //Закрывающий тег
}


/*
Класс FuraxBBSemanticBlockWithoutParametersEntity описывает сущность тега смыслового блока без параметров ([code], [quote] и т. д.).
*/
class FuraxBBSemanticBlockWithoutParametersEntity extends FuraxBBSemanticBlockEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$State - состояние тега по умолчанию;
		$SubTagsEnabled - разрешён ли разбор вложенных тегов;
		$Prefix - открывающий тег или комбинация тегов;
		$Posfix - закрывающий тег или комбинация тегов;
		$ActionsToModify - массив изменения правил обработки действий при обработке содержимого тега в формате "идентификатор действия => новое состояние".
	*/
	public function __construct($FuraxBB, $Name, $State, $SubTagsEnabled, $Prefix, $Posfix, $ActionsToModify = array())
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, $State, $SubTagsEnabled, $Prefix, $Posfix, $ActionsToModify);
	}

	protected function compileDoubleTag($Compiler)
	{
		$Compiler->addSimpleDoubleTagRunner(); //Используется исполнитель 'simpleDoubleTagRunner'
		return new FuraxBBDoubleTagRunnerData('simpleDoubleTagRunner', array($this->prefix, $this->posfix)); //Проверка допустимости открывающего тега не требуется
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return $this->prefix . $ProcessedContents . $this->posfix;
	}
}


/*
Класс FuraxBBParameteredSemanticBlockEntity описывает сущность тега смыслового блока с единственным параметром (например, [quote=Username], [code=Language] и т. д.).
*/
class FuraxBBParameteredSemanticBlockEntity extends FuraxBBSemanticBlockEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$State - состояние тега по умолчанию;
		$SubTagsEnabled - разрешён ли разбор вложенных тегов;
		$ParameterRegularExpression - регулярное выражение, которому должен удовлетворять единственный параметр тега;
		$Prefix - открывающий тег или комбинация тегов;
		$Posfix - закрывающий тег или комбинация тегов;
		$ActionsToModify - массив изменения правил обработки действий при обработке содержимого тега в формате "идентификатор действия => новое состояние".
	*/
	public function __construct($FuraxBB, $Name, $State, $SubTagsEnabled, $ParameterRegularExpression, $Prefix, $Posfix, $ActionsToModify = array())
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_SingleParameter, $State, $SubTagsEnabled, $Prefix, $Posfix, $ActionsToModify);

		$this->parameterRegularExpression = $ParameterRegularExpression;
	}

	protected function checkOpeningTag($Tag)
	{
		return preg_match($this->parameterRegularExpression, $Tag->getParameter()); //Допустим ли параметр тега
	}

	protected function compileDoubleTag($Compiler)
	{
		return $Compiler->compileParameteredDoubleTag($this->parameterRegularExpression, $this->prefix, $this->posfix); //Точка входа проверяет соответствие параметра тега регулярному выражению
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		return str_replace('$parameter', $OpeningTag->getParameter(), $this->prefix) . $ProcessedContents . $this->posfix;
	}


	private $parameterRegularExpression; //Регулярное выражение, которому должен соответствовать параметр тега
}


/*
Класс FuraxBBMediaWithoutParametersEntity описывает сущность тега добавления медиасодержимого, не имеющего параметров (медиасодержимое определяется содержимым тега), например, [img]путь_к_изображению[/img].
*/
class FuraxBBMediaWithoutParametersEntity extends FuraxBBDoubleTagEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$ContentsRegularExpression - регулярное выражение, котрому должно соответствовать содержимое тега;
		$Code - HTML-код тега, в котором осуществляется подстановка '$contents' (содержимое тега).
	*/
	public function __construct($FuraxBB, $Name, $ContentsRegularExpression, $Code)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_NoParameters, FuraxBB_Enabled, false, false, FuraxBB_UnembeddableEndTag, false);

		$this->contentsRegularExpression = $ContentsRegularExpression;
		$this->code = $Code;

		$this->addToGroups('media'); //Группа тегов вставки медиасодержимого
		$this->modifier->toggleAction('processingStopper', FuraxBB_Enabled); //Единственное действие, производимое над содержимым тега, - замена опасных с точки зрения HTML символов их HTML-эквивалентами
	}

	protected function compileDoubleTag($Compiler)
	{
		$this->compileMediaWithoutParametersRunner($Compiler);
		$code = explode('$contents', $this->code, 2); //Код до и после подстановки содержимого тега
		return new FuraxBBDoubleTagRunnerData('runMediaWithoutParameters', array($this->contentsRegularExpression, $code[0], $code[1]));
	}

	private function compileMediaWithoutParametersRunner($Compiler)
	{
		$runner = $Compiler->addDoubleTagRunner('runMediaWithoutParameters');
		if (!$runner) //Исполнитель уже создан
			return;

		//Соответствие содержимого тега регулярному выражению проверяет исполнитель, поскольку на этапе запуска точки входа содержимое тега ещё не получено
		$code = <<< CODE_END
if (preg_match(\$runnerParameters[0], \$contents)) return "\$runnerParameters[1]\$contents\$runnerParameters[2]";
		else return NULL;
CODE_END;
		$runner->addCode($code);
	}

	protected function parseDoubleTag($OpeningTag, $ProcessedContents, $ExtraParameters)
	{
		if (preg_match($this->contentsRegularExpression, $ProcessedContents)) //Проверка допустимости содержимого тега
			return str_replace('$contents', $ProcessedContents, $this->code);
		else
			return NULL;
	}


	private $contentsRegularExpression; //Регулярное выражение, которому должно соответствовать содержимое тега
	private $code; //Код тега с подстановкой '$contents'
}


/*
Класс FuraxBBMediaSingleParameterEntity описывает тег вставки медиасодержимого, имеющий один параметр и не имеющий закрывающего тега.
*/
class FuraxBBMediaSingleParameterEntity extends FuraxBBSingleTagEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$ParameterRegularExpression - регулярное выражение, которому должен соответствовать единственный параметр тега;
		$Code - HTML-код тега, в котором осуществляется подстановка '$parameter' (параметр тега).
	*/
	public function __construct($FuraxBB, $Name, $ParameterRegularExpression, $Code)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_SingleParameter, FuraxBB_Enabled);

		$this->parameterRegularExpression = $ParameterRegularExpression;
		$this->code = $Code;

		$this->addToGroups('media'); //Группа тегов подстановки медиасодержимого
	}

	private function compileMediaSingleParameterRunner($Compiler)
	{
		$runner = $Compiler->addSingleTagRunner('runMediaSingleParameter');
		if (! $runner) //Исполнитель уже создан
			return;

		$code = <<< CODE_END
\$parameter = htmlSpecialChars(\$parameters);
		if (preg_match(\$runnerParameters[0], \$parameter)) return "\$runnerParameters[1]\$parameter\$runnerParameters[2]";
		else return NULL;
CODE_END;
		$runner->addCode($code);
	}

	protected function compileSingleTag($Compiler)
	{
		$this->compileMediaSingleParameterRunner($Compiler);
		$code = explode('$parameter', $this->code, 2); //HTML-код, идущий до и после параметра
		return new FuraxBBSingleTagRunnerData('runMediaSingleParameter', array($this->parameterRegularExpression, $code[0], $code[1]));
	}

	protected function parseSingleTag($OpeningTag, $ExtraParameters)
	{
		$parameter = htmlSpecialChars($OpeningTag->getParameter()); //Содержимое параметра должно быть безопасно

		if (preg_match($this->parameterRegularExpression, $parameter)) //Содержимое параметра допустимо
			return str_replace('$parameter', $parameter, $this->code);
		else
			return NULL;
	}


	private $parameterRegularExpression; //Регулярное выражение, которому должен удовлетворять единственный параметр тега
	private $code; //HTML-код тега с подстановкой '$parameter'
}


/*
Класс FuraxBBMediaListedParametersEntity описывает сущность тега вставки медиасодержимого, имеющего набор проименованных параметров и не имеющего закрывающего тега.
*/
class FuraxBBMediaListedParametersEntity extends FuraxBBSingleTagEntity
{
	/*
	Аргументы конструктора:
		$FuraxBB - указатель на объект кавайного парсера;
		$Name - имя тега;
		$Code - HTML-код тега, в котором осуществляются подстановки '$имя_параметра';
		$Parameters - параметры тега в формате "имя параметра => регулярное выражение, которому должен соответствовать параметр с таким именем".
	*/
	public function __construct($FuraxBB, $Name, $Code, $Parameters)
	{
		parent :: __construct($FuraxBB, $Name, FuraxBB_ListedParameters, FuraxBB_Enabled);

		$this->code = $Code;
		$this->parameters = array();
		foreach ($Parameters as $name => $regularExpression)
			$this->parameters[$FuraxBB->convertCase($name)] = $regularExpression; //Имена параметров приводятся к нижнему регистру, если парсер действует независимо от их регистра

		$this->addToGroups('media'); //Группа тегов подстановки медиасодержимого
	}

	private function compileMediaListedParameters($Compiler)
	{
		$runner = $Compiler->addSingleTagRunner('runMediaListedParameters');
		if (! $runner) //Исполнитель уже создан
			return;

		$code = <<< CODE_END
\$checkedParameters = array();
		foreach (\$runnerParameters[0] as \$name => \$regexp)
			if (preg_match(\$regexp, \$parameter = htmlSpecialChars(@\$parameters[\$name]))) \$checkedParameters["\\\$\$name"] = \$parameter;
			else return NULL;
		return strtr(\$runnerParameters[1], \$checkedParameters);
CODE_END;
		$runner->addCode($code);
	}

	protected function compileSingleTag($Compiler)
	{
		$this->compileMediaListedParameters($Compiler);
		return new FuraxBBSingleTagRunnerData('runMediaListedParameters', array($this->parameters, $this->code)); //Параметры исполнителя - это параметры тега и его HTML-код
	}

	protected function parseSingleTag($OpeningTag, $ExtraParameters)
	{
		$tagParameters = $OpeningTag->getParameters(); //Фактические параметры тега
		$parameters = array(); //Обработанные параметры

		foreach ($this->parameters as $name => $regularExpression)
			if (preg_match($regularExpression, $parameter = htmlSpecialChars(@$tagParameters[$name])))
				$parameters["\$$name"] = $parameter;
			else //Тег считается недопустимым, если хоть один параметр не удовлетворяет своему регулярному выражению
				return NULL;

		return strtr($this->code, $parameters);
	}


	private $code; //HTML-код тега со всеми подстановками
	private $parameters; //Параметры тега и регулярные выражения, которым они должны соответствовать
}


?>