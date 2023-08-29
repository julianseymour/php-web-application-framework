<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\db;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidateAnonymousConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;

class ValidateUnlistedIpAddressCodeUseCase extends ValidateAnonymousConfirmationCodeUseCase{

	public static function getBruteforceAttemptClass(): string{
		return ListIpAddressAttempt::class;
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public static function getConfirmationCodeClass(): string{
		return UnlistedIpAddressConfirmationCode::class;
	}

	public function getActionAttribute(): ?string{
		return "/authorize_ip";
	}

	public function getFormClass(): ?string{
		return ConfirmIpAddressListForm::class;
	}

	public function getDataOperandObject(): ?DataStructure{
		$mysqli = db()->getConnection(PublicReadCredentials::class);
		$ip = $this->acquireConfirmationCodeObject($mysqli)->getIpAddressObject();
		return $ip;
	}

	public static function validateOnFormSubmission(): bool{
		return true;
	}

	protected function initializeSwitchUseCases(): ?array{
		return [
			SUCCESS => ClassifyIpAddressUseCase::class
		];
	}
}
