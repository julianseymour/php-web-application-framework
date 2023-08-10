<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetForeignDataStructureCommand extends ForeignDataStructureCommand implements ValueReturningCommandInterface
{

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if ($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		$vn = $this->getColumnName();
		if ($vn instanceof JavaScriptInterface) {
			$vn = $vn->toJavaScript();
		} elseif (is_string($vn) || $vn instanceof StringifiableInterface) {
			$vn = single_quote($vn);
		}
		$cs = $this->getCommandId();
		return "{$idcs}.{$cs}({$vn})";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //GetForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$context = $this->getDataStructure();
		$vn = $this->getColumnName();
		if ($context instanceof Command) {
			if ($print) {
				Debug::print("{$f} context is a media command; about to evaluate");
			}
			while ($context instanceof Command) {
				$context = $context->evaluate();
			}
		}
		if (! $context->hasForeignDataStructure($vn)) {
			if ($print) {
				Debug::warning("{$f} context does not have a foreign data structure for column \"{$vn}\"");
			}
			return null;
		}
		return $context->getForeignDataStructure($vn);
	}

	public static function getCommandId(): string
	{
		return "getForeignDataStructure";
	}

	public function getColumnValueCommand($index): GetColumnValueCommand
	{
		return new GetColumnValueCommand($this, $index);
	}

	/*
	 * public function getIdentifierValueCommand(){
	 * return new GetColumnValueCommand($this, new GetIdentifierNameCommand($this));
	 * }
	 */
	public function getForeignDataStructureListMemberAtOffset($column_name, $offset)
	{
		$f = __METHOD__; //GetForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->getForeignDataStructureListMemberAtOffset()";
		$fds = $this->evaluate();
		if (! $fds instanceof DataStructure) {
			Debug::error("{$f} command did not evaluate to a usable data structure");
		}
		return $fds->getForeignDataStructureListMemberAtOffset($column_name, $offset);
	}

	public function hasColumnValueCommand($column_name): HasColumnValueCommand
	{
		return new HasColumnValueCommand($this, $column_name);
	}
}
