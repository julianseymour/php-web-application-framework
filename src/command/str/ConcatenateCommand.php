<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class ConcatenateCommand extends Command implements JavaScriptInterface, SQLInterface, StringifiableInterface, ValueReturningCommandInterface{
	
	public static function getCommandId(): string{
		return "concat";
	}

	public function __construct(...$strings){
		$f = __METHOD__;
		parent::__construct();
		$print = false;
		$count = count($strings);
		if(isset($strings) && $count > 0){
			$this->setStrings($strings);
			if(!$this->hasStrings()){
				Debug::error("{$f} strings are mandatory");
			}elseif($print){
				Debug::print("{$f} {$count} strings");
			}
		}elseif($print){
			Debug::print("{$f} this object was instantiated without input parameters");
		}
	}

	/*public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"reserved"
		]);
	}*/

	public function getStringAtOffset(int $offset){
		return $this->getArrayPropertyValueAtOffset("strings", $offset);
	}

	public function startsWith($needle){
		return starts_with($this->evaluate(), $needle);
	}

	public function contains($needle):bool{
		return str_contains($this->evaluate(), $needle);
	}

	public function endsWith($needle):bool{
		return ends_with($this->evaluate(), $needle);
	}

	public function setStrings($strings){
		return $this->setArrayProperty("strings", $strings);
	}

	public function pushString(...$strings):int{
		return $this->pushArrayProperty('strings', ...$strings);
	}
	
	public function getStrings(){
		$f = __METHOD__;
		if($this->getStringCount() === 0){
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::error("{$f} string count is zero. eclared {$decl} with debug ID {$did}");
		}
		return $this->getProperty("strings");
	}

	public function getStringCount():int{
		return $this->getArrayPropertyCount("strings");
	}

	public function hasStrings():bool{
		return $this->hasArrayProperty("strings");
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$q = $this->getQuoteStyle();
			$strings = $this->getStrings();
			if(!array_key_exists(0, $strings)){
				Debug::warning("{$f} strings array is either associative or empty");
				Debug::printArray($strings);
				Debug::printStackTrace();
			}
			$string = $strings[0];
			if($string instanceof JavaScriptInterface){
				$string = $string->toJavaScript();
			}elseif(is_string($string) || $string instanceof StringifiableInterface){
				$string = escape_quotes($string, $q);
				$string = "{$q}{$string}{$q}";
			}
			for ($i = 1; $i < count($strings); $i ++){
				$s = $strings[$i];
				if($s instanceof JavaScriptInterface){
					$s = $s->toJavaScript();
				}elseif(is_string($s) || $s instanceof StringifiableInterface){
					$s = escape_quotes($s, $q);
					$s = "{$q}{$s}{$q}";
				}
				$string .= ".concat({$s})";
				if($print){
					Debug::print("{$f} after concatenating string {$i}, we have \"{$string}\"");
				}
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		try{
			$strings = $this->getStrings();
			$value = "";
			foreach($strings as $s){
				if($s instanceof ValueReturningCommandInterface){
					while($s instanceof ValueReturningCommandInterface){
						$s = $s->evaluate();
					}
				}
				$value .= $s;
			}
			return $value;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('strings', $this->getStrings());
		parent::echoInnerJson($destroy);
	}

	public function __toString(): string{
		return $this->evaluate();
	}
	
	public function toSQL(): string{
		//CONCAT('My', 'S', 'QL')
		$count = 0;
		$string = "CONCAT(";
		foreach($this->getStrings() as $s){
			if($count++ > 0){
				$string .= ',';
			}
			if($s instanceof SQLInterface){
				$s = $s->toSQL();
			}else{
				while($s instanceof ValueReturningCommandInterface){
					$s = $s->evaluate();
				}
				$s = single_quote($s);
			}
			$string .= $s;
		}
		$string .= ")";
		return $string;
	}
}
