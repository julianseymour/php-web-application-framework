<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SetInputNameCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	use NamedTrait;

	public static function getCommandId(): string
	{
		return "name";
	}

	public function __construct($element, $name)
	{
		parent::__construct($element);
		$this->setName($name);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //SetInputNameCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try{
			$e = $this->getIdCommandString();
			if($e instanceof JavaScriptInterface) {
				$e = $e->toJavaScript();
			}
			$name = $this->getName();
			if($name instanceof JavaScriptInterface) {
				$name = $name->toJavaScript();
			}elseif(is_string($name) || $name instanceof StringifiableInterface) {
				$q = $this->getQuoteStyle();
				$name = escape_quotes($name, $q);
				$name = "{$q}{$name}{$q}";
			}
			return "{$e}.name = {$name}";
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function resolve()
	{
		$f = __METHOD__; //SetInputNameCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		$print = false;
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$name = $this->getName();
		while ($name instanceof ValueReturningCommandInterface) {
			$name = $name->evaluate();
		}
		if($print) {
			Debug::print("{$f} evaluated \"{$name}\"");
		}
		$element->setNameAttribute($name);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair("name", $this->getName(), $destroy);
		parent::echoInnerHSON($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->name);
	}
}
