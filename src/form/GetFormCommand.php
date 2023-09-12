<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class GetFormCommand extends ElementCommand implements ServerExecutableCommandInterface, ValueReturningCommandInterface
{

	public function toJavaScript(): string
	{
		$ids = $this->getIdCommandString();
		return "{$ids}.form";
	}

	public static function getCommandId(): string
	{
		return "form";
	}

	public function __construct($element)
	{
		$f = __METHOD__; //GetFormCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($element);
		if(!$this->hasElement()) {
			Debug::error("{$f} element is undefined");
		}
	}

	public function resolve()
	{
		return $this->evaluate();
	}

	public function evaluate(?array $params = null)
	{
		return $this->getElement()->getForm();
	}
}
