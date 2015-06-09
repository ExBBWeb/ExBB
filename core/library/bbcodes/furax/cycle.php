<?php
/*
Файл cycle.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определение классов цикла разбора, набора входных данных и тега.
*/


/*
Класс FuraxBBTag описывает тег, присутствующий в разбираемой строке, определяя его индекс в парсере, тип и параметр либо параметры.
*/
class FuraxBBTag
{
	public function __construct($Matches, $FuraxBB) //Первый аргумент - это результат сопоставления строки, содержащей тег, регулярному выражению тега
	{
		$this->furaxBB = $FuraxBB;

		$this->position = $Matches[0][1];
		$this->length = strlen($Matches[0][0]);
		$this->end = $this->position + $this->length;

		if (strlen(@$Matches[1][0])) //Имя тега находится сразу после левой квадратной скобки
		{
			$this->name = $Matches[1][0];

			if (strlen(@$Matches[2][0])) //У тега есть единственный параметр
			{
				$this->parameter = $Matches[2][0];
				$this->type = FuraxBB_SingleParameter;
			}
			elseif (@ $Matches[3][0]) //У тега есть набор параметров
			{
				$this->parametersSource = $Matches[3][0];
				$this->type = FuraxBB_ListedParameters;
			}
			else //У тега нет параметров
			{
				$this->type = FuraxBB_NoParameters;
			}
		}
		else //Имя тега находится после левой квадратной скобки и прямого слэша
		{
			$this->type = FuraxBB_EndTag;
			$this->name = $Matches[4][0];
		}

		$this->name = $FuraxBB->convertCase($this->name); //Если задана независимость имён тегов от регистра, все имена приводятся к нижнему регистру
		$this->index = $FuraxBB->getTagIndex($this->name, $this->type);
	}

	public function getEnd() //Возвращает позицию конца тега в исходной строке
	{
		return $this->end;
	}

	public function getIndex() //Возвращает номер тега в парсере
	{
		return $this->index;
	}

	public function getLength() //Возвращает число символов в записи тега
	{
		return $this->length;
	}

	public function getName() //Возвращает имя тега
	{
		return $this->name;
	}

	public function getParameter() //Возвращает единственный параметр тега
	{
		return $this->parameter;
	}

	public function getParameters() //Возвращает набор параметров тега
	{
		if ($this->parameters !== NULL) //Параметры уже разобраны
			return $this->parameters;

		$this->parameters = array();
		$matches = array();

		//Параметры разбираются при первом обращении
		$parametersNumber = preg_match_all($this->furaxBB->getListedParameterRegularExpression(), $this->parametersSource, $matches, PREG_SET_ORDER);

		for ($parameter = 0; $parameter < $parametersNumber; ++$parameter)
			$this->parameters[$this->furaxBB->convertCase($matches[$parameter][1])] = $matches[$parameter][2]; //Имена параметров также могут быть независимыми от регистра

		return $this->parameters;
	}

	public function getPosition() //Возвращает положение тега в исходной строке
	{
		return $this->position;
	}

	public function getType() //Возвращает тип тега
	{
		return $this->type;
	}


	private $position, $length, $end; //Положение начала тега в исходной строке, длину записи тега и положение конца тега

	private $name, $type; //Имя и тип тега
	private $index; //Номер тега в парсере

	private $furaxBB; //Указатель на объект кавайного парсера

	private $parameter = ''; //Единственный параметр тега
	private $parameters = NULL; //Массив параметров тега
	private $parametersSource = ''; //Строка, содержащая список параметров тега
}


/*
Класс FuraxBBInputData описывает набор, составленный из входных данных - входной строки и присутствующих в ней тегов.
*/
class FuraxBBInputData
{
	public function __construct($Text, $EntitiesList, $FuraxBB) //Второй аргумент - массив определённых в парсере сущностей
	{
		$this->text = $Text;
		$this->length = strlen($Text);

		preg_match_all($FuraxBB->getTagRegularExpression(), $Text, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE); //Поиск тегов во входной строке

		foreach ($matches as $match) //Просмотр всех совпадений
		{
			$tag = new FuraxBBTag($match, $FuraxBB);
			$index = $tag->getIndex();

			if ($index && isSet($EntitiesList[$index])) //В массив добавляются только те теги, для которых в парсере определены соответствующие сущности
				$this->tags[] = $tag;
		}
		$this->tagsNumber = count($this->tags);
	}

