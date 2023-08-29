<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class ExecutiveLoginUseCase extends UniversalLoginUseCase{
	
	public function execute():int{
		$f = __METHOD__;
		$print = false;
		$status = parent::execute();
		if($status === SUCCESS){
			$predecessor = $this->getPredecessor();
			if($print){
				Debug::print("{$f} parent function executed successfully. About to execute predecessor of class ".$predecessor->getShortClass());
			}
			return $predecessor->execute();
		}elseif($print){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} parent function returned error status \"{$err}\"");
		}
		return $status;
	}
	
	public function getSwitchUseCase(int $status){
		$f = __METHOD__;
		$print = false;
		if($status === SUCCESS){
			if($print){
				Debug::print("{$f} returning predecessor's switch use case for successful execution");
			}
			return $this->getPredecessor()->getSwitchUseCase($status);
		}elseif($print){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} error status \"{$err}\"");
		}
		return null;
	}
	
	public function hasSwitchUseCase(int $status): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			if($status === SUCCESS){
				Debug::print("{$f} execution successful");
			}else{
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} error status \"{$err}\"");
			}
		}
		return $status === SUCCESS;
	}
}
