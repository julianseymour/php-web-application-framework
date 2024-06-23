<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SubstituteCommand extends StringTransformationCommand{

	public static function getCommandId(): string{
		return "substitute";
	}

	public function __construct($subject=null, ...$substitutions){
		$f = __METHOD__;
		parent::__construct($subject);
		if(isset($substitutions) && count($substitutions) > 0){
			$arr = [];
			foreach($substitutions as $sub){
				if(is_array($sub)){
					Debug::error("{$f} one of your substitutions is an array");
				}
				array_push($arr, $sub);
			}
			$this->setSubstitutions($arr);
		}
	}

	public function hasSubstitutions():bool{
		return $this->hasProperty("substitutions");
	}

	public function getSubstitutions(){
		return $this->getProperty("substitutions");
	}

	public function setSubstitutions($substitutions){
		return $this->setArrayProperty("substitutions", $substitutions);
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$substitutions = $this->hasSubstitutions() ? $this->getSubstitutions() : [];
		$value = substitute($this->getSubject(), ...$substitutions);
		return $value;
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('subject', $this->getSubject(), $destroy);
		if($this->hasSubstitutions()){
			Json::echoKeyValuePair('substitutions', $this->substitutions, $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$subject = $this->getSubject();
		if($subject instanceof JavaScriptInterface){
			$subject = $subject->toJavaScript();
		}
		if(is_string($subject)){
			$subject = single_quote($subject);
		}
		$cmd = $this->getCommandId();
		$string = "{$cmd}({$subject}";
		if($this->hasSubstitutions()){
			$string .= ", [";
			$count = 0;
			foreach($this->getSubstitutions() as $sub){
				if(is_array($sub)){
					Debug::error("{$f} subsistution is an array");
				}elseif($count > 0){
					$string .= ", ";
				}
				if($sub instanceof JavaScriptInterface){
					$sub = $sub->toJavaScript();
				}elseif(is_string($sub) || $sub instanceof StringifiableInterface){
					$sub = single_quote($sub);
				}
				$string .= $sub;
				$count ++;
			}
			$string .= "]";
		}
		$string .= ")";
		return $string;
	}
}
