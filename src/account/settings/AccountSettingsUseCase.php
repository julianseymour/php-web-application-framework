<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings;

use function JulianSeymour\PHPWebApplicationFramework\directive;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\avatar\ProfileImageThumbnailForm;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\account\settings\display_name\DisplayNameOuterForm;
use JulianSeymour\PHPWebApplicationFramework\account\settings\timezone\TimezoneSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\settings\MfaSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\auth\password\change\ChangePasswordForm;
use JulianSeymour\PHPWebApplicationFramework\auth\password\change\ChangePasswordResponder;
use JulianSeymour\PHPWebApplicationFramework\auth\password\reset\PasswordResetOptionsForm;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNotificationSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\email\change\ChangeEmailAddressConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\email\change\ChangeEmailAddressForm;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushNotificationSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\SessionHijackPreventionSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryCookie;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoverySettingsForm;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SetSessionRecoveryResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\UpdateResponder;
use mysqli;

class AccountSettingsUseCase extends InteractiveUseCase
{

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public static function allowFileUpload(): bool
	{
		return true;
	}

	public function getConditionalDataOperandClasses(): ?array
	{
		return [
			DATATYPE_USER => user()->getClass(),
			DATATYPE_SESSION_RECOVERY => SessionRecoveryData::class
		];
	}

	public function getConditionalProcessedFormClasses(): ?array
	{
		return [
			ProfileImageThumbnailForm::class,
			DisplayNameOuterForm::class,
			MfaSettingsForm::class,
			SessionRecoverySettingsForm::class,
			ChangePasswordForm::class,
			PasswordResetOptionsForm::class,
			ChangeEmailAddressForm::class,
			EmailNotificationSettingsForm::class,
			PushNotificationSettingsForm::class,
			// ThemeSettingsForm::class,
			// OnlineStatusForm::class,
			// MessagePreviewSettingsForm::class,
			TimezoneSettingsForm::class,
			SessionHijackPreventionSettingsForm::class
		];
	}

	public function getProcessedDataType(): ?string
	{
		if(hasInputParameter('dispatch')) {
			switch (getInputParameter('dispatch')) {
				case SessionRecoverySettingsForm::getFormDispatchIdStatic():
					return DATATYPE_SESSION_RECOVERY;
				default:
					break;
			}
		}
		return DATATYPE_USER;
	}

	public function acquireDataOperandObject(mysqli $mysqli): ?DataStructure
	{
		$f = __METHOD__;
		$print = false;
		$user = user();
		if(static::getProcessedDataType() === DATATYPE_SESSION_RECOVERY) {
			$session = new SessionRecoveryData();
			$session->setUserData($user);
			$cookie = new SessionRecoveryCookie();
			if($cookie->hasRecoveryKey()) {
				$recovery_key = $cookie->getRecoveryKey();
				$session->setIdentifierValue($recovery_key);
				if($session->unpack($mysqli, $recovery_key) !== null) {
					$session->setObjectStatus(SUCCESS);
					$cookie->setSessionRecoveryData($session);
				}else{
					$session->ejectIdentifierValue();
				}
			}
			$this->setOriginalOperand($session->replicate());
			return $this->setDataOperandObject($session);
		}elseif($print) {
			if(! user()->hasProfileImageData()) {
				Debug::print("{$f} user lacks profile image data");
			}
		}
		$this->setOriginalOperand($user);
		return $this->setDataOperandObject($user);
	}

	public function getValidateMediaBehavior()
	{
		return MEDIA_COMMAND_REPLACE;
	}

	public function getConfirmationCodeClass(): string
	{
		return ChangeEmailAddressConfirmationCode::class;
	}

	public function getDataOperandClass(): ?string
	{
		return $this->getConditionalDataOperandClass($this->getProcessedDataType(), $this);
	}

	public function getConditionalElementClasses(): ?array
	{
		return [
			DATATYPE_USER => $this->getProcessedFormClass(),
			DATATYPE_SESSION_RECOVERY => $this->getProcessedFormClass()
		];
	}

	public function getProcessedDataListClasses(): ?array{
		return null;
	}

	protected function getExecutePermissionClass(){
		return AuthenticatedAccountTypePermission::class;
	}

	public function getActionAttribute(): ?string{
		return '/settings';
	}

	public function isCurrentUserDataOperand(): bool{
		return true;
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData{
		return user();
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if($status !== SUCCESS) {
			return parent::getResponder($status);
		}
		$directive = directive();
		switch ($directive) {
			case DIRECTIVE_DELETE:
			case DIRECTIVE_DELETE_FOREIGN:
			case DIRECTIVE_INSERT:
			case DIRECTIVE_VALIDATE:
				if($print) {
					Debug::print("{$f} directive is \"{$directive}\". Returning an update responder");
				}
				return new UpdateResponder(false);
			case DIRECTIVE_MASS_DELETE:
				return new SetSessionRecoveryResponder();
			case DIRECTIVE_UPDATE:
				if($print){
					Debug::print("{$f} update");
				}
				switch (getInputParameter('dispatch', $this)) {
					case ChangePasswordForm::getFormDispatchIdStatic():
						if($print){
							Debug::print("{$f} changed password");
						}
						return new ChangePasswordResponder();
					case MfaSettingsForm::getFormDispatchIdStatic():
						if($print) {
							Debug::print("{$f} this is an update of the MFA settings form. Returning an UpdateResponder.");
						}
						return new UpdateResponder(false);
					default:
						if($print){
							Debug::print("{$f} account setting responder");
						}
						return new AccountSettingsResponder();
				}
			default:
		}
		return parent::getResponder($status);
	}
}

