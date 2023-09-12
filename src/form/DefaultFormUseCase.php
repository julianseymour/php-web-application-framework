<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\ui\ProcessedDataListElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use mysqli;

abstract class DefaultFormUseCase extends InteractiveUseCase{

	public function getProcessedDataListClasses(): ?array{
		return [
			$this->getDataOperandClass()
		];
	}

	public function getConditionalElementClasses(): ?array{
		return [
			$this->getProcessedDataType() => DefaultForm::class
		];
	}

	public function getConditionalDataOperandClasses(): ?array{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalProcessedFormClasses(): ?array{
		return [
			DefaultForm::class
		];
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData{
		return null;
	}

	public function isCurrentUserDataOperand(): bool{
		return false;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}

	public function getResponder(int $status): ?Responder{
		if($status !== SUCCESS) {
			return parent::getResponder($status);
		}
		switch (directive()) {
			case DIRECTIVE_INSERT:
				return new InsertAfterResponder();
			default:
		}
		return parent::getResponder($status);
	}

	public function getProcessedDataType(): ?string{
		return $this->getDataOperandClass()::getDataType();
	}
	
	public function getPageContent():?array{
		return [new ProcessedDataListElement(ALLOCATION_MODE_EAGER, $this)];
	}
}