	public function getLength()
	{
		return $this->length;
	}

	public function getTag($Index)
	{
		return $this->tags[$Index];
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function getTagsNumber()
	{
		return $this->tagsNumber;
	}

	public function getText()
	{
		return $this->text;
	}


	private $text; //Разбираемая строка
	private $length; //Длина разбираемой строки

	private $tags = array(); //Теги в разбираемой строке
	private $tagsNumber; //Число тегов в разбираемой строке
}


/*
Класс FuraxBBParsingCycle описывает цикл разбора тегов - однократный линейный просмотр тегов, входящих в исходную строку, с попытками разобрать каждый из них. Разбор вложенных тегов осуществляется вложенными циклами разбора.
*/
class FuraxBBParsingCycle
{
	/*
	Конструктор может вызываться в двух вариантах:
		* в первом варианте он прнимает указатели на набор входных данных, на текущий контекст и на действие парсера тегов;
		* во втором варианте он принимает указатели на вышестоящий цикл разбора и сущность, соответствующую тегу, с которого начался разбор вложенного цикла, а также позицию этого тега в списке присутствующих во входной строке тегов.
	*/
	public function __construct($Input_Cycle, $Context_Entity, $Action_CurrentTag)
	{
		if (is_a($Input_Cycle, 'FuraxBBInputData')) //Создание цикла верхнего уровня
			$this->constructCycle($Input_Cycle, $Context_Entity, $Action_CurrentTag);
		else //Создание вложенного цикла разбора
			$this->cloneCycle($Input_Cycle, $Context_Entity, $Action_CurrentTag);
	}

	private function cloneCycle($Cycle, $Entity, $CurrentTag)
	{
		$this->input = $Cycle->getInput();
		$this->action = $Cycle->getAction();

		$this->processedTo = $CurrentTag->getEnd(); //Разбор надо производить с позиции после тега, с которого начался вложенный цикл
		$this->currentTag = $Cycle->getCurrentTag() + 1; //Разбор вложенного цикла начинается с тега, следующего после того, с которого и начался вложенный цикл

		$this->endTags = array();
		foreach ($Cycle->getEndTags() as $tag => $type) //Наследование набора закрывающих тегов от вышестоящего цикла
			switch ($type)
			{
				case FuraxBB_LocalEndTag:
				case FuraxBB_BubblingEndTag:
					if ($Entity->allowedParentEndTags())
						$this->endTags[$tag] = FuraxBB_BubblingEndTag;
					break;

				case FuraxBB_UnembeddableEndTag:
				case FuraxBB_ThrowableEndTag:
					$this->endTags[$tag] = FuraxBB_ThrowableEndTag;
					break;
			}
		foreach ($Entity->getEndTags() as $tag => $type) //Добавление закрывающих тегов текущей сущности
			$this->endTags[$tag] = $type;

		$this->lineEndAllowed = $Entity->allowedLineEnd();
		$this->context = new FuraxBBParsingContext($Cycle->getContext(), $Entity->getModifier()); //Создание контекста в соответствии с модификатором контекста, принадлежащим сущности, начавшей вложенный цикл
	}

	private function constructCycle($Input, $Context, $Action)
	{
		$this->input = $Input;
		$this->action = $Action;

		$this->processedTo = 0; //Строка ещё не обработана
		$this->currentTag = 0; //Ни один тег ещё не обработан

		$this->endTags = array(); //Цикл верхнего уровня не имеет закрывающих тегов
		$this->lineEndAllowed = true; //Цикл верхнего уровня всегда заканчивается вместе с входной строкой

		$this->context = $Context; //Контекст не модифицируется - нечем
	}

	public function getAction() //Возвращает действие парсера тегов
	{
		return $this->action;
	}

	public function getContext() //Возвращает текущий контекст
	{
		return $this->context;
	}

	public function getCurrentTag() //Возвращает номер текущего тега в списке присутствующих во входной строке тегов
	{
		return $this->currentTag;
	}

