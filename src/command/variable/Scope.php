<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;


use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class Scope extends Basic
{

	use ParentScopedTrait;

	protected $localValues;

	public function setLocalValue($index, $value)
	{
		$f = __METHOD__;
		if (! is_int($index) && ! is_string($index)) {
			Debug::error("{$f} index is neither integer nor string");
		} elseif (! is_array($this->localValues)) {
			$this->localValues = [];
		}
		return $this->localValues[$index] = $value;
	}

	public function hasLocalValue($index)
	{
		return is_array($this->localValues) && ! empty($this->localValues) && array_key_exists($index, $this->localValues);
	}

	public function let($name, $value = null): DeclareVariableCommand
	{
		return DeclareVariableCommand::let($name, $value, $this);
	}

	public function redeclare($name, $value = null)
	{
		return DeclareVariableCommand::redeclare($name, $value, $this);
	}

	public function var($name, $value = null): DeclareVariableCommand
	{
		return DeclareVariableCommand::var($name, $value, $this);
	}

	public function getLocalValue($index)
	{
		$f = __METHOD__;
		if (! $this->hasLocalValue($index)) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} local variable \"{$index}\" is undefined. Declared {$decl}");
		}
		return $this->localValues[$index];
	}

	protected function declareVariableCommand($name, $value): DeclareVariableCommand
	{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} declaring variable \"{$name}\"");
		}
		return new DeclareVariableCommand($name, $value, $this);
	}

	protected function declareMultiple(?string $scope_type, array $arr)
	{
		$f = __METHOD__;
		if (empty($arr)) {
			Debug::error("{$f} received empty array as input parameter");
		} elseif (! is_associative($arr)) {
			Debug::error("{$f} array must be associative");
		}
		$ret = [];
		foreach ($arr as $name => $value) {
			$dec = $this->declareVariableCommand($name, $value);
			if ($scope_type !== null) {
				$dec->setScopeType($scope_type);
			}
			array_push($ret, $dec);
		}
		return $ret;
	}

	public function letNames(...$names): array
	{
		$ret = [];
		foreach ($names as $name) {
			array_push($ret, $this->let($name));
		}
		return $ret;
	}

	public function letMultiple(array $arr): array
	{
		return $this->declareMultiple(SCOPE_TYPE_LET, $arr);
	}

	public function varMultiple(array $arr): array
	{
		return $this->declareMultiple(SCOPE_TYPE_VAR, $arr);
	}

	public function constMultiple(array $arr): array
	{
		return $this->declareMultiple(SCOPE_TYPE_CONST, $arr);
	}

	public function redeclareMultiple(array $arr): array
	{
		return $this->declareMultiple(null, $arr);
	}

	protected function declareColumnValues(?string $scope_type, object $obj, ...$column_names): array
	{
		$f = __METHOD__;
		if (! isset($column_names) || ! is_array($column_names) || empty($column_names)) {
			Debug::error("{$f} column names must be a non-empty array");
		}
		$ret = [];
		foreach ($column_names as $name) {
			$value = new GetColumnValueCommand($obj, $name); // ->getColumnValueCommand($name);
			$dec = $this->declareVariableCommand($name, $value);
			if ($scope_type !== null) {
				$dec->setScopeType($scope_type);
			}
			array_push($ret, $dec);
		}
		return $ret;
	}

	public function letColumnValues(object $obj, ...$column_names): array
	{
		return $this->declareColumnValues(SCOPE_TYPE_LET, $obj, ...$column_names);
	}

	public function varColumnValues(object $obj, ...$column_names): array
	{
		return $this->declareColumnValues(SCOPE_TYPE_VAR, $obj, ...$column_names);
	}

	public function constColumnValues(object $obj, ...$column_names): array
	{
		return $this->declareColumnValues(SCOPE_TYPE_CONST, $obj, ...$column_names);
	}

	public function redeclareColumnValues(object $obj, ...$column_names): array
	{
		return $this->declareColumnValues(null, $obj, ...$column_names);
	}

	public function getDeclaredVariableCommand($name): GetDeclaredVariableCommand
	{
		// $f = __METHOD__;
		/*
		 * if($this->getDebugFlag()){
		 * Debug::printStackTraceNoExit("{$f} entered");
		 * }
		 */
		return new GetDeclaredVariableCommand($name, $this);
	}
}

//fuck autoload and fuck psr4 VariableCommandDirectory()."/DeclareVariableCommand;
//fuck autoload and fuck psr4 VariableCommandDirectory()."/GetDeclaredVariableCommand;
