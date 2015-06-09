<?php
/*
Файл smiles.php пакета FuraxKawaiBB.
Домашняя страница пакета: http://furax.narod.ru/furaxkawaibb/
Адрес для связи с автором: furax@yandex.ru
Описание пакета находится в файле readme.htm.

Данный файл содержит определения классов, отвечающих за разбор смайлов.
*/


/*
Класс FuraxBBSmilesParser описывает действие парсера смайлов. Он производен от FuraxBBAction, поскольку описывает действие в составе кавайного парсера.
*/
class FuraxBBSmilesParser extends FuraxBBAction
{
	public function __construct($FuraxBB, $Spaces, $SmilesDefaultPath) //$Spaces - это флаг отбивания смайлов пробелами с обеих сторон, а $SmilesDefaultPath - путь к директории со смайлами, входящими в набор 'default'
	{
		parent :: __construct($FuraxBB, 'smilesParser', FuraxBB_Enabled);

		$this->spaces = $Spaces;
		$this->setSmilesSet('default', $SmilesDefaultPath, '', NULL, 0, 0, NULL); //Создание набора смайлов 'default'
	}

	private function calculateSmilesArray() //Создание массива "код смайла => HTML-код смайла" (с осуществлением всех подстановок)
	{
		$smiles = array();
		foreach ($this->smiles as $code => $smile)
			if ($this->spaces)
				$smiles[" $code "] = ' ' . $smile->getHTML($this->smilesSets) . ' ';
			else
				$smiles[$code] = $smile->getHTML($this->smilesSets);
		return $smiles;
	}

	public function compile($Action, $Compiler, $Index) //Компиляция парсера смайлов в состав брутального парсера
	{
		$Compiler->addProperty('smilesCodes', $this->calculateSmilesArray()); //Добавление массива смайлов к парсеру
		$Action->addCode("return strtr(\$text, self::\$smilesCodes);"); //Код замены смайлов
	}

	public function run($Text, &$ProcessFollowingActions, $Context) //Запуск действия на выполнение
	{
		return strtr($Text, $this->calculateSmilesArray()); //Простая замена смайлов их HTML-кодами
	}

	public function setSmile($Code, $FileName, $Alt, $Width, $Height, $SmilesSet) //Создание смайла, заданного своими аттрибутами
	{
		$this->smiles[$Code] = new FuraxBBCommonSmile($Code, $FileName, $Alt, $Width, $Height, $SmilesSet);
	}

	public function setSmileHTML($Code, $HTML) //Создание смайла, заданного своим HTML-кодом
	{
		$this->smiles[$Code] = new FuraxBBHTMLSmile($Code, $HTML);
	}

	public function setSmilesSet($Name, $Path, $Alt, $Directory, $Width, $Height, $Code) //Создание набора смайлов
	{
		$this->smilesSets[$Name] = new FuraxBBSmilesSet($Path, $Alt, $Directory, $Width, $Height, $Code);
	}


	private $spaces; //Отбиваются ли смайлы пробелами
	private $smiles = array(); //Смайлы, известные парсеру
	private $smilesSets = array(); //Наборы смайлов
}


/*
Класс FuraxBBSmilesSet описывает набор смайлов, характеризующихся набором общих свойств - находящихся в одной директории, имеющих общий замещающий текст и так далее.
*/
class FuraxBBSmilesSet
{
	public function __construct($Path, $Alt, $Directory, $Width, $Height, $Code)
	{
		$this->path = $Path;
		$this->alt = htmlSpecialChars($Alt);
		$this->directory = ($Directory === NULL ? $Path : $Directory); //Если локальная директория со смайлами не указана, но зато указан путь с точностью до хоста, предпринимается попытка узнавать размеры смайлов, открывая их файлы по сети
		$this->width = $Width;
		$this->height = $Height;
		if ($Code)
			$this->code = $Code;
	}

	public $path; //Сетевой путь к директории со смайлами
	public $alt; //Замещающий текст смайлов
	public $directory; //Локальная директория со смайлами
	public $width, $height; //Размеры смайла
	public $code = '<img src="$src" width="$width" height="$height" alt="$alt">'; //Шаблон HTML-кода смайла
}


/*
Класс FuraxBBSmile описывает смайл, заданный каким-либо образом. Он абстрактен, потому что способ предоставления HTML-кода смайла различен для разных типов смайлов и определяется в дочернем классе.
*/
abstract class FuraxBBSmile
{
	public function __construct($Code)
	{
		$this->code = $Code;
	}

	public function getCode() //Возвращает код смайла
	{
		return $this->code;
	}

	abstract public function getHTML($SmilesSets); //Должен возвращать HTML-код смайла

	private $code; //Код смайла
}


/*
Класс FuraxBBHTMLSmile описывает смайл, заданный своим HTML-кодом. Он, очевидно, производен от FuraxBBSmile.
*/
class FuraxBBHTMLSmile extends FuraxBBSmile
{
	public function __construct($Code, $HTML)
	{
		parent :: __construct($Code);
		$this->html = $HTML;
	}

	public function getHTML($SmilesSets)
	{
		return $this->html; //HTML-код смайла задан жёстко
	}

	private $html; //HTML-код смайла
}


/*
Класс FuraxBBCommonSmile описывает смайл, заданный набором своих аттрибутов.
*/
class FuraxBBCommonSmile extends FuraxBBSmile
{
	public function __construct($Code, $FileName, $Alt, $Width, $Height, $SmilesSet)
	{
		parent :: __construct($Code);

		$this->fileName = $FileName;
		$this->alt = $Alt;
		$this->smilesSet = $SmilesSet;

		$this->width = $Width;
		$this->height = $Height;
	}

	public function getHTML($SmilesSets) //Формирование HTML-кода смайла
	{
		$smilesSet = (isSet($SmilesSets[$this->smilesSet]) ? $SmilesSets[$this->smilesSet] : $SmilesSets['default']); //Если набор смайлов, к которому относится текущий смайл, не существует, используется набор 'default'

		$replaces = array
		(
			'$code' => $this->getCode(),
			'$alt' => ($this->alt ? $this->alt : $smilesSet->alt), //Замещающий текст набора используется, если не задан замещающий текст смайла
			'$src' => $smilesSet->path . $this->fileName,
			'$width' => '',
			'$height' => ''
		); //Массив подстановок, осуществляемых в HTML-коде смайла

		if ($this->width && $this->height) //Ширина и высота смайла уже известны
		{
			$replaces['$width'] = $this->width;
			$replaces['$height'] = $this->height;
		}
		else
		{
			if ($data = @getImageSize($smilesSet->directory . $this->fileName)) //Удалось получить данные о файле смайла
			{
				$this->width = $replaces['$width'] = $data[0];
				$this->height = $replaces['$height'] = $data[1];
			}
			else
			{
				if ($smilesSet->width) //Данные определены для всего набора смайлов; при следующем формировании HTML-кода смайла будет предпринята попытка повторного получения более индивидуальной информации о смайле
				{
					$replaces['$width'] = $smilesSet->width;
				}
				if ($smilesSet->height)
				{
					$replaces['$height'] = $smilesSet->height;
				}
			}
		}

		return strtr($smilesSet->code, $replaces); //Выполнение подстановок
	}

	private $fileName; //Имя файла
	private $alt; //Замещающий текст
	private $width, $height; //Размеры изображения
	private $smilesSet; //Имя набора, к которому относится смайл
}


?>