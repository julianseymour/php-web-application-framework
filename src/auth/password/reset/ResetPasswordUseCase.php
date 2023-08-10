<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordData;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\ui\PageContentElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

class ResetPasswordUseCase extends ValidConfirmationCodeUseCase
{

	/**
	 * reset password when the user doesn't have access to the old one.
	 * destroys old messages
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function resetPassword($mysqli)
	{
		$f = __METHOD__; //ResetPasswordUseCase::getShortClass()."(".static::getShortClass().")->resetPassword()";
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} about to update password data");
			}
			// user()->validatePrivateKey(get_file_line());
			$password_data = PasswordData::generate(getInputParameter('password'));
			// user()->validatePrivateKey(get_file_line());
			if (! isset($password_data)) {
				Debug::warning("{$f} password data returned null");
				return $this->setObjectStatus(ERROR_NULL_PASSWORD_DATA);
			} elseif (! $mysqli->ping()) {
				Debug::error("{$f} mysqli failed ping test");
			}
			$correspondent = user()->getCorrespondentObject();
			if (! $correspondent instanceof AuthenticatedUser) {
				Debug::error("{$f} correspondent is a guest");
			} elseif ($print) {
				$cc = $correspondent->getClass();
				Debug::print("{$f} correspondent class is \"{$cc}\"");
			}
			$name = $correspondent->getName();
			if ($print) {
				Debug::print("{$f} correspondent name is \"{$name}\"");
			}
			$correspondent->setReceptivity(DATA_MODE_RECEPTIVE);
			// user()->validatePrivateKey(get_file_line());
			// $dsk_column = new SecretKeyDatum("deterministicSecretKey");
			// $dsk_column->volatilize();
			// $correspondent->pushColumn($dsk_column);
			$correspondent->processPasswordData($password_data);
			// user()->validatePrivateKey(get_file_line());
			$correspondent->setHardResetCount($correspondent->getHardResetCount() + 1);
			// user()->validatePrivateKey(get_file_line());
			$correspondent->setReceptivity(DATA_MODE_DEFAULT);
			$backup = $correspondent->getPermission(DIRECTIVE_UPDATE);
			$correspondent->setPermission(DIRECTIVE_UPDATE, SUCCESS // new AnonymousAccountTypePermission(DIRECTIVE_UPDATE)
			);
			// user()->validatePrivateKey(get_file_line());
			$status = $correspondent->update($mysqli);
			$correspondent->setPermission(DIRECTIVE_UPDATE, $backup);
			// user()->validatePrivateKey(get_file_line());
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error updating password data: \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} successfully reset password");
			}
			// user()->validatePrivateKey(get_file_line());
			if (Request::isXHREvent()) {
				if ($print) {
					Debug::print("{$f} about to push media command");
				}
				$e = PageContentElement::wrap(ErrorMessage::getVisualError(RESULT_RESET_SUCCESS));
				$e->setIdAttribute("page_content");
				// user()->validatePrivateKey(get_file_line());
				$this->pushCommand($e->updateInnerHTML());
			} elseif ($print) {
				Debug::print("{$f} this is not an XHR");
			}
			// user()->validatePrivateKey(get_file_line());
			return RESULT_RESET_SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function execute(): int
	{
		$f = __METHOD__; //ResetPasswordUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $this->resetPassword($mysqli);
			if ($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} resetPassword returned status \"{$err}\"");
			}
			// user()->validatePrivateKey("In ResetPasswordUseCase->execute()");

			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getActionAttribute(): ?string
	{
		return '/reset';
	}

	public function getUseCaseId()
	{
		return USE_CASE_RESET_PASSWORD;
	}

	public function getTransitionFromPermission(): Permission
	{
		return new Permission(DIRECTIVE_TRANSITION_FROM, function (PlayableUser $user, UseCase $target, UseCase $predecessor) {
			$f = __METHOD__; //"ResetPasswordUseCase transition from permission closure";
			try {
				$print = false;
				if (! $predecessor instanceof ValidateResetPasswordCodeUseCase) {
					if ($print) {
						Debug::print("{$f} predecessor is the wrong class");
					}
					return FAILURE;
				} elseif ($print) {
					Debug::print("{$f} predecessor has the right class");
				}
				$status = $predecessor->getObjectStatus();
				if ($status !== SUCCESS) {
					if ($print) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} predecessor has error status \"{$err}\"");
					}
					return $status;
				} elseif ($print) {
					Debug::print("{$f} transition validated");
				}
				return SUCCESS;
			} catch (Exception $x) {
				x($f, $x);
			}
		});
	}
}
