<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\language\translate\TranslatableObjectResolver;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;
use Exception;

class MultilingualNameData extends MultilingualStringData
{

	public function getStringIdentifier()
	{
		ErrorMessage::unimplemented(f(static::class));
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Multilingual name");
	}

	public static final function getStringTypeStatic(): string
	{
		return STRING_TYPE_NAME;
	}

	public static function normalize()
	{
		return true;
	}

	public static function throttleOnInsert(): bool
	{
		return false;
	}

	protected static function skipMandatoryUserKeys()
	{
		return true;
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Multilingual names");
	}

	public static function getMultilingualNameKeyDatum(string $class, $column_name = null)
	{
		$f = __METHOD__;
		try {
			$print = false;
			if (! class_exists($class)) {
				Debug::error("{$f} class \"{$class}\" does not exist");
			} elseif (! is_a($class, DataStructure::class, true)) {
				Debug::error("{$f} class \"{$class}\" is not a data structure");
			} elseif ($print) {
				Debug::print("{$f} about to create intersection data for foreign class \"{$class}\"");
			}
			if ($column_name == null) {
				$column_name = "name";
			}
			$name = new TextDatum($column_name);
			$lang = app()->hasUserData() ? user()->getLanguagePreference() : LANGUAGE_ENGLISH;
			$name->setSubqueryClass(MultilingualNameData::class);
			$name->setSubqueryExpression(new ColumnAlias(new ColumnAliasExpression("translated_names_alias", $lang), $column_name));
			// translatedObjectKey is stored in an intersection table so this query requires nested alias expressions
			$name->setSubqueryWhereCondition(CommandBuilder::equals(new ColumnAliasExpression("translated_names_alias", "uniqueKey"), MultilingualNameData::generateLazyAliasExpression($class)));
			$name->setSubqueryTypeSpecifier('s');
			$name->setSubqueryParameters([
				"translatedObjectKey"
			]);
			return $name;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasTranslatedObject()
	{
		return $this->hasForeignDataStructure("translatedObjectKey");
	}

	public function getTranslatedObject()
	{
		return $this->getForeignDataStructure("translatedObjectKey");
	}

	public function ejectTranslatedObjectDataType()
	{
		return $this->ejectColumnValue("translatedObjectDataType");
	}

	public function hasTranslatedObjectDataType()
	{
		return $this->hasColumnValue("translatedObjectDataType");
	}

	public function getTranslatedObjectDataType()
	{
		return $this->getColumnValue("translatedObjectDataType");
	}

	public function setTranslatedObjectDataType($type)
	{
		return $this->setColumnValue("translatedObjectDataType", $type);
	}

	public function ejectTranslatedObjectKey()
	{
		return $this->ejectColumnValue('translatedObjectKey');
	}

	public function hasTranslatedObjectKey()
	{
		return $this->hasColumnValue('translatedObjectKey');
	}

	public function getTranslatedObjectKey()
	{
		return $this->getColumnValue('translatedObjectKey');
	}

	public function setTranslatedObjectKey($key)
	{
		return $this->setColumnValue('translatedObjectKey', $key);
	}

	public function getTranslatedObjectClass()
	{
		$type = $this->getTranslatedObjectDataType();
		// $subtype = $this->getTranslatedObjectSubtype();
		return mods()->getDataStructureClass($type);
	}

	public function setTranslatedObject($obj)
	{
		$this->setTranslatedObjectKey($obj->getIdentifierValue());
		$this->setTranslatedObjectDataType($obj->getDataType());
		return $this->setForeignDataStructure('translatedObjectKey', $obj);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::declareColumns($columns, $ds);
		$lang_bundle = new MultilingualStringBundle("name");
		$lang_bundle->setNormalizeFlag(true);
		$foreign_bundle = new ForeignMetadataBundle("translatedObject", $ds);
		$foreign_bundle->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$foreign_bundle->setValidDataTypes(array_keys(mods()->getTranslatableDataStructureTypes()));
		$foreign_bundle->setForeignDataStructureClassResolver(TranslatableObjectResolver::class);
		$foreign_bundle->setOnUpdate($foreign_bundle->setOnDelete(REFERENCE_OPTION_CASCADE));
		$foreign_bundle->constrain();
		static::pushTemporaryColumnsStatic($columns, $lang_bundle, $foreign_bundle);
	}

	public static function getTableNameStatic(): string
	{
		return "translated_names";
	}
}
