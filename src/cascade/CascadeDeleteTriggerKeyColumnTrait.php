<?php

namespace JulianSeymour\PHPWebApplicationFramework\cascade;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInsertForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use Exception;
use mysqli;

/**
 * A trait for DataStructure classes that initiate a cascade delete event when they themselves are deleted. this is useful for deleting automatically deleting related foreign data structures that do not have a directe reference to the instigator.
 * @author j
 *
 */
trait CascadeDeleteTriggerKeyColumnTrait{

	public function setCascadeDeleteFlag(bool $value = true): bool{
		return $this->setFlag("cascadeDelete", $value);
	}

	public function getCascadeDeleteFlag(): bool{
		return $this->getFlag("cascadeDelete");
	}

	public function cascadeDelete(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		$delete = QueryBuilder::delete()->from(CascadeDeleteTriggerData::getDatabaseNameStatic(), CascadeDeleteTriggerData::getTableNameStatic())->where(CascadeDeleteTriggerData::whereIntersectionalHostKey(static::class, "instigatorKey"))->withTypeSpecifier('ss')->withParameters($this->getIdentifierValue(), "instigatorKey");
		if($print){
			Debug::print("{$f} cascade delete query is \"{$delete}\"");
		}
		$status = $delete->executeGetStatus($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::error("{$f} executing cascade delete query statement \"{$delete}\" returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} successfully executed cascase delete query statement");
		}
		return SUCCESS;
	}

	public function getCascadeDeleteTriggerKey(): string{
		return $this->getColumnValue("cascadeDeleteTriggerKey");
	}

	public function hasCascadeDeleteTriggerKey(): bool{
		return $this->hasColumnValue("cascadeDeleteTriggerKey");
	}

	public function setCascadeDeleteTriggerKey(string $value): string{
		return $this->setValue("cascadeDeleteTriggerKey", $value);
	}

	public function ejectCascadeDeleteTriggerKey(): ?string{
		return $this->ejectColumnValue("cascadeDeleteTriggerKey");
	}

	public function setCascadeDeleteTriggerData(CascadeDeleteTriggerData $struct): CascadeDeleteTriggerData{
		return $this->setForeignDataStructure("cascadeDeleteTriggerKey", $struct);
	}

	public function hasCascadeDeleteTriggerData(): bool{
		return $this->hasForeignDataStructure("cascadeDeleteTriggerKey");
	}

	public function ejectCascadeDeleteTriggerData(): ?CascadeDeleteTriggerData{
		return $this->ejectForeignDataStructure("cascadeDeleteTriggerKey");
	}

	public function getCascadeDeleteTriggerData(): CascadeDeleteTriggerData{
		return $this->getForeignDataStructure("cascadeDeleteTriggerKey");
	}

	public static function generateCascadeDeleteTriggerKeyColumn(): ForeignKeyDatum{
		$column = new ForeignKeyDatum("cascadeDeleteTriggerKey");
		$column->setForeignDataStructureClass(CascadeDeleteTriggerData::class);
		$column->autoload();
		$column->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$column->setNullable(true);
		return $column;
	}
	
	public function generateCascadeDeleteTriggerData(mysqli $mysqli): CascadeDeleteTriggerData{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::printStackTraceNoExit("{$f} entered for this ".$this->getDebugString());
			}
			if($this->hasCascadeDeleteTriggerData()){
				Debug::error("{$f} do not call this if the data has already been generated");
				return $this->getCascadeDeleteTriggerData();
			}elseif($this->hasColumn("cascadeDeleteTriggerKey")){
				Debug::print("{$f} cascade delete trigger key column already exists");
			}else{
				$column = new ForeignKeyDatum("cascadeDeleteTriggerKey", RELATIONSHIP_TYPE_ONE_TO_ONE);
				$column->setForeignDataStructureClass(CascadeDeleteTriggerData::class);
				$column->volatilize();
				$column->setDataStructure($this);
				$this->pushColumn($column);
			}
			$cdtd = new CascadeDeleteTriggerData();
			$cdtd->setInstigatorData($this);
			$select = CascadeDeleteTriggerData::selectStatic()->where(CascadeDeleteTriggerData::whereIntersectionalForeignKey(static::class, "instigatorKey"))->withTypeSpecifier('ss')->withParameters($this->getIdentifierValue(), "instigatorKey");
			if($print){
				Debug::print("{$f} select statement is \"{$select}\"");
			}
			$result = $select->executeGetResult($mysqli);
			$rows = $result->num_rows;
			switch($rows){
				case 0:
					$result->free_result();
					if($print){
						Debug::print("{$f} there were no results");
					}
					$cdtd->generateKey();
					$this->addEventListener(EVENT_BEFORE_INSERT_FOREIGN, function (BeforeInsertForeignDataStructuresEvent $event, DataStructure $target) use ($f){
						$when = $event->getProperty('when');
						if($when !== CONST_AFTER){
							return SUCCESS;
						}
						$cdtd = $target->getForeignDataStructure("cascadeDeleteTriggerKey");
						$mysqli = db()->getConnection(PublicWriteCredentials::class);
						$cdtd->setPermission(DIRECTIVE_INSERT, SUCCESS);
						$status = $cdtd->insert($mysqli);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} inserting CascadeDeleteTriggerData returned error status \"{$err}\"");
							return $target->setObjectStatus($status);
						}
						return SUCCESS;
					});
					break;
				case 1:
					if($print){
						Debug::print("{$f} there was 1 result");
					}
					$results = $result->fetch_all(MYSQLI_ASSOC);
					$result->free_result();
					$status = $cdtd->processQueryResultArray($mysqli, $results[0]);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processing query results array for CascadeDeleteTriggerData returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					break;
				default:
					Debug::error("{$f} {$rows} rows selected");
			}
			return $this->setForeignDataStructure("cascadeDeleteTriggerKey", $cdtd);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
