<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class StringTransformationCommand extends Command implements JavaScriptInterface, StringifiableInterface, ValueReturningCommandInterface
{

	protected $subject;

	public function __construct($subject)
	{
		parent::__construct();
		$this->setSubject($subject);
	}

	public function setSubject($subject)
	{
		$f = __METHOD__; //StringTransformationCommand::getShortClass()."(".static::getShortClass().")->setSubject()";
		$print = false;
		if ($subject === null || is_string($subject) && $subject === "") {
			Debug::error("{$f} subject is null or empty string");
		}
		if ($print) {
			if (is_string($subject)) {
				Debug::print("{$f} setting subject to \"{$subject}\"");
			} else {
				Debug::print("{$f} subject is not a string");
			}
		}
		return $this->subject = $subject;
	}

	public function hasSubject()
	{
		return isset($this->subject);
	}

	public function getSubject()
	{
		$f = __METHOD__; //StringTransformationCommand::getShortClass()."(".static::getShortClass().")->getSubject()";
		if (! $this->hasSubject()) {
			Debug::error("{$f} subject \"{$this->subject}\" is undefined");
		}
		return $this->subject;
	}

	public function toJavaScript(): string
	{
		$subject = $this->getSubject();
		if ($subject instanceof JavaScriptInterface) {
			$subject = $subject->toJavaScript();
		}
		$command = $this->getCommandId();
		return "({$subject}).{$command}()";
	}

	public final function __toString(): string
	{
		return $this->evaluate();
	}
}
