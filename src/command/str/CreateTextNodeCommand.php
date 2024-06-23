<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class CreateTextNodeCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface{

	use TextContentTrait;

	public static function getCommandId(): string{
		return "createTextNode";
	}

	public function __construct($text = null){
		parent::__construct();
		if(isset($text)){
			$this->setTextContent($text);
		}
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$text = $this->getTextContent();
		if($text instanceof JavaScriptInterface){
			$text = $text->toJavaScript();
		}elseif(is_string($text) || $text instanceof StringifiableInterface){
			$q = $this->getQuoteStyle();
			$text = "{$q}" . escape_quotes($text, $q) . "{$q}";
		}
		return "document.createTextNode({$text})";
	}

	public function extractChildNodes(int $mode): ?array{
		return [
			$this->getTextContent()
		];
	}

	public static function extractAnyway():bool{
		return false;
	}

	public function evaluate(?array $params = null){
		$text = $this->getTextContent();
		while($text instanceof ValueReturningCommandInterface){
			$text = $text->evaluate();
		}
		return $text;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->textContent, $deallocate);
	}
}
