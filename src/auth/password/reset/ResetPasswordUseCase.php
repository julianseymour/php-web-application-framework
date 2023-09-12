<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordData;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

class ResetPasswordUseCase extends ValidConfirmationCodeUseCase{

	/**
	 * reset password when the user doesn't have access to the old one.
	 * destroys old messages
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function resetPassword($mysqli){
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} about to update password data");
			}
			$password_data = PasswordData::generate(getInputParameter('password'));
			if(! isset($password_data)) {
				Debug::warning("{$f} password data returned null");
				return $this->setObjectStatus(ERROR_NULL_PASSWORD_DATA);
			}elseif(!$mysqli->ping()) {
				Debug::error("{$f} mysqli failed ping test");
			}
			$correspondent = user()->getCorrespondentObject();
			if(!$correspondent instanceof AuthenticatedUser) {
				Debug::error("{$f} correspondent is a guest");
			}elseif($print) {
				$cc = $correspondent->getClass();
				Debug::print("{$f} correspondent class is \"{$cc}\"");
			}
			$name = $correspondent->getName();
			if($print) {
				Debug::print("{$f} correspondent name is \"{$name}\"");
			}
			$correspondent->setReceptivity(DATA_MODE_RECEPTIVE);
			$correspondent->processPasswordData($password_data);
			$correspondent->setHardResetCount($correspondent->getHardResetCount() + 1);
			$correspondent->setReceptivity(DATA_MODE_DEFAULT);
			$backup = $correspondent->getPermission(DIRECTIVE_UPDATE);
			$correspondent->setPermission(DIRECTIVE_UPDATE, SUCCESS);
			$status = $correspondent->update($mysqli);
			$correspondent->setPermission(DIRECTIVE_UPDATE, $backup);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error updating password data: \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully reset password");
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} entered");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $this->resetPassword($mysqli);
			if($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} resetPassword returned status \"{$err}\"");
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return '/reset';
	}

	public function getTransitionFromPermission(): Permission{
		return new Permission(DIRECTIVE_TRANSITION_FROM, function (PlayableUser $user, UseCase $target, UseCase $predecessor) {
			$f = __METHOD__;
			try{
				$print = false;
				if(!$predecessor instanceof ValidateResetPasswordCodeUseCase) {
					if($print) {
						Debug::print("{$f} predecessor is the wrong class");
					}
					return FAILURE;
				}elseif($print) {
					Debug::print("{$f} predecessor has the right class");
				}
				$status = $predecessor->getObjectStatus();
				if($status !== SUCCESS) {
					if($print) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} predecessor has error status \"{$err}\"");
					}
					return $status;
				}elseif($print) {
					Debug::print("{$f} transition validated");
				}
				return SUCCESS;
			}catch(Exception $x) {
				x($f, $x);
			}
		});
	}
	
	public function getPageContent():?array{
		$status = $this->getObjectStatus();
		if($status !== SUCCESS){
			return parent::getPageContent();
		}
		return [ErrorMessage::getVisualNotice(_("Your password was reset."))];
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
				Document::createElement("div")->withIdAttribute("reset_password_form")->withInnerHTML(
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
