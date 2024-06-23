<?php
namespace JulianSeymour\PHPWebApplicationFramework\poll;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use mysqli;

class ShortPollUseCase extends UseCase implements ClientUseCaseInterface{

	use JavaScriptCounterpartTrait;

	public function __construct(?UseCase $predecessor = null){
		$f = __METHOD__;
		$print = false;
		parent::__construct($predecessor);
		$pollers = mods()->getPollingUseCaseClasses();
		if(empty($pollers)){
			if($print){
				Debug::print("{$f} there are no short polling use cases");
			}
			return;
		}elseif($print){
			$count = count($pollers);
			Debug::print("{$f} {$count} short polling use cases");
		}
		foreach($pollers as $ucc){
			if($print){
				Debug::print("{$f} instantiating use case \"{$ucc}\"");
			}
			$use_case = new $ucc($this);
			$this->pushUseCase($use_case);
		}
	}

	public function pushUseCase(...$use_case): int{
		return $this->pushArrayProperty("useCases", ...$use_case);
	}

	public function getUseCases(): ?array{
		return $this->getProperty("useCases");
	}

	public function beforeAuthenticateHook(): int{
		$f = __METHOD__;
		$print = false;
		foreach($this->getUseCases() as $use_case){
			if($print){
				$ucc = get_class($use_case);
			}
			$status = $use_case->beforeAuthenticateHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$ucc}->veforeAuthenticateHook() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		if($print){
			Debug::print("{$f} returning normally");
		}
		return SUCCESS;
	}

	public function afterAuthenticateHook(): int{
		$f = __METHOD__;
		$print = false;
		foreach($this->getUseCases() as $use_case){
			if($print){
				$ucc = get_class($use_case);
			}
			$status = $use_case->afterAuthenticateHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$ucc}->afterAuthenticateHook() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		if($print){
			Debug::print("{$f} returning normally");
		}
		return SUCCESS;
	}

	public function beforeLoadHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		foreach($this->getUseCases() as $use_case){
			if($print){
				$ucc = get_class($use_case);
			}
			$status = $use_case->beforeLoadHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$ucc}->veforeLoadHook() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		if($print){
			Debug::print("{$f} returning normally");
		}
		return SUCCESS;
	}

	public function afterLoadHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		foreach($this->getUseCases() as $use_case){
			if($print){
				$ucc = get_class($use_case);
			}
			$status = $use_case->afterLoadHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$ucc}->afterLoadHook() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		if($print){
			Debug::print("{$f} returning normally");
		}
		return SUCCESS;
	}

	public function execute(): int{
		$f = __METHOD__;
		$print = false;
		foreach($this->getUseCases() as $use_case){
			$ucc = get_class($use_case);
			$use_case->validateTransition();
			$status = $use_case->safeExecute();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} {$ucc}->execute() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully executed {$ucc}");
			}
		}
		if($print){
			Debug::print("{$f} returning successfully");
		}
		return SUCCESS;
	}

	public function getActionAttribute(): ?string{
		return "poll";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if($status !== SUCCESS){
			Debug::warning("{$f} returning parent function");
			return parent::getResponder($status);
		}
		return new ShortPollResponder();
	}

	public function getClientUseCaseName(): ?string{
		return "poll";
	}

	public function getLoadoutGeneratorClass(?PlayableUser $object = null): ?string{
		return ShortPollLoadoutGenerator::class;
	}
}

