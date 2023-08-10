<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\common\TypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class EventListenerCommand extends Command implements JavaScriptInterface
{

	use TypeTrait;

	protected $eventTarget;

	protected $listener;

	public function __construct($eventTarget, $type, $listener)
	{
		$this->setEventTarget($eventTarget);
		$this->setType($type);
		$this->setEventListener($listener);
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			'capture'
		]);
	}

	public function setCaptureFlag(bool $value = true): bool
	{
		return $this->setFlag("capture", $value);
	}

	public function getCaptureFlag(): bool
	{
		return $this->getFlag("capture");
	}

	public function setEventListener($listener)
	{
		if ($listener == null) {
			unset($this->listener);
			return null;
		}
		return $this->listener = $listener;
	}

	public function hasEventListener()
	{
		return isset($this->listener);
	}

	public function getEventListener()
	{
		$f = __METHOD__; //AddEventListenerCommand::getShortClass()."(".static::getShortClass().")->getEventListener()";
		if (! $this->hasEventListener()) {
			Debug::error("{$f} event listener is undefined");
		}
		return $this->listener;
	}

	public function setEventTarget($target)
	{
		if ($target == null) {
			unset($this->target);
			return null;
		}
		return $this->target = $target;
	}

	public function hasEventTarget()
	{
		return isset($this->target);
	}

	public function getEventTarget()
	{
		$f = __METHOD__; //AddEventListenerCommand::getShortClass()."(".static::getShortClass().")->getEventTarget()";
		if (! $this->hasEventTarget()) {
			Debug::error("{$f} event target is undefined");
		}
		return $this->target;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->type);
		unset($this->listener);
		unset($this->eventTarget);
	}
}