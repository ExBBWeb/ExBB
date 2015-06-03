<?php
namespace Core\Library\DB;

/**
 * Класс для обработки SQL запросов
 *
 * @name DB
 * @author Николай Пауков
 */
class Parser {
	public static function parse($query) {
		$args = func_get_args();
		unset($args[0]);
		$placeholders = $args;
	
		$parts = preg_split('~(\?[siu])~u',$query,null,PREG_SPLIT_DELIM_CAPTURE);
		$query = array_shift($parts);

		$i = 1;
		
		foreach ($parts as $part) {
			switch ($part) {
				case '?i':
					$query .= intval($placeholders[$i]);
					$i++;
				break;
				
				case '?u':
					$temp = array();
					
					foreach ($placeholders[$i] as $name => $value) {
						$temp[] = '`'.$name.'`="'.mysqli_real_escape_string($value).'"';
					}
					
					$query .= implode(',', $temp);
					
					$i++;
				break;
				
				case '?s':
					$query .= '"'.mysql_real_escape_string($placeholders[$i]).'"';
					$i++;
				break;
				
				default:
					$query .= $part;
				break;
			}
		}
		
		return $query;
	}
}
?>