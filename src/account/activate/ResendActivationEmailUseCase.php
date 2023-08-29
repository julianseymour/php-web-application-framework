<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\CustomerAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ResendActivationEmailUseCase extends UseCase{

	public function execute(): int{
		$f = __METHOD__;
		try {
			$status = parent::execute();
			$user = user();
			if ($user == null) {
				Debug::error("{$f} user data returned null");
				return $this->setObjectStatus(ERROR_NULL_USER_OBJECT);
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = PreActivationConfirmationCode::submitStatic($mysqli, $user);
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getActionAttribute(): ?string{
		return "/resend_activation";
	}

	protected function getExecutePermissionClass(){
		return CustomerAccountTypePermission::class;
	}
	
	public function getResponder(int $status):?Responder{
		switch($status){
			case SUCCESS:
				return new ResendActivationEmailResponder();
			default:
		}
		return parent::getResponder($status);
	}
	
	public function getPageContent():?array{
		$status = $this->getObjectStatus();
		if($status === SUCCESS){
			return [
				ErrorMessage::getVisualNotice(
					substitute(
						_("A new email has been sent to %1% containing a link to activate your account."),
						user()->getEmailAddress()
					)
				)
			];
		}
		return parent::getPageContent();
	}
}
