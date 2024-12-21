<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\StandardDataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameTrait;

class TranslatedStringData extends StandardDataStructure implements StaticDatabaseNameInterface{
	
	use StaticDatabaseNameTrait;
	
	public static function getDataType(): string{
		return DATATYPE_STRING_TRANSLATED;
	}

	public static function getDatabaseNameStatic():string{
		return "strings";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds=null):void{
		$multi = new ForeignKeyDatum("multilingualStringKey", RELATIONSHIP_TYPE_ONE_TO_ONE);
		$multi->setForeignDataStructureClass(MultilingualStringData::class);
		$value = new TextDatum("value");
		$value->setFulltextFlag(true);
		$value->setAdminInterfaceFlag(true);
		$value->setNullable(true);
		$columns = [
			$multi,
			$value
		];
	}
	
	public static function getKeyGenerationMode():int{
		return KEY_GENERATION_MODE_NATURAL;
	}
	
	public static function getIdentifierNameStatic():?string{
		return "multilingualStringKey";
	}
	
	protected function afterSetForeignDataStructureHook(string $column_name, DataStructure $struct): int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$ret = parent::afterSetForeignDataStructureHook($column_name, $struct);
		if($column_name === "multilingualStringKey"){
			if($print){
				Debug::print("{$f} about to set table name and human readable name");
			}
			$msk = $this->getColumn("multilingualStringKey");
			if($msk->hasConverseRelationshipKeyName()){
				$code = $msk->getConverseRelationshipKeyName();
				$this->setTableName($code);
				if($print){
					Debug::print("{$f} set table name to \"{$code}\"");
				}
				if($struct->hasForeignDataStructure('translatedObjectKey')){
					if($print){
						Debug::print("{$f} multilingual string object has a reference to the object is's translating");
					}
					$tsk = $struct->getColumn("translatedObjectKey");
					if($tsk->hasConverseRelationshipKeyName()){
						$subject = $struct->getForeignDataStructure('translatedObjectKey');
						$crkn = $tsk->getConverseRelationshipKeyName();
						if($subject->hasColumn($crkn)){
							$column = $subject->getColumn($crkn);
							if($column->hasHumanReadableName()){
								$lang = Internationalization::getLanguageNameFromCode($code);
								$hrn = $column->getHumanReadableName()." ({$lang})";
								$this->getColumn('value')->setHumanReadableName($hrn);
								if($print){
									Debug::print("{$f} set human readable name to \"{$hrn}\"");
								}
							}elseif($print){
								Debug::print("{$f} the column we're translating ({$crkn}) doesn't have a human readable name");
							}
						}elseif($print){
							Debug::print("{$f} subject ".$subject->getDebugString()." does not have a column \"{$crkn}\"");
						}
					}elseif($print){
						Debug::print("{$f} translated object key column does not know its converse relationship key name");
					}
				}elseif($print){
					Debug::print("{$f} multilingual string does not know about the subject it is translating");
				}
			}elseif($print){
				Debug::printStackTraceNoExit("{$f} multilingual string key datum does not know its converse relationship key name");
			}
		}
		return $ret;
	}
	
	public function hasValue():bool{
		return $this->hasColumnValue('value');
	}
	
	public function setValue($value){
		return $this->setColumnValue('value', $value);
	}
	
	public function getValue(){
		return $this->getColumnValue('value');
	}
}
