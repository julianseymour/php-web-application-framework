<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\Datastructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use mysqli;

class MultilingualStringData extends DataStructure implements StaticElementClassInterface{

	use ElementBindableTrait;
	
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
			$content = new TextDatum($language, $language);
			//$content->setBBCodeFlag(true);
			$content->setFulltextFlag(true);
			$hrvn = Internationalization::getLanguageNameFromCode($language);
			$content->setHumanReadableName($hrvn);
			$content->setAdminInterfaceFlag(true);
			$content->setNullable(true);
			array_push($components, $content);
		}
		$foreign_bundle = new ForeignMetadataBundle("translatedObject", $ds);
		$foreign_bundle->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$foreign_bundle->setForeignDataStructureClassResolver(TranslatableObjectResolver::class);
		$foreign_bundle->setOnUpdate($foreign_bundle->setOnDelete(REFERENCE_OPTION_CASCADE));
		$foreign_bundle->constrain();
		$foreign_bundle->setNullable(true);
		static::pushTemporaryColumnsStatic($columns, $foreign_bundle, ...$components);
	}
	
	public static function getDataType(): string{
		return DATATYPE_STRING;
	}
	
	public static function getDeledObjectClass(){
		return DeletedStringData::class;
	}
	
	protected function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$english = $this->getColumnValue(LANGUAGE_DEFAULT);
		if (empty($english)) {
			Debug::warning("{$f} nothing to insert");
			return $this->setObjectStatus(ERROR_NULL_STRING);
		}
		return parent::beforeInsertHook($mysqli);
	}
	
	public static function getPhylumName(): string{
		return "strings";
	}
	
	public static function getTableNameStatic(): string{
		return "strings";
	}
}
