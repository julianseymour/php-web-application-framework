<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class ControlStatementCommand extends Command implements JavaScriptInterface, ServerExecutableCommandInterface
{

	use ExpressionalTrait;

	public function __construct($expr = null)
	{
		parent::__construct();
		if(isset($expr)) {
			$this->setExpression($expr);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->expression);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //ControlStatementCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		if($this->hasExpression()) {
			Json::echoKeyValuePair('expression', $this->expression, $destroy);
		}
		parent::echoInnerJson($destroy);
	}
}
