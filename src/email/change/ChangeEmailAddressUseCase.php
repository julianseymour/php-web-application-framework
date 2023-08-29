<?php

namespace JulianSeymour\PHPWebApplicationFramework\email\change;

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
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ChangeEmailAddressUseCase extends ValidConfirmationCodeUseCase{

	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = false;
			$predecessor = $this->getPredecessor();
			if($predecessor instanceof ExecutiveLoginUseCase){
				$predecessor = $predecessor->getPredecessor();
			}
			if($predecessor instanceof ExecutiveLoginUseCase){
				Debug::error("{$f} it's still an ExecutiveLoginUseCase");
			}elseif($print){
				Debug::print("{$f} predecessor class is ".$predecessor->getClass());
			}
			$confirmation_code = $predecessor->getConfirmationCodeObject();
			$email = $confirmation_code->getNewEmailAddress();
			$user = user();
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$user->setNormalizedEmailAddress(EmailAddressDatum::normalize($email));
			$user->setEmailAddress($email);
			$status = $user->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} user->update() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/confirm_email";
	}
	
	protected function getTransitionFromPermission(){
		$f = __METHOD__;
		$print = false;
		return new Permission("transitionFrom",
			function(PlayableUser $user, ChangeEmailAddressUseCase $subject, UseCase $predecessor) use ($f, $print){
			if(
				$predecessor instanceof ValidateChangeEmailCodeUseCase || 
				$predecessor instanceof ExecutiveLoginUseCase
			){
				if($print){
					Debug::print("{$f} predecessor is satisfactory");
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
			return [ErrorMessage::getVisualNotice(_("Your email address has been updated."))];
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
				Document::createElement("main")->withIdAttribute("page_content")->setInnerHTMLCommand(
					$this->getPageContent()[0]->__toString()
				)
			]);
		}elseif($print){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} error status \"{$err}\"");
		}
		return $responder;
	}
}
