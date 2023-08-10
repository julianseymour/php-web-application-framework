<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class HasForeignDataStructureCommand extends DataStructureCommand implements ValueReturningCommandInterface
{

	public function __construct($context = null, $vn = null)
	{
		$f = __METHOD__; //HasForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($context);
		if ($context instanceof DataStructure) {
			$this->setIdCommand("context");
		}
		if (isset($vn)) {
			$this->setColumnName($vn);
		}
	}

	public static function getCommandId(): string
	{
		return "hasForeignDataStructure";
	}

	public function evaluate(?array $params = null)
	{
		$context = $this->getDataStructure();
		while ($context instanceof ValueReturningCommandInterface) {
			$context = $context->evaluate();
		}
		return $context->hasForeignDataStructure($this->getColumnName());
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //HasForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$e = $this->getIdCommandString();
			if ($e instanceof JavaScriptInterface) {
				$e = $e->toJavaScript();
			}
			$vn = $this->getColumnName();
			/*
			 * if($vn instanceof ValueReturningCommandInterface){
			 * while($vn instanceof ValueReturningCommandInterface){
			 * $vn = $vn->evaluate();
			 * }
			 * }
			 */
			if ($vn instanceof JavaScriptInterface) {
				$vn = $vn->toJavaScript();
			} elseif (is_string($vn) || $vn instanceof StringifiableInterface) {
				$vn = single_quote($vn);
			}
			return "{$e}.hasForeignDataStructure({$vn})";
		} catch (Exception $x) {
			x($f, x);
		}
	}

	public function getConditionalString()
	{
		return $this->toJavaScript();
	}
}
