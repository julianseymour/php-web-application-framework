<?php

namespace JulianSeymour\PHPWebApplicationFramework\email\content;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\command\str\TextContentTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;

class PlaintextEmailContent extends EmailContent{

	use CharacterSetTrait;
	use TextContentTrait;

	public function __construct($text){
		parent::__construct();
		$this->setTextContent($text);
	}

	public function getCharacterSet(): string{
		if(!$this->hasCharacterSet()){
			return "utf-8";
		}
		return $this->getCharacterSet();
	}

	public static function getTextType(): string{
		return "plain";
	}

	public function getContentType(){
		$text_type = static::getTextType();
		$charset = $this->getCharacterSet();
		return "text/{$text_type};charset={$charset}";
	}

	public function __toString(): string{
		$f = __METHOD__;
		$eol = "\r\n";
		$ret = "";
		if($this->hasParentNode()){
			$content_type = $this->getContentType();
			$ret .= "Content-Type:{$content_type}{$eol}{$eol}";
		}else{
			Debug::error("{$f} parent node is undefined");
		}
		$ret .= $this->getTextContent() . "{$eol}";
		return $ret;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->characterSet, $deallocate);
		$this->release($this->textContent, $deallocate);
	}
}
