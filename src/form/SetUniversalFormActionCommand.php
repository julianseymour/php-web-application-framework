<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class SetUniversalFormActionCommand extends Command
{

	use UriTrait;

	public function __construct($action = null)
	{
		parent::__construct();
		if($action !== null){
			$this->setUri($action);
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair("action", $this->getUri());
		parent::echoInnerJson($destroy);
	}

	public static function getCommandId(): string
	{
		return "setUniversalFormAction";
	}
}