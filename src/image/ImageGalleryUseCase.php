<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use JulianSeymour\PHPWebApplicationFramework\ui\ProcessedDataListElement;

abstract class ImageGalleryUseCase extends InteractiveUseCase{

	public static function allowFileUpload(): bool{
		return true;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if($status !== SUCCESS){
			if($print){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} object status \"{$err}\"");
			}
			return parent::getResponder($status);
		}
		$directive = directive();
		if($print){
			Debug::print("{$f} directive is \"{$directive}\"");
		}
		switch($directive){
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPLOAD:
				if($print){
					Debug::print("{$f} returning an insert after responder");
				}
				return new InsertAfterResponder();
			default:
		}
		if($print){
			Debug::print("{$f} returning parent function");
		}
		return parent::getResponder($status);
	}

	public function getProcessedDataType(): ?string{
		return DATATYPE_FILE;
	}

	public function getConditionalElementClasses(): ?array{
		return [
			$this->getProcessedDataType() => $this->getProcessedFormClass()
		];
	}

	public function getConditionalDataOperandClasses(): ?array{
		return [
			$this->getProcessedDataType() => $this->getDataOperandClass()
		];
	}

	public function getConditionalProcessedFormClasses(): ?array{
		return [
			$this->getProcessedDataType() => $this->getProcessedFormClass()
		];
	}

	public function getProcessedDataListClasses(): ?array{
		return [
			$this->getDataOperandClass()
		];
	}

	public function isCurrentUserDataOperand(): bool{
		return true;
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}
	
	public function getPageContent():?array{
		return [new ProcessedDataListElement(ALLOCATION_MODE_EAGER, $this)];
	}

}
