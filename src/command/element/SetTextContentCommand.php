<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\str\TextContentTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SetTextContentCommand extends ElementCommand{

	use TextContentTrait;

	public static function getCommandId(): string{
		return "setTextContent";
	}

	public function __construct($element = null, $textContent = null){
		$f = __METHOD__;
		$print = false;
		parent::__construct($element);
		if($textContent !== null){
			if($print){
				Debug::print("{$f} setting text content to \"{$textContent}\"");
			}
			$this->setTextContent($textContent);
		}elseif($print){
			Debug::print("{$f} text content is null");
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('textContent', $this->getTextContent(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasTextContent()){
			$this->setTextContent(replicate($that->getTextContent()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->textContent, $deallocate);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$print = false;
		try{
			$id = $this->getIdCommandString();
			if($id instanceof JavaScriptInterface){
				$id = $id->toJavaScript();
			}
			$textContent = $this->getTextContent();
			if($textContent instanceof JavaScriptInterface){
				$textContent = $textContent->toJavaScript();
				if($print){
					Debug::print("{$f} after string conversion, textContent is \"{$textContent}\"");
				}
			}elseif(is_string($textContent) || $textContent instanceof StringifiableInterface){
				$q = $this->getQuoteStyle();
				$textContent = "{$q}" . escape_quotes($textContent, $q) . "{$q}";
			}
			return "{$id}.textContent = {$textContent}";
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