	public function getEndTags() //Возвращает массив закрывающих тегов текущего цикла
	{
		return $this->endTags;
	}

	public function getInput() //Возвращает набор входных данных
	{
		return $this->input;
	}

	public function getProcessedTo() //Возвращает позицию во входной строке, до которой уже произведена обработка
	{
		return $this->processedTo;
	}

	public function parse($TagsParsing = true) //Выполняет непосредственный разбор цикла (если аргумент - false, разбор открывающих тегов не производится, а лишь ищутся теги закрывающие)
	{
		$entities = $this->action->getEntities(); //Набор известных парсеру сущностей

		while ($this->currentTag < $this->input->getTagsNumber()) //Цикл по всем тегам, начиная с некоторого (определяемого в конструкторе цикла)
		{
			$tag = $this->input->getTag($this->currentTag); //Объект текущего тега
			$index = $tag->getIndex();
			$entity = $entities[$index]; //Соответствующая ему сущность

			switch ($entity->isEndTag($this->endTags)) //Проверка на то, является ли тег закрывающим для текущего цикла
			{
				case FuraxBB_LocalEndTag: //Тег корректно завершает текущий цикл, и на этом его действие заканчивается
				case FuraxBB_UnembeddableEndTag:
					$this->process($tag->getPosition(), '', $tag->getEnd(), $this->currentTag+1);
					return $this->result;

				case FuraxBB_BubblingEndTag: //Тег корректно завершает текущий цикл и поступает на рассмотрение цикла вышестоящего
					$this->process($tag->getPosition(), '', $tag->getPosition(), $this->currentTag);
					return $this->result;

				case FuraxBB_ThrowableEndTag: //Тег, появившись в текущем цикле, указывает на некорректность разметки текущего уровня и прекращает разборку текущего цикла
					return NULL;
			}

			$success = false; //Успешно ли разобран текущий тег
			if ($TagsParsing && $this->context->getState($index) == FuraxBB_Enabled) //Разбор текущего тега разрешён
				$success = $entity->parse($tag, $this); //Попытка разобрать текущий тег
			if (! $success)
				++ $this->currentTag; //При неудаче просто переходим к следующему тегу; при удаче тег сам переведёт указатель разбираемого тега на нужную позицию
		}

		if ($this->lineEndAllowed) //Цикл может закончиться вместе с входной строкой
		{
			$this->process($this->input->getLength(), '', $this->input->getLength(), $this->input->getTagsNumber()); //Дообработка оставшейся части входной строки
			return $this->result;
		}
		else
			return NULL;
	}

	/*
	Разбор строки между тегами с помощью следующих в очереди действий, и добавление результата в конец результата разбора строки, а также сдвижка указателей позиций в исходной строке и в списке тегов. Аргументы:
		$ProcessingTextBeginning - правая граница разбираемой части строки (левая определяется свойством processedTo текущего цикла);
		$ProcessedText - текст, который нужно добавить после "выпавшей" между тегов строки - результат разбора тега, в который она упирается;
		$ProcessedTo - новое значение позиции, до которой произведён разбор исходной строки;
		$CurrentTag - новое значение номера тега, до которого произведён разбор.
	*/
	public function process($ProcessingTextBegining, $ProcessedText, $ProcessedTo, $CurrentTag)
	{
		$this->result .= $this->action->runSubActions(substr($this->input->getText(), $this->processedTo, $ProcessingTextBegining - $this->processedTo),
			$this->context) . $ProcessedText;

		$this->processedTo = $ProcessedTo;
		$this->currentTag = $CurrentTag;
	}


	private $input; //Набор входных данных
	private $action; //Действие разбора тегов

	private $processedTo; //Позиция в исходной строке, до которой произведён разбор
	private $currentTag; //Позиция в списке присутствующих в исходной строке тегов, до которой произведён разбор

	private $result = ''; //Результат цикла разбора тегов

	private $endTags; //Массив тегов, являющихся тегами конца текущего цикла
	private $lineEndAllowed; //Может ли текущий цикл закончиться вместе с входной строкой

	private $context; //Контекст, согласно которому производится разбор
}


?>