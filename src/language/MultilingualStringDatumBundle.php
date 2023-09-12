<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetForeignDataStructureEvent;

class MultilingualStringDatumBundle extends DatumBundle implements StaticElementClassInterface{
	
	use ElementBindableTrait;
	
	public static function getStringDatumClassStatic():string{
		return TextDatum::class;
	}
	
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return MultilingualTextForm::class;
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
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$column_name = $this->getName();
		$datum_class = static::getStringDatumClassStatic();
		$localized = new $datum_class($column_name);
		if($this->hasHumanReadableName()){
			$localized->setHumanReadableName($this->getHumanReadableName());
		}
		$localized->setSearchable(true);
		$lang = app()->hasUserData() ? user()->getLanguagePreference() : LANGUAGE_DEFAULT;
		$localized->setSubqueryClass(TranslatedStringData::class);
		$localized->setSubqueryColumnName("value");
		$localized->setSubqueryDatabaseName(TranslatedStringData::getDatabaseNameStatic());
		$localized->setSubqueryTableName($lang);
		$alias = $localized->getSubqueryTableAlias();
		$idn = $localized->getSubqueryClass()::getIdentifierNameStatic();
		$rcn = $localized->setReferenceColumnName("{$column_name}Key");
		$where = BinaryExpressionCommand::equals(
			new GetDeclaredVariableCommand("{$alias}.{$idn}"),
			new GetDeclaredVariableCommand("t0.{$rcn}")
		);
		$localized->setSubqueryWhereCondition($where);
		if($print){
			Debug::print("{$f} subquery \"".$where->toSQL()."\"");
		}
		return $localized;
	}
	
	/**
	 * XXX TODO I forgot why the event handler is necessary
	 * {@inheritDoc}
	 * @see \JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle::generateComponents()
	 */
	public function generateComponents(?DataStructure $ds = null):array{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$column_name = $this->getName();
		//foreign key for selecting name data structure
			$translatedStringKey = $this->generateTranslatedStringKeyDatum();
		//aliased coolumn for automatically loading the correct localized string
			$localized = $this->generateTranslatedStringValueDatum();
		//updates the aliased column when the foreign data structure is set
			$closure1 = function(AfterSetForeignDataStructureEvent $event, DataStructure $target) use ($column_name, $f, $print){
				$cn = $event->getColumnName();
				if($cn !== "{$column_name}Key"){
					return SUCCESS;
				}
				$user = user();
				$lang = $user->hasLanguagePreference() ? $user->getLanguagePreference() : LANGUAGE_DEFAULT;
				$struct = $event->getForeignDataStructure();
				if($struct->hasForeignDataStructure($lang)){
					$target->setColumnValue(
						$column_name, 
						$struct->getForeignDataStructure($lang)->getColumnValue('value')
					);
				}else{
					$did = $struct->getDebugId();
					if($print){
						Debug::print("{$f} multilingual string data with debug ID {$did} does not have a link to its translated string data  \"{$lang}\"");
					}
					$closure2 = function(AfterSetForeignDataStructureEvent $event, MultilingualStringData $msd) use ($target, $column_name, $lang, $f, $print, $did){
						if($event->getColumnName() !== $lang){
							return SUCCESS;
						}
						$tsd = $event->getForeignDataStructure();
						if(!$tsd->hasColumnValue('value')){
							if($print){
								Debug::print("{$f} translated string data doesn't have its value yet");
							}
							return SUCCESS;
						}
						$msd->removeEventListener($event);
						$target->setColumnValue($column_name, $tsd->getColumnValue('value'));
						if($print){
							Debug::print("{$f} successfully set string value with nested AfterSetForeignDataStructureEvent for MultilingualStringData with debug ID {$did}");
						}
						return SUCCESS;
					};
					$struct->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure2);
				}
				return SUCCESS;
			};
			$ds->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure1);
		return [$translatedStringKey, $localized];
	}
}
