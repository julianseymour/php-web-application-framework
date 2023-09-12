<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class LazyLoadHelper extends Basic
{

	/**
	 * counts the most recent LazyLoadingQueue to load
	 *
	 * @var int
	 */
	protected $lazyLoaderIndex;

	/**
	 * array of LazyLoadingQueues
	 *
	 * @var LazyLoadingQueue[]
	 */
	protected $lazyLoadingQueues;

	public function __construct(){
		$this->lazyLoaderIndex = 0;
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure[] $flat_array
	 * @return int
	 */
	public static function loadIntersectionTableKeys(mysqli $mysqli, array &$flat_array){
		$f = __METHOD__;
		try{
			$print = false;
			$intersectionTableNames = [];
			$intersectionTableKeys = [];
			// prepare list of objects that need to load keys stored in intersection tables
			foreach($flat_array as $key => $object) {
				$tempTableNames = $object->getLoadableIntersectionTableNames();
				if(empty($tempTableNames)) {
					if($print) {
						Debug::print("{$f} object with key \"{$key}\" has no intersection table names");
					}
					continue;
				}elseif($print) {
					Debug::print("{$f} about to print the output of getLoadableIntersectionTableNames");
					Debug::printArray($tempTableNames);
					Debug::print("{$f} printed output of getLoadableIntersectionTableNames");
				}
				foreach($tempTableNames as $intersectionTableName => $ftn) {
					if(! array_key_exists($intersectionTableName, $intersectionTableKeys)) {
						$intersectionTableKeys[$intersectionTableName] = [];
					}
					array_push($intersectionTableKeys[$intersectionTableName], $key);
				}
				$intersectionTableNames = array_merge($intersectionTableNames, $tempTableNames);
				unset($tempTableNames);
			}
			if(empty($intersectionTableKeys)) {
				if($print) {
					Debug::print("{$f} no intersection tables to load");
				}
				foreach($flat_array as $object){
					$object->setObjectStatus(SUCCESS);
				}
				return SUCCESS;
			}
			// load intersection table keys
			if($print) {
				Debug::print("{$f} about to print keys of objects with values stored in intersection tables");
				Debug::printArray($intersectionTableKeys);
				Debug::print("{$f} printed keys of objects with values stored in intersection tables; about to print host key names");
				Debug::printArray($intersectionTableNames);
				Debug::print("{$f} printed host key names");
			}
			$objects = [];
			foreach($intersectionTableKeys as $intersectionTableName => $keyList) {
				if($print) {
					Debug::print("{$f} intersection table name \"{$intersectionTableName}\"");
				}
				$where = new WhereCondition("hostKey", OPERATOR_IN);
				$where->setParameterCount(count($keyList));
				$select = new SelectStatement();
				$ftn = $intersectionTableNames[$intersectionTableName];
				$select->from($ftn->getDatabaseName(), $ftn->getTableName())->where($where);
				$typedef = str_pad("", count($keyList), 's');
				$result = $select->prepareBindExecuteGetResult($mysqli, $typedef, ...$keyList);
				$count = $result->num_rows;
				if($count == 0) {
					if($print) {
						Debug::warning("{$f} query \"{$select}\" returned 0 results");
					}
					continue;
				}
				$results = $result->fetch_all(MYSQLI_ASSOC);
				if($print) {
					Debug::print("{$f} query \"{$select}\" returned the following results:");
					Debug::printArray($results);
				}
				foreach($results as $result) {
					$key = $result["hostKey"];
					if($print) {
						Debug::print("{$f} about to call processIntersectionTableQueryResultArray on object with key \"{$key}\" for the following array");
						Debug::printArray($result);
					}
					$object = $flat_array[$key];
					if(!array_key_exists($key, $objects)){
						$objects[$key] = $object;
					}
					$status = $object->processIntersectionTableQueryResultArray($result);
					if($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processIntersectionTableQueryResultArray on object with key \"{$key}\" returned error status \"{$err}\"");
						return $object->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} successfully processed intersection table query results for object with key \"{$key}\"");
					}
					if(CACHE_ENABLED && $object->isRegistrable() && $object->hasIdentifierValue()) {
						if(cache()->hasAPCu($key)) {
							$columns = $object->getFilteredColumns(COLUMN_FILTER_DIRTY_CACHE);
							if(!empty($columns)) {
								$cached_value = cache()->getAPCu($key);
								foreach($columns as $column_name => $column) {
									$cached_value[$column_name] = $column->getDatabaseEncodedValue();
									$column->setDirtyCacheFlag(false);
								}
								cache()->setAPCu($key, $cached_value);
							}elseif($print) {
								Debug::print("{$f} there are no dirty cache flagged columns");
							}
						}elseif($print) {
							Debug::print("{$f} there is no cached value with key \"{$key}\"");
						}
					}elseif($print) {
						Debug::print("{$f} cache is not enabled");
					}
					if($print){
						Debug::print("{$f} completely done with object {$key}");
					}
				}
			}
			foreach($flat_array as $object){
				if($print){
					$did = $object->getDebugId();
					Debug::print("{$f} marking service with debug ID {$did} as loaded");
				}
				$object->setObjectStatus(SUCCESS);
				$object->setLoadedFlag(true);
			}
			if($print){
				Debug::print("{$f} returning");
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @param DataStructure $object
	 */
	public function deferLoad($object): int
	{
		$f = __METHOD__; //LazyLoadHelper::getShortClass()."(".static::getShortClass().")->deferLoad()";
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} entered");
			}
			if(! app()->hasUserData()) {
				Debug::error("{$f} user data is undefined");
			}
			$user = app()->getUserData();
			if($user->hasIdentifierValue() && $object->getIdentifierValue() === $user->getIdentifierValue()) {
				Debug::error("{$f} cannot lazy load the user data, sorry");
			}
			$queue = $this->getLazyLoadingQueue();
			$queue->defer($object);
			if($object->isRegistrable()) {
				$key = $object->getIdentifierValue();
				registry()->update($key, $object);
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dropLazyLoadingQueues()
	{
		unset($this->lazyLoaderIndex);
		unset($this->lazyLoadingQueues);
	}

	public function getLazyLoadingQueue(): ?LazyLoadingQueue
	{
		$f = __METHOD__; //LazyLoadHelper::getShortClass()."(".static::getShortClass().")->getLazyLoadingQueue()";
		if(!is_array($this->lazyLoadingQueues)) {
			$this->lazyLoadingQueues = [];
		}
		$index = $this->getLazyLoadIndex();
		if(! array_key_exists($index, $this->lazyLoadingQueues)) {
			if($index !== 0) {
				Debug::error("{$f} lazy loading index {$index} !== 0 does not exist");
				return null;
			}
			$queue = new LazyLoadingQueue($this);
			array_push($this->lazyLoadingQueues, $queue);
			return $queue;
		}
		$queue = $this->lazyLoadingQueues[$index];
		if(!$queue->getLoadedFlag()) {
			return $queue;
		}
		$this->lazyLoaderIndex ++;
		// Debug::print("{$f} loader has already loaded -- incremented index to {$this->lazyLoaderIndex}");
		$queue2 = new LazyLoadingQueue($this);
		array_push($this->lazyLoadingQueues, $queue2);
		return $queue2;
	}

	public function loadLazyIndex($mysqli, $index){
		$f = __METHOD__;
		$print = false;
		$queue = $this->lazyLoadingQueues[$index];
		if($queue->getLoadedFlag()) {
			Debug::error("{$f} lazy loader at index \"{$index}\" has already been used");
			return FAILURE;
		}elseif($print) {
			Debug::print("{$f} about to call LazyLoadingQueue->load()");
		}
		return $queue->load($mysqli);
	}

	public function getLazyLoadIndex(): ?int{
		return $this->lazyLoaderIndex;
	}

	public function hasLazyLoadingQueues(): bool{
		return isset($this->lazyLoadingQueues) && is_array($this->lazyLoadingQueues) && ! empty($this->lazyLoadingQueues);
	}

	public function processQueues(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasLazyLoadingQueues()) {
			if($print) {
				Debug::print("{$f} lazy loading queue is empty");
			}
			return SUCCESS;
		}
		// lazy load to optimize foreign data structures that would otherwise be loaded piecemeal
		$count = count($this->lazyLoadingQueues);
		if($print) {
			Debug::print("{$f} before lazy loading, queue has {$count} items");
		}
		$index = 0;
		while (true) {
			$this->loadLazyIndex($mysqli, $index);
			$index ++;
			if($index > $this->getLazyLoadIndex()) {
				if($print) {
					Debug::print("{$f} index {$index} exceeds lazy loader index");
				}
				break;
			}elseif($print) {
				Debug::print("{$f} about to load from lazy loader at index {$index}");
			}
		}
		if($print) {
			$mem = memory_get_usage();
			Debug::print("{$f} memory after loading: {$mem}");
		}
		return SUCCESS;
	}
}
