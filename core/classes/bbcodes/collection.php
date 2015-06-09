<?php
namespace Core\Classes\BBCodes;

class Collection implements \IteratorAggregate {
	protected $bbcodes = array();
	protected $position = 0;
	
	public function __construct() {
		$this->add('b', new SimpleBB('b'));
		$this->add('i', new SimpleBB('i'));
		$this->add('s', new SimpleBB('s', 'strike'));
		$this->add('u', new SimpleBB('u', 'span', 'u', 'span', 'underline'));
		$this->add('sub', new SimpleBB('sub'));
		$this->add('sup', new SimpleBB('sup'));
		$this->add('s2', new SimpleBBAttr('s2'));
	}
	
	public function add($code, $object) {
		$this->bbcodes[$code] = $object;
	}
	
	public function set($code, $object) {
		$this->bbcodes[$code] = $object;
	}

	public function getIterator() {
		return new \ArrayIterator($this->bbcodes);
	}
}

interface RegExpSimpleCode {
	public function getRegExp();
	public function getReplacement();
}

interface ReplaceSimpleCode {
	public function replace(&$string);
}

class BBCode {
	protected $search_tag;
	protected $close_search_tag;
	protected $replace_tag;
	protected $close_replace_tag;
	protected $tag_class;
	protected $style;
	//protected $classes;
	
	public function __construct($search_tag, $replace_tag=false, $close_search_tag=false, $close_replace_tag=false, $class=false, $style=false) {
		if (!$replace_tag) $replace_tag = $search_tag;
		if (!$close_search_tag) $close_search_tag = $search_tag;
		if (!$close_replace_tag) $close_replace_tag = $replace_tag;
		
		$this->search_tag = $search_tag;
		$this->replace_tag = $replace_tag;
		$this->close_search_tag = $close_search_tag;
		$this->close_replace_tag = $close_replace_tag;
		$this->style = $style;
		$this->tag_class = $class;
	}
}

class SimpleBB extends BBCode implements RegExpSimpleCode {
	public function getRegExp() {
		return '#\['.$this->search_tag.'\](.+?)\[/'.$this->close_search_tag.'\]#is';
	}
	
	public function getReplacement() {
		$class = ($this->tag_class) ? ' class="'.$this->tag_class.'"' : '';
		$style = ($this->style) ? ' style="'.$this->style.'"' : '';
		
		return '<'.$this->replace_tag.$class.$style.'>\\1</'.$this->close_replace_tag.'>';
	}

}

class AttrBBCode extends BBCode {
	protected $attributes;
	
	protected function parseAttrs() {
		
	}
}

class SimpleBBAttr extends BBCode implements ReplaceSimpleCode {
	public function setAttr($attr, $default) {
		$this->attributes[$attr] = $default;
	}
	
	public function replace(&$string) {
		$class = ($this->tag_class) ? ' class="'.$this->tag_class.'"' : '';
		$style = ($this->style) ? ' style="'.$this->style.'"' : '';
		
		$string = preg_replace('#\['.$this->search_tag.'\](.+?)\[/'.$this->close_search_tag.'\]#is', '<'.$this->replace_tag.$class.$style.'>\\1</'.$this->close_replace_tag.'>', $string);
	}
}
?>