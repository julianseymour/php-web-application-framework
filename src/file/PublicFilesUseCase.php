<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use JulianSeymour\messenger\ui\AttachedFileElement;
use mysqli;

class PublicFilesUseCase extends InteractiveUseCase
{

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData
	{
		return config()->getAdministratorclass()::getAdministratorStatic($mysqli);
	}

	public function getProcessedDataListClasses(): ?array
	{
		return [
			PublicFileData::class
		];
	}

	public function getConditionalElementClasses(): ?array
	{
		$f = __METHOD__;
		return [DATATYPE_FILE => PublicFileUploadForm::class];
	}

	public function getActionAttribute(): ?string
	{
		return "/files";
	}

	public function getConditionalDataOperandClasses(): ?array
	{
		return [
			DATATYPE_FILE_CLEARTEXT => PublicFileData::class
		];
	}

	public function getConditionalProcessedFormClasses(): ?array
	{
		return [
			PublicFileUploadForm::class
		];
	}

	public function getProcessedDataType(): ?string
	{
		return DATATYPE_FILE_CLEARTEXT;
	}

	public function isCurrentUserDataOperand(): bool
	{
		return false;
	}

	public function getUseCaseId()
	{
		return USE_CASE_FILES;
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getDataOperandClass(): ?string
	{
		return PublicFileData::class;
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}

	public function getResponder(): ?Responder
	{
		$status = $this->getObjectStatus();
		if ($status !== SUCCESS) {
			return parent::getResponder();
		}
		switch (directive()) {
			case DIRECTIVE_INSERT:
				return new InsertAfterResponder();
			default:
		}
		return parent::getResponder();
	}

	public function getPageContent(): ?array
	{
		$arr = [];
		$user = user();
		if ($user instanceof Administrator) {
			array_push($arr, new PublicFileUploadForm(ALLOCATION_MODE_LAZY, new PublicFileData()));
		}
		$ec = $this->getConditionalElementClass(PublicFileData::getDataType());
		if ($user->hasForeignDataStructureList(PublicFileData::getPhylumName())) {
			foreach ($user->getForeignDataStructureList(PublicFileData::getPhylumName()) as $file) {
				array_push($arr, new $ec(ALLOCATION_MODE_LAZY, $file));
			}
		}
		return $arr;
	}

	public static function allowFileUpload(): bool
	{
		return true;
	}
}
