<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\cascade\CascadeDeletableInterface;
use JulianSeymour\PHPWebApplicationFramework\cascade\CascadeDeleteTriggerData;
use JulianSeymour\PHPWebApplicationFramework\cascade\CascadeDeleteTriggerKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\HashKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\PseudokeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\SerialNumberDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\DatabaseCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

abstract class StandardDataStructure extends DataStructure
implements CascadeDeletableInterface, ReplacementKeyRequestableInterface{

	use CascadeDeleteTriggerKeyColumnTrait;
	use ReplacementKeyRequestableTrait;

	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		try{
			parent::__construct($mode);
			if(
				!app()->getFlag("install") &&
				method_exists($this, "getTableNameStatic") &&
				method_exists($this, "getDatabaseNameStatic") &&
				!$this instanceof EmbeddedData &&
				!$this instanceof IntersectionData &&
				!$this instanceof EventSourceData &&
				!$this instanceof DatabaseCredentials &&
				$this->getDefaultPersistenceMode() === PERSISTENCE_MODE_DATABASE
			){
				if(!$this->tableExists(db()->getConnection(PublicReadCredentials::class))){
					Debug::error("{$f} table \"".$this->getTableName()."\" does not exist");
				}
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"globalIndex"
		]);
	}
	
	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_PSEUDOKEY;
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::declareColumns($columns, $ds);
			$num = new SerialNumberDatum("num");
			$num->setHumanReadableName(_("Serial number"));
			$mode = static::getKeyGenerationMode();
			if($mode !== KEY_GENERATION_MODE_NATURAL){
				switch($mode){
					case KEY_GENERATION_MODE_PSEUDOKEY:
						$key = new PseudokeyDatum('uniqueKey');
						break;
					case KEY_GENERATION_MODE_HASH:
						$key = new HashKeyDatum('uniqueKey');
						break;
					default:
						Debug::error("{$f} invalid key generation mode \"{$mode}\"");
				}
				$key->setUniqueFlag(true);
				$key->setIndexFlag(true);
				array_push($columns, $key);
			}
			$insert = new TimestampDatum("insertTimestamp");
			$insert->setHumanReadableName(_("Insert timestamp"));
			// $insert->setSortable(true);
			$updated = new TimestampDatum("updatedTimestamp");
			$updated->setUserWritableFlag(true);
			// $updated->setSortable(true);
			$updated->setHumanReadableName(_("Update timestamp"));
			$insert_ip = new IpAddressDatum("insertIpAddress");
			$insert_ip->setSensitiveFlag(true);
			$globalIndexKey = new ForeignKeyDatum("globalIndexKey");
			$globalIndexKey->constrain(true);
			$globalIndexKey->setForeignDataStructureClass(GlobalIndexData::class);
			$globalIndexKey->setNullable(true);
			$globalIndexKey->setPersistenceMode(PERSISTENCE_MODE_VOLATILE);
			$globalIndexKey->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
			array_push(
				$columns,
				$num,
				$insert,
				$updated,
				$insert_ip,
				$globalIndexKey
			);
			if($ds instanceof CascadeDeletableInterface){
				$cascade = static::generateCascadeDeleteTriggerKeyColumn();
				$cascade->setNullable(true);
				array_push($columns, $cascade);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * override this to create additional functionality that gets called before deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function beforeDeleteHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		try{
			$result = parent::beforeDeleteHook($mysqli);
			// delete cascade triggers
			if(!$this instanceof CascadeDeleteTriggerData){ //$this->getFlag("cascadeDelete")){
				$this->cascadeDelete($mysqli);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::warning("{$f} override this is derived classes to determine which data are returned in the toArray function for a given use case");
		}
		switch($config_id){
			case "default":
			default:
				$print = false;
				if($print){
					if($this->hasElementClass()){
						Debug::print("{$f} element class is defined");
					}else{
						Debug::print("{$f} element class is undefined");
						if($this->getSearchResultFlag()){
							Debug::error("{$f} element class must be defined for object with search result flag set");
						}
					}
				}
				$config = [
					"num" => $this->hasColumn("num"),
					$this->getIdentifierName() => $this->hasColumn($this->getIdentifierName()),
					"insertTimestamp" => $this->hasColumn("insertTimestamp"),
					"updatedTimestamp" => $this->hasColumn("updatedTimestamp"),
					"dataType" => $this->hasColumn("dataType"),
					"searchResult" => $this->hasColumn("searchResult") && $this->getSearchResultFlag(),
					"status" => $this->hasColumn("status"),
					"prettyClassName" => false,
					"elementClass" => $this->hasElementClass()
				];
				//some common ones
				if($this->hasColumnValue('subtype') || $this instanceof StaticSubtypeInterface){
					$config['subtype'] = true;
				}
				if($this->hasColumn("name")){
					$config['name'] = true;
				}
				break;
		}
		return $config;
	}
	
	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		switch($column_name){
			case "insertTimestampString":
				return $this->getInsertTimestampString();
			case "updatedTimestampString":
				return $this->getUpdatedTimestampString();
			default:
				return parent::getVirtualColumnValue($column_name);
		}
	}
	
	public function hasVirtualColumnValue(string $column_name): bool{
		$f = __METHOD__;
		switch($column_name){
			case "insertTimestampString":
				return $this->hasInsertTimestamp();
			case "updatedTimestampString":
				return $this->hasUpdatedTimestamp();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}
	
	protected function beforeGenerateInitialValuesHook():int{
		$f = __METHOD__;
		$ret = parent::beforeGenerateInitialValuesHook();
		if($this->hasColumn("insertTimestamp")){
			$time = $this->generateInsertTimestamp();
		}else{
			$time = time();
		}
		if($this->hasColumn("updatedTimestamp") && ! $this->hasColumnValue("updatedTimestamp")){
			$this->setUpdatedTimestamp($time);
		}
		if($this->hasColumn("insertIpAddress") && ! $this->hasColumnValue("insertIpAddress")){
			if(isset($_SERVER['REMOTE_ADDR'])){
				$ip = $_SERVER['REMOTE_ADDR'];
			}else{
				$ip = SERVER_PUBLIC_IP_ADDRESS;
			}
			$this->setInsertIpAddress($ip);
		}
		return $ret;
	}
	
	public function ejectInsertIpAddress():?string{
		return $this->ejectColumnValue("insertIpAddress");
	}
	
	public function hasInsertIpAddress():bool{
		return $this->hasColumnValue("insertIpAddress");
	}
	
	public function setInsertIpAddress(string $ip):string{
		return $this->setColumnValue("insertIpAddress", $ip);
	}
	
	public function getInsertIpAddress():string{
		return $this->getColumnValue("insertIpAddress");
	}
	
	public function hasInsertTimestamp():bool{
		return $this->hasColumnValue('insertTimestamp');
	}
	
	public function getInsertTimestamp():int{
		return $this->getColumnValue('insertTimestamp');
	}
	
	public function setInsertTimestamp(int $ts):int{
		return $this->setColumnValue("insertTimestamp", $ts);
	}
	
	public function setUpdatedTimestamp(int $ts):int{
		return $this->setColumnValue("updatedTimestamp", $ts);
	}
	
	public function generateInsertTimestamp():int{
		$f = __METHOD__;
		try{
			if(!$this->hasInsertTimestamp()){
				// Debug::print("{$f} generating insert timestamp now");
				return $this->setInsertTimestamp(time());
			}
			// Debug::print("{$f} insert timestamp already generated");
			return $this->getInsertTimestamp();
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function hasSerialNumber():bool{
		return $this->hasColumnValue('num');
	}
	
	public function getSerialNumber():int{
		$f = __METHOD__;
		if(!$this->hasSerialNumber()){
			Debug::error("{$f} serial number is undefined");
		}
		return $this->getColumnValue("num");
	}
	
	public function setSerialNumber(int $num): int{
		return $this->setColumnValue("num", $num);
	}
	
	public function ejectSerialNumber():?int{
		return $this->ejectColumnValue("num");
	}
	
	public function updateTimestamp(mysqli $mysqli):int{
		$f = __METHOD__;
		try{
			$this->setUpdatedTimestamp(time());
			$status = $this->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// Debug::print("{$f} timestamp update succeeded");
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getInsertTimestampString():string{
		return getDateTimeStringFromTimestamp($this->getInsertTimestamp());
	}
	
	public function getUpdatedTimestampString():string{
		return getDateTimeStringFromTimestamp($this->getUpdatedTimestamp());
	}
	
	public function hasUpdatedTimestamp():bool{
		return $this->hasColumnValue("updatedTimestamp");
	}
	
	public function getUpdatedTimestamp():int{
		if(!$this->hasUpdatedTimestamp()){
			return $this->getInsertTimestamp();
		}
		return $this->getColumnValue("updatedTimestamp");
	}
	
	public function setGlobalIndexFlag(bool $value=true):bool{
		return $this->setFlag("globalIndex", $value);
	}
	
	public function getGlobalIndexFlag():bool{
		return $this->getFlag("globalIndex");
	}
	
	public function globalIndex(bool $value=true):StandardDataStructure{
		$this->setGlobalIndexFlag($value);
		return $this;
	}
	public function afterGenerateKeyHook($key):int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::afterGenerateKeyHook($key);
		if($this->getGlobalIndexFlag()){
			if($print){
				Debug::print("{$f} initializing cascade delete trigger data and global index for this ".$this->getDebugString());
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$cascader = $this->generateCascadeDeleteTriggerData($mysqli);
			$globalindex = new GlobalIndexData();
			$globalindex->setIndexedData($this);
			$globalindex->setDeleteListenerData($cascader);
			$globalindex->setInsertFlag(true);
			$globalindex->setPermission(DIRECTIVE_INSERT, SUCCESS);
			$this->setForeignDataStructure("globalIndexKey", $globalindex);
			$this->setPostInsertForeignDataStructuresFlag(true);
		}elseif($print){
			Debug::print("{$f} global index flag is not set for this ".$this->getDebugString());
		}
		return $ret;
	}
}