<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\directive;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\UpdateResponder;
use Exception;
use mysqli;

class RegisterAccountUseCase extends InteractiveUseCase{

	public function getPageContent(): ?array{
		$f = __METHOD__;
		try {
			$status = $this->getObjectStatus();
			if ($status === SUCCESS) {
				return [
					new SuccessfulRegistrationNotice(ALLOCATION_MODE_LAZY, $this->getDataOperandObject())
				];
			} else {
				return [
					ErrorMessage::getResultMessage($status)
				];
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getProcessedDataListClasses(): ?array
	{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getConditionalElementClasses(): ?array
	{
		// $f = __METHOD__;
		return [
			DATATYPE_USER => SuccessfulRegistrationNotice::class
		];
	}

	public function getInsertHereElement(?DataStructure $ds = null)
	{
		$div = new DivElement();
		$div->setIdAttribute("lenap_pilf");
		$div->addClassAttribute("lenap_pilf");
		return $div;
	}

	public function getConditionalDataOperandClasses(): ?array
	{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalProcessedFormClasses(): ?array
	{
		return [
			static::getProcessedDataType() => static::getProcessedFormClass()
		];
	}

	public function getProcessedDataType(): ?string
	{
		return DATATYPE_USER;
	}

	public function getProcessedFormClass(): ?string{
		return RegistrationForm::class;
	}

	public function getDataOperandClass(): ?string{
		return RegisteringUser::class;
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if ($status !== SUCCESS) {
			if ($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} error status \"{$err}\". Returning parent function");
			}
			return new RegistrationErrorResponder();
		}
		$directive = directive();
		switch ($directive) {
			case DIRECTIVE_INSERT:
				return new UpdateResponder(false);
			default:
		}
		if ($print) {
			Debug::print("{$f} directive \"{$directive}\". Returning parent function.");
		}
		return parent::getResponder($status);
	}

	public function getActionAttribute(): ?string{
		return "/register";
	}

	public function isCurrentUserDataOperand(): bool{
		return false;
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData{
		return $this->getDataOperandObject();
	}

	protected function getExecutePermissionClass(){
		return AnonymousAccountTypePermission::class;
	}
}

