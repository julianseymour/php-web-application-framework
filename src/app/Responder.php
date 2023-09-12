<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\MultipleCommandsTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class Responder extends Basic{
	
	use MultipleCommandsTrait;
	
	public static function create():Responder{
		$class = static::class;
		return new $class();
	}
	
	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		try{
			$print = false;
			$response->setProperty("time", floor(microtime(true) * 1000));
			if(app()->hasUserData()) {
				$user = user();
				if(!$response->hasDataStructure($user)) {
					$user->configureArrayMembership(CONST_DEFAULT);
					$response->pushDataStructure($user);
				}
			}elseif($print) {
				Debug::print("{$f} application runtime lacks user data");
			}
			if($use_case->hasObjectStatus()){
				$status = $use_case->getObjectStatus();
				$response->setProperty("status", $status);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status); //$use_case->getErrorMessage();
					if($print) {
						Debug::print("{$f} error status \"{$err}\"");
					}
					$response->setProperty('info', $err);
				}elseif($print) {
					Debug::print("{$f} use case was successful");
				}
			}
			if($use_case instanceof ClientUseCaseInterface) {
				if(!$response->hasProperty("action")) {
					$action = $use_case->getClientUseCaseName();
					if($action !== null) {
						$response->setProperty("action", $action);
					}
				}else{
					if($print) {
						Debug::print("{$f} the response already has an action attribute");
					}
					$actions = $response->getProperty("action");
					if(!is_array($actions)) {
						$actions = [
							$actions
						];
					}
					array_push($actions, $use_case->getClientUseCaseName());
					$response->setProperty("action", $actions);
				}
			}elseif($print) {
				$ucc = get_class($use_case);
				Debug::printStackTraceNoExit("{$f} use case \"{$ucc}\" is not a ClientUseCaseinterface");
			}
			if($this->hasCommands()){
				if($print){
					Debug::print("{$f} pushing ".$this->getCommandCount()." commands");
				}
				$response->pushCommand(...$this->getCommands());
			}elseif($print){
				Debug::print("{$f} no commands to push");
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
