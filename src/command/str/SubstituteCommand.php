<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SubstituteCommand extends Command implements JavaScriptInterface, StringifiableInterface, ValueReturningCommandInterface{

	protected $subject;

	protected $substitutions;

	public static function getCommandId(): string{
		return "substitute";
	}

	public function __toString(): string{
		return $this->evaluate();
	}

	public function __construct($subject, ...$substitutions){
		$f = __METHOD__;
		parent::__construct();
		if(! isset($subject)) {
			Debug::error("{$f} subject is undefined");
		}
		$this->setSubject($subject);
		if(!$this->hasSubject()) {
			Debug::error("{$f} after setting string ID it is still undefined");
		}
		if(!empty($substitutions)) {
			$arr = [];
			foreach($substitutions as $sub) {
				if(is_array($sub)) {
					Debug::error("{$f} one of your substitutions is an array");
				}
				array_push($arr, $sub);
			}
			$this->setSubstitutions($arr);
		}
	}

	public function hasSubject(){
		return isset($this->subject);
	}

	public function setSubject($subject){
		return $this->subject = $subject;
	}

	public function getSubject(){
		$f = __METHOD__;
		if(!$this->hasSubject()) {
			Debug::error("{$f} string ID is undefined");
		}
		return $this->subject;
	}

	public function hasSubstitutions():bool{
		return ! empty($this->substitutions);
	}

	public function getSubstitutions(){
		return $this->substitutions;
	}

	public function setSubstitutions($substitutions){
		return $this->substitutions = $substitutions;
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$substitutions = $this->hasSubstitutions() ? $this->getSubstitutions() : [];
		$value = substitute($this->getSubject(), ...$substitutions);
		return $value;
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('subject', $this->getSubject(), $destroy);
		if($this->hasSubstitutions()) {
			Json::echoKeyValuePair('substitutions', $this->substitutions, $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->subject);
		unset($this->substitutions);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$subject = $this->getSubject();
		if($subject instanceof JavaScriptInterface) {
			$subject = $subject->toJavaScript();
		}
		if(is_string($subject)){
			$subject = single_quote($subject);
		}
		$cmd = $this->getCommandId();
		$string = "{$cmd}({$subject}";
		if($this->hasSubstitutions()) {
			$string .= ", [";
			$count = 0;
			foreach($this->getSubstitutions() as $sub) {
				if(is_array($sub)) {
					Debug::error("{$f} subsistution is an array");
				}elseif($count > 0) {
					$string .= ", ";
				}
				if($sub instanceof JavaScriptInterface) {
					$sub = $sub->toJavaScript();
				}elseif(is_string($sub) || $sub instanceof StringifiableInterface) {
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
