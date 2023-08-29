<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\KeypairColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;

class GroupInvitation extends UserOwned{

	use GroupKeyColumnTrait;
	use KeypairColumnsTrait;

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$group = new ForeignMetadataBundle("group", $ds);
		$group->setForeignDataStructureClass(GroupData::class);
		$group->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$group->constrain();
		// $user = new UserMetadataBundle("invitee", $ds);
		$groupPrivateKey = new BlobDatum("groupPrivateKey");
		$groupPrivateKey->encrypt(AsymmetricEncryptionScheme::class);
		$groupPrivateKey->setSensitiveFlag(true);
		$groupPrivateKey->setNeverLeaveServer(true);
		$groupPrivateKey->setNullable(true); // false);

		static::pushTemporaryColumnsStatic($columns, $group, $groupPrivateKey);
	}

	public static function getPrettyClassName():string{
		return _("Invitation");
	}

	public static function getTableNameStatic(): string{
		return "invitations";
	}

	public static function getDataType(): string{
		return DATATYPE_GROUP_INVITE;
	}

	public static function getPrettyClassNames():string{
		return _("Invitations");
	}

	public static function getPhylumName(): string{
		return "invitations";
	}

	public function setGroupPrivateKey(string $value):string{
		return $this->setColumnValue("groupPrivateKey", $value);
	}

	public function hasGroupPrivateKey(): bool
	{
		return $this->hasColumnValue("groupPrivateKey");
	}

	public function getGroupPrivateKey(): ?string
	{
		return $this->getColumnValue("groupPrivateKey");
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::reconfigureColumns($columns, $ds);
		$columns['groupSubtype']->setNullable(true);
	}

	public function hasFounderKey(): bool
	{
		return $this->hasColumnValue("founderKey");
	}

	public function setFounderKey($value)
	{
		return $this->setColumnValue("founderKey", $value);
	}

	public function getFounderKey()
	{
		return $this->getColumnValue("founderKey");
	}

	public function setFounderData($struct)
	{
		return $this->setForeignDataStructure("founderKey", $struct);
	}

	public function hasFounderData(): bool
	{
		return $this->hasColumnValue("founderKey");
	}

	public function getFounderData()
	{
		return $this->getForeignDataStructure("founderKey");
	}

	public function withFounderData($struct): GroupInvitation
	{
		return $this->withForeignDataStructure("founderKey", $struct);
	}

	protected function nullPrivateKeyHook(): int
	{
		$f = __METHOD__; //GroupInvitation::getShortClass()."(".static::getShortClass().")->nullPrivateKeyHook()";
		Debug::error("{$f} private key is null");
		return FAILURE;
	}
}
