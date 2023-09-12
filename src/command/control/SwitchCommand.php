<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;


use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\IndirectParentScopeTrait;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ParentScopeInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SwitchCommand extends ControlStatementCommand implements ParentScopeInterface, ScopedCommandInterface, ServerExecutableCommandInterface
{

	use IndirectParentScopeTrait;
	use ScopedTrait;

	protected $cases;

	protected $default;

	public static function getCommandId(): string
	{
		return "switch";
	}

	public function __construct($expr = null, $cases = null, $default = null)
	{
		parent::__construct($expr);
		/*
		 * if(isset($expr)){
		 * $this->setExpression($expr);
		 * }
		 */
		if(!empty($cases)) {
			$this->setCases($cases);
		}
		if(!empty($default)) {
			$this->setDefault($default);
		}
	}

	/*
	 * public function setExpression($expr){
	 * return $this->expression = $expr;
	 * }
	 *
	 * public function hasExpression(){
	 * return isset($this->expression);
	 * }
	 *
	 * public function getExpression(){
	 * $f = __METHOD__;
	 * if(!$this->hasExpression()){
	 * Debug::error("{$f} expression is undefined");
	 * }
	 * return $this->expression;
	 * }
	 */
	public function setCases($cases)
	{
		return $this->cases = $cases;
	}

	public function hasCases()
	{
		return is_array($this->cases) && ! empty($this->cases);
	}

	public function getCases()
	{
		$f = __METHOD__;
		if(!$this->hasCases()) {
			Debug::error("{$f} cases are undefined");
		}
		return $this->cases;
	}

	public function setDefault(...$default)
	{
		if(count($default) === 1 && is_array($default[0])) {
			$default = $default[0];
		}
		return $this->default = $default;
	}

	public function hasDefault()
	{
		return is_array($this->default) && ! empty($this->default);
	}

	public function getDefault()
	{
		$f = __METHOD__;
		if(!$this->hasDefault()) {
			Debug::error("{$f} default is undefined");
		}
		return $this->default;
	}

	public function toJavaScript(): string
	{
		// $f = __METHOD__;
		$expr = $this->getExpression();
		if($expr instanceof JavaScriptInterface) {
			$expr = $expr->toJavaScript();
		}
		$string = "switch({$expr}){\n";
		$cases = $this->getCases();
		foreach(array_keys($cases) as $case) {
			if($case instanceof JavaScriptInterface) {
				$escaped = $case->toJavaScript();
			}elseif(is_string($case) || $case instanceof StringifiableInterface) {
				$escaped = single_quote($case);
			}else{
				$escaped = $case;
			}
			$string .= "\tcase {$escaped}:\n";
			foreach($cases[$case] as $command) {
				$string .= "\t\t" . $command->toJavaScript() . ";\n";
			}
		}
		$string .= "\tdefault:\n";
		$default = $this->getDefault();
		foreach($default as $d) {
			$string .= "\t\t" . $d->toJavaScript() . ";\n";
		}
		$string .= "}\n";
		return $string;
	}

	public function resolve()
	{
		$commands = $this->getEvaluatedCommands();
		foreach($commands as $c) {
			/*
			 * if($c instanceof ReturnCommand){
			 * $ret = $c->evaluate();
			 * if($ret === null){
			 * $this->setReturnNullFlag(true);
			 * }
			 * return $ret;
			 * }
			 * if($c instanceof ValueReturningCommandInterface){
			 * $ret = $c->evaluate();
			 * if($ret !== null || $c->getReturnNullFlag()){
			 * if($ret === null){
			 * $this->returnNull();
			 * }
			 * return $ret;
			 * }
			 *
			 * }
			 */
			$c->resolve();
		}
	}

	public static function switch($expr): SwitchCommand
	{
		$class = static::class;
		$switch = new $class();
		$switch->setExpression($expr);
		return $switch;
	}

	public function case($value, ...$blocks)
	{
		$case = [];
		foreach($blocks as $b) {
			array_push($case, $b);
		}
		if(!is_array($this->cases)) {
			$this->cases = [];
		}
		$this->cases[$value] = $case;
		return $this;
	}

	public function default(...$blocks)
	{
		$this->setDefault(...$blocks);
		return $this;
	}

	public function getEvaluatedCommands()
	{
		$f = __METHOD__;
		try{
			$print = false;
			$match = false;
			$expr = $this->getExpression();
			if($print) {
				if(is_object($expr)) {
					$decl = $expr->getDeclarationLine();
					$class = $expr->getClass();
					Debug::print("{$f} expression is a {$class} declared {$decl}");
				}else{
					$gottype = gettype($expr);
					Debug::print("{$f} expression is the {$gottype} \"{$expr}\"");
				}
			}
			if($expr instanceof Command) {
				if($expr instanceof ScopedCommandInterface && ! $expr->hasScope()) {
					$expr->setScope($this->getScope());
				}
				while ($expr instanceof ValueReturningCommandInterface) {
					$expr = $expr->evaluate();
				}
			}
			if($print) {
				Debug::print("{$f} after evaluation, expression is \"{$expr}\"");
			}
			$cases = $this->getCases();
			$return_us = [];
			$break = false;
			foreach(array_keys($cases) as $case) {
				if($print) {
					Debug::print("{$f} case \"{$case}\"");
				}
				if($break) {
					if($print) {
						Debug::print("{$f} breaking");
					}
					break;
				}
				if(!$match) {
					if($case !== $expr) {
						if($print) {
							Debug::print("{$f} case \"{$case}\" does not match expression \"{$expr}\", continuing");
						}
						continue;
					}elseif($print) {
						Debug::print("{$f} case \"{$case}\" matches expression \"{$expr}\"");
					}
					$match = true;
				}elseif($print) {
					Debug::print("{$f} no match found yet ");
				}
				foreach($cases[$case] as $command) {
					if($command instanceof BreakCommand) {
						$break = true;
						break;
					}
					array_push($return_us, $command);
				}
			}
			if($match) {
				if($print) {
					$count = count($return_us);
					Debug::print("{$f} returning {$count} commands");
				}
				return $return_us;
			}
			$default = $this->getDefault();
			foreach($default as $command) {
				if($command instanceof BreakCommand) {
					break;
				}
				array_push($return_us, $command);
			}
			if($print) {
				$count = count($return_us);
				Debug::print("{$f} returning {$count} commands");
				foreach($return_us as $cmd) {
					Debug::print($cmd->getClass() . " declared " . $cmd->getDeclarationLine());
				}
			}
			return $return_us;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function cases(array $cases): SwitchCommand
	{
		$this->setCases($cases);
		return $this;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->cases);
		unset($this->default);
	}

	/*
	 * public function evaluate(?array $params=null){
	 * $f = __METHOD__; //SwitchCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
	 * $ret = $this->resolve();
	 * if($ret !== null || $this->getReturnNullFlag()){
	 * return $ret;
	 * }
	 * Debug::error("{$f} no return value");
	 * return;
	 * }
	 */
}
