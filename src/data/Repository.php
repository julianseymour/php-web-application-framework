<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\db\load\LazyLoadHelper;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\UpdateStatement;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\insert\InsertStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\query\AssignmentExpression;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;

class Repository extends Basic{
	
	use DatabaseNameTrait;
	use DataStructureClassTrait;
	use TableNameTrait;
	
	public function update(mysqli $mysqli, DataStructure $that):int{
		$f = __METHOD__;
		try{
			$print = $that->getDebugFlag();
			if($mysqli == null){
				Debug::error("{$f} mysqli is null");
			}
			// check user has permission to update this object
			$status = $that->permit(user(), DIRECTIVE_UPDATE);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update permission for class ".$that->getShortClass()." returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}
			$that->logDatabaseOperation(DIRECTIVE_UPDATE);
			// start database transaction
			$transactionId = null;
			if(!db()->hasPendingTransactionId()){
				$transactionId = sha1(random_bytes(32));
				db()->beginTransaction($mysqli, $transactionId);
			}
			// pre-update hook
			$status = $that->beforeUpdateHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before update hook returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} beforeUpdateHook returned successfully");
			}
			// insert/update foreign data structures that must be dealt with before this object's column is updated
			if($that->getPreInsertForeignDataStructuresFlag() || $that->getPreUpdateForeignDataStructuresFlag()){
				$status = $that->preUpdateForeignDataStructures($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} preUpdateForeignDataStructures returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} preinsert and preupdate foreign data structures flags are both not set");
			}
			// list the datums to update
			$write_indices = [];
			$typedef = "";
			$params = [];
			$cache_columns = $that->getFilteredColumns(COLUMN_FILTER_UPDATE, '!'.COLUMN_FILTER_VOLATILE);
			if($that instanceof EmbeddedData){
				$columns = $cache_columns;
			}else{
				$columns = $that->getFilteredColumns(COLUMN_FILTER_DATABASE, COLUMN_FILTER_UPDATE);
			}
			foreach($columns as $vn => $column){
				if($column->getUpdateFlag()){
					if($print){
						Debug::print("{$f} datum \"{$vn}\" has its update flag set");
					}
					$column->setUpdateFlag(false);
					$write_indices[] = new AssignmentExpression($vn);
					$typedef .= $column->getTypeSpecifier();
					array_push($params, $column->getDatabaseEncodedValue());
				}
			}
			// write datums that are flagged for update
			if(!empty($write_indices)){
				if($that->hasConcreteColumn("updatedTimestamp") && !array_key_exists("updatedTimestamp", $write_indices)){
					if($print){
						Debug::print("{$f} updating timestamp");
					}
					$now = $that->setUpdatedTimestamp(time());
					$write_indices[]= new AssignmentExpression("updatedTimestamp");
					$typedef .= "i";
					array_push($params, $now);
				}elseif($print){
					Debug::print("{$f} timestamp does not exist or is already getting updated");
				}
				$identifier = $that->getIdentifierName();
				if($print){
					Debug::print("{$f} about to update the following indices:");
					Debug::printArray($write_indices);
					Debug::print("{$f} ... with the following values:");
					Debug::printArray($params);
				}
				$update = $that->getUpdateStatement($write_indices);
				/*$update = new UpdateStatement(
					$that->getUpdateDatabaseName(),
					$that->getUpdateViewName()
				);
				$update->set($write_indices)->where(
					new WhereCondition($that->getIdentifierName(), OPERATOR_EQUALS)
				);*/
				$typedef .= $that->getColumn($identifier)->getTypeSpecifier();
				if($print){
					Debug::print("{$f} type specifier is \"{$typedef}\"");
				}
				array_push($params, $that->getColumn($identifier)->getDatabaseEncodedValue());
				if($print){
					Debug::print("{$f} about to execute the following update statement: \"{$update}\"");
				}
				$status = $update->prepareBindExecuteGetStatus($mysqli, $typedef, ...$params);
				deallocate($update);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} update query returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} write indices array is empty for ".$that->getDebugString());
			}
			$that->setUpdateFlag(false);
			// insert, update or delete foreign data structures that must be dealt with after this object is updated
			if(
				$that->getPostInsertForeignDataStructuresFlag()
				|| $that->getPostUpdateForeignDataStructuresFlag()
				|| $that->getDeleteForeignDataStructuresFlag()
			){
				if($print){
					if($that->getPostInsertForeignDataStructuresFlag()){
						Debug::print("{$f} post insert flag is set");
					}
					if($that->getPostUpdateForeignDataStructuresFlag()){
						Debug::print("{$f} post update flag is set");
					}
					if($that->getDeleteForeignDataStructuresFlag()){
						Debug::print("{$f} delete flag is set");
					}
				}
				$status = $that->postUpdateForeignDataStructures($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} postUpdateForeignDataStructures returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}
			// update embedded and polymorphic columns
			if(!$that->getFlag("inserting")){
				if($print){
					Debug::print("{$f} this object is NOT in the middle of getting inserted");
				}
				$status = $that->updateForeignColumns($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} updateForeignColumns returned error status \"{$err}\"");
					return $that->setObjectStatus();
				}
			}elseif($print){
				Debug::print("{$f} this object is in the middle of getting inserted; will allow insertIntersectionData to handle polymorphic foreign keys");
			}
			// update cache but don't touch the TTL
			if(CACHE_ENABLED && $that->isRegistrable()){
				$key = $that->getIdentifierValue();
				if(cache()->hasAPCu($key)){
					$hit = cache()->getAPCu($key);
					foreach($cache_columns as $column_name => $column){
						$hit[$column_name] = $column->getDatabaseEncodedValue();
					}
					cache()->setAPCu($key, $hit);
				}elseif($print){
					Debug::print("{$f} cache miss");
				}
			}elseif($print){
				Debug::print("{$f} non-registrable");
			}
			// post-update hook
			$status = $that->afterUpdateHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after update hook returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}elseif($transactionId !== null){
				db()->commitTransaction($mysqli, $transactionId);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * Select all objects of class $class from the database that satisfy $select for parameters $params
	 *
	 * @param mysqli $mysqli
	 * @param string $class
	 * @param SelectStatement $select
	 * @param string $typedef
	 * @param array $params
	 * @return DataStructure[]
	 */
	public function loadMultiple(mysqli $mysqli, string $class, SelectStatement $select, string $typedef = null, ...$params): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			if($typedef !== null){
				$select->setTypeSpecifier($typedef);
			}
			if(isset($params)){
				$select->setParameters($params);
			}
			$result = $select->executeGetResult($mysqli);
			if($result === null){
				if($print){
					Debug::print("{$f} executeGetResult returned null -- there are no objects to load");
				}
				return null;
			}elseif(!is_object($result)){
				$gottype = gettype($result);
				Debug::error("{$f} executeQueryGetResult returned {$gottype}");
			}elseif($result->num_rows === 0){
				return [];
			}
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$arr = [];
			foreach($results as $result){
				$obj = new $class();
				$status = $obj->processQueryResultArray($mysqli, $result);
				$id = $obj->getIdentifierValue();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\" for object with ID \"{$id}\"");
					return [];
				}
				$arr[$id] = $obj;
			}
			$status = LazyLoadHelper::loadIntersectionTableKeys($mysqli, $arr);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} LazyLoadHelper::loadIntersectionTableKeys returned error status \"{$err}\"");
			}
			return $arr;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * load DataStructure $that from the database to satisfy WhereCondition $where for parameters $params.
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $that
	 * @param WhereCondition|WhereCondition[] $where
	 * @param mixed|mixed[] $params
	 * @param OrderByClause $order_by
	 * @param int $limit : optional limit parameter
	 */
	public final function load(mysqli $mysqli, DataStructure $that, $where, $params, $order_by = null, $limit = null): int{
		$f = __METHOD__;
		try{
			$print = false && $that->getDebugFlag();
			if($print){
				Debug::print("{$f} data structure class is ".$that->getShortClass());
			}
			$typedef = "";
			if(!is_array($params)){
				$params = [
					$params
				];
			}
			if(is_string($where)){
				if($that->hasColumn($where) && $that->getColumn($where)->getPersistenceMode() === PERSISTENCE_MODE_ALIAS){
					Debug::error("{$f} where condition is an aliased column name");
					// $select = $that->getAliasedColumnSelectStatement($where, $params[0]);
				}else{
					if($print){
						Debug::print("{$f} where condition is the string \"{$where}\"");
					}
					$select = $that->select()->where(new WhereCondition($where));
				}
			}elseif(
				$where instanceof WhereCondition ||
				$where instanceof AndCommand ||
				$where instanceof OrCommand
				
				){
					if($print){
						Debug::print("{$f} where condition is not a string");
					}
					$select = $that->select()->where($where);
			}else{
				$gottype = is_object($where) ? $where->getShortClass() : gettype($where);
				if(is_object($where)){
					$decl = $where->getDeclarationLine();
					Debug::error("{$f} where condition is a {$gottype}, declared {$decl}");
				}else{
					Debug::error("{$f} where condition is a {$gottype}");
				}
			}
			// generate parameter list
			if(!empty($params)){
				if(!$select->hasTypeSpecifier()){
					$conditions = $select->getSuperflatWhereConditionArray();
					foreach($conditions as $i => $condition){
						if($condition->hasUnbindableOperator()){
							if($print){
								Debug::print("{$f} parameter at column {$i} is null");
							}
							continue;
						}
						$column_name = $condition->getColumnName();
						if($print){
							Debug::print("{$f} about to get type specifier for column \"{$column_name}\"");
						}
						if($that->hasColumn($column_name)){
							$typedef .= $that->getColumn($column_name)->getTypeSpecifier();
						}else{ // condition is accomplice to an aliased column
							$ts = $condition->getTypeSpecifier();
							if(empty($ts)){
								Debug::error("{$f} type specifier is empty string");
							}
							$typedef .= $condition->getTypeSpecifier();
						}
					}
					if(!empty($params) || !empty($typedef)){
						$length = strlen($typedef);
						$count = isset($params) ? count($params) : 0;
						if($length !== $count){
							$where = $select->getWhereCondition();
							if($where->hasSelectStatement()){
								$where->setParameterCount($count);
							}
							Debug::error("{$f} type specifier \"{$typedef}\" length {$length} does not match parameter count {$count} in query statement \"{$select}\"");
						}
						$select->setParameters($params);
						$select->setTypeSpecifier($typedef);
					}
				}elseif(!$select->hasParameters()){
					$select->setParameters($params);
				}
			}
			// order by expression
			if(isset($order_by)){
				$select->setOrderBy($order_by);
			}elseif($print){
				Debug::print("{$f} order by expressions is undefined");
			}
			// limit
			if(isset($limit)){
				if($limit !== 1){
					Debug::error("{$f} you can only specify a limit of 1");
				}
				$select->setLimit(1);
			}
			// prepare, bind parameters, execute, fetch results
			if($print){
				Debug::print("{$f} about to execute query \"{$select}\"");
			}
			$result = $select->executeGetResult($mysqli);
			if($print){
				$ss = $select->toSQL();
				$sds = $select->getDebugString();
			}
			deallocate($select);
			if($result == null){
				if($print){
					Debug::error("{$f} result is null");
				}
				return $that->loadFailureHook();
			}elseif(!is_object($result)){
				$gottype = gettype($result);
				Debug::error("{$f} executeGetResult returned a {$gottype}");
			}
			$count = $result->num_rows;
			if($count === 0){
				if($print){
					Debug::print("{$f} object not found");
				}
				return $that->loadFailureHook();
			}elseif($count > 1){
				Debug::error("{$f} multiple results for query \"{$ss}\" from {$sds}! This function is for loading a single uniquely identifiable object.");
				return $that->setObjectStatus(ERROR_DUPLICATE_ENTRY);
			}
			$results = $result->fetch_all(MYSQLI_ASSOC);
			// processed fetched results
			$status = $that->processQueryResultArray($mysqli, $results[0]);
			// cache
			if(CACHE_ENABLED && $that->hasTimeToLive()){
				$key = $that->getIdentifierValue();
				cache()->setAPCu($key, $results[0], $that->getTimeToLive());
			}elseif($print){
				Debug::print("{$f} cache is disabled");
			}
			// load foreign keys stored in intersection tables
			if($status === SUCCESS){
				if(!$that instanceof IntersectionData){
					if($print){
						Debug::print("{$f} this is not an intersection data -- about to load intersection table keys");
					}
					$status = $that->loadIntersectionTableKeys($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadIntersectionTableKeys returned error status \"{$err}\"");
						return $that->setObjectStatus($status);
					}
				}elseif($print){
					Debug::print("{$f} skipping intersection table key load for intersection data");
				}
			}else{
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
			}
			// register object to application global object registry
			if($that->isRegistrable()){
				$key = $that->getIdentifierValue();
				if(! registry()->hasObjectRegisteredToKey($key)){
					registry()->registerObjectToKey($key, $that);
				}elseif($print){
					Debug::print("{$f} an object is already mapped to key \"{$key}\"");
				}
			}elseif($print){
				Debug::print("{$f} this object is not registrable");
			}
			return $that->setObjectStatus($status);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * delete this object from the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function delete(mysqli $mysqli, DataStructure $that): int{
		$f = __METHOD__;
		try{
			$print = false;
			// for superglobals
			$storage = $that->getDefaultPersistenceMode();
			if($storage !== PERSISTENCE_MODE_DATABASE){
				Debug::error("{$f} to delete superglobal data use unsetColumnValues");
			}
			// check permissions
			$status = $that->permit(user(), DIRECTIVE_DELETE);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} delete permission returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}
			$that->logDatabaseOperation(DIRECTIVE_DELETE);
			// start a database transaction if one hasn't already
			if(! db()->hasPendingTransactionId()){
				$transactionId = sha1(random_bytes(32));
				db()->beginTransaction($mysqli, $transactionId);
			}
			// pre-deletion hook. Includes a special check for rejecting the deletion because a shared foreign data structure is in use by multiple objects.
			$status = $that->beforeDeleteHook($mysqli);
			switch($status){
				case RESULT_DELETE_FAILED_IN_USE:
					if($print){
						Debug::print("{$f} object is still in use -- forgoing deletion");
					}
					return SUCCESS;
				case SUCCESS:
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} before delete hook returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
			}
			// delete foreign data structures that were explicitly flagged, if applicable
			if($that->getDeleteForeignDataStructuresFlag()){
				$status = $that->deleteForeignDataStructures($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} deleteForeignDataStructures returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
				Debug::print("{$f} successfully deleted subordinate data structures");
			}
			// prepare bind execute delete query
			$delete = $that->getDeleteStatement();
			if(empty($delete)){
				Debug::error("{$f} deletion query is undefined");
			}
			$typedef = $that->getColumn($that->getIdentifierName())->getTypeSpecifier();
			$id = $that->getIdentifierValue();
			if($id == null){
				Debug::error("{$f} unique identifier is null");
			}
			$st = $delete->prepareBindExecuteGetStatement($mysqli, $typedef, $id);
			deallocate($delete);
			if($st == null){
				$status = $that->getObjectStatus();
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} prepared query statement returned error status \"{$err}\"");
				return $status;
			}elseif($print){
				Debug::print("{$f} deletion successful");
			}
			// post-deletion hook
			$status = $that->afterDeleteHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after delete hook returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}elseif(isset($transactionId)){
				db()->commitTransaction($mysqli, $transactionId);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * insert DataStructure $that into the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public /*final*/ function insert(mysqli $mysqli, DataStructure $that): int{
		$f = __METHOD__;
		try{
			$print = false && $that->getDebugFlag();
			if(!$that->getAllocatedFlag()){
				Debug::error("{$f} allocated flag is not set for this ".$that->getDebugString());
			}elseif($that->getFlag("inserting")){
				Debug::error("{$f} this object is already being inserted");
			}elseif(!isset($mysqli)){
				Debug::warning("{$f} mysqli object is undefined");
				return $that->setObjectStatus(ERROR_MYSQL_CONNECT);
			}elseif($print){
				$class = $that->getClass();
				$did = $that->getDebugId();
				Debug::print("{$f} inserting {$class} with debug ID \"{$did}\"");
			}
			$that->setFlag("inserting", true);
			// validate insert permission
			$status = $that->permit(user(), DIRECTIVE_INSERT);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} permission returned error status \"{$err}\"");
				Debug::printStackTraceNoExit();
				return $that->setObjectStatus($status);
			}
			// start database transaction
			if(!db()->hasPendingTransactionId()){
				$transactionId = sha1(random_bytes(32));
				db()->beginTransaction($mysqli, $transactionId);
			}
			// pre-insertion hook
			$status = $that->beforeInsertHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} before insert hook returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}
			// generate initial columns values
			$status = $that->generateInitialValues();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} generateInitialValues returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}
			// ensure this item has not already been inserted
			if(!$that->getOnDuplicateKeyUpdateFlag()){
				$status = $that->preventDuplicateEntry($mysqli);
				switch($status){
					case SUCCESS:
						if($print){
							Debug::print("{$f} This is not a duplicate entry");
						}
						break;
					case ERROR_DUPLICATE_ENTRY:
						if($print){
							Debug::warning("{$f} duplicate entry detected");
						}
						$recourse = $that->getDuplicateEntryRecourse();
						switch($recourse){
							case RECOURSE_ABORT:
								if($print){
									Debug::print("{$f} abort");
								}
								return $that->setObjectStatus($status);
							case RECOURSE_CONTINUE:
								if($print){
									Debug::print("{$f} continue");
								}
								return SUCCESS;
							case RECOURSE_EXIT:
								Debug::error("{$f} exit");
								exit();
							case RECOURSE_IGNORE:
								if($print){
									Debug::print("{$f} ignore");
								}
								break 2;
							case RECOURSE_RETRY:
								if($print){
									Debug::print("{$f} retry");
								}
							default:
								Debug::error("{$f} undefined recourse");
						}
					default:
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} checking for duplicate entries returned error status \"{$err}\"");
						return $that->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} on duplicate entry update");
			}
			$that->logDatabaseOperation(DIRECTIVE_INSERT);
			// insert foreign data structures that must exist prior to this object
			if($that->getPreInsertForeignDataStructuresFlag()){
				if($print){
					Debug::print("{$f} insert before foreign data structures flag is set");
				}
				$status = $that->insertForeignDataStructures($mysqli, CONST_BEFORE);
				$that->setPreInsertForeignDataStructuresFlag(false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} insertForeignDataStructures returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} preinsert foreign data structures flag is not set");
			}
			// generate param signature and execute prepared insertion query
			$insert = $that->getInsertStatement();
			/*$expressions = [];
			foreach($that->getFilteredColumnNames(COLUMN_FILTER_INSERT) as $column_name){
				$expressions[]= new AssignmentExpression($column_name);
			}
			$insert = new InsertStatement();
			$insert->into($that->getDatabaseName(), $that->getTableName())->set($expressions);
			if($that->getOnDuplicateKeyUpdateFlag()){
				$expressions = [];
				foreach($that->getFilteredColumnNames(COLUMN_FILTER_INSERT, "!".COLUMN_FILTER_ID) as $column_name){
					$expressions[]= new AssignmentExpression($column_name);
				}
				$insert->setDuplicateColumnExpressions($expressions);
			}*/
			$idn = $that->getIdentifierName();
			$typedef = "";
			$duplicate = "";
			$params = [];
			$params2 = [];
			$columns = $that->getFilteredColumns(DIRECTIVE_INSERT);
			foreach($columns as $column_name => $column){
				if($print){
					Debug::print("{$f} inserting column \"{$column_name}\"");
				}
				$typedef .= $column->getTypeSpecifier();
				array_push($params, $column->getDatabaseEncodedValue());
				if($that->getOnDuplicateKeyUpdateFlag() && $column_name !== $idn){
					$duplicate .= $column->getTypeSpecifier();
					array_push($params2, $column->getDatabaseEncodedValue());
				}
			}
			if($that->getOnDuplicateKeyUpdateFlag()){
				$typedef .= $duplicate;
				$params = array_merge($params, $params2);
			}
			$length = strlen($typedef);
			$count = count($params);
			if($length === 0){
				Debug::error("{$f} type specifier is empty string");
			}elseif($count === 0){
				Debug::error("{$f} insert parameter count is 0");
			}elseif($length !== $count){
				Debug::warning("{$f} type definition string \"{$typedef}\" does not match parameter count {$count} for query statement \"{$insert}\" with the following parameters:");
				Debug::printArray($params);
				Debug::printStackTrace();
			}elseif($print){
				Debug::print("{$f} about to prepare insertion query \"{$insert}\" with type definition string \"{$typedef}\" and parameter the following {$count} parameters");
				Debug::printArray($params);
			}
			if(!$that->getBlockInsertionFlag()){
				$status = $insert->prepareBindExecuteGetStatus($mysqli, $typedef, ...$params);
			}else{
				if($print){
					Debug::print("{$f} block insertion flag is set");
				}
				$status = SUCCESS;
			}
			deallocate($insert);
			$that->setInsertedFlag(true);
			if($that->getInsertFlag()){
				$that->setInsertFlag(false);
			}
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} failed to execute prepared insert query \"{$insert}\": \"{$err}\"");
				return $that->setObjectStatus(ERROR_MYSQL_EXECUTE);
			}elseif($print){
				Debug::print("{$f} successfully executed prepared insertion query statement \"{$insert}\"");
			}
			// insert embedded data
			$embeds = $that->getEmbeddedDataStructures();
			if(!empty($embeds)){
				foreach($embeds as $groupname => $embed){
					if($print){
						Debug::print("{$f} about to insert embedded data structure \"{$groupname}\"");
					}
					if(!$that->getBlockInsertionFlag()){
						$status = $embed->insert($mysqli);
						
					}else{
						if($print){
							Debug::print("{$f} block insertion flag is set");
						}
						$status = SUCCESS;
					}
					deallocate($embed);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} inserting embedded data structure \"{$groupname}\" returned error status \"{$err}\"");
						return $that->setObjectStatus($status);
					}
				}
				if($print){
					Debug::print("{$f} successfully inserted embedded data structure \"{$groupname}\"");
				}
				unset($embeds);
			}elseif($print){
				Debug::print("{$f} there are no embedded data structures to insert");
			}
			// insert foreign data structures with foreign key constraints referring to this object
			if($that->getPostInsertForeignDataStructuresFlag()){
				if($print){
					Debug::print("{$f} insert foreign data structures flag is set");
				}
				$status = $that->insertForeignDataStructures($mysqli, CONST_AFTER);
				$that->setPostInsertForeignDataStructuresFlag(false);
				if($that->getPostInsertForeignDataStructuresFlag()){
					Debug::error("{$f} postinsertforeign flag is not getting clleared properly");
				}
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} insertForeignDataStructures returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} post insert foreign data structures flag is not set");
			}
			$that->setObjectStatus(SUCCESS);
			// insert polymorphic foreign key intersection data. This has to happen after dealing with foreign data structures because intersection tables have foreign key constraints
			$status = $that->insertIntersectionData($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} insertIntersectionData returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully inserted IntersectionData");
			}
			// post-insertion hook
			$status = $that->afterInsertHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} after insert hook returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}elseif(isset($transactionId)){
				db()->commitTransaction($mysqli, $transactionId);
			}
			return $that->setObjectStatus($status);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * returns SUCCESS if the database does not already contain a row determined to be a duplicate of this object,
	 * ERROR_DUPLICATE_ENTRY otherwise.
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function preventDuplicateEntry(mysqli $mysqli, DataStructure $that): int{
		$f = __METHOD__;
		try{
			$print = false;
			$or = new OrCommand();
			$varnames = [];
			$typedef = "";
			$params = [];
			foreach($that->getFilteredColumns(COLUMN_FILTER_DATABASE, COLUMN_FILTER_UNIQUE, COLUMN_FILTER_VALUED) as $vn => $column){
				$or->pushParameters(new WhereCondition($vn, OPERATOR_EQUALS));
				array_push($varnames, $vn);
				array_push($params, $column->getValue());
				$typedef .= $column->getTypeSpecifier();
			}
			$composites = $that->getCompositeUniqueColumnNames();
			if(!empty($composites)){
				foreach($composites as $group){
					if(!is_array($group)){
						Debug::error("{$f} getCompositeUniqueColumnNames must return a multidimensional array");
					}
					$and = new AndCommand();
					foreach($group as $column_name){
						$column = $that->getColumn($column_name);
						if($column->getUniqueFlag()){
							Debug::error("{$f} datum at column \"{$column_name}\" cannot be singularly and composite unique");
						}elseif($column instanceof ForeignKeyDatum && $column->getPersistenceMode() === PERSISTENCE_MODE_INTERSECTION){
							if($print){
								Debug::print("{$f} column \"{$column_name}\" is composite unique, foreign and stored in an intersection table");
							}
							if(!$column->hasValue()){
								if($print){
									Debug::print("{$f} column \"{$column_name}\" has no value");
								}
								if($column->hasForeignDataTypeName()){
									$typename = $column->getForeignDataTypeName();
								}elseif($column->hasForeignDataSubtypeName()){
									$typename = $column->getForeignDataSubtypeName();
								}
								$and->pushParameters(new WhereCondition($typename, OPERATOR_IS_NULL));
								array_push($varnames, $typename);
								continue;
							}
							if($print){
								Debug::print("{$f} column {$column_name} is stored in an intersection table");
							}
							// generate a WhereCondition selecting rows from the intersection table
							$where2 = $that->whereIntersectionalHostKey($column->getForeignDataStructureClass(), $column_name);
							$typedef .= $column->getTypeSpecifier() . "s";
							array_push($params, $column->getValue(), $column_name);
						}else{
							array_push($varnames, $column_name);
							array_push($params, $column->getValue());
							$typedef .= $column->getTypeSpecifier();
							$where2 = new WhereCondition($column_name, OPERATOR_EQUALS);
						}
						$and->pushParameters($where2);
					}
					$or->pushParameters($and);
				}
			}
			if(!$or->hasParameters()){
				if($print){
					Debug::print("{$f} no unique variables");
				}
				deallocate($or);
				return SUCCESS;
			}
			$db = $that->getDatabaseName();
			$table = $that->getTableName();
			$select = new SelectStatement(...$varnames);
			$select->from($db, $table)->where($or);
			if($print){
				Debug::print("{$f} query for checking duplicate entries is \"{$select}\"");
			}
			$count = $select->prepareBindExecuteGetResultCount($mysqli, $typedef, ...$params);
			if($count === 0){
				if($print){
					Debug::print("{$f} no duplicates, you're good");
				}
				deallocate($select);
				return SUCCESS;
			}elseif($print){
				if($that->hasColumn("name")){
					$name = $that->getName();
				}else{
					$name = "unnamed";
				}
				$key = $that->getIdentifierValue();
				Debug::warning("{$f} object \"{$name}\" with unique identifier \"{$key}\" already exists in table \"{$db}{$table}\"; query statement is \"{$select}\"; parameters are as follows");
				Debug::printArray($params);
			}
			deallocate($select);
			return ERROR_DUPLICATE_ENTRY;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * return an object of this class that satisifes the conedition $varname=$value
	 *
	 * @param mysqli $mysqli
	 * @param string $varname
	 * @param mixed $value
	 * @return DataStructure|NULL
	 */
	public function getObjectFromVariable(mysqli $mysqli, string $class, string $varname, $value, ?int $mode = null): ?DataStructure{
		$f = __METHOD__;
		$print = false;
		if(!class_exists($class)){
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif(is_abstract($class)){
			Debug::error("{$f} don't call this on abstract classes");
		}elseif($print){
			Debug::printStackTraceNoExit("{$f} entered; allocation mode is {$mode}");
		}
		$obj = new $class($mode);
		$status = $obj->load($mysqli, $varname, $value);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} loading object with {$varname} \"{$value}\" returned error status \"{$err}\"");
			return null;
		}
		return $obj;
	}
	
	public function reload(mysqli $mysqli, DataStructure $that, bool $foreign=true):int{
		$f = __METHOD__;
		try{
			$print = false;
			$idn = $that->getIdentifierName();
			$iv = $that->getIdentifierValue();
			$results = $that->select()->where($idn)->prepareBindExecuteGetResult($mysqli, $that->getTypeSpecifier($idn), $iv)->fetch_all(MYSQLI_ASSOC);
			if(!array_key_exists(0, $results)){
				if($print){
					Debug::warning("{$f} failed to reload object: not found");
				}
				return $that->loadFailureHook();
			}elseif($print){
				Debug::print("{$f} successfully reloaded object with key \"{$iv}\"");
			}
			$status = $that->processQueryResultArray($mysqli, $results[0]);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}
			$that->setReloadedFlag(true);
			if($foreign){
				$status = $that->reloadForeignDataStructures($mysqli, $foreign);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} reloadForeignDataStructures returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} skipping foreign data structure reload");
			}
			$that->setReloadedFlag(false);
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
