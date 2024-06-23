<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class Scope extends Basic implements ReplicableInterface{

	use ArrayPropertyTrait;
	use ParentScopedTrait;
	use ReplicableTrait;

	protected static function getExcludedConstructorFunctionNames():?array{
		return array_merge(parent::getExcludedConstructorFunctionNames(), [
			"getResolvedKey", 
			"getResolvedScope"
		]);
	}
	
	public function copy($that):int{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
		$ret = parent::copy($that);
		if($that->hasParentScope()){
			$this->setParentScope(replicate($that->getParentScope()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		if($this->hasParentScope()){
			$this->release($this->parentScope, $deallocate);
		}
	}
	
	public function hasLocalValues():bool{
		return $this->hasArrayProperty('localValues');
	}
	
	public function setLocalValues(?array $lv):?array{
		return $this->setArrayProperty('localValues', $lv);
	}
	
	public function getLocalValues():array{
		return $this->getProperty('localValues');
	}
	
	public function setLocalValue($index, $value){
		$f = __METHOD__;
		if(!is_int($index) && ! is_string($index)){
			Debug::error("{$f} index is neither integer nor string");
		}
		return $this->setArrayPropertyValue('localValues', $index, $value);
	}

	public function hasLocalValue($index){
		return $this->hasArrayPropertyKey('localValues', $index);
	}

	public function const($name, $value=null):DeclareVariableCommand{
		return DeclareVariableCommand::const($name, $value, $this);
	}
	
	public function let($name, $value = null): DeclareVariableCommand{
		return DeclareVariableCommand::let($name, $value, $this);
	}

	public function redeclare($name, $value = null){
		return new DeclareVariableCommand($name, $value, $this);
	}

	public function var($name, $value = null): DeclareVariableCommand{
		return DeclareVariableCommand::var($name, $value, $this);
	}

	public function getLocalValue($index){
		$f = __METHOD__;
		if(!$this->hasLocalValue($index)){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} local variable \"{$index}\" is undefined. Declared {$decl}");
		}
		return $this->getArrayPropertyValue('localValues', $index); //$this->localValues[$index];
	}

	protected function declareVariableCommand($name, $value):DeclareVariableCommand{
		return new DeclareVariableCommand($name, $value, $this);
	}

	protected function declareMultiple(?string $scope_type, array $arr){
		$f = __METHOD__;
		if(empty($arr)){
			Debug::error("{$f} received empty array as input parameter");
		}elseif(!is_associative($arr)){
			Debug::error("{$f} array must be associative");
		}
		$ret = [];
		foreach($arr as $name => $value){
			$dec = $this->declareVariableCommand($name, $value);
			if($scope_type !== null){
				$dec->setScopeType($scope_type);
			}
			array_push($ret, $dec);
		}
		return $ret;
	}

	public function letNames(...$names): array{
		$ret = [];
		foreach($names as $name){
			array_push($ret, $this->let($name));
		}
		return $ret;
	}

	public function letMultiple(array $arr): array{
		return $this->declareMultiple(SCOPE_TYPE_LET, $arr);
	}

	public function varMultiple(array $arr): array{
		return $this->declareMultiple(SCOPE_TYPE_VAR, $arr);
	}

	public function constMultiple(array $arr): array{
		return $this->declareMultiple(SCOPE_TYPE_CONST, $arr);
	}

	public function redeclareMultiple(array $arr): array{
		return $this->declareMultiple(null, $arr);
	}

	protected function declareColumnValues(?string $scope_type, object $obj, ...$column_names): array{
		$f = __METHOD__;
		if(!isset($column_names) || !is_array($column_names) || empty($column_names)){
			Debug::error("{$f} column names must be a non-empty array");
		}
		$ret = [];
		foreach($column_names as $name){
			$value = new GetColumnValueCommand($obj, $name);
			$dec = $this->declareVariableCommand($name, $value);
			if($scope_type !== null){
				$dec->setScopeType($scope_type);
			}
			array_push($ret, $dec);
		}
		return $ret;
	}

	public function letColumnValues(object $obj, ...$column_names): array{
		return $this->declareColumnValues(SCOPE_TYPE_LET, $obj, ...$column_names);
	}

	public function varColumnValues(object $obj, ...$column_names): array{
		return $this->declareColumnValues(SCOPE_TYPE_VAR, $obj, ...$column_names);
	}

	public function constColumnValues(object $obj, ...$column_names): array{
		return $this->declareColumnValues(SCOPE_TYPE_CONST, $obj, ...$column_names);
	}

	public function redeclareColumnValues(object $obj, ...$column_names): array{
		return $this->declareColumnValues(null, $obj, ...$column_names);
	}

	public function getDeclaredVariableCommand($name):GetDeclaredVariableCommand{
		$f = __METHOD__;
		$print = false;
		$gdvc = new GetDeclaredVariableCommand();
		if($print){
			$did = $gdvc->getDebugId();
			Debug::print("{$f} instantiated a new GetDeclaredVariableCommand with variable name {$name} and debug ID {$did}");
		}
		$gdvc->setVariableName($name);
		$gdvc->setScope($this);
		return $gdvc;
	}
}
