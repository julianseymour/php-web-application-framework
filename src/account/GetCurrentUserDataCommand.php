<?php
namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\DataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetCurrentUserDataCommand extends DataStructureCommand implements JavaScriptInterface, ValueReturningCommandInterface
{

	public function __construct()
	{
		parent::__construct();
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		$cs = $this->getCommandId();
		return "{$idcs}.getResponseText().{$cs}()";
	}

	public static function getCommandId(): string
	{
		return "user";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //GetCurrentUserDataCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		if(! app()->hasUserData()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} application runtime lacks a user data. This command was instantiated at {$decl}");
		}
		return user();
	}

	public function getDataStructure()
	{
		$f = __METHOD__; //GetCurrentUserDataCommand::getShortClass()."(".static::getShortClass().")->getDataStructure()";
		if(! app()->hasUserData()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} application runtime lacks a user data. This command was instantiated at {$decl}");
		}
		return $this->evaluate();
	}

	public function getIdCommandString()
	{
		if(!$this->hasIdCommand()) {
			return "context";
		}
		return $this->idCommand;
	}
}
