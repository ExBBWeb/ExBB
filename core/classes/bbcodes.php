<?php
/**
 * Реализует функции для обработки BB кодов
 * @author Николай Пауков <nikolai.paukov@googlemail.com>
 * @version 0.0.2
 * @package Core
 */
class BBCodes {
	/**
	* Шаблоны для поиска BB кодов
	* @var array
	*/
	public static $bbcodes = array(
		"#\[b\](.+?)\[\/b\]#is" => "<strong>\\1</strong>",
		"#\[i\](.+?)\[\/i\]#is" => "<i>\\1</i>",
		"#\[u\](.+?)\[\/u\]#is" => "<span style='text-decoration:underline'>\\1</span>",
		"#\[s\](.+?)\[\/s\]#is" => "<strike>\\1</strike>",
		//"#\[quote\](.+?)\[\/quote\]#is" => "<blockquote><p>\\1</p></blockquote>",
		"#\[url=(.+?)\](.+?)\[\/url\]#is" => "<a href='\\1'>\\2</a>",
		"#\[url\](.+?)\[\/url\]#is" => "<a href='\\1'>\\1</a>",
		"#\[img\](.+?)\[\/img\]#is" => "<img class='img-thumbnail' src='\\1' alt = 'Изображение' />",
		"#\[size=(.+?)\](.+?)\[\/size\]#is" => "<span style='font-size:\\1'>\\2</span>",
		"#\[color=(.+?)\](.+?)\[\/color\]#is" => "<span style='color:\\1'>\\2</span>",
		"#\[list\](.+?)\[\/list\]#is" => "<ul>\\1</ul>",
		"#\[list=(.+?)\](.+?)\[\/list\]#is" => "<ol>\\2</ol>",
		"#\[\*\](.+?)\[\/\*\]#is" => "<li>\\1</li>",
		"#\[center\](.+?)\[\/center\]#is" => "<div class='text-center'>\\1</div>",
		"#\[left\](.+?)\[\/left\]#is" => "<div class='text-left'>\\1</div>",
		"#\[right\](.+?)\[\/right\]#is" => "<div class='text-right'>\\1</div>",
		"#\[justify\](.+?)\[\/justify\]#is" => "<div style='text-align: justify'>\\1</div>",
		"#\[tr\](.+?)\[\/tr\]#is" => "<tr>\\1</tr>",
		"#\[td\](.+?)\[\/td\]#is" => "<td>\\1</td>",
	);
	
	public static $smiles = array(
		'O:-)' => 'angel.gif',
		':-)' => 'smile.gif',
		';-)' => 'wink.gif',
		':-D' => 'biggrin24.gif',
		':-(' => 'sad.gif',
		':-O' => 'surprised.gif',
		':-/' => 'annoyed.gif',
	);

	/**
	* Хранит текст для обработки
	* @var array
	*/
	public static $text = '';

	/**
	* Хранит шаблоны для некоторых bb кодов
	* @var array
	*/
	public static $custom = array(
		'code' => '<pre class="pre-scrollable">%s</pre>',
		'quote' => '<blockquote><p>%s</p><p>%s</p></blockquote>',
		'spoiler' => '<div class="spoiler-wrapper"><div class="spoiler folded"><span>%s</span></div><div class="spoiler-text">%s</div></div>'
	);
	
	/**
	* Обрабатывает текст, заменяя BB коды на HTML.
	* @param string $text Текст для обработки
	* @param bool $html Включить/выключить HTML
	* @return string
	*/
	public static function parse($text, $html=false) {
		self::$text = $text;
		
		if (!$html) self::$text = htmlspecialchars(self::$text);
		
		core::setAction('bb_parse');		

		self::$text = preg_replace("#\\\n#is", "<br />", self::$text);
		
		self::$text = preg_replace_callback("#\[code\](.+?)\[/code\]#is",
				create_function(
						'$matches',
						'global $array,$num;
						$key = "%__".$num."__%";
						$num++;
						$matches[1] = str_replace(array("[", "]", "<br />"), array("&#91;", "&#93;", ""), $matches[1]);
						$array[$key] = sprintf(bbcodes::$custom["code"], $matches[1]);
						return $array[$key];'
				),self::$text);	
		
		self::$text = preg_replace(array_keys(self::$bbcodes), array_values(self::$bbcodes), self::$text);
		
		while (preg_match("#\[(q|quote)(|=([^\[\]]+?))\](?!.*\[\\1(|=([^\[\]]+?))\])(.+?)\[/\\1\]#is", self::$text, $matches)){
			$title = ($matches[3] !== '') ? $matches[3].' пишет:':'Цитата:';
			self::$text = str_replace($matches[0],sprintf(self::$custom['quote'], $title, $matches[6]), self::$text);
		}
		
		while (preg_match("#\[spoiler(\=(.+?)|)\](.+?)\[\/spoiler\]#is", self::$text, $matches)){
			if ($matches[2] == '') $matches[2] = 'Спойлер';
			self::$text = str_replace($matches[0], sprintf(self::$custom['spoiler'], $matches[2], $matches[3]), self::$text);
		}

		self::$text = preg_replace_callback("#\[table\](.+?)\[/table\]#is",
				create_function(
						'$matches',
						'global $array,$num;
						$key = "%__".$num."__%";
						$num++;
						$array[$key] = "<table class=\"post_bb_table\" style=\"width: 100%;\">".str_replace(\'<br />\', \'\', $matches[1])."</table>";
						return $array[$key];'
				),self::$text);
		
		// Замена нескольких идущих подряд <br /> на один
		//self::$text = preg_replace('/(<br[^>]*>)(?:\s*\1)+/','$1',self::$text);
		
		self::$text = str_replace("[br]", '<br />', self::$text);	
		
		self::$text = self::parseSmiles(self::$text);
		
		return self::$text;
	}
	
	public static function parseSmiles($text) {
		$smiles = array();
		foreach (self::$smiles as $smile => $image) {
			$smiles[$smile] = '<img src="'.URL.'/media/images/smile/'.$image.'">';
		}

		return str_replace(array_keys($smiles), array_values($smiles), $text);
	}
	
	/**
	* Добавляет BB код
	* @param array $code Содержит шаблоны для поиска и замены кода.
	* @return void
	*/
	public static function addBBCode($code,$smile=false) {
		if (!$smile) {
			self::$bbcodes[$code['search']] = $code['replace'];
		}
		else {
			self::$smiles[$code['search']] = $code['replace'];
		}
	}

	/**
	* Удаляет все BB коды
	* @return void
	*/
	public static function clear() {
		self::$bbcodes = array();
	}
	
	public static function parseSignature($text) {
		$text = preg_replace("#\\\n#is", "<br />", $text);
		$text = preg_replace('/(<br[^>]*>)(?:\s*\1)+/','$1', $text);
		
		$text = preg_replace(array("#\[b\](.+?)\[\/b\]#is","#\[i\](.+?)\[\/i\]#is","#\[u\](.+?)\[\/u\]#is","#\[s\](.+?)\[\/s\]#is",),
		array("<strong>\\1</strong>","<i>\\1</i>","<span style='text-decoration:underline'>\\1</span>","<strike>\\1</strike>",), $text);
		
		return $text;
	}
}
?>