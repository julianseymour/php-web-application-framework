<?php

namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\MultipleElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CodeBlocksTrait{
	
	use ArrayPropertyTrait;
	
	public function setCodeBlocks($blocks){
		return $this->setArrayProperty("codeBlocks", $blocks);
	}
	
	public function hasCodeBlocks():bool{
		return $this->hasArrayProperty("codeBlocks");
	}
	
	public function getCodeBlocks(){
		return $this->getProperty("codeBlocks");
	}
	
	public function pushCodeBlock(...$blocks):int{
		return $this->pushArrayProperty("codeBlocks", ...$blocks);
	}
	
	public function getCodeBlockCount():int{
		return $this->getArrayPropertyCount("codeBlocks");
	}
	
	/**
	 * do not put this inside a try/catch block because that would break TryCatchCommand->resolve
	 *
	 * @return string
	 */
	public function resolveCodeBlocks($blocks, ?Scope $scope=null):int{
		$f = __METHOD__;
		$print = false;
		foreach($blocks as $b){
			if($print){
				$bc = $b->getClass();
				Debug::print("{$f} resolving a code block of class \"{$bc}\"");
			}
			if($b instanceof ElementCommand || $b instanceof MultipleElementCommand){
				$b->setTemplateLoopFlag(true);
			}
			$b->resolve();
		}
		return SUCCESS;
	}
	
	public function withCodeBlocks(...$blocks){
		$this->setCodeBlocks($blocks);
		return $this;
	}
}