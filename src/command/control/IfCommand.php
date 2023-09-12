<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasChildrenCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ParentScopeInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ParentScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

/**
 * if a condition is satisfied, invoke thenCommands; otherwise invoke elseCommands
 */
class IfCommand extends ControlStatementCommand implements ParentScopeInterface, SQLInterface{

	use ElseBlockTrait;
	use ParentScopedTrait;

	public function __construct($expr = null, $then = null, $else = null){
		$f = __METHOD__;
		parent::__construct($expr);
		if(!empty($then)) {
			$this->setThenCommands($then);
		}
		if(!empty($else)) {
			$this->setElseCommands($else);
		}
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"subsequent"
		]);
	}

	public function setSubsequentFlag(bool $value): bool{
		return $this->setFlag("subsequent", $value);
	}

	public function getSubsequentFlag(): bool{
		return $this->getFlag("subsequent");
	}

	public static function hasForeignDataStructureList($context, string $phylum){
		return static::if(new HasChildrenCommand($context, $phylum));
	}

	public static function hasForeignDataStructure($context, string $phylum){
		return static::if(new HasForeignDataStructureCommand($context, $phylum));
	}

	public static function hasColumnValue($context, string $index){
		$f = __METHOD__;
		if($context instanceof DataStructure) {
			$expr = new HasColumnValueCommand($context, $index); // ->hasColumnValueCommand($index);
		}else{
			$expr = new HasColumnValueCommand($context, $index);
		}
		return static::if($expr);
	}

	public static function equals($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::equals($lhs, $rhs));
	}

	public static function assign($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::assign($lhs, $rhs));
	}

	public static function lessThan($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::lessThan($lhs, $rhs));
	}

	public static function lessThanOrEquals($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::lessThanOrEquals($lhs, $rhs));
	}

	public static function greaterThan($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::greaterThan($lhs, $rhs));
	}

	public static function greaterThanOrEquals($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::greaterThanOrEquals($lhs, $rhs));
	}

	public static function notEquals($lhs, $rhs): IfCommand{
		return static::if(BinaryExpressionCommand::notEquals($lhs, $rhs));
	}

	/**
	 * alternative promise-like syntax
	 *
	 * @param ExpressionCommand $expr
	 * @return IfCommand
	 */
	public static function if($expr){
		$class = static::class;
		return new $class($expr);
	}

	public function then(...$blocks){
		return $this->withProperty("then", $blocks);
	}

	public function getEvaluatedCommands(){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			$ex = $this->getExpression();
			if($ex instanceof ValueReturningCommandInterface){
				if($print){
					$did = $ex->getDebugId();
					$decl = $ex->getDeclarationLine();
					Debug::print("{$f} about to evaluate expression with debug ID {$did} declared on {$decl}");
				}
				$evaluated = $ex->evaluate();
			}else{
				if($print){
					Debug::print("{$f} expression is not a value-returning commandd interface");
				}
				$evaluated = $ex;
			}
			if($evaluated) {
				if($this->hasThenCommands()) {
					if($print) {
						Debug::print("{$f} expression evaluates to true and there are then commands");
					}
					return $this->getThenCommands();
				}elseif($print) {
					Debug::print("{$f} expression evaluates to true and there are no then commands");
				}
			}elseif($this->hasElseCommands()) {
				if($print) {
					Debug::print("{$f} expression evaluates to false and there are else commands");
				}
				return $this->getElseCommands();
			}elseif($print) {
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} expression evaluates to false and there are no else commands. Declared {$decl}");
			}
			return [];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function resolve(){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			if($print){
				Debug::print("{$f} entered");
			}
			$commands = $this->getEvaluatedCommands();
			foreach($commands as $cmd) {
				if($print){
					$cc = $cmd->getShortClass();
					$did = $cmd->getDebugId();
					$decl = $cmd->getDeclarationLine();
					Debug::print("{$f} about to call resolve() on a {$cc} with debug ID \"{$did}\", declared {$decl}");
				}
				$cmd->resolve();
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getCommandId(): string{
		return "if";
	}

	public function hasThenCommands(){
		return $this->hasArrayProperty("then");
	}

	public function getThenCommands(){
		$f = __METHOD__;
		if(!$this->hasThenCommands()) {
			Debug::error("{$f} then commands are undefined");
		}
		return $this->getProperty("then");
	}

	public function setThenCommands($then){
		$f = __METHOD__;
		try{
			if(!is_array($then)) {
				$then = [
					$then
				];
			}
			return $this->setArrayPropertY("then", $then);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		if($this->hasThenCommands()) {
			Json::echoKeyValuePair('then', $this->getThenCommands(), $destroy);
		}
		if($this->hasElseCommands()) {
			Json::echoKeyValuePair('elseCommands', $this->getElseCommands(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$result = $this->getExpression()->evaluate();
		if($print) {
			if($result) {
				Debug::print("{$f} condition satisfied");
			}else{
				Debug::print("{$f} condition failed");
			}
		}
		return $result;
	}

	public function elseif($expression): IfCommand{
		if($this->hasElseCommands() && $this->getElseCommandCount() === 1) {
			$elseCommands = $this->getElseCommands();
			if($elseCommands[0] instanceof IfCommand) {
				return $elseCommands[0]->elseif($expression);
			}
		}
		$class = static::class;
		$else = new $class($expression);
		$else->setSubsequentFlag(true);
		$this->setElseCommands([
			$else
		]);
		return $else;
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$conditional = $this->getExpression();
			if($conditional instanceof JavaScriptInterface) {
				$conditional = $conditional->toJavaScript();
			}elseif(is_bool($conditional)) {
				if($conditional) {
					$conditional = "true";
				}else{
					$conditional = "false";
				}
			}
			if($print) {
				Debug::print("{$f} about to convert the if statement to string");
			}
			$string = "if({$conditional}){\n";
			if($print) {
				Debug::print("{$f} converted the if statement to string");
			}
			foreach($this->getThenCommands() as $then) {
				if(!$then instanceof JavaScriptInterface) {
					$type = gettype($then);
					Debug::error("{$f} then command is not a javascript command! It's a {$type}");
				}
				$string .= $then->toJavaScript() . ";\n";
				if($print) {
					Debug::print("{$f} converted a then command to string");
				}
			}
			if($this->hasElseCommands()) {
				$string .= "}else";
				$elseCommands = $this->getElseCommands();
				if($this->getElseCommandCount() === 1 && $elseCommands[0] instanceof IfCommand) {
					$string .= " " . $elseCommands[0]->toJavaScript();
					$string .= "//end else if";
				}else{
					$string .= "{\n";
					foreach($elseCommands as $else) {
						if(!$else instanceof JavaScriptInterface) {
							Debug::error("{$f} else command is not a javascript command!");
						}
						$string .= $else->toJavaScript() . ";\n";
						if($print) {
							Debug::print("{$f} converted else command to string");
						}
					}
					$string .= "}\n";
				}
			}else{
				$string .= "}\n";
			}
			if($print) {
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$conditional = $this->getExpression();
			if($conditional instanceof SQLInterface) {
				$conditional = $conditional->toSQL();
			}
			if($print) {
				Debug::print("{$f} about to convert the if statement to string");
			}
			$string = "if {$conditional} then\n";
			if($print) {
				Debug::print("{$f} converted the if statement to string");
			}
			foreach($this->getThenCommands() as $then) {
				if(!$then instanceof SQLInterface) {
					$type = gettype($then);
					Debug::error("{$f} then command is not a SQL command! It's a {$type}");
				}
				$string .= $then->toSQL() . "\n";
				if($print) {
					Debug::print("{$f} converted a then command to string");
				}
			}
			if($this->hasElseCommands()) {
				$string .= "else";
				$elseCommands = $this->getElseCommands();
				if($this->getElseCommandCount() === 1 && $elseCommands[0] instanceof IfCommand) {
					$elseCommands[0]->setSubsequentFlag(true);
				}else{
					$string .= "\n";
				}
				foreach($elseCommands as $else) {
					if(!$else instanceof SQLInterface) {
						Debug::error("{$f} else command is not an SQL command!");
					}
					$string .= $else->toSQL() . "\n";
					if($print) {
						Debug::print("{$f} converted else command to string");
					}
				}
			}
			if(!$this->getSubsequentFlag()) {
				$string .= "end if;\n";
			}
			if($print) {
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function and(...$conditions): IfCommand{
		return new IfCommand(new AndCommand(...$conditions));
	}

	public static function or(...$conditions): IfCommand{
		return new IfCommand(new OrCommand(...$conditions));
	}
}
