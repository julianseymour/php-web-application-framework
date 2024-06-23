<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\common\NullableValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\TypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseScopeEvent;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class DeclareVariableCommand extends Command 
implements JavaScriptInterface, ScopedCommandInterface, ServerExecutableCommandInterface, SQLInterface{

	use IndirectParentScopeTrait;
	use TypeTrait;
	use NullableValuedTrait;
	use VariableNameTrait;

	protected $scopeType;

	public function __construct($name = null, $value = null, $scope = null){
		$f = __METHOD__;
		$print = false;
		parent::__construct();
		if($name !== null ){
			if($name === ""){
				Debug::error("{$f} name is null or empty string");
			}elseif($name instanceof Element){
				$name = $name->getIdOverride();
			}
			$this->setVariableName($name);
		}
		if(isset($value)){
			$this->setValue($value);
		}
		if(isset($scope)){
			$this->setScope($scope);
		}
	}
	
	protected static function getExcludedConstructorFunctionNames():?array{
		return array_merge(parent::getExcludedConstructorFunctionNames(), [
			"const",
			"constColumnValues",
			"constMultiple",
			"let",
			"letColumnValues",
			"letMultiple",
			"redeclare",
			"redeclareColumnValues",
			"redeclareMultiple",
			"var",
			"varColumnValues",
			"varMultiple"
		]);
	}
	
	public static function getCommandId(): string{
		return "declare";
	}

	public static function let($name = null, $value = null, $scope = null): DeclareVariableCommand{
		$f = __METHOD__;
		$print = false;
		$cmd = new DeclareVariableCommand($name, $value, $scope);
		$cmd->setScopeType(SCOPE_TYPE_LET);
		if($print){
			$ds = $cmd->getDebugString();
			Debug::print("{$f} {$ds}");
			Debug::printStackTraceNoExit("{$f} entered; variable name is \"{$name}\".");
		}
		return $cmd;
	}

	public static function var($name, $value = null, $scope = null): DeclareVariableCommand{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		$cmd = new DeclareVariableCommand($name, $value, $scope);
		$cmd->setScopeType(SCOPE_TYPE_VAR);
		return $cmd;
	}

	public static function declare($name, $type = null): DeclareVariableCommand{
		$cmd = new DeclareVariableCommand($name);
		if($type !== null){
			return $cmd->withType($type);
		}
		return $cmd;
	}

	public static function redeclare($name, $value = null, $scope = null):DeclareVariableCommand{
		return new DeclareVariableCommand($name, $value, $scope);
	}

	public static function const($name, $value = null, $scope = null):DeclareVariableCommand{
		$cmd = new DeclareVariableCommand($name, $value, $scope);
		$cmd->setScopeType(SCOPE_TYPE_CONST);
		return $cmd;
	}

	public static function declareElementById($varname, $id){
		return DeclareVariableCommand::let($varname, new GetElementByIdCommand($id));
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"null"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"null"
		]);
	}
	
	public function hasScopeType():bool{
		return isset($this->scopeType);
	}

	public function getScopeType(){
		return $this->hasScopeType() ? $this->scopeType : "var";
	}

	public function setScopeType($st){
		if($this->hasScopeType()){
			$this->release($this->scopeType);
		}
		return $this->scopeType = $this->claim($st);
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Json::echoKeyValuePair('name', $this->getVariableName(), $destroy);
		Json::echoKeyValuePair('value', $this->getValue(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function setScope(?Scope $scope):?Scope{
		return $this->scope = $scope;
	}
	
	public function releaseScope(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasScope()){
			Debug::error("{$f} scope is undefined");
		}
		$scope = $this->scope;
		unset($this->scope);
		if($this->hasAnyEventListener(EVENT_RELEASE_SCOPE)){
			$this->dispatchEvent(new ReleaseScopeEvent($scope, $deallocate));
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasScope()){
			$this->releaseScope($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->scopeType, $deallocate);
		$this->release($this->type, $deallocate);
		$this->release($this->value, $deallocate);
		$this->release($this->variableName, $deallocate);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasScopeType()){
			$this->setScopeType(replicate($this->getScopeType()));
		}
		if($that->hasScope()){
			$this->setScope($that->getScope());
		}
		if($that->hasType()){
			$this->setType(replicate($that->getType()));
		}
		if($that->hasValue()){
			$this->setValue(replicate($that->getValue()));
		}
		if($that->hasVariableName()){
			$this->setVariableName(replicate($that->getVariableName()));
		}
		return $ret;
	}
	
	public static function arrayToObjectString($arr){
		$string = "{\n";
		$count = 0;
		foreach($arr as $key => $value){
			if(is_array($value)){
				$value = static::arrayToObjectString($value);
			}elseif($value instanceof Command){
				$value = $value->toJavaScript();
			}elseif(is_string($value) || $value instanceof StringifiableInterface){
				$value = "'" . escape_quotes($value, QUOTE_STYLE_SINGLE) . "'";
			}
			if($count > 0){
				$string .= ",\n\t";
			}
			$string .= "{$key}:{$value}";
			$count ++;
		}
		$string .= "\n}";
		return $string;
	}

	public static function getArrayDeclarationString($arr){
		$f = __METHOD__;
		$string = "[\n";
		$count = 0;
		foreach($arr as $key => $value){
			if(is_array($value)){
				$value = static::getArrayDeclarationString($value);
			}elseif($value instanceof Command){
				$value = $value->toJavaScript();
			}elseif(is_string($value) || $value instanceof StringifiableInterface){
				$value = "'" . escape_quotes($value, QUOTE_STYLE_SINGLE) . "'";
			}
			if($count > 0){
				$string .= ",\n\t";
			}
			if(is_string($key)){
				$string .= "{$key} => {$value}";
			}elseif(is_int($key)){
				$string .= "{$value}";
			}else{
				$gottype = gettype($key);
				Debug::error("{$f} key is a \"{$gottype}\"");
			}
			$count ++;
		}
		$string .= "]";
		return $string;
	}

	public function toJavaScript():string{
		$f = __METHOD__;
		$print = false;
		$string = "";
		if($this->hasScopeType()){
			$st = $this->getScopeType();
			if($st instanceof JavaScriptInterface){
				$st = $st->toJavaScript();
			}
			$string .= "{$st} ";
		}
		$name = $this->getVariableName();
		$string .= "{$name}";
		if(!$this->hasValue()){
			return $string;
		}
		$value = $this->getValue();
		if($value === null){
			if(!$this->getNullFlag()){
				Debug::error("{$f} value should not be null unless it is flagged as permissible");
			}
			$value = "null";
		}elseif($value instanceof JavaScriptInterface){
			$value = $value->toJavaScript();
		}elseif(is_array($value)){
			if($this->hasEscapeType() && $this->getEscapeType() === ESCAPE_TYPE_OBJECT){
				$value = static::arrayToObjectString($value);
			}else{
				$value = static::getArrayDeclarationString($value);
			}
		}elseif(is_string($value) || $value instanceof StringifiableInterface){
			if($print){
				if(is_string($value)){
					Debug::print("{$f} value is a string");
				}else{
					$type = $value->getClass();
					Debug::print("{$f} value is a \"{$type}\"");
				}
			}
			$q = $this->getQuoteStyle();
			$value = "{$q}" . escape_quotes($value, $q) . "{$q}";
		}elseif(is_bool($value)){
			$value = $value ? "true" : "false";
		}
		$string .= " = {$value}";
		return $string;
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if(!isset($this->value)){
			if($print){
				Debug::print("{$f} value is null");
			}
			if($this->getNullFlag()){
				if($print){
					Debug::print("{$f} null flag is set");
				}
				return null;
			}
			$name = $this->getVariableName();
			while($name instanceof ValueReturningCommandInterface){
				$name = $name->evaluate();
			}
			Debug::error("{$f} value is undefined for variable name \"{$name}\" of this ".$this->getDebugString());
		}elseif($print){
			Debug::print("{$f} value is non-null");
		}
		$value = $this->getValue();
		while($value instanceof ValueReturningCommandInterface){
			$value = $value->evaluate();
		}
		if($print){
			Debug::print("{$f} returning \"{$value}\"");
		}
		return $value;
	}
	
	public function resolve(){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($print){
			Debug::print("{$f} resolving ".$this->getDebugString());
		}
		if(!$this->hasValue()){
			if($print){
				Debug::print("{$f} value is undefined. This is likely a declaration without assignment for the sake of a template function");
			}
			return;
		}
		$value = $this->getValue();
		while($value instanceof ValueReturningCommandInterface){
			$value = $value->evaluate();
		}
		$name = $this->getVariableName();
		while($name instanceof ValueReturningCommandInterface){
			$name = $name->evaluate();
		}
		$scope = $this->getScope();
		while($scope instanceof ValueReturningCommandInterface){
			$scope = $scope->evaluate();
		}
		return $scope->setLocalValue($name, $value);
	}
	
	public function toSQL(): string{
		if(!$this->hasValue()){
			$string = "declare " . $this->getVariableName() . " " . $this->getType() . ";\n";
			return $string;
		}
		$value = $this->getValue();
		if($value instanceof SQLInterface){
			$value = $value->toSQL();
		}elseif(is_string($value) || $value instanceof StringifiableInterface){
			$value = single_quote($value);
		}
		$string = "set " . $this->getVariableName() . " = {$value};\n";
		return $string;
	}
}
