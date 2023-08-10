<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetForeignDataStructureCommand extends ForeignDataStructureCommand implements ServerExecutableCommandInterface
{

	protected $foreignDataStructure;

	public function __construct($context, $cn, $fds = null)
	{
		parent::__construct($context, $cn);
		if (isset($fds)) {
			$this->setForeignDataStructure($fds);
		}
	}

	public function setForeignDataStructure($fds)
	{
		return $this->foreignDataStructure = $fds;
	}

	public function hasForeignDataStructure()
	{
		return isset($this->foreignDataStructure);
	}

	public function getForeignDataStructure()
	{
		$f = __METHOD__; //SetForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->getForeignDataStructure()";
		if (! $this->hasForeignDataStructure()) {
			Debug::error("{$f} foreign data structure is undefined");
		}
		return $this->foreignDataStructure;
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //SetForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$idcs = $this->getIdCommandString();
		if ($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		$cn = $this->getColumnName();
		if ($cn instanceof JavaScriptInterface) {
			$cn = $cn->toJavaScript();
		} elseif (is_string($cn) || $cn instanceof StringifiableInterface) {
			$cn = single_quote($cn);
		}
		$fds = $this->getForeignDataStructure();
		if ($fds instanceof JavaScriptInterface) {
			$fds = $fds->toJavaScript();
		} else {
			Debug::error("{$f} foreign data structure cannot be converted to javascript");
		}
		$cs = $this->getCommandId();
		return "{$idcs}.{$cs}({$cn}, {$fds})";
	}

	public static function getCommandId(): string
	{
		return "setForeignDataStructure";
	}

	public function resolve()
	{
		$context = $this->getDataStructure();
		while ($context instanceof ValueReturningCommandInterface) {
			$context = $context->evaluate();
		}
		$cn = $this->getColumnName();
		while ($cn instanceof ValueReturningCommandInterface) {
			$cn = $cn->evaluate();
		}
		$fds = $this->getForeignDataStructure();
		while ($fds instanceof ValueReturningCommandInterface) {
			$fds = $fds->evaluate();
		}
		$context->setForeignDataStructure($cn, $fds);
	}
}
