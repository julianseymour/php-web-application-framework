<?php

namespace JulianSeymour\PHPWebApplicationFramework\command;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\control\ControlStatementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\LoopCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\TryCatchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReturnTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class Routine extends Basic implements CacheableInterface, JavaScriptInterface, ValueReturningCommandInterface, SQLInterface{

	use CacheableTrait;
	use CodeBlocksTrait;
	use NamedTrait;
	use ParametricTrait;
	use ReturnTypeTrait;
	use RoutineTypeTrait;
	use ScopedTrait;

	public function __construct($name = null, ...$params){
		parent::__construct();
		if(isset($name)){
			$this->setName($name);
		}
		if(!empty($params)){
			$this->setParameters($params);
		}
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			'arrow'
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"arrow"
		]);
	}
	
	public function getReturnNullFlag(): bool{
		return $this->getFlag("returnNull");
	}

	public function setReturnNullFlag(bool $value = true): bool{
		return $this->setFlag("returnNull", $value);
	}

	public function returnNull(bool $value = true): Routine{
		$this->setReturnNullFlag($value);
		return $this;
	}

	public function setArrowFlag(bool $value = true): bool{
		if($value && isset($this->name)){
			$this->release($this->name);
		}
		return $this->setFlag("arrow", $value);
	}

	public function setName(?string $name):?string{
		$f = __METHOD__;
		if(!is_string($name) && !$name instanceof ValueReturningCommandInterface){
			Debug::error("{$f} name must be a string or value-returning command");
		}elseif(isset($this->name)){
			$this->release($this->name);
		}
		return $this->name = $this->claim($name);
	}
	
	public function getArrowFlag(): bool{
		return $this->getFlag("arrow");
	}

	public static function arrow(...$params): Routine{
		$class = static::class;
		$func = new $class(null, ...$params);
		$func->setArrowFlag(true);
		return $func;
	}

	private function getParamSignatureString(){
		$f = __METHOD__;
		try{
			if(!$this->hasParameters()){
				return "";
			}
			$string = "";
			foreach($this->getParameters() as $param){
				if(!empty($string)){
					$string .= ", ";
				}
				$string .= $param;
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasName():bool{
		return isset($this->name) && !$this->getArrowFlag();
	}

	public function getFunctionSignatureString():string{
		$f = __METHOD__;
		try{
			$string = "";
			if($this->hasRoutineType() && ! $this->getArrowFlag()){
				$string .= $this->getRoutineTypeString();
			}
			$name = $this->hasName() ? " " . $this->getName() : null;
			$params = $this->getParamSignatureString();
			$string .= "{$name}({$params})";
			if($this->getArrowFlag()){
				$string .= " => ";
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function echoJson(bool $destroy = false): void{
		echo json_encode($this->toJavaScript());
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$cache = false;
			if($this->isCacheable() && JAVASCRIPT_CACHE_ENABLED){
				if(cache()->hasFile($this->getCacheKey().".js")){
					if($print){
						Debug::print("{$f} cache hit");
					}
					return cache()->getFile($this->getCacheKey().".js");
				}else{
					if($print){
						Debug::print("{$f} cache miss");
					}
					$cache = true;
				}
			}elseif($print){
				Debug::print("{$f} this function is not cacheable");
			}
			// Debug::printStackTraceNoExit("{$f} entered");
			$name = $this->hasName() ? $this->getName() : "anonymous";
			if($print){
				Debug::print("{$f} echoing function \"{$name}\"");
			}
			$string = $this->getFunctionSignatureString() . "{\n";
			if($this->getArrowFlag()){
				foreach($this->getCodeBlocks() as $num => $line){
					$string .= "\t";
					if($line instanceof JavaScriptInterface){
						$string .= $line->toJavaScript();
					}elseif(is_string($line) || $string instanceof StringifiableInterface){
						$string .= $line;
					}else{
						Debug::error("{$f} line {$num} is cannot be converted into javascript");
					}
					$string .= ";\n";
				}
			}else{
				$dec = new DeclareVariableCommand();
				$dec->setScopeType(SCOPE_TYPE_LET);
				$dec->setVariableName("f");
				$dec->setValue("{$name}()");
				$string .= "\t".($dec->toJavaScript())."\n";
				deallocate($dec);
				$trycatch = new TryCatchCommand();
				//$trycatch->setDisableClaimFlag(true);
				$trycatch->setTryBlocks($this->getCodeBlocks());
				$trycatch->catch(
					new ReturnCommand(
						new CallFunctionCommand(
							"error", 
							new GetDeclaredVariableCommand("f"), 
							new GetDeclaredVariableCommand("x")
						)
					)
				);
				$string .= $trycatch->toJavaScript();
				deallocate($trycatch);
			}
			$string .= "}\n";
			if($cache){
				cache()->setFile($this->getCacheKey() . ".js", $string, time() + 30 * 60);
			}
			if($print){
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function toSQL(): string{
		$string = "begin ";
		foreach($this->getCodeBlocks() as $b){
			if($b instanceof SQLInterface){
				$b = $b->toSQL();
			}
			$string .= "{$b}";
		}
		$string .= "end";
		return $string;
	}

	public static function getCommandId(): string{
		return "routine";
	}

	public function getRoutineTypeString():string{
		$f = __METHOD__;
		if(!$this->hasRoutineType()){
			return "";
		}
		$type = strtolower($this->getRoutineType());
		switch($type){
			case ROUTINE_TYPE_FUNCTION:
			case ROUTINE_TYPE_STATIC:
			case ROUTINE_TYPE_PROCEDURE:
				return $type;
			default:
				Debug::error("{$f} invalid routine type \"{$type}\"");
				return null;
		}
	}

	public function echo(bool $destroy = false): void{
		$js = $this->toJavaScript();
		echo $js;
	}

	public function getScope(): Scope{
		$f = __METHOD__;
		if(!$this->hasScope()){
			return app()->getGlobalScope();
		}
		return $this->scope;
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$ret = $this->resolve($params);
		if($ret !== null || $this->getReturnNullFlag()){
			return $ret;
		}
		$decl = $this->getDeclarationLine();
		Debug::error("{$f} never reached a return statement. Declared {$decl}");
		return;
	}

	public function resolveCodeBlocks($commands, ?Scope $scope = null){
		$f = __METHOD__;
		$print = false;
		foreach($commands as $cmd){
			if($print){
				Debug::print("{$f} resolving a " . $cmd->getClass() . " declared " . $cmd->getDeclarationLine());
			}
			if($cmd instanceof ScopedCommandInterface && $scope instanceof Scope){
				$cmd->setParentScope($scope);
			}
			if($cmd instanceof LoopCommand){
				Debug::error("{$f} resolution of loops is comletely unsupported, sorry");
			}
			if($cmd instanceof ControlStatementCommand){
				if($print){
					Debug::print("{$f} " . $cmd->getClass() . " is a ControlStatementCommand");
				}
				$c2s = $cmd->getEvaluatedCommands();
				$ret = $this->resolveCodeBlocks($c2s);
				if($ret !== null || $this->getReturnNullFlag()){
					if($print){
						Debug::print("{$f} " . $cmd->getClass() . " returned non-null, or the return null flag is set");
					}
					return $ret;
				}elseif($print){
					Debug::print("{$f} no return value yet");
				}
			}elseif($cmd instanceof ReturnCommand){
				if($print){
					Debug::print("{$f} command is a ReturnCommand");
				}
				$ret = $cmd->evaluate();
				if($ret === null){
					if($print){
						Debug::print("{$f} returning null");
					}
					$this->returnNull();
				}elseif($print){
					Debug::print("{$f} returning a non-null value");
				}
				return $ret;
			}else{
				$cmd->resolve();
			}
		}
		if($print){
			Debug::print("{$f} did not encounter a ReturnCommand");
		}
		return null;
	}

	public function resolve($params){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasScope()){
			$scope = $this->setScope(new Scope());
			$scope->setParentScope(app()->getGlobalScope());
			if(isset($params)){
				if($print){
					$did = $scope->getDebugId();
					Debug::print("{$f} assigning the following parameters to scope with debug ID \"{$did}\"");
					Debug::printArray($params);
				}
				foreach($params as $name => $value){
					$scope->setLocalValue($name, $value);
				}
			}else{
				Debug::printStackTrace("{$f} no parameters");
			}
		}else{
			$scope = $this->getScope();
		}
		return $this->resolveCodeBlocks($this->getCodeBlocks(), $scope);
	}

	public function debugPrint(){
		$f = __METHOD__;
		Debug::print($this->getFunctionSignatureString());
	}

	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		if($this->hasScope()){
			$this->releaseScope($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		$this->release($this->returnType, $deallocate);
		$this->release($this->routineType, $deallocate);
	}
}
