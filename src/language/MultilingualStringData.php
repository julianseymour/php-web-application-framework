<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\f;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsSessionData;
use mysqli;

abstract class MultilingualStringData extends DataStructure
{

	public abstract function getStringIdentifier();

	public abstract static function getStringTypeStatic(): string;

	public static function getDataType(): string
	{
		return DATATYPE_STRING;
	}

	public final function hasSubtypeValue(): bool
	{
		return true;
	}

	public static function getSubtypeStatic(): string
	{
		return static::getStringTypeStatic();
	}

	public function getLocalizedStringDatum()
	{
		// $f = __METHOD__;
		$session = new LanguageSettingsSessionData();
		$language = $session->getLanguageCode();
		return $this->getColumn($language);
	}

	public function getLocalizedString()
	{
		return $this->getLocalizedStringDatum()->getHumanReadableValue();
	}

	public static function getDeledObjectClass()
	{
		return DeletedStringData::class;
	}

	protected function beforeInsertHook(mysqli $mysqli): int
	{
		$f = __METHOD__;
		$english = $this->getColumnValue(LANGUAGE_DEFAULT);
		if (empty($english)) {
			Debug::warning("{$f} nothing to insert");
			return $this->setObjectStatus(ERROR_NULL_STRING);
		}
		return parent::beforeInsertHook($mysqli);
	}

	public static function normalize()
	{
		return false;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		// $f = __METHOD__;
		$bundle = new MultilingualStringBundle("localized");
		if (static::normalize()) {
			$bundle->setNormalizeFlag(true);
		}
		parent::declareColumns($columns, $ds);
		static::pushTemporaryColumnsStatic($columns, $bundle);
	}

	public function getSpanishString()
	{
		return $this->getColumnValue(LANGUAGE_SPANISH);
	}

	public function hasSpanishString()
	{
		return $this->hasColumnValue(LANGUAGE_SPANISH);
	}

	public function setSpanishString($spanish)
	{
		return $this->setColumnValue(LANGUAGE_SPANISH, $spanish);
	}

	public function getEnglishString()
	{
		return $this->getColumnValue(LANGUAGE_ENGLISH);
	}

	public function hasEnglishString()
	{
		return $this->hasColumnValue(LANGUAGE_ENGLISH);
	}

	public function setEnglishString($english)
	{
		return $this->setColumnValue(LANGUAGE_ENGLISH, $english);
	}

	public function getName()
	{
		return _("Multilingual string");
	}

	public static function getPhylumName(): string
	{
		return "strings";
	}

	public static function getTableNameStatic(): string
	{
		return "multilingual_strings";
	}
}
