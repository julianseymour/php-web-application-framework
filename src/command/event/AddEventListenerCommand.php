<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class AddEventListenerCommand extends EventListenerCommand implements ServerExecutableCommandInterface
{

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			'once',
			'passive'
		]);
	}

	public function setOnceFlag(bool $value = true): bool
	{
		return $this->setFlag("once", $value);
	}

	public function getOnceFlag(): bool
	{
		return $this->getFlag("once");
	}

	public function setPassiveFlag(bool $value = true): bool
	{
		return $this->setFlag("passive", $value);
	}

	public function getPassiveFlag(): bool
	{
		return $this->getFlag("passive");
	}

	public static function getCommandId(): string
	{
		return "addEventListener";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //AddEventListenerCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$et = $this->getEventTarget();
		if ($et instanceof JavaScriptInterface) {
			$et = $et->toJavaScript();
		} elseif ($et instanceof Element) {
			if (! $et->hasIdAttribute()) {
				Debug::error("{$f} element lacks an ID attribute");
			}
			$et = new GetElementByIdCommand($et->getIdAttribute());
			$et = $et->toJavaScript();
		}
		$type = $this->getType();
		if (is_string($type)) {
			$type = single_quote($type);
		} elseif ($type instanceof JavaScriptInterface) {
			$type = $type->toJavaScript();
		}
		$listener = $this->getEventListener();
		if ($listener instanceof JavaScriptInterface) {
			$listener = $listener->toJavaScript();
		}
		return "{$et}.addEventListener({$type}, {$listener})";
	}

	public function resolve()
	{
		$target = $this->getEventTarget();
		if ($target instanceof ValueReturningCommandInterface) {
			while ($target instanceof ValueReturningCommandInterface) {
				$target = $target->evaluate();
			}
		}
		$type = $this->getType();
		if ($type instanceof ValueReturningCommandInterface) {
			while ($type instanceof ValueReturningCommandInterface) {
				$type = $type->evaluate();
			}
		}
		$listener = $this->getEventListener();
		if ($listener instanceof ValueReturningCommandInterface) {
			while ($listener instanceof ValueReturningCommandInterface) {
				$listener = $listener->evaluate();
			}
		}
		$target->addEventListener($type, $listener);
	}
}
	