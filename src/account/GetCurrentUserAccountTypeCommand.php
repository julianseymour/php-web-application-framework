<?php
namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserAccountType;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

/**
 * Returns the account type of the user evaluating the command
 *
 * @author j
 */
class GetCurrentUserAccountTypeCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "accountType";
	}

	public function evaluate(?array $params = null)
	{
		return getCurrentUserAccountType();
		// $f = __METHOD__; //GetCurrentUserAccountTypeCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		// $user = user();
		// return $user->getAccountType();
	}

	public function toJavaScript(): string
	{
		return "getCurrentUserAccountType()";
	}
}
