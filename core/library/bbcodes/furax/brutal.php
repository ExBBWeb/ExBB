<?php
class FuraxBrutalBB
{
	static public function run($Text, $ExtraParameters=NULL)
	{
		return self::runSubActions($Text, 0, self::$ParametersSet, $ExtraParameters);
	}
	static private $ParametersSet=array(2, 1, 2, 1, 2, 2, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 2, 2, 1, 2, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 2, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 2, 2, 2, 1, 2, 2);
	static private function runSubActions($text, $index, $states, $extraData)
	{
		$running = true;
		while ($running && $index < 6) {
			if ($states[$index] == 2) {
				$methodName = "action$index";
				$text=self::$methodName($text, $running, $states, $extraData);
			}
			++$index;
		}
		return $text;
	}
	static private function modifyStates($oldStates, $newStates)
	{
		foreach ($newStates as $algorithm => $state)
			$oldStates[$algorithm] = $state*(bool)$oldStates[$algorithm];
		return $oldStates;
	}
	static private function action0($text, &$running, $states, $extraData)
	{
		if (! preg_match_all('/\\[(?:(?:([a-zA-Z0-9\\-\\*]+)(?:(?:[\\=\\:]([^\\[\\]]*))?|((?:\\s+[a-zA-Z0-9\\-]+\\=[^\\[\\]\\=]*)*)))|(?:\\/([a-zA-Z0-9\\-\\*]+)))\\]/u', $text, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) return $text;
		$tags = array();
		foreach ($matches as $match) {
			if (strlen(@$match[1][0])) {
				$name = $match[1][0];
				if (strlen(@$match[2][0])) {
					if ($index = @self::$singleParameterTags[$name]) $tags[] = array($index, $match[0][1], $match[0][1]+strlen($match[0][0]), $match[2][0]);
				}
				elseif (@$match[3][0]) {
					if ($index = @self::$listedParametersTags[$name]) {
						$parameters = array();
						$parametersNumber = preg_match_all('/\\s+([a-zA-Z0-9\\-]+)\\=([^\\[\\]\\=]*)(?=\\s|$)/u', $match[3][0], $parametersMatches, PREG_SET_ORDER);
						for ($parameter = 0; $parameter < $parametersNumber; ++$parameter) $parameters[strToLower($parametersMatches[$parameter][1])] = $parametersMatches[$parameter][2];
						$tags[] = array($index, $match[0][1], $match[0][1]+strlen($match[0][0]), $parameters);
					}
				}
				elseif ($index = @self::$noParametersTags[$name]) $tags[] = array($index, $match[0][1], $match[0][1]+strlen($match[0][0]), NULL);
			} else {
				$name = strToLower($match[4][0]);
				if ($index = @self::$endTags[$name]) $tags[] = array($index, $match[0][1], $match[0][1]+strlen($match[0][0]), NULL);
			}
		}
		if ($tags) {
			$running = false;
			$tag=0;
			$processedTo=0;
			return self::parseCycle($text, $states, $extraData, $tags, $tag, $processedTo, array(), true, true);
		}
		else return $text;
	}
	static private $noParametersTags=array('b'=>6, 'i'=>8, 'u'=>10, 'highlight'=>12, 'sub'=>14, 'sup'=>16, 's'=>18, 'strike'=>20, 'l'=>28, 'r'=>30, 'c'=>32, 'j'=>34, 'left'=>36, 'right'=>38, 'center'=>40, 'justify'=>42, 'url'=>44, 'email'=>46, 'list'=>50, '*'=>53, 'table'=>54, 'tr'=>57, 'td'=>60, 'th'=>63, 'nobb'=>66, 'hr'=>68, 'code'=>69, 'quote'=>71, 'q'=>73, 'spoiler'=>75, 'off'=>77, 'edit'=>79, 'img'=>84);
	static private $singleParameterTags=array('size'=>22, 'color'=>24, 'font'=>26, 'url'=>48, 'email'=>49, 'list'=>52, 'table'=>56, 'tr'=>59, 'td'=>62, 'th'=>65, 'code'=>81, 'quote'=>82, 'q'=>83, 'img'=>86);
	static private $listedParametersTags=array('img'=>87);
	static private $endTags=array('b'=>7, 'i'=>9, 'u'=>11, 'highlight'=>13, 'sub'=>15, 'sup'=>17, 's'=>19, 'strike'=>21, 'size'=>23, 'color'=>25, 'font'=>27, 'l'=>29, 'r'=>31, 'c'=>33, 'j'=>35, 'left'=>37, 'right'=>39, 'center'=>41, 'justify'=>43, 'url'=>45, 'email'=>47, 'list'=>51, 'table'=>55, 'tr'=>58, 'td'=>61, 'th'=>64, 'nobb'=>67, 'code'=>70, 'quote'=>72, 'q'=>74, 'spoiler'=>76, 'off'=>78, 'edit'=>80, 'img'=>85);
	static private function process($text, $processFrom, $processTo, $states, $extraData)
	{
		return self::runSubActions(substr($text, $processFrom, $processTo-$processFrom), 1, $states, $extraData);
	}
	static private function parseCycle($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags, $lineEndAllowed, $parseTags)
	{
		$result = '';
		while ($tag < count($tags)) {
			switch (@$endTags[$tags[$tag][0]]) {
				case 1: case 3:
					$result .= self::process($text, $processedTo, $tags[$tag][1], $states, $extraData);
					$processedTo = $tags[$tag][2];
					++$tag;
					return $result;
				case 2:
					$result .= self::process($text, $processedTo, $tags[$tag][1], $states, $extraData);
					$processedTo = $tags[$tag][1];
					return $result;
				case 4:
					return NULL;
			}
			$success = NULL;
			if ($parseTags && $states[$tags[$tag][0]] == 2) {
				$method = 'entity'.$tags[$tag][0];
				$success = self::$method($text, $states, $extraData, $tags, $tag, $processedTo, $endTags);
			}
			if ($success === NULL) ++$tag;
			else $result .= $success;
		}
		if ($lineEndAllowed) {
			$result .= self::process($text, $processedTo, strlen($text), $states, $extraData);
			$processedTo = strlen($text);
			return $result;
		} else return NULL;
	}
	static private function entity6($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(6=>0), array(7=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<b>', '</b>'));
	}
	static private function modifyEndTags($oldTags, $newTags, $parentEndTagsAllowed)
	{
		$endTags = array();
		$changedStates = array(0 => 0, 1 => $parentEndTagsAllowed*2, 2 => $parentEndTagsAllowed*2, 3 => 4, 4 => 4);
		foreach ($oldTags as $tag => $state)
			if ($changedStates[$state])
				$endTags[$tag] = $changedStates[$state];
		return $newTags + $endTags;
	}
	static private function parseDoubleTag($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags, $modifierData, $specifiedEndTags, $parentEndTagsAllowed, $lineEndAllowed, $parseTags, $runnerName, $runnerArguments)
	{
		$internalTag = $tag+1;
		$internalProcessedTo = $tags[$tag][2];
		$subParsingResult = self::parseCycle($text, self::modifyStates($states, $modifierData), $extraData, $tags, $internalTag, $internalProcessedTo, self::modifyEndTags($endTags, $specifiedEndTags, $parentEndTagsAllowed), $lineEndAllowed, $parseTags);
		if ($subParsingResult === NULL) return NULL;
		$result = self::$runnerName($tags[$tag][3], $subParsingResult, $extraData, $runnerArguments);
		if ($result === NULL) return NULL;
		$result = self::process($text, $processedTo, $tags[$tag][1], $states, $extraData) . $result;
		$tag = $internalTag;
		$processedTo = $internalProcessedTo;
		return $result;
	}
	static private function simpleDoubleTagRunner($parameters, $contents, $extraData, $runnerParameters)
	{
		return $runnerParameters[0].$contents.$runnerParameters[1];
	}
	static private function entity8($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(8=>0), array(9=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<i>', '</i>'));
	}
	static private function entity10($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(10=>0), array(11=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<u>', '</u>'));
	}
	static private function entity12($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(12=>0), array(13=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<font style="background: yellow;">', '</font>'));
	}
	static private function entity14($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(14=>0, 28=>0, 30=>0, 32=>0, 34=>0, 36=>0, 38=>0, 40=>0, 42=>0, 50=>0, 52=>0, 53=>0, 54=>0, 56=>0, 57=>0, 59=>0, 60=>0, 62=>0, 63=>0, 65=>0, 69=>0, 71=>0, 73=>0, 75=>0, 77=>0, 79=>0, 81=>0, 82=>0, 83=>0), array(15=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<sub>', '</sub>'));
	}
	static private function entity16($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(16=>0, 28=>0, 30=>0, 32=>0, 34=>0, 36=>0, 38=>0, 40=>0, 42=>0, 50=>0, 52=>0, 53=>0, 54=>0, 56=>0, 57=>0, 59=>0, 60=>0, 62=>0, 63=>0, 65=>0, 69=>0, 71=>0, 73=>0, 75=>0, 77=>0, 79=>0, 81=>0, 82=>0, 83=>0), array(17=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<sup>', '</sup>'));
	}
	static private function entity18($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(18=>0, 20=>0), array(19=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<strike>', '</strike>'));
	}
	static private function entity20($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(20=>0, 18=>0), array(21=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<strike>', '</strike>'));
	}
	static private function entity22($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('/^\\d{1,3}$/u', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(22=>0), array(23=>3), 1, 0, 1, 'parameteredDoubleTagRunner', array('<font style="font-size: ', 'pt;">', '</font>'));
	}
	static private function parameteredDoubleTagRunner($parameters, $contents, $extraData, $runnerParameters)
	{
		return "$runnerParameters[0]$parameters$runnerParameters[1]$contents$runnerParameters[2]";
	}
	static private function entity24($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('/^(?:(?:rgb\\(\\d{1,3}\\,\\d{1,3}\\,\\d{1,3}\\))|(?:#[0-9a-f]{3,6})|(?:[a-z]+))$/u', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(24=>0), array(25=>3), 1, 0, 1, 'parameteredDoubleTagRunner', array('<font style="color: ', ';">', '</font>'));
	}
	static private function entity26($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('/^[a-z\\-0-9]+(?: [a-z\\-0-9]+)*$/u', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(26=>0), array(27=>3), 1, 0, 1, 'parameteredDoubleTagRunner', array('<font style="font-family: &quot;', '&quot;;">', '</font>'));
	}
	static private function entity28($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 29=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: left;">', '</p>'));
	}
	static private function entity30($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 31=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: right;">', '</p>'));
	}
	static private function entity32($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 33=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: center;">', '</p>'));
	}
	static private function entity34($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 35=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: justify;">', '</p>'));
	}
	static private function entity36($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 37=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: left;">', '</p>'));
	}
	static private function entity38($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 39=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: right;">', '</p>'));
	}
	static private function entity40($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 41=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: center;">', '</p>'));
	}
	static private function entity42($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 43=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<p style="text-align: justify;">', '</p>'));
	}
	static private function entity44($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(3=>2), array(45=>3), 0, 0, 0, 'runSimpleLink', array('', ' target="_blank"', '/^(?:(?:https?)|(?:ftp))\\:\\/\\/(?:(?:(?:[\\w\\-_]+\\.)+[a-z]{2,4})|(?:\\d{1,3}(?:\\.\\d{1,3}){3}))(?:\\/[^\\s\\;"\']*)*$/ui'));
	}
	static private function makeLink($href, $contents, $parameters)
	{
		return "<a href=\"$parameters[0]$href\"$parameters[1]>$contents</a>";
	}
	static private function runSimpleLink($parameters, $contents, $extraData, $runnerParameters)
	{
		if (preg_match($runnerParameters[2], $contents)) return self::makeLink($contents, $contents, $runnerParameters);
		else return NULL;
	}
	static private function entity46($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(3=>2), array(47=>3), 0, 0, 0, 'runSimpleLink', array('mailto:', '', '/^[a-z0-9\\-_\\.]+@(?:[a-z0-9\\-_]+\\.)+[a-z]{2,5}$/ui'));
	}
	static private function entity48($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('/^(?:(?:https?)|(?:ftp))\\:\\/\\/(?:(?:(?:[\\w\\-_]+\\.)+[a-z]{2,4})|(?:\\d{1,3}(?:\\.\\d{1,3}){3}))(?:\\/[^\\s\\;"\']*)*$/ui', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(5=>0, 44=>0, 46=>0, 48=>0, 49=>0, 28=>0, 30=>0, 32=>0, 34=>0, 36=>0, 38=>0, 40=>0, 42=>0, 50=>0, 52=>0, 53=>0, 54=>0, 56=>0, 57=>0, 59=>0, 60=>0, 62=>0, 63=>0, 65=>0, 69=>0, 71=>0, 73=>0, 75=>0, 77=>0, 79=>0, 81=>0, 82=>0, 83=>0), array(45=>3), 0, 0, 1, 'runEmbeddableLink', array('', ' target="_blank"'));
	}
	static private function runEmbeddableLink($parameters, $contents, $extraData, $runnerParameters)
	{
		return self::makeLink(htmlSpecialChars($parameters), $contents, $runnerParameters);
	}
	static private function entity49($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('/^[a-z0-9\\-_\\.]+@(?:[a-z0-9\\-_]+\\.)+[a-z]{2,5}$/ui', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(5=>0, 44=>0, 46=>0, 48=>0, 49=>0, 28=>0, 30=>0, 32=>0, 34=>0, 36=>0, 38=>0, 40=>0, 42=>0, 50=>0, 52=>0, 53=>0, 54=>0, 56=>0, 57=>0, 59=>0, 60=>0, 62=>0, 63=>0, 65=>0, 69=>0, 71=>0, 73=>0, 75=>0, 77=>0, 79=>0, 81=>0, 82=>0, 83=>0), array(47=>3), 0, 0, 1, 'runEmbeddableLink', array('mailto:', ''));
	}
	static private function entity50($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(53=>2, 1=>2, 6=>1, 8=>1, 10=>1, 12=>1, 14=>1, 16=>1, 18=>1, 20=>1, 22=>1, 24=>1, 26=>1, 28=>1, 30=>1, 32=>1, 34=>1, 36=>1, 38=>1, 40=>1, 42=>1, 44=>1, 46=>1, 48=>1, 49=>1, 50=>1, 52=>1, 54=>1, 56=>1, 66=>1, 68=>1, 69=>1, 71=>1, 73=>1, 75=>1, 77=>1, 79=>1, 81=>1, 82=>1, 83=>1, 84=>1, 86=>1, 87=>1), array(51=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<ul>', '</ul>'));
	}
	static private function entity52($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!in_array($tags[$tag][3], array('A', 'a', 'I', 'i', '1', 'disc', 'circle', 'square'))) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(53=>2, 1=>2, 6=>1, 8=>1, 10=>1, 12=>1, 14=>1, 16=>1, 18=>1, 20=>1, 22=>1, 24=>1, 26=>1, 28=>1, 30=>1, 32=>1, 34=>1, 36=>1, 38=>1, 40=>1, 42=>1, 44=>1, 46=>1, 48=>1, 49=>1, 50=>1, 52=>1, 54=>1, 56=>1, 66=>1, 68=>1, 69=>1, 71=>1, 73=>1, 75=>1, 77=>1, 79=>1, 81=>1, 82=>1, 83=>1, 84=>1, 86=>1, 87=>1), array(51=>1), 0, 0, 1, 'runParameteredList', NULL);
	}
	static private function runParameteredList($parameters, $contents, $extraData, $runnerParameters)
	{
		if (in_array($parameters, array('A', 'a', 'I', 'i', '1')))
			return "<ol type=\"$parameters\">$contents</ol>";
		else
			return "<ul type=\"$parameters\">$contents</ul>";
	}
	static private function entity53($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(53=>1, 1=>1, 4=>1, 6=>2, 8=>2, 10=>2, 12=>2, 14=>2, 16=>2, 18=>2, 20=>2, 22=>2, 24=>2, 26=>2, 28=>0, 30=>0, 32=>0, 34=>0, 36=>0, 38=>0, 40=>0, 42=>0, 44=>2, 46=>2, 48=>2, 49=>2, 50=>2, 52=>2, 54=>0, 56=>0, 66=>2, 68=>2, 69=>0, 71=>0, 73=>0, 75=>0, 77=>0, 79=>0, 81=>0, 82=>0, 83=>0, 84=>2, 86=>2, 87=>2, 57=>0, 59=>0, 60=>0, 62=>0, 63=>0, 65=>0), array(53=>2), 1, 0, 1, 'simpleDoubleTagRunner', array('<li>', '</li>'));
	}
	static private function entity54($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(1=>2, 6=>1, 8=>1, 10=>1, 12=>1, 14=>1, 16=>1, 18=>1, 20=>1, 22=>1, 24=>1, 26=>1, 28=>1, 30=>1, 32=>1, 34=>1, 36=>1, 38=>1, 40=>1, 42=>1, 44=>1, 46=>1, 48=>1, 49=>1, 50=>1, 52=>1, 54=>1, 56=>1, 66=>1, 68=>1, 69=>1, 71=>1, 73=>1, 75=>1, 77=>1, 79=>1, 81=>1, 82=>1, 83=>1, 84=>1, 86=>1, 87=>1, 57=>2, 59=>2), array(55=>1), 0, 0, 1, 'simpleDoubleTagRunner', array('<table>', '</table>'));
	}
	static private function entity56($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!self::isAlignment($tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(1=>2, 6=>1, 8=>1, 10=>1, 12=>1, 14=>1, 16=>1, 18=>1, 20=>1, 22=>1, 24=>1, 26=>1, 28=>1, 30=>1, 32=>1, 34=>1, 36=>1, 38=>1, 40=>1, 42=>1, 44=>1, 46=>1, 48=>1, 49=>1, 50=>1, 52=>1, 54=>1, 56=>1, 66=>1, 68=>1, 69=>1, 71=>1, 73=>1, 75=>1, 77=>1, 79=>1, 81=>1, 82=>1, 83=>1, 84=>1, 86=>1, 87=>1, 57=>2, 59=>2), array(55=>1), 0, 0, 1, 'alignedRunner', 'table');
	}
	static private function alignedRunner($parameters, $contents, $extraData, $runnerParameters)
	{
		return "<$runnerParameters align=\"$parameters\">$contents</$runnerParameters>";
	}
	static private function isAlignment($parameter)
	{
		return in_array($parameter, array('left', 'right', 'center', 'justify'));
	}
	static private function entity57($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(57=>1, 59=>1, 60=>2, 62=>2, 63=>2, 65=>2), array(57=>2, 59=>2, 58=>1), 1, 0, 1, 'simpleDoubleTagRunner', array('<tr>', '</tr>'));
	}
	static private function entity59($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!self::isAlignment($tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(57=>1, 59=>1, 60=>2, 62=>2, 63=>2, 65=>2), array(57=>2, 59=>2, 58=>1), 1, 0, 1, 'alignedRunner', 'tr');
	}
	static private function entity60($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(1=>1, 60=>1, 62=>1, 63=>1, 65=>1, 6=>2, 8=>2, 10=>2, 12=>2, 14=>2, 16=>2, 18=>2, 20=>2, 22=>2, 24=>2, 26=>2, 28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 44=>2, 46=>2, 48=>2, 49=>2, 50=>2, 52=>2, 54=>2, 56=>2, 66=>2, 68=>2, 69=>2, 71=>2, 73=>2, 75=>2, 77=>2, 79=>2, 81=>2, 82=>2, 83=>2, 84=>2, 86=>2, 87=>2), array(60=>2, 62=>2, 63=>2, 65=>2, 61=>1), 1, 0, 1, 'simpleDoubleTagRunner', array('<td>', '</td>'));
	}
	static private function entity62($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!self::isAlignment($tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(1=>1, 60=>1, 62=>1, 63=>1, 65=>1, 6=>2, 8=>2, 10=>2, 12=>2, 14=>2, 16=>2, 18=>2, 20=>2, 22=>2, 24=>2, 26=>2, 28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 44=>2, 46=>2, 48=>2, 49=>2, 50=>2, 52=>2, 54=>2, 56=>2, 66=>2, 68=>2, 69=>2, 71=>2, 73=>2, 75=>2, 77=>2, 79=>2, 81=>2, 82=>2, 83=>2, 84=>2, 86=>2, 87=>2), array(60=>2, 62=>2, 63=>2, 65=>2, 61=>1), 1, 0, 1, 'alignedRunner', 'td');
	}
	static private function entity63($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(1=>1, 60=>1, 62=>1, 63=>1, 65=>1, 6=>2, 8=>2, 10=>2, 12=>2, 14=>2, 16=>2, 18=>2, 20=>2, 22=>2, 24=>2, 26=>2, 28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 44=>2, 46=>2, 48=>2, 49=>2, 50=>2, 52=>2, 54=>2, 56=>2, 66=>2, 68=>2, 69=>2, 71=>2, 73=>2, 75=>2, 77=>2, 79=>2, 81=>2, 82=>2, 83=>2, 84=>2, 86=>2, 87=>2), array(60=>2, 62=>2, 63=>2, 65=>2, 64=>1), 1, 0, 1, 'simpleDoubleTagRunner', array('<th>', '</th>'));
	}
	static private function entity65($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!self::isAlignment($tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(1=>1, 60=>1, 62=>1, 63=>1, 65=>1, 6=>2, 8=>2, 10=>2, 12=>2, 14=>2, 16=>2, 18=>2, 20=>2, 22=>2, 24=>2, 26=>2, 28=>2, 30=>2, 32=>2, 34=>2, 36=>2, 38=>2, 40=>2, 42=>2, 44=>2, 46=>2, 48=>2, 49=>2, 50=>2, 52=>2, 54=>2, 56=>2, 66=>2, 68=>2, 69=>2, 71=>2, 73=>2, 75=>2, 77=>2, 79=>2, 81=>2, 82=>2, 83=>2, 84=>2, 86=>2, 87=>2), array(60=>2, 62=>2, 63=>2, 65=>2, 64=>1), 1, 0, 1, 'alignedRunner', 'th');
	}
	static private function entity66($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(67=>1), 1, 1, 0, 'runListModifier', NULL);
	}
	static private function runListModifier($parameters, $contents, $extraData, $runnerParameters)
	{
		return $contents;
	}
	static private function entity68($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseSingleTag($text, $tags, $tag, $processedTo, $states, $extraData, 'runSingleTagWithoutParameters', '<hr>');
	}
	static private function parseSingleTag($text, $tags, &$tag, &$processedTo, $states, $extraData, $parserName, $parserArguments)
	{
		$result = self::$parserName($tags[$tag][3], $extraData, $parserArguments);
		if ($result === NULL) return NULL;
		else {
			$result = self::process($text, $processedTo, $tags[$tag][1], $states, $extraData) . $result;
			$processedTo = $tags[$tag][2];
			++$tag;
			return $result;
		}
	}
	static private function runSingleTagWithoutParameters($parameters, $extraData, $runnerParameters)
	{
		return $runnerParameters;
	}
	static private function entity69($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(3=>2), array(70=>3), 0, 0, 0, 'simpleDoubleTagRunner', array('<div><strong>Код:</strong><pre style="border: 1px solid #808080;">', '</pre></div>'));
	}
	static private function entity71($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(72=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<div><strong>Цитата:</strong><div style="border: 1px solid #808080;">', '</div></div>'));
	}
	static private function entity73($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(74=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<div><strong>Цитата:</strong><div style="border: 1px solid #808080;">', '</div></div>'));
	}
	static private function entity75($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(76=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<div style="border: 1px solid #808080;"><a href="javascript:void(0);" onclick="if (this.nextSibling.style.display==\'none\') { this.nextSibling.style.display=\'block\'; this.firstChild.nodeValue=\'Спойлер (спрятать содержимое)\'; } else { this.nextSibling.style.display=\'none\'; this.firstChild.nodeValue=\'Спойлер (показать содержимое)\'; }">Спойлер (показать содержимое)</a><div style="display: none;">', '</div></div>'));
	}
	static private function entity77($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(78=>3), 0, 0, 1, 'simpleDoubleTagRunner', array('<div style="border: 1px solid #808080;"><strong>Оффтопик:</strong><br>', '</div>'));
	}
	static private function entity79($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(3=>2), array(80=>3), 0, 0, 0, 'simpleDoubleTagRunner', array('<textarea style="width: 100%;" cols="100" rows="10">', '</textarea>'));
	}
	static private function entity81($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('/^[\\w\\s\\+]+$/', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(3=>2), array(70=>3), 0, 0, 0, 'parameteredDoubleTagRunner', array('<div><strong>Код (', '):</strong><pre style="border: 1px solid #808080;">', '</pre></div>'));
	}
	static private function entity82($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('//', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(72=>3), 0, 0, 1, 'parameteredDoubleTagRunner', array('<div><strong>', ' пишет:</strong><div style="border: 1px solid #808080;">', '</div></div>'));
	}
	static private function entity83($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		if (!preg_match('//', $tags[$tag][3])) return NULL;
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(), array(74=>3), 0, 0, 1, 'parameteredDoubleTagRunner', array('<div><strong>', ' пишет:</strong><div style="border: 1px solid #808080;">', '</div></div>'));
	}
	static private function entity84($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseDoubleTag($text, $states, $extraData, $tags, $tag, $processedTo, $endTags, array(3=>2), array(85=>3), 0, 0, 0, 'runMediaWithoutParameters', array('/^(?:(?:https?)|(?:ftp))\\:\\/\\/(?:(?:(?:[\\w\\-_]+\\.)+[a-z]{2,4})|(?:\\d{1,3}(?:\\.\\d{1,3}){3}))(?:\\/[^\\s\\;"\']*)*$/ui', '<img src="', '" alt="Изображение">'));
	}
	static private function runMediaWithoutParameters($parameters, $contents, $extraData, $runnerParameters)
	{
		if (preg_match($runnerParameters[0], $contents)) return "$runnerParameters[1]$contents$runnerParameters[2]";
		else return NULL;
	}
	static private function entity86($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseSingleTag($text, $tags, $tag, $processedTo, $states, $extraData, 'runMediaSingleParameter', array('/^(?:(?:https?)|(?:ftp))\\:\\/\\/(?:(?:(?:[\\w\\-_]+\\.)+[a-z]{2,4})|(?:\\d{1,3}(?:\\.\\d{1,3}){3}))(?:\\/[^\\s\\;"\']*)*$/ui', '<img src="', '" alt="Изображение">'));
	}
	static private function runMediaSingleParameter($parameters, $extraData, $runnerParameters)
	{
		$parameter = htmlSpecialChars($parameters);
		if (preg_match($runnerParameters[0], $parameter)) return "$runnerParameters[1]$parameter$runnerParameters[2]";
		else return NULL;
	}
	static private function entity87($text, $states, $extraData, $tags, &$tag, &$processedTo, $endTags)
	{
		return self::parseSingleTag($text, $tags, $tag, $processedTo, $states, $extraData, 'runMediaListedParameters', array(array('src'=>'/^(?:(?:https?)|(?:ftp))\\:\\/\\/(?:(?:(?:[\\w\\-_]+\\.)+[a-z]{2,4})|(?:\\d{1,3}(?:\\.\\d{1,3}){3}))(?:\\/[^\\s\\;"\']*)*$/ui', 'width'=>'/^\\d+$/', 'height'=>'/^\\d+$/', 'alt'=>'/^[^\'"]*$/'), '<img src="$src" width="$width" height="$height" alt="$alt">'));
	}
	static private function runMediaListedParameters($parameters, $extraData, $runnerParameters)
	{
		$checkedParameters = array();
		foreach ($runnerParameters[0] as $name => $regexp)
			if (preg_match($regexp, $parameter = htmlSpecialChars(@$parameters[$name]))) $checkedParameters["\$$name"] = $parameter;
			else return NULL;
		return strtr($runnerParameters[1], $checkedParameters);
	}
	static private function action1($text, &$running, $states, $extraData)
	{
		$running = false;
		return '';
	}
	static private function action2($text, &$running, $states, $extraData)
	{
		return htmlSpecialChars($text);
	}
	static private function action3($text, &$running, $states, $extraData)
	{
		$running = false;
		return $text;
	}
	static private function action4($text, &$running, $states, $extraData)
	{
		return str_replace('
', '<br>
', $text);
	}
	static private function action5($text, &$running, $states, $extraData)
	{
		return preg_replace('/(?:(?:https?)|(?:ftp))\\:\\/\\/(?:(?:(?:[\\w\\-_]+\\.)+[a-z]{2,4})|(?:\\d{1,3}(?:\\.\\d{1,3}){3}))(?:\\/[^\\s\\;"\']*)*/ui', '<a href="\\0" target="_blank">\\0</a>', preg_replace('/[a-z0-9\\-_\\.]+@(?:[a-z0-9\\-_]+\\.)+[a-z]{2,5}/ui', '<a href="mailto:\\0">\\0</a>', $text));
	}
}

?>