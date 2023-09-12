<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\control\ControlStatementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\LoopCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\TryCatchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReturnTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class Routine extends Command implements JavaScriptInterface, ValueReturningCommandInterface, SQLInterface
{

	use NamedTrait;
	use ParametricTrait;
	use ReturnTypeTrait;
	use RoutineTypeTrait;
	use ScopedTrait;

	public function __construct($name = null, ...$params)
	{
		parent::__construct(); // ALLOCATION_MODE_UNDEFINED, null);
		                       // $this->setRoutineType(ROUTINE_TYPE_FUNCTION);
		if(isset($name)) {
			$this->setName($name);
		}
		if(!empty($params)) {
			/*
			 * $arr = [];
			 * foreach($params as $param){
			 * array_push($arr, $param);
			 * }
			 */
			$this->setParameters($params);
		} /*
		   * else{
		   * $this->setParamSignature([]);
		   * }
		   */
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			'arrow'
		]);
	}

	public function getReturnNullFlag(): bool
	{
		return $this->getFlag("returnNull");
	}

	public function setReturnNullFlag(bool $value = true): bool
	{
		return $this->setFlag("returnNull", $value);
	}

	public function returnNull(bool $value = true): Routine
	{
		$this->setReturnNullFlag($value);
		return $this;
	}

	public function setArrowFlag(bool $value = true): bool
	{
		return $this->setFlag("arrow", $value);
	}

	public function getArrowFlag(): bool
	{
		return $this->getFlag("arrow");
	}

	public static function arrow(...$params): Routine
	{
		$class = static::class;
		$func = new $class(null, ...$params);
		$func->setArrowFlag(true);
		return $func;
	}

	private function getParamSignatureString()
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->getParamSignatureString()";
		try{
			if(!$this->hasParameters()) { // empty($this->paramSignature)){
				return "";
			}
			$string = "";
			foreach($this->getParameters() as $param) { // paramSignature as $param){
				if(!empty($string)) {
					$string .= ", ";
				}
				$string .= $param;
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasName()
	{
		return isset($this->name) && ! $this->getArrowFlag();
	}

	public function getFunctionSignatureString()
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->getFunctionSignatureString()";
		try{
			$string = "";
			if($this->hasRoutineType() && ! $this->getArrowFlag()) {
				$string .= $this->getRoutineTypeString();
			}
			$name = $this->hasName() ? " " . $this->getName() : null;
			$params = $this->getParamSignatureString();
			$string .= "{$name}({$params})";
			if($this->getArrowFlag()) {
				$string .= " => ";
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function echoJson(bool $destroy = false): void
	{
		echo json_encode($this->toJavaScript());
		if($destroy) {
			$this->dispose();
		}
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try{
			$print = false;

			$cache = false;
			if($this->isCacheable() && JAVASCRIPT_CACHE_ENABLED) {
				if(cache()->hasFile($this->getCacheKey() . ".js")) {
					if($print) {
						Debug::print("{$f} cache hit");
					}
					return cache()->getFile($this->getCacheKey() . ".js");
				}else{
					if($print) {
						Debug::print("{$f} cache miss");
					}
					$cache = true;
				}
			}elseif($print) {
				Debug::print("{$f} this function is not cacheable");
			}

			// Debug::printStackTraceNoExit("{$f} entered");

			$name = $this->hasName() ? $this->getName() : "anonymous";
			if($print) {
				Debug::print("{$f} echoing function \"{$name}\"");
			}
			$string = $this->getFunctionSignatureString() . "{\n";
			if($this->getArrowFlag()) {
				foreach($this->getSubcommands() as $num => $line) {
					$string .= "\t";
					if($line instanceof JavaScriptInterface) {
						$string .= $line->toJavaScript();
					}elseif(is_string($line) || $string instanceof StringifiableInterface) {
						$string .= $line;
					}else{
						Debug::error("{$f} line {$num} is cannot be converted into javascript");
					}
					$string .= ";\n";
				}
			}else{
				$dec = DeclareVariableCommand::let("f", "{$name}()");
				$string .= "\t" . ($dec->toJavaScript()) . "\n";
				$string .= TryCatchCommand::try(...$this->getSubcommands())->catch(CommandBuilder::return(CommandBuilder::call("error", new GetDeclaredVariableCommand("f"), new GetDeclaredVariableCommand("x"))))->toJavaScript();
			}
			$string .= "}\n";
			if($cache) {
				cache()->setFile($this->getCacheKey() . ".js", $string, time() + 30 * 60);
			}

			if($print) {
				Debug::print("{$f} returning \"{$string}\"");
			}

			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function toSQL(): string
	{
		$string = "begin ";
		foreach($this->getSubcommands() as $b) {
			if($b instanceof SQLInterface) {
				$b = $b->toSQL();
			}
			$string .= "{$b}";
		}
		$string .= "end";
		return $string;
	}

	public static function getCommandId(): string
	{
		return "routine";
	}

	public function getRoutineTypeString()
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->getRoutineTypeString()";
		if(!$this->hasRoutineType()) {
			return "";
		}
		$type = strtolower($this->getRoutineType());
		switch ($type) {
			case ROUTINE_TYPE_FUNCTION:
			case ROUTINE_TYPE_STATIC:
			case ROUTINE_TYPE_PROCEDURE:
				return $type;
			default:
				Debug::error("{$f} invalid routine type \"{$type}\"");
				return null;
		}
	}

	public function echo(bool $destroy = false): void
	{
		$js = $this->toJavaScript();
		if($destroy) {
			$this->dispose();
		}
		echo $js;
	}

	public function getScope(): Scope
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->getScope()";
		if(!$this->hasScope()) {
			return app()->getGlobalScope();
		}
		return $this->scope;
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->evaluate()";
		$ret = $this->resolve($params);
		if($ret !== null || $this->getReturnNullFlag()) {
			return $ret;
		}
		$decl = $this->getDeclarationLine();
		Debug::error("{$f} never reached a return statement. Declared {$decl}");
		return;
	}

	public function resolveSubcommands($commands, $scope = null)
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->resolveSubcommands()";
		$print = false;
		foreach($commands as $cmd) {
			if($print) {
				Debug::print("{$f} resolving a " . $cmd->getClass() . " declared " . $cmd->getDeclarationLine());
			}
			if($cmd instanceof ScopedCommandInterface && $scope instanceof Scope) {
				$cmd->setParentScope($scope);
			}
			if($cmd instanceof LoopCommand) {
				Debug::error("{$f} resolution of loops is comletely unsupported, sorry");
			}
			if($cmd instanceof ControlStatementCommand) {
				if($print) {
					Debug::print("{$f} " . $cmd->getClass() . " is a ControlStatementCommand");
				}
				$c2s = $cmd->getEvaluatedCommands();
				$ret = $this->resolveSubcommands($c2s);
				if($ret !== null || $this->getReturnNullFlag()) {
					if($print) {
						Debug::print("{$f} " . $cmd->getClass() . " returned non-null, or the return null flag is set");
					}
					return $ret;
				}elseif($print) {
					Debug::print("{$f} no return value yet");
				}
			}elseif($cmd instanceof ReturnCommand) {
				if($print) {
					Debug::print("{$f} command is a ReturnCommand");
				}
				$ret = $cmd->evaluate();
				if($ret === null) {
					if($print) {
						Debug::print("{$f} returning null");
					}
					$this->returnNull();
				}elseif($print) {
					Debug::print("{$f} returning a non-null value");
				}
				return $ret;
			}else{
				$cmd->resolve();
			}
		}
		if($print) {
			Debug::print("{$f} did not encounter a ReturnCommand");
		}
		return null;
	}

	public function resolve($params)
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->resolve()";
		$print = false;
		if(!$this->hasScope()) {
			$scope = $this->setScope(new Scope());
			$scope->setParentScope(app()->getGlobalScope());
			if(isset($params)) {
				if($print) {
					$did = $scope->getDebugId();
					Debug::print("{$f} assigning the following parameters to scope with debug ID \"{$did}\"");
					Debug::printArray($params);
				}
				foreach($params as $name => $value) {
					$scope->setLocalValue($name, $value);
				}
			}else{
				Debug::printStackTrace("{$f} no parameters");
			}
		}else{
			$scope = $this->getScope();
		}
		return $this->resolveSubcommands($this->getSubcommands(), $scope);
	}

	public function debugPrint()
	{
		$f = __METHOD__; //Routine::getShortClass()."(".static::getShortClass().")->debugPrint()";
		Debug::print($this->getFunctionSignatureString());
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->returnType);
		unset($this->routineType);
		unset($this->name);
		// unset($this->paramSignature);
		unset($this->scope);
	}
}
