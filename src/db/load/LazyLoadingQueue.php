<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\app\InstallFrameworkUseCase;

class LazyLoadingQueue extends Basic{

	use LoadedFlagTrait;

	protected $deferredObjectMap;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"loaded"
		]);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->deferredObjectMap);
	}

	public function defer($object): int{
		$f = __METHOD__;
		$print = false;
		if($this->getLoadedFlag()) {
			Debug::error("{$f} this lazy loader has already been used");
		}
		$object->setObjectStatus(STATUS_PRELAZYLOAD);
		if(!is_array($this->deferredObjectMap)) {
			$this->deferredObjectMap = [];
		}
		$key = $object->getIdentifierValue();
		if(!is_int($key) && ! is_string($key)) {
			$gottype = gettype($key);
			Debug::error("{$f} key is a \"{$gottype}\"");
		}elseif($print) {
			$class = $object->getClass();
		}
		if(! array_key_exists($key, $this->deferredObjectMap)) {
			if($print) {
				Debug::print("{$f} deferring {$class} with key \"{$key}\"");
			}
			$this->deferredObjectMap[$key] = $object;
		}elseif($print) {

			Debug::print("{$f} {$class} with key {$key} has already been mapped for lazy loading");
		}
		return SUCCESS;
	}

	public function load(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->getLoadedFlag()) {
				Debug::error("{$f} this lazy loader has already been used");
			}
			$this->setLoadedFlag(true);
			$loadMap = [];
			foreach($this->deferredObjectMap as $key => $object) {
				$db = $object->getDatabaseName();
				$table = $object->getTableName();
				$dbtable = "{$db}.{$table}";
				if(! array_key_exists($dbtable, $loadMap)) {
					$loadMap[$dbtable] = [
						$key => $object
					];
				}else{
					$loadMap[$dbtable][$key] = $object;
				}
			}
			$flat_array = [];
			// load the bare row object (including embedded columns but no intersection tables or foreign data structures)
			foreach($loadMap as $dbtable => $objectsInTable) {
				if($print) {
					Debug::print("{$f} about to lazy load from table \"{$dbtable}\"");
				}
				$keys = array_keys($objectsInTable);
				$first_object = $objectsInTable[array_keys($objectsInTable)[0]];
				$idn = $first_object->getIdentifierName();
				$where = new WhereCondition($idn, OPERATOR_IN);
				$count = count($keys);
				$where->setParameterCount($count);
				$select = $first_object->select()->where($where);
				if($print) {
					Debug::print("{$f} about to query \"{$select}\"");
				}
				$select->pushParameters(...$keys);
				$typedef = Loadout::assignTypeSpecifier(get_class($first_object), $select);
				$select->setTypeSpecifier($typedef);
				$result = $select->executeGetResult($mysqli);
				if(! isset($result)) {
					Debug::error("{$f} retuls of query {$select} returned null");
					return $this->setObjectStatus(ERROR_MYSQL_RESULT);
				}
				$results = $result->fetch_all(MYSQLI_ASSOC);
				$count = count($results);
				if($count === 0) {
					if($print) {
						Debug::warning("{$f} fetched 0 results for table \"{$dbtable}\" with query statement \"{$select}\" and the following parameters:");
						Debug::printArray($select->getParameters());
					}
					continue;
				}
				if($print) {
					Debug::print("{$f} fetched {$count} results");
				}
				foreach($results as $r) {
					$key = $r[$idn];
					$load_me = $objectsInTable[$key];
					if($print && $load_me->getDebugFlag()) {
						Debug::print("{$f} loaded object with key \"{$key}\" using select statement \"{$select}\"");
					}
					$status = $load_me->processQueryResultArray($mysqli, $r);
					$load_me->setObjectStatus($status);
					if($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processing loaded object with key \"{$key}\" returned error status \"{$err}\"");
						continue;
					}elseif($print) {
						Debug::print("{$f} assigning object with key \"{$key}\" to flat array");
					}
					$flat_array[$key] = $load_me;
					if(CACHE_ENABLED && $load_me->hasTimeToLive()) {
						cache()->setAPCu($key, $r, $load_me->getTimeToLive());
					}elseif($print) {
						Debug::print("{$f} cache is disabled, or object with key \"{$key}\" does not have a time to live");
					}
				}
			}
			unset($loadMap);
			if(empty($flat_array)) {
				if($print) {
					Debug::warning("{$f} not a single object was loaded successfully");
				}
				foreach($this->deferredObjectMap as $pathetic_failure) {
					$pathetic_failure->loadFailureHook(); // setObjectStatus(STATUS_DELETED);
				}
				return FAILURE;
			}elseif($print) {
				Debug::print("{$f} flat array is NOT empty");
			}
			// load keys stored in intersection tables
			LazyLoadHelper::loadIntersectionTableKeys($mysqli, $flat_array);
			// load foreign data structures
			foreach($flat_array as $key => $object) {
				if($object->getObjectStatus() !== SUCCESS) {
					if($print) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::print("{$f} object with key \"{$key}\" has abnormal status \"{$err}\"");
					}
					continue;
				}
				$status = $object->loadForeignDataStructures($mysqli, true, 3);
				$object->setObjectStatus($status);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} loading foreign data structures for object with key \"{$key}\" returned error status \"{$err}\"");
					continue;
				}
			}
			// mark deleted objects as deleted
			foreach($this->deferredObjectMap as $key => $object) {
				if($object->getObjectStatus() !== SUCCESS) {
					if($print) {
						$oc = $object->getClass();
						Debug::print("{$f} {$oc} with key \"{$key}\" not found");
					}
					$object->loadFailureHook();
				}
				$idn = $object->getIdentifierName();
				if($object->hasColumn($idn) && ! ($object->getKeyGenerationMode() === KEY_GENERATION_MODE_NATURAL && $object->getColumn($idn) instanceof ForeignKeyDatum) && $object->hasIdentifierValue()) {
					registry()->update($key, $object);
				}
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
