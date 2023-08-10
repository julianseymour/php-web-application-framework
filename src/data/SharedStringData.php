<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\language\MultilingualStringBundle;
use JulianSeymour\PHPWebApplicationFramework\language\MultilingualStringData;

class SharedStringData extends MultilingualStringData implements SharedDataInterface
{

	protected static function skipMandatoryUserKeys()
	{
		return true;
	}

	public function loadFailureHook(): int
	{
		return $this->setObjectStatus(ERROR_NOT_FOUND);
	}

	public static function getCompositeUniqueColumnNames(): ?array
	{
		return [
			config()->getSupportedLanguages()
		];
	}

	public static function getKeyGenerationMode(): int
	{
		return KEY_GENERATION_MODE_HASH;
	}

	public static function getForeignKeyNamesAsSharedDataStructure(): array
	{
		return [
			"descriptionKey",
			"briefDescriptionKey",
			"categoryKey"
		];
	}

	public static function getDuplicateEntryRecourse(): int
	{
		return RECOURSE_CONTINUE;
	}

	public static function getPhylumName(): string
	{
		return "strings";
	}

	public static function throttleOnInsert(): bool
	{
		return false;
	}

	public function getStringIdentifier()
	{
		return $this->getIdentifierValue();
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Shared string");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Shared strings");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		// $f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$bundle = new MultilingualStringBundle("localized");
		static::pushTemporaryColumnsStatic($columns, $bundle);
	}

	public static function getStringTypeStatic(): string
	{
		return STRING_TYPE_SHARED;
	}
}