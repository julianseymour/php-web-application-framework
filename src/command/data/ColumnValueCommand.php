<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class ColumnValueCommand extends DataStructureCommand implements ValueReturningCommandInterface
{

	public function __construct($context, $vn)
	{
		$f = __METHOD__; //ColumnValueCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($context);
		if ($context instanceof DataStructure) {
			$this->setIdCommand(new GetDeclaredVariableCommand("context"));
		} elseif ($context instanceof ValueReturningCommandInterface) {
			$this->setIdCommand($context);
		} else {
			Debug::error("{$f} context is neither data structure nor value returning media command");
		}
		if (isset($vn)) {
			$this->setColumnName($vn);
		}
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //ColumnValueCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$e = $this->getIdCommandString();
		if ($e instanceof JavaScriptInterface) {
			$e = $e->toJavaScript();
		} elseif (is_string($e) || $e instanceof StringifiableInterface) {
			$e = single_quote($e);
		} else {
			Debug::error("{$f} could not convert ID command string");
		}
		$vn = $this->getColumnName();
		if ($vn instanceof JavaScriptInterface) {
			$vn = $vn->toJavaScript();
		} elseif (is_string($vn) || $vn instanceof StringifiableInterface) {
			$vn = single_quote($vn);
		} else {
			$gottype = is_object($vn) ? $vn->getClass() : gettype($vn);
			if (is_object($vn)) {
				$decl = ", declared " . $vn->getDeclarationLine();
			}
			Debug::error("{$f} could not convert column name to string; type is \"{$gottype}\"{$decl}. This object was declared " . $this->getDeclarationLine());
		}
		$command = static::getCommandId();
		return "{$e}.{$command}({$vn})";
	}
}
