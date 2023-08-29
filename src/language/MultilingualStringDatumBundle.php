<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;

class MultilingualStringDatumBundle extends DatumBundle implements StaticElementClassInterface{
	
	use ElementBindableTrait;
	
	public static function getStringDatumClassStatic():string{
		return TextDatum::class;
	}
	
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return TextInput::class;
	}
	
	public function generateTranslatedStringKeyDatum():ForeignKeyDatum{
		$column_name = $this->getName();
		$translatedStringKey = new ForeignKeyDatum("{$column_name}Key", RELATIONSHIP_TYPE_ONE_TO_ONE);
		$translatedStringKey->setForeignDataStructureClass(MultilingualStringData::class);
		$translatedStringKey->setConverseRelationshipKeyName("translatedObjectKey");
		$translatedStringKey->setNullable(true);
		$translatedStringKey->constrain();
		$translatedStringKey->setElementClass($this->getElementClass());
		$translatedStringKey->setAdminInterfaceFlag(true);
		if($this->hasHumanReadableName()){
			$translatedStringKey->setHumanReadableName($this->getHumanReadableName());
		}
		$translatedStringKey->autoload();
		return $translatedStringKey;
	}
	
	public function generateTranslatedStringValueDatum():TextDatum{
		$column_name = $this->getName();
		$datum_class = static::getStringDatumClassStatic();
		$localized = new $datum_class($column_name);
		if($this->hasHumanReadableName()){
			$localized->setHumanReadableName($this->getHumanReadableName());
		}
		$lang = app()->hasUserData() ? user()->getLanguagePreference() : LANGUAGE_DEFAULT;
		$localized->setSubqueryClass(MultilingualStringData::class);
		/*$localized->setSubqueryExpression(
			new ColumnAlias(
				new ColumnAliasExpression("strings_alias", $lang),
				$column_name
			)
		);*/
		$localized->setSubqueryColumnName($lang);
		$alias = $localized->getSubqueryTableAlias();
		$idn = $localized->getSubqueryClass()::getIdentifierNameStatic();
		$rcn = $localized->setReferenceColumnName("{$column_name}Key");
		$where = BinaryExpressionCommand::equals(
			new GetDeclaredVariableCommand("{$alias}.{$idn}"),
			new GetDeclaredVariableCommand("t0.{$rcn}")
		);
		$localized->setSubqueryWhereCondition($where);
		return $localized;
	}
	
	public function generateComponents(?DataStructure $ds = null): array{
		$column_name = $this->getName();
		//foreign key for selecting name data structure
			$translatedStringKey = $this->generateTranslatedStringKeyDatum();
		//aliased coolumn for automatically loading the correct localized string
			$localized = $this->generateTranslatedStringValueDatum();
		//updates the aliased column when the foreign data structure is set
			$closure = function(AfterSetForeignDataStructureEvent $event, DataStructure $target) 
			use ($column_name){
				$cn = $event->getColumnName();
				if($cn !== "{$column_name}Key"){
					return SUCCESS;
				}
				$user = user();
				$lang = $user->hasLanguagePreference() ? $user->getLanguagePreference() : LANGUAGE_DEFAULT;
				$struct = $event->getForeignDataStructure();
				if($struct->hasColumnValue($lang)){
					$target->setColumnValue($column_name, $struct->getColumnValue($lang));
				}
				return SUCCESS;
			};
			$ds->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure);
		return [$translatedStringKey, $localized];
	}
}
