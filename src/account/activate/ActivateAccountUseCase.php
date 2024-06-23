<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\ExecutiveLoginUseCase;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ActivateAccountUseCase extends ValidConfirmationCodeUseCase{

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$user = user();
			$user->setActivationTimestamp(time());
			$user->getColumn("activationTimestamp")->setUpdateFlag(true);
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $user->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} upating activation timestamp returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully updated user's activation timestamp");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return '/activate';
	}
	
	protected function getTransitionFromPermission(){
		$f = __METHOD__;
		$print = false;
		return new Permission("transitionFrom", 
			function(PlayableUser $user, ActivateAccountUseCase $subject, UseCase $predecessor) use ($f, $print){
			if($predecessor instanceof ValidateAccountActivationCodeUseCase || 
				$predecessor instanceof ExecutiveLoginUseCase
			){
				if($print){
					Debug::print("{$f} permission granted");
				}
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} predecessor class is ".$predecessor->getShortClass());
			}
			return FAILURE;
		});
	}
	
	public function getPageContent():?array{
		$status = $this->getObjectStatus();
		if($status === SUCCESS){
			return [ErrorMessage::getVisualNotice(_("Your account has been activated."))];
		}
		return parent::getPageContent();
	}
	
	public function getResponder(int $status):?Responder{
		$f = __METHOD__;
		$print = false;
		$responder = new Responder();
		if($status === SUCCESS){
			if($print){
				Debug::print("{$f} success");
			}
			$responder->setCommands([
				Document::createElement("div")->withIdAttribute("activation_form")->withInnerHTML(
					$this->getPageContent()[0]->__toString()
				)->update()
			]);
		}elseif($print){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} error status \"{$err}\"");
		}
		return $responder;
	}
}
