<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;


use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureListMemberAtOffsetCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureListMemberCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasChildrenCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\SetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\SetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetSourceAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetStylePropertiesCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetDeclaredVariableCommand extends Command implements JavaScriptInterface, ScopedCommandInterface, SQLInterface, StringifiableInterface, TypeSpecificInterface, ValueReturningCommandInterface{

	use IndirectParentScopeTrait;
	use TypeSpecificTrait;
	use VariableNameTrait;

	public static function getCommandId(): string{
		return "local";
	}

	public function __construct($vn, ?Scope $scope = null, ?string $parseType = null){
		parent::__construct();
		if ($vn instanceof Element) {
			$vn = $vn->getIdOverride();
		}
		$this->setVariableName($vn);
		if (isset($scope)) {
			$this->setScope($scope);
		}
		if (isset($parseType)) {
			$this->setParseType($parseType);
		}
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if ($print) {
			if ($this->hasScope()) {
				$scope = $this->getScope();
				$decl = $scope->getDeclarationLine();
				$did = $scope->getDebugId();
				Debug::print("{$f} scope is defined, declared {$decl} with debug ID \"{$did}\"");
			} elseif ($print) {
				Debug::print("{$f} scope is undefined, declared {$decl} with debug ID \"{$did}\"");
			}
		}
		$scope = $this->getScope();
		$vn = $this->getVariableName();
		$value = $scope->getLocalValue($vn);
		if ($value instanceof ValueReturningCommandInterface) {
			while ($value instanceof ValueReturningCommandInterface) {
				if ($print) {
					$vc = $value->getClass();
					$did = $value->getDebugId();
					$decl = $value->getDeclarationLine();
					Debug::print("{$f} value is an instance of {$vc} from an object with debug ID {$did}, declared {$decl}");
					if ($value instanceof GetColumnValueCommand) {
						$data = $value->getDataStructure();
						$dsc = $data->getClass();
						$did = $data->getDebugId();
						$decl = $data->getDeclarationLine();
						Debug::print("{$f} data structure is a {$dsc} with debug ID \"{$did}\" declared {$decl}");
					}
				}
				$value = $value->evaluate($params);
			}
		} elseif ($print) {
			Debug::print("{$f} value is not a value-returning command interface");
		}
		return $value;
	}

	public function setValue($i){
		return $this->getScope()->setLocalValue($this->getVariableName(), $i);
	}

	public function toJavaScript(): string{
		$vn = $this->getVariableName();
		if ($vn instanceof JavaScriptInterface) {
			$vn = $vn->toJavaScript();
		}
		return $vn;
	}

	public function __toString(): string{
		$vn = $this->getVariableName();
		return "{$vn}";
	}

	public function hasForeignDataStructureCommand($column_name): HasForeignDataStructureCommand{
		return new HasForeignDataStructureCommand($this, $column_name);
	}

	public function getForeignDataStructureCommand($column_name): GetForeignDataStructureCommand{
		Debug::printStackTrace(__METHOD__.": entered");
		return new GetForeignDataStructureCommand($this, $column_name);
	}

	public function hasColumnValueCommand($column_name): HasColumnValueCommand{
		return new HasColumnValueCommand($this, $column_name);
	}

	public function getColumnValueCommand($column_name): GetColumnValueCommand{
		return new GetColumnValueCommand($this, $column_name);
	}

	public function setColumnValueCommand($column_name, $value): SetColumnValueCommand{
		return new SetColumnValueCommand($this, $column_name, $value);
	}

	public function setForeignDataStructureCommand($column_name, $fds): SetForeignDataStructureCommand{
		return new SetForeignDataStructureCommand($this, $column_name, $fds);
	}

	public function getChildCommand($phylum, $child_key): GetForeignDataStructureListMemberCommand{
		return new GetForeignDataStructureListMemberCommand($this, $phylum, $child_key);
	}

	public function hasForeignDataStructureListMemberCommand($phylum, $child_key): HasForeignDataStructureCommand{
		return new HasForeignDataStructureCommand($this, $phylum, $child_key);
	}

	public function getForeignDataStructureListMemberAtOffset($phylum, $offset): GetForeignDataStructureListMemberAtOffsetCommand{
		return new GetForeignDataStructureListMemberAtOffsetCommand($this, $phylum, $offset);
	}

	public function hasChildrenCommand($column_name): HasChildrenCommand{
		return new HasChildrenCommand($this, $column_name);
	}

	public function toSQL(): string{
		$vn = $this->getVariableName();
		if ($vn instanceof SQLInterface) {
			$vn = $vn->toSQL();
		}
		return $vn;
	}

	public function setSourceAttributeCommand($value): SetSourceAttributeCommand{
		return new SetSourceAttributeCommand($this, $value);
	}

	public function setInnerHTMLCommand($innerHTML): SetInnerHTMLCommand{
		return new SetInnerHTMLCommand($this, $innerHTML);
	}

	public function setStylePropertiesCommand($properties): SetStylePropertiesCommand{
		return new SetStylePropertiesCommand($this, $properties);
	}
}
