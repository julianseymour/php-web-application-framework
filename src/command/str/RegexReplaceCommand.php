<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\LimitedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class RegexReplaceCommand extends StringTransformationCommand{

	use LimitedTrait;

	protected $count;

	protected $pattern;

	protected $replacement;

	public function __construct($pattern=null, $replacement=null, $subject=null, $limit = -1){
		parent::__construct($subject);
		if($pattern !== null){
			$this->setPattern($pattern);
		}
		if($replacement !== null){
			$this->setReplacement($replacement);
		}
		$this->setLimit($limit);
	}

	public static function getCommandId(): string{
		return "preg_replace";
	}

	public function getLimit(): int{
		if(!$this->hasLimit()){
			return - 1;
		}
		return $this->limitCount;
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$print = false;
		$pattern = $this->getPattern();
		if($pattern instanceof ValueReturningCommandInterface){
			while($pattern instanceof ValueReturningCommandInterface){
				$pattern = $pattern->evaluate();
			}
		}
		$replacement = $this->getReplacement();
		if($replacement instanceof ValueReturningCommandInterface){
			while($replacement instanceof ValueReturningCommandInterface){
				$replacement = $replacement->evaluate();
			}
		}
		$subject = $this->getSubject();
		if($subject instanceof ValueReturningCommandInterface){
			while($subject instanceof ValueReturningCommandInterface){
				$subject = $subject->evaluate();
			}
		}
		if($subject === null){
			return null;
		}
		$limit = $this->getLimit();
		if($limit instanceof ValueReturningCommandInterface){
			while($limit instanceof ValueReturningCommandInterface){
				$limit = $limit->evaluate();
			}
		}
		//$limit = -1; // javascript string.replace() does not have this argument
		$count = $this->getCount();
		if($count instanceof ValueReturningCommandInterface){
			while($count instanceof ValueReturningCommandInterface){
				$count = $count->evaluate();
			}
		}
		if($print){
			Debug::print("{$f} replacing pattern \"{$pattern}\" with \"{$replacement}\" in string \"{$subject}\"");
		}
		return preg_replace($pattern, $replacement, $subject, $limit, $count);
	}

	public function toJavaScript(): string{
		$subject = $this->getSubject();
		if($subject instanceof JavaScriptInterface){
			$subject = $subject->toJavaScript();
		}elseif(is_string($subject) || $subject instanceof StringifiableInterface){
			$subject = single_quote($subject);
		}
		$pattern = $this->getPattern();
		if($pattern instanceof JavaScriptInterface){
			$pattern = $pattern->toJavaScript();
		}
		// XXX this is only needed for attribute names
		$replacement = $this->getReplacement();
		if($replacement instanceof JavaScriptInterface){
			$replacement = $replacement->toJavaScript();
		}elseif(is_string($replacement) || $replacement instanceof StringifiableInterface){
			$replacement = single_quote($replacement);
		}
		return "{$subject}.replace({$pattern}, {$replacement})";
	}

	public function setPattern($pattern){
		if($this->hasPattern()){
			$this->release($this->pattern);
		}
		return $this->pattern = $this->claim($pattern);
	}

	public function hasPattern(): bool{
		return isset($this->pattern);
	}

	public function getPattern(){
		$f = __METHOD__;
		if(!$this->hasPattern()){
			Debug::error("{$f} pattern is undefined");
		}
		return $this->pattern;
	}

	public function setCount($count){
		if($this->hasCount()){
			$this->release($this->count);
		}
		return $this->count = $this->claim($count);
	}
	
	public function getCount():int{
		if(!$this->hasCount()){
			return 0;
		}
		return $this->count;
	}

	public function hasCount():bool{
		return isset($this->count);
	}

	public function setReplacement($replacement){
		if($this->hasReplacement()){
			$this->release($this->replacement);
		}
		return $this->replacement = $this->claim($replacement);
	}

	public function hasReplacement(): bool{
		return isset($this->replacement);
	}

	public function getReplacement(){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasReplacement()){
			if($print){
				Debug::print("{$f} replacement is not defined");
			}
			return "";
		}
		return $this->replacement;
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasCount()){
			$this->setCount(replicate($that->getCount()));
		}
		if($that->hasLimit()){
			$this->setLimit(replicate($that->getLimit()));
		}
		if($that->hasPattern()){
			$this->setPattern(replicate($that->getPattern()));
		}
		if($that->hasReplacement()){
			$this->setReplacement(replicate($that->getReplacement()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->count, $deallocate);
		$this->release($this->limitCount, $deallocate);
		$this->release($this->pattern, $deallocate);
		$this->release($this->replacement, $deallocate);
	}
}
