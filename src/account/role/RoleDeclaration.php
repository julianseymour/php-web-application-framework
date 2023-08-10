<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\role;

use JulianSeymour\PHPWebApplicationFramework\account\group\GroupData;
use JulianSeymour\PHPWebApplicationFramework\account\group\GroupKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\common\IteratorTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;

class RoleDeclaration extends DataStructure
{

	use GroupKeyColumnTrait;
	use IteratorTrait;
	use NameColumnTrait;

	// use ParentKeyColumnTrait;
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::declareColumns($columns, $ds);
		$name = new NameDatum("name");
		$name->setNullable(false);
		$group = new ForeignMetadataBundle("group", $ds);
		$group->setForeignDataStructureClass(GroupData::class);
		$group->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$group->constrain();
		/*
		 * $parent = new ForeignMetadataBundle("parent", $ds);
		 * $parent->setNullable(true);
		 * $parent->setForeignDataStructureClass(static::class);
		 * $parent->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		 * $parent->constrain();
		 */
		static::pushTemporaryColumnsStatic($columns, $name, 
			// $parent,
			$group);
	}

	public static function escapeCustomRoleName(string $s): string
	{
		$prefix = CUSTOM_ROLE_PREFIX;
		return "{$prefix}{$s}";
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Role declaration");
	}

	public static function getTableNameStatic(): string
	{
		return "role_declarations";
	}

	public static function getDataType(): string
	{
		return DATATYPE_ROLE_DECLARATION;
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Role declarations");
	}

	public static function getPhylumName(): string
	{
		return "role_declarations";
	}
}
