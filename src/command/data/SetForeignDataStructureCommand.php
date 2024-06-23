<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetForeignDataStructureCommand extends ForeignDataStructureCommand implements ServerExecutableCommandInterface{

	protected $foreignDataStructure;

	public function __construct($context=null, $column_name=null, $fds = null){
		parent::__construct($context, $column_name);
		if(isset($fds)){
			$this->setForeignDataStructure($fds);
		}
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->foreignDataStructure, $deallocate);
	}
	
	public function setForeignDataStructure($fds){
		if($this->hasForeignDataStructure()){
			$this->release($this->foreignDataStructure);
		}
		return $this->foreignDataStructure = $this->claim($fds);
	}

	public function hasForeignDataStructure():bool{
		return isset($this->foreignDataStructure);
	}

	public function getForeignDataStructure(){
		$f = __METHOD__;
		if(!$this->hasForeignDataStructure()){
			Debug::error("{$f} foreign data structure is undefined");
		}
		return $this->foreignDataStructure;
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface){
			$idcs = $idcs->toJavaScript();
		}
		$column_name = $this->getColumnName();
		if($column_name instanceof JavaScriptInterface){
			$column_name = $column_name->toJavaScript();
		}elseif(is_string($column_name) || $column_name instanceof StringifiableInterface){
			$column_name = single_quote($column_name);
		}
		$fds = $this->getForeignDataStructure();
		if($fds instanceof JavaScriptInterface){
			$fds = $fds->toJavaScript();
		}else{
			Debug::error("{$f} foreign data structure cannot be converted to javascript");
		}
		$cs = $this->getCommandId();
		return "{$idcs}.{$cs}({$column_name}, {$fds})";
	}

	public static function getCommandId(): string{
		return "setForeignDataStructure";
	}

	public function resolve(){
		$context = $this->getDataStructure();
		while($context instanceof ValueReturningCommandInterface){
			$context = $context->evaluate();
		}
		$column_name = $this->getColumnName();
		while($column_name instanceof ValueReturningCommandInterface){
			$column_name = $column_name->evaluate();
		}
		$fds = $this->getForeignDataStructure();
		while($fds instanceof ValueReturningCommandInterface){
			$fds = $fds->evaluate();
		}
		$context->setForeignDataStructure($column_name, $fds);
	}
}
