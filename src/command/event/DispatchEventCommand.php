<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class DispatchEventCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	Use ParametricTrait;

	private $event;

	public static function getCommandId(): string
	{
		return "dispatchEvent";
	}

	public function setEvent($event)
	{
		return $this->event = $event;
	}

	public function hasEvent()
	{
		return ! empty($this->event);
	}

	public function getEvent()
	{
		$f = __METHOD__; //DispatchEventCommand::getShortClass()."(".static::getShortClass().")->getEvent()";
		if(!$this->hasEvent()) {
			Debug::error("{$f} event is undefined");
		}
		return $this->event;
	}

	public function __construct($element, $event, ...$parameters)
	{
		parent::__construct($element);
		$this->setEvent($event);
		if(isset($parameters)) {
			foreach($parameters as $p) {
				$this->pushParameters($p);
			}
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('event', $this->getEvent(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->event);
		unset($this->parameters);
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$event = $this->getEvent();
		while ($event instanceof ValueReturningCommandInterface) {
			$event = $event->evaluate();
		}
		$params = $this->getParameters();
		$element->dispatchEvent($event, ...$params);
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		$event = $this->getEvent();
		if($event instanceof JavaScriptInterface) {
			$event = $event->toJavaScript();
		}elseif(is_string($event) || $event instanceof StringifiableInterface) {
			$q = $this->getQuoteStyle();
			$event = "{$q}" . escape_quotes($event, $q) . "{$q}";
		}
		return "{$idcs}.dispatchEvent(new Event({$event}))";
	}
}
