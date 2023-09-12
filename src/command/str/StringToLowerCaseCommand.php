<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class StringToLowerCaseCommand extends StringTransformationCommand
{

	public static function getCommandId(): string
	{
		return "toLowerCase";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //StringToLowerCaseCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$subject = $this->getSubject();
		while ($subject instanceof Command) {
			$subject = $subject->evaluate();
		}
		if($print) {
			Debug::print("{$f} about to strtolower \"{$subject}\"");
		}
		return strtolower($subject);
	}
}
