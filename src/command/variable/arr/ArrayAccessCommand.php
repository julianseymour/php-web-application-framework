<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable\arr;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ArrayAccessCommand extends ArrayCommand implements ValueReturningCommandInterface{

	protected $offset;

	public function __construct($array=null, $offset=null){
		parent::__construct($array);
		if($offset !== null){
			$this->setOffset($offset);
		}
	}

	public function setOffset($offset){
		if($this->hasOffset()){
			$this->release($this->offset);
		}
		return $this->offset = $this->claim($offset);
	}

	public function hasOffset():bool{
		return isset($this->offset);
	}

	public function getOffset(){
		$f = __METHOD__;
		if(!$this->hasOffset()){
			Debug::error("{$f} offset is undefined");
		}
		return $this->offset;
	}

	public static function getCommandId(): string{
		return "[]";
	}

	public function evaluate(?array $params = null){
		$array = $this->getArray();
		while($array instanceof ValueReturningCommandInterface){
			$array = $array->evaluate();
		}
		$offset = $this->getOffset();
		while($offset instanceof ValueReturningCommandInterface){
			$offset = $offset->evaluate();
		}
		return $array[$offset];
	}

	public function toJavaScript(): string{
		$array = $this->getArray();
		if($array instanceof JavaScriptInterface){
			$array = $array->toJavaScript();
		}
		$offset = $this->getOffset();
		if($offset instanceof JavaScriptInterface){
			$offset = $offset->toJavaScript();
		}
		return "{$array}[{$offset}]";
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->offset, $deallocate);
	}
}
