<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclaration;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclarationForm;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use mysqli;

class CreateGroupUseCase extends InteractiveUseCase
{

	public function getLoadoutGeneratorClass(?DataStructure $object = null): ?string
	{
		return CreateGroupLoadoutGenerator::class;
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData
	{
		return user();
	}

	public function getProcessedDataListClasses(): ?array
	{
		return [
			GroupData::class
		];
	}

	public function getConditionalElementClasses(): ?array
	{
		return [
			DATATYPE_GROUP_INVITE => CreateGroupForm::class,
			DATATYPE_GROUP => EditGroupForm::class,
			DATATYPE_ROLE_DECLARATION => RoleDeclarationForm::class
		];
	}

	public function getActionAttribute(): ?string
	{
		return '/groups';
	}

	public function getConditionalDataOperandClasses(): ?array
	{
		return [
			DATATYPE_GROUP_INVITE => GroupFoundingInvitation::class,
			DATATYPE_GROUP => GroupData::class,
			DATATYPE_ROLE_DECLARATION => RoleDeclaration::class
		];
	}

	public function getConditionalProcessedFormClasses(): ?array
	{
		return [
			CreateGroupForm::class,
			EditGroupForm::class
		];
	}

	public function getProcessedDataType(): ?string
	{
		$f = __METHOD__;
		$print = false;
		if (request()->matchDirective(DIRECTIVE_INSERT) && request()->matchForm(CreateGroupForm::class)) {
			if ($print) {
				Debug::print("{$f} we are creating a new group");
			}
			return DATATYPE_GROUP_INVITE;
		} elseif ($print) {
			Debug::print("{$f} ");
		}
	}

	public function isCurrentUserDataOperand(): bool
	{
		return false;
	}

	public function getUseCaseId()
	{
		return USE_CASE_CREATE_GROUP;
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return true;
	}

	public function getDataOperandClass(): ?string
	{
		return $this->getConditionalDataOperandClass($this->getProcessedDataType());
	}

	protected function getExecutePermissionClass()
	{
		return AuthenticatedAccountTypePermission::class;
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
		$mode = ALLOCATION_MODE_LAZY;
		$elements = [];
		if (user()->hasForeignDataStructureList(GroupData::getPhylumName())) {
			foreach (user()->getForeignDataStructureList(GroupData::getPhylumName()) as $group) {
				array_push($elements, new EditGroupForm($mode, $group));
			}
		}
		$invite = new GroupFoundingInvitation();
		return [
			new CreateGroupForm($mode, $invite->withFounderData(user())),
			...$elements
		];
	}
}
