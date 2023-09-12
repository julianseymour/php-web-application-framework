<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\TypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class DeclareVariableCommand extends Command implements JavaScriptInterface, ScopedCommandInterface, ServerExecutableCommandInterface, SQLInterface
{

	use IndirectParentScopeTrait;
	use TypeTrait;
	use ValuedTrait;
	use VariableNameTrait;

	protected $scopeType;

	public static function getCommandId(): string
	{
		return "var";
	}

	public static function let($name, $value = null, $scope = null): DeclareVariableCommand{
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::printStackTraceNoExit("{$f} entered; variable name is \"{$name}\"");
		}
		$cmd = new DeclareVariableCommand($name, $value, $scope);
		$cmd->setScopeType(SCOPE_TYPE_LET);
		return $cmd;
	}

	public static function var($name, $value = null, $scope = null): DeclareVariableCommand
	{
		$f = __METHOD__; //DeclareVariableCommand::getShortClass()."(".static::getShortClass().")::var()";
		$print = false;
		if($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		$cmd = new DeclareVariableCommand($name, $value, $scope);
		$cmd->setScopeType(SCOPE_TYPE_VAR);
		return $cmd;
	}

	public static function declare($name, $type = null): DeclareVariableCommand
	{
		$cmd = new DeclareVariableCommand($name);
		if($type !== null) {
			return $cmd->withType($type);
		}
		return $cmd;
	}

	public static function redeclare($name, $value = null, $scope = null): DeclareVariableCommand
	{
		return new DeclareVariableCommand($name, $value, $scope);
	}

	public static function const($name, $value = null, $scope = null): DeclareVariableCommand
	{
		$cmd = new DeclareVariableCommand($name, $value, $scope);
		$cmd->setScopeType(SCOPE_TYPE_CONST);
		return $cmd;
	}

	public function __construct($name, $value = null, $scope = null)
	{
		$f = __METHOD__; //DeclareVariableCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		$print = false;
		parent::__construct();
		if($name === null || $name === "") {
			Debug::error("{$f} name is null or empty string");
		}elseif($name instanceof Element) {
			$name = $name->getIdOverride();
		}
		$this->setVariableName($name);
		if(isset($value)) {
			$this->setValue($value);
		}
		if(isset($scope)) {
			$this->setScope($scope);
			if($this->hasVariableName() && $this->hasValue()) {
				if($print) {
					$did = $this->getDebugId();
					Debug::print("{$f} setting variable \"{$name}\"; debug ID is \"{$did}\"");
				}
				$scope->setLocalValue($this->getVariableName(), $this->getValue());
			}
		}
	}

	public static function declareElementById($varname, $id)
	{
		return DeclareVariableCommand::let($varname, new GetElementByIdCommand($id));
	}

	public function setNullFlag(bool $value = true): bool
	{
		return $this->setFlag("null", $value);
	}

	public function getNullFlag(): bool
	{
		return $this->getFlag("null");
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"null",
			"valueAssigned"
		]);
	}

	public function hasScopeType()
	{
		return isset($this->scopeType);
	}

	public function getScopeType()
	{
		return $this->hasScopeType() ? $this->scopeType : "var";
	}

	public function setScopeType($st)
	{
		return $this->scopeType = $st;
	}

	public function hasValue()
	{
		return $this->getFlag("valueAssigned");
	}

	public function setValue($value)
	{
		$this->value = $value;
		if($this->hasScope() && $this->hasVariableName()) {
			$this->getScope()->setLocalValue($this->getVariableName(), $value);
		}
		$this->setFlag("valueAssigned", true);
		return $this->getValue();
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //DeclareVariableCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		Json::echoKeyValuePair('name', $this->getVariableName(), $destroy);
		Json::echoKeyValuePair('value', $this->getValue(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->scopeType);
		unset($this->value);
	}

	public static function arrayToObjectString($arr)
	{
		$string = "{\n";
		$count = 0;
		foreach($arr as $key => $value) {
			if(is_array($value)) {
				$value = static::arrayToObjectString($value);
			}elseif($value instanceof Command) {
				$value = $value->toJavaScript();
			}elseif(is_string($value) || $value instanceof StringifiableInterface) {
				$value = "'" . escape_quotes($value, QUOTE_STYLE_SINGLE) . "'";
			}
			if($count > 0) {
				$string .= ",\n\t";
			}
			$string .= "{$key}:{$value}";
			$count ++;
		}
		$string .= "\n}";
		return $string;
	}

	public static function getArrayDeclarationString($arr)
	{
		$f = __METHOD__; //DeclareVariableCommand::getShortClass()."(".static::getShortClass().")::getArrayDeclarationString()";
		$string = "[\n";
		$count = 0;
		foreach($arr as $key => $value) {
			if(is_array($value)) {
				$value = static::getArrayDeclarationString($value);
			}elseif($value instanceof Command) {
				$value = $value->toJavaScript();
			}elseif(is_string($value) || $value instanceof StringifiableInterface) {
				$value = "'" . escape_quotes($value, QUOTE_STYLE_SINGLE) . "'";
			}
			if($count > 0) {
				$string .= ",\n\t";
			}
			if(is_string($key)) {
				$string .= "{$key} => {$value}";
			}elseif(is_int($key)) {
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

	public function toJavaScript(): string{
		$f = __METHOD__;
		$print = false;
		$string = "";
		if($this->hasScopeType()) {
			$st = $this->getScopeType();
			if($st instanceof JavaScriptInterface) {
				$st = $st->toJavaScript();
			}
			$string .= "{$st} ";
		}
		$name = $this->getVariableName();
		$string .= "{$name}";
		if(!$this->hasValue()) {
			return $string;
		}
		$value = $this->getValue();
		if($value instanceof JavaScriptInterface) {
			$value = $value->toJavaScript();
		}elseif(is_array($value)) {
			if($this->hasEscapeType() && $this->getEscapeType() === ESCAPE_TYPE_OBJECT) {
				$value = static::arrayToObjectString($value);
			}else{
				$value = static::getArrayDeclarationString($value);
			}
		}elseif(is_string($value) || $value instanceof StringifiableInterface) {
			if($print) {
				if(is_string($value)) {
					Debug::print("{$f} value is a string");
				}else{
					$type = $value->getClass();
					Debug::print("{$f} value is a \"{$type}\"");
				}
			}
			$q = $this->getQuoteStyle();
			$value = "{$q}" . escape_quotes($value, $q) . "{$q}";
		}elseif(is_bool($value)) {
			$value = $value ? "true" : "false";
		}
		$string .= " = {$value}";
		return $string;
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //DeclareVariableCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		if(!$this->hasValue()) {
			if($this->getNullFlag()) {
				return null;
			}
			$name = $this->getVariableName();
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::error("{$f} value is undefined -- variable name is \"{$name}\"; debug ID is \"{$did}\"; declared {$decl}");
		}
		$value = $this->getValue();
		while ($value instanceof ValueReturningCommandInterface) {
			$value = $value->evaluate();
		}
		return $value;
	}

	public function resolve()
	{
		$f = __METHOD__; //DeclareVariableCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		$print = false;
		if(!$this->hasValue()) {
			if($this->getScopeType() === SCOPE_TYPE_LET) {
				if($print) {
					Debug::print("{$f} nothing is wrong, it's doing what it's supposed to");
				}
			}else{
				Debug::error("{$f} cannot assign value to scope");
			}
			return null;
		}
		return $this->getScope()->setLocalValue($this->getVariableName(), $this->evaluate());
	}

	public function toSQL(): string
	{
		if(!$this->hasValue()) { // $this->hasType()){
			$string = "declare " . $this->getVariableName() . " " . $this->getType() . ";\n";
			return $string;
		}
		$value = $this->getValue();
		if($value instanceof SQLInterface) {
			$value = $value->toSQL();
		}elseif(is_string($value) || $value instanceof StringifiableInterface) {
			$value = single_quote($value);
		}
		$string = "set " . $this->getVariableName() . " = {$value};\n";
		return $string;
	}
}
