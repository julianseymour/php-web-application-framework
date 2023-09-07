<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use mysqli;

class MultilingualStringData extends DataStructure implements StaticElementClassInterface, StaticTableNameInterface{

	use ElementBindableTrait;
	use StaticTableNameTrait;
	
	public static function getDatabaseNameStatic():string{
		return "strings";
	}
	
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return MultilingualTextForm::class;
	}
	
	public static function throttleOnInsert(): bool{
		return false;
	}

	public function hasTranslatedObject():bool{
		return $this->hasForeignDataStructure("translatedObjectKey");
	}

	public function getTranslatedObject():string{
		return $this->getForeignDataStructure("translatedObjectKey");
	}

	public function ejectTranslatedObjectDataType():string{
		return $this->ejectColumnValue("translatedObjectDataType");
	}

	public function hasTranslatedObjectDataType():bool{
		return $this->hasColumnValue("translatedObjectDataType");
	}

	public function getTranslatedObjectDataType():string{
		return $this->getColumnValue("translatedObjectDataType");
	}

	public function setTranslatedObjectDataType(string $type):string{
		return $this->setColumnValue("translatedObjectDataType", $type);
	}

	public function ejectTranslatedObjectKey():?string{
		return $this->ejectColumnValue('translatedObjectKey');
	}

	public function hasTranslatedObjectKey():bool{
		return $this->hasColumnValue('translatedObjectKey');
	}

	public function getTranslatedObjectKey():string{
		return $this->getColumnValue('translatedObjectKey');
	}

	public function setTranslatedObjectKey(string $key):string{
		return $this->setColumnValue('translatedObjectKey', $key);
	}

	public function setTranslatedObjectSubtype(string $subtype):string{
		return $this->setColumnValue("translatedObjectSubtype", $subtype);
	}
	
	public function hasTranslatedObjectSubtype():bool{
		return $this->hasColumnValue("translatedObjectSubtype");
	}
	
	public function getTranslatedObjectSubtype():string{
		return $this->getColumnValue("translatedObjectSubtype");
	}
	
	public function getTranslatedObjectClass():string{
		if(!$this->hasTranslatedObjectSubtype()){
			return mods()->getDataStructureClass(
				$this->getTranslatedObjectDataType(), 
				$this->getTranslatedObjectSubtype()
			);
		}
		return mods()->getDataStructureClass($this->getTranslatedObjectDataType());
	}

	public function setTranslatedObject(DataStructure $obj):DataStructure{
		$this->setTranslatedObjectKey($obj->getIdentifierValue());
		$this->setTranslatedObjectDataType($obj->getDataType());
		return $this->setForeignDataStructure('translatedObjectKey', $obj);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$components = [];
		$supported = config()->getSupportedLanguages();
		foreach ($supported as $language) {
			$string = new ForeignKeyDatum($language, RELATIONSHIP_TYPE_ONE_TO_ONE);
			$hrvn = Internationalization::getLanguageNameFromCode($language);
			$string->setHumanReadableName($hrvn);
			$string->setAdminInterfaceFlag(true);
			$string->setNullable(true);
			$string->setForeignDataStructureClass(TranslatedStringData::class);
			$string->setDatabaseName(TranslatedStringData::getDatabaseNameStatic());
			$string->setTableName($language);
			$string->setConverseRelationshipKeyName("multilingualStringKey");
			$string->volatilize();
			$string->autoload();
			array_push($components, $string);
		}
		$foreign_bundle = new ForeignMetadataBundle("translatedObject", $ds);
		$foreign_bundle->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$foreign_bundle->setForeignDataStructureClassResolver(TranslatableObjectResolver::class);
		$foreign_bundle->setOnUpdate($foreign_bundle->setOnDelete(REFERENCE_OPTION_CASCADE));
		$foreign_bundle->constrain();
		$foreign_bundle->setNullable(true);
		array_push($columns, $foreign_bundle, ...$components);
	}
	
	public static function getDataType(): string{
		return DATATYPE_STRING_MULTILINGUAL;
	}
	
	public static function getTableNameStatic():string{
		return "multilingual";
	}
	
	protected function afterSetForeignDataStructureHook(string $column_name, DataStructure $struct):int{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($struct->hasColumn('multilingualStringKey')){
			if($print){
				Debug::print("{$f} setting converse relationship key name to \"{$column_name}\"");
			}
			$struct->getColumn('multilingualStringKey')->setConverseRelationshipKeyName($column_name);
		}
		return parent::afterSetForeignDataStructureHook($column_name, $struct);
	}
}
